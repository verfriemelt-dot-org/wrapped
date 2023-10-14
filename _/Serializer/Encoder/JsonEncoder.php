<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Serializer\Encoder;

use verfriemelt\wrapped\_\DI\ArgumentMetadataFactory;
use RuntimeException;

class JsonEncoder implements EncoderInterface
{
    public function __construct() {}

    public function deserialize(string $input, string $class): object
    {
        if (!\class_exists($class)) {
            throw new RuntimeException("class {$class} cannot be found");
        }

        $decodedInput = \json_decode($input, true, flags: \JSON_THROW_ON_ERROR);
        return $this->mapInputOnClass($decodedInput, $class);
    }

    public function serialze(object $input): string
    {
        return \json_encode($input, \JSON_THROW_ON_ERROR);
    }

    /**
     * @template T of object
     *
     * @param array<string,mixed> $input
     * @param class-string<T>     $class
     *
     * @return T
     */
    public function mapInputOnClass(array $input, string $class): object
    {
        $constructorProperties = (new ArgumentMetadataFactory())->createArgumentMetadata($class);
        $arguments = [];

        foreach ($constructorProperties as $argument) {
            if (!isset($input[$argument->getName()]) && !$argument->hasDefaultValue()) {
                throw new RuntimeException("cannot map input on {$class}");
            }

            // if we found defaults, we are done
            if (!isset($input[$argument->getName()]) && $argument->hasDefaultValue()) {
                break;
            }

            if (count($argument->getTypes()) !== 1) {
                throw new RuntimeException('no support for untyped, union or intersection types');
            }

            // handling of non scalar, non composite types
            if (is_array($input[$argument->getName()]) && $argument->getTypes()[0] !== 'array') {
                $argumentType = $argument->getTypes()[0];
                assert(\class_exists($argumentType));

                $arguments[] = $this->mapInputOnClass($input[$argument->getName()], $argumentType);
                continue;
            }

            $arguments[] = $input[$argument->getName()];
        }

        return new $class(...$arguments);
    }
}
