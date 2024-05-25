<?php

declare(strict_types=1);

namespace UMA\DIC;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class Container implements ContainerInterface
{
    private array $container;

    /**
     * @param array $entries array of string => mixed
     */
    public function __construct(array $entries = [])
    {
        $this->container = $entries;
    }

    public function get(string $id)
    {
        if (!$this->resolved($id)) {
            $this->container[$id] = \call_user_func($this->container[$id], $this);
        }

        return $this->container[$id];
    }

    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->container);
    }

    public function set(string $id, $entry): void
    {
        $this->container[$id] = $entry;
    }

    public function register(ServiceProvider $provider): void
    {
        $provider->provide($this);
    }

    /**
     * Returns whether a given service has already been resolved
     * into its final value, or is still a callable.
     *
     * @throws NotFoundExceptionInterface
     */
    public function resolved(string $id): bool
    {
        if (!$this->has($id)) {
            throw new class() extends \LogicException implements NotFoundExceptionInterface {};
        }

        return !$this->container[$id] instanceof \Closure;
    }
}
