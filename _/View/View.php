<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\View;

use Exception;
use Override;
use RuntimeException;
use verfriemelt\wrapped\_\DataModel\DataModel;
use verfriemelt\wrapped\_\DI\ContainerInterface;
use verfriemelt\wrapped\_\Output\Viewable;
use verfriemelt\wrapped\_\Template\Template;

abstract class View implements Viewable
{
    public string $tplPath;

    public Template $tpl;

    protected static ContainerInterface $container;

    abstract public function getTemplatePath(): string;

    public function __construct()
    {
        $this->tpl = $this->getTemplateInstance();
    }

    protected function getTemplateInstance(): Template
    {
        if (empty($this->tplPath)) {
            throw new Exception('unset Template Path in view ' . static::class);
        }

        $template = static::$container->get(Template::class);
        assert($template instanceof Template);
        return $template->parse(\file_get_contents($this->getTemplatePath() . $this->tplPath) ?: throw new RuntimeException());
    }

    public static function create(...$params): static
    {
        if (count($params) === 0) {
            return static::$container->get(static::class);
        }

        /* @phpstan-ignore-next-line */
        return new static(...$params);
    }

    public static function make(...$params): string
    {
        return static::create(...$params)->getContents();
    }

    public static function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }

    public function writeDataModelProperties($prefix, DataModel $object, $context = null)
    {
        $properties = $object::createDataModelAnalyser()->fetchProperties();
        $context ??= $this->tpl;

        foreach ($properties as $prop) {
            $value = $object->{$prop->getGetter()}();

            if (!$value instanceof Viewable && !\is_scalar($value)) {
                continue;
            }

            if (\is_scalar($value)) {
                $value = (string) $value;
            }

            $context->set($prefix . \ucfirst($prop->getName()), $value);
        }
    }

    /**
     * this functions prepares the template, so that we can move the heavy
     * lifting out of the constructor
     */
    abstract protected function prepare(): void;

    #[Override]
    public function getContents(): string
    {
        $this->prepare();
        return $this->tpl->render();
    }
}
