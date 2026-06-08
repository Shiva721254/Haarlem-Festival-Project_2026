<?php

namespace App\Framework;

use Closure;

/**
 * Tiny inversion-of-control container.
 *
 * Services register a factory once (typically in bootstrap) and resolve by
 * key. Resolved instances are cached so the same object is shared across a
 * request, which lets controllers depend on abstractions instead of newing
 * up their own collaborators.
 */
class Container
{
    /** @var array<string, Closure> */
    private array $factories = [];

    /** @var array<string, object> */
    private array $instances = [];

    /**
     * Register a factory for an id (usually an interface name).
     */
    public function bind(string $id, Closure $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * Resolve a shared instance for the given id.
     */
    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new \RuntimeException("No binding registered for '{$id}'.");
        }

        return $this->instances[$id] = ($this->factories[$id])($this);
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]) || isset($this->instances[$id]);
    }
}
