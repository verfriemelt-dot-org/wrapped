<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DI;

    use \Closure;
    use \ReflectionClass;
    use \ReflectionFunction;
    use \ReflectionFunctionAbstract;
    use \ReflectionMethod;
    use \ReflectionNamedType;
    use \ReflectionParameter;

    class ArgumentMetadataFactory {

        public function createArgumentMetadata( object | string $obj, string $method = null ): array {

            $reflection = null;
            $arguments  = [];

            if ( $obj instanceof Closure ) {
                $reflection = new ReflectionFunction( $obj );
            } elseif ( ( is_object( $obj ) || class_exists( $obj ) ) ) {

                $constructor = (new ReflectionClass( $obj ) )->getConstructor();

                // no constructor defined
                if ( $method === null && $constructor === null ) {
                    return [];
                }

                $reflection = new ReflectionMethod( $obj, $method ?? $constructor->getName() );
            }

            foreach ( $reflection->getParameters() as $param ) {

                $arguments[] = new ArgumentMetadata(
                    $param->getName(),
                    $this->getType( $param, $reflection ),
                    $param->isDefaultValueAvailable(),
                    $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
                );
            }

            return $arguments;
        }

        /**
         *
         * @param ReflectionParameter $parameter
         * @param ReflectionFunctionAbstract $function
         * @return class-string|null
         */
        private function getType( ReflectionParameter $parameter, ReflectionFunctionAbstract $function ): ?string {

            if ( !$type = $parameter->getType() ) {
                return null;
            }

            /** @var class-string $name */
            $name = $type instanceof ReflectionNamedType ? $type->getName() : (string) $type;
            return $name;
        }

    }
