<?php

declare(strict_types=1);

namespace UMA\DIC;

use Closure;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function array_key_exists;
use function call_user_func;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $container;

    /**
     * @param array $entries Array of string => mixed.
     */
    public function __construct(array $entries = [])
    {
        $this->container = $entries;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id)
    {
        if (!$this->resolved($id)) {
            $this->container[$id] = call_user_func($this->container[$id], $this);
        }

        return $this->container[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->container);
    }

    /**
     * @param string $id
     * @param mixed  $entry
     */
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
     * @throws NotFoundExceptionInterface No entry was found for **this** identifier.
     */
    public function resolved(string $id): bool
    {
        if (!$this->has($id)) {
            throw new class extends LogicException implements NotFoundExceptionInterface {};
        }

        return !$this->container[$id] instanceof Closure;
    }
}
