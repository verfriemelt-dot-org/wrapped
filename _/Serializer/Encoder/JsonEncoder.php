<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Serializer\Encoder;

use RuntimeException;
use verfriemelt\wrapped\_\DI\ArgumentMetadataFactory;

class JsonEncoder implements EncoderInterface
{
    public function __construct() {}

    public function deserialize(string $input, string $class): object
    {
        if (!\class_exists($class)) {
            throw new RuntimeException("class {$class} cannot be found");
        }

        $decodedInput = \json_decode($input, true, flags: \JSON_THROW_ON_ERROR);
        return $this->mapJsonOnObject($decodedInput, $class);
    }

    public function serialize(object $input, bool $pretty = false): string
    {
        return \json_encode($input, \JSON_THROW_ON_ERROR | ($pretty ? \JSON_PRETTY_PRINT : 0));
    }

    /**
     * @template T of object
     *
     * @param array<string,mixed> $input
     * @param class-string<T>     $class
     *
     * @return T
     */
    public function mapJsonOnObject(array $input, string $class): object
    {
        $constructorProperties = (new ArgumentMetadataFactory())->createArgumentMetadata($class);
        $arguments = [];

        foreach ($constructorProperties as $argument) {
            if ($argument->isVariadic() && \array_is_list($input)) {
                $argumentType = $argument->getTypes()[0];
                \assert(\class_exists($argumentType));

                // variadics are always the last argument, so we can stop here
                $variadic = \array_map(fn (array $input) => $this->mapJsonOnObject($input, $argumentType), $input);

                return new $class(...$arguments, ...$variadic);
            }

            if (!\array_key_exists($argument->getName(), $input) && !$argument->hasDefaultValue()) {
                throw new RuntimeException("cannot map {$argument->getName()} on {$class}");
            }

            // if we found defaults, we are done
            if (!\array_key_exists($argument->getName(), $input) && $argument->hasDefaultValue()) {
                break;
            }

            if (count($argument->getTypes()) !== 1) {
                throw new RuntimeException('no support for untyped, union or intersection types');
            }

            // handling of non scalar, non composite types
            if (\is_array($input[$argument->getName()]) && $argument->getTypes()[0] !== 'array') {
                $argumentType = $argument->getTypes()[0];
                \assert(\class_exists($argumentType));

                $arguments[] = $this->mapJsonOnObject($input[$argument->getName()], $argumentType);
                continue;
            }

            $arguments[] = $input[$argument->getName()];
        }

        return new $class(...$arguments);
    }
}
