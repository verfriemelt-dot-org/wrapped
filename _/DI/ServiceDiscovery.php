<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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
    public function findTaggedServices(
        string $path,
        string $pathPrefix,
        string $namespace,
        string $filterAttribute,
    ): iterable {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
            ),
            '/\.php$/',
        );

        foreach ($iterator as $file) {
            \assert($file instanceof SplFileInfo);

            $class =  $namespace . '\\' . \str_replace('/', '\\', \ltrim($file->getPath(), $pathPrefix)) . '\\' . \basename($file->getFilename(), '.php');

            if (!\class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            foreach ($reflection->getAttributes($filterAttribute) as $attribute) {
                $this->container->tag($filterAttribute, $class);
            }
        }

        return $this->container->tagIterator($filterAttribute);
    }
}
