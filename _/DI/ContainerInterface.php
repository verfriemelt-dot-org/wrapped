<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DI;

interface ContainerInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param T               $instance
     *
     * @return ServiceConfiguration<T>
     */
    public function register(string $class, ?object $instance = null): ServiceConfiguration;

    /**
     * @param class-string $id
     */
    public function has(string $id): bool;

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     *
     * @throws ContainerException
     */
    public function get(string $id): object;

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param T               $instance
     *
     * @return ServiceConfiguration<T>
     */
    public function replaceInterace(string $class, object $instance): ServiceConfiguration;

    public function tag(string $tag, string $class): void;

    /**
     * @return iterable<class-string>
     */
    public function tagIterator(string $tag): iterable;
}
