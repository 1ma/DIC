<?php

declare(strict_types=1);

namespace UMA\DIC;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class Container implements ContainerInterface
{
    private array $container;
    private array $factories;

    /**
     * @param array $entries array of string => mixed
     */
    public function __construct(array $entries = [])
    {
        $this->container = $entries;
        $this->factories = [];
    }

    public function get(string $id): mixed
    {
        if (!$this->resolved($id)) {
            $entry = \call_user_func($this->container[$id], $this);

            if (\array_key_exists($id, $this->factories)) {
                return $entry;
            }

            $this->container[$id] = $entry;
        }

        return $this->container[$id];
    }

    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->container);
    }

    public function set(string $id, mixed $entry): void
    {
        $this->container[$id] = $entry;
    }

    public function factory(string $id, \Closure $factory): void
    {
        $this->factories[$id] = true;
        $this->set($id, $factory);
    }

    public function register(ServiceProvider $provider): void
    {
        $provider->provide($this);
    }

    /**
     * Returns whether a given service has already been resolved
     * into its final value, or is still a callable.
     *
     * Always returns false for factory services, as they never resolve.
     *
     * @throws NotFoundExceptionInterface
     */
    public function resolved(string $id): bool
    {
        if (!$this->has($id)) {
            throw new class extends \LogicException implements NotFoundExceptionInterface {};
        }

        return !$this->container[$id] instanceof \Closure;
    }
}
