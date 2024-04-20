<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Processor;

use Closure;
use verfriemelt\wrapped\_\DI\ContainerInterface;
use verfriemelt\wrapped\_\Template\Expression;
use verfriemelt\wrapped\_\Template\Token\ForToken;
use verfriemelt\wrapped\_\Template\Token\RootToken;
use verfriemelt\wrapped\_\Template\Token\StringToken;
use verfriemelt\wrapped\_\Template\Token\Token;
use verfriemelt\wrapped\_\Template\Token\VariableToken;
use verfriemelt\wrapped\_\Template\VariableFormatter;
use Override;

final readonly class Processor implements TemplateProcessor
{
    public function __construct(private ContainerInterface $container) {}

    #[Override]
    public function process(Token $token, array $data): string
    {
        if ($token instanceof ForToken) {
            return $this->loop($token, $data);
        }

        if ($token instanceof RootToken) {
            $output = '';

            foreach ($token->children() as $child) {
                $output .= $this->process($child, $data);
            }

            return $output;
        }

        return match ($token::class) {
            StringToken::class => $token->content(),
            VariableToken::class => $this->printVariable($token, $data),
            default => ''
        };
    }

    /**
     * @param mixed[] $input
     */
    private function loop(ForToken $token, array $input): string
    {
        [$data, $part] = $this->extract($token->collectionExpression(), $input);

        if (!\is_iterable($data)) {
            throw new TemplateProcessorException("cannot read non-scalar {$part} token {$token->collectionExpression()->expr} ");
        }

        $output = '';

        foreach ($data as $key => $value) {
            foreach ($token->children() as $childToken) {
                $output .= $this->process(
                    $childToken,
                    [
                        ... $input,
                        $token->valueName() => $value,
                    ]
                );
            }
        }

        return $output;
    }

    /**
     * @param mixed[] $input
     */
    private function printVariable(VariableToken $token, array $input): string
    {
        [$data, $part] = $this->extract($token->expression(), $input);

        if ($data instanceof Closure) {
            $data = ($data)();
        }

        if (!\is_scalar($data)) {
            throw new TemplateProcessorException("cannot read non-scalar {$part} token {$token->expression()->expr} ");
        }

        $data = (string) $data;

        if ($token->hasFormatter()) {
            foreach ($this->container->tagIterator(VariableFormatter::class) as $formatterClass) {
                $formatter = $this->container->get($formatterClass);
                \assert($formatter instanceof VariableFormatter);

                if (!$formatter->supports($token->formatter())) {
                    continue;
                }

                $data = $formatter->format($data);
            }
        }

        return !$token->raw() ? \htmlspecialchars($data, \ENT_QUOTES) : $data;
    }

    /**
     * @param mixed[] $data
     *
     * @return array{mixed,string}
     */
    private function extract(Expression $expr, array $data): array
    {
        $parts = explode('.', $expr->expr);
        $partsCount = count($parts);

        assert($partsCount > 0);

        for ($i = 0; $i < $partsCount; ++$i) {
            $part = $parts[$i];

            // read from array
            if (\is_array($data)) {
                $data = $data[$part];
                // read from object
            } elseif (\is_object($data)) {
                if (\str_ends_with($part, '()')) {
                    $methodName = \substr($part, 0, -2);
                    if (!\is_callable([$data, $methodName])) {
                        throw new TemplateProcessorException(
                            "cannot call {$part} on " . $data::class . " for {$expr->expr}"
                        );
                    }

                    $data = $data->{$methodName}();
                } else {
                    if (!property_exists($data, $part) ||  !isset($data->{$part})) {
                        throw new TemplateProcessorException(
                            "cannot read {$part} on " . $data::class . " for {$expr->expr}"
                        );
                    }

                    $data = $data->{$part};
                }
            } else {
                throw new TemplateProcessorException("cannot read {$part} token {$expr->expr} ");
            }
        }

        assert(isset($part));

        return [$data, $part];
    }
}
