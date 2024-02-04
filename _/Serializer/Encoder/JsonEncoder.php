<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Serializer\Encoder;

use BackedEnum;
use RuntimeException;
use verfriemelt\wrapped\_\DI\ArgumentMetadataFactory;
use verfriemelt\wrapped\_\Serializer\Transformer;
use Override;

class JsonEncoder implements EncoderInterface
{
    /** @var Transformer[] */
    private array $transformer = [];

    public function __construct() {}

    #[Override]
    public function deserialize(string $input, string $class): object
    {
        if (!\class_exists($class)) {
            throw new RuntimeException("class {$class} cannot be found");
        }

        $decodedInput = \json_decode($input, true, flags: \JSON_THROW_ON_ERROR);
        return $this->mapJsonOnObject($decodedInput, $class);
    }

    /**
     * @param mixed[]|object $input
     */
    #[Override]
    public function serialize(array|object $input, bool $pretty = false): string
    {
        return \json_encode($input, \JSON_THROW_ON_ERROR | ($pretty ? \JSON_PRETTY_PRINT : 0));
    }

    #[Override]
    public function addTransformer(Transformer $transformer): void
    {
        $this->transformer[] = $transformer;
    }

    /**
     * @template T of object
     *
     * @param array<string,mixed> $input
     * @param class-string<T>     $class
     *
     * @return T
     */
    private function mapJsonOnObject(array $input, string $class): object
    {
        $constructorProperties = (new ArgumentMetadataFactory())->createArgumentMetadata($class);
        $arguments = [];

        foreach ($this->transformer as $transformer) {
            if ($transformer->supports($input)) {
                $input = $transformer->transform($input);
            }
        }

        foreach ($constructorProperties as $argument) {
            if (\count($argument->getTypes()) !== 1) {
                throw new RuntimeException('no support for untyped, union or intersection types');
            }

            $argumentType = $argument->getTypes()[0];

            // handling of variadic arguments
            if ($argument->isVariadic() && \array_is_list($input)) {
                if (\in_array($argumentType, ['string', 'int'], true)) {
                    // no mapping
                    $variadic =  $input;
                } else {
                    // variadics are always the last argument, so we can stop here
                    \assert(\class_exists($argumentType));
                    $variadic = \array_map(fn (array $input) => $this->mapJsonOnObject($input, $argumentType), $input);
                }

                return new $class(...$arguments, ...$variadic);
            }

            if (!\array_key_exists($argument->getName(), $input) && !$argument->hasDefaultValue()) {
                throw new RuntimeException("missing value from input for argument {$class}::{$argument->getName()}");
            }

            // if we have defaults and no input, we stick to default
            if (!\array_key_exists($argument->getName(), $input) && $argument->hasDefaultValue()) {
                continue;
            }

            // check for backed enum
            if (\class_exists($argumentType) && \in_array(BackedEnum::class, \class_implements($argumentType), true)) {
                $arguments[$argument->getName()] = $argumentType::from($input[$argument->getName()]);
                continue;
            }

            // handling of non scalar, non composite types
            if (\is_array($input[$argument->getName()]) && $argument->getTypes()[0] !== 'array') {
                \assert(\class_exists($argumentType));

                $arguments[$argument->getName()] = $this->mapJsonOnObject($input[$argument->getName()], $argumentType);
                continue;
            }

            $arguments[$argument->getName()] = $input[$argument->getName()];
        }

        return new $class(...$arguments);
    }
}
