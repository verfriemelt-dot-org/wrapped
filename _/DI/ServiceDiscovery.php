<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use RegexIterator;
use SplFileInfo;

final readonly class ServiceDiscovery
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * @param class-string $filterAttribute
     *
     * @return class-string[]
     */
    public function findTags(
        string $path,
        string $pathPrefix,
        string $namespace,
        string $filterAttribute,
    ): iterable {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/\.php$/'
        );

        foreach ($iterator as $file) {
            \assert($file instanceof SplFileInfo);

            /** @var class-string $class */
            $class =  $namespace . '\\' . \str_replace('/', '\\', \ltrim($file->getPath(), $pathPrefix)) . '\\' . \basename($file->getFilename(), '.php');
            $reflection = new ReflectionClass($class);
            $attributes = \array_filter($reflection->getAttributes(), fn (ReflectionAttribute $ra): bool => $ra->getName() === $filterAttribute);

            foreach ($attributes as $attribute) {
                $this->container->tag($filterAttribute, $class);
            }
        }

        return $this->container->tagIterator($filterAttribute);
    }
}
