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

    private static ?Container $instance = null;

    /**
     * Expose the application's configured container so the shared view layer
     * (header/footer partials) can resolve services through it instead of
     * newing concrete classes. Set once in bootstrap.
     */
    public static function setInstance(Container $container): void
    {
        self::$instance = $container;
    }

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Container has not been initialised.');
        }
        return self::$instance;
    }

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

    /**
     * Autowire a class by resolving its constructor dependencies from the
     * container. Bound ids (typically interfaces) are resolved via their
     * factory; unbound concrete classes are instantiated recursively. This is
     * what lets the router build a controller without knowing its collaborators.
     */
    public function make(string $class): object
    {
        if ($this->has($class)) {
            return $this->get($class);
        }
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $class();
        }
        return $reflection->newInstanceArgs($this->resolveParameters($constructor, $class));
    }

    private function resolveParameters(\ReflectionMethod $constructor, string $class): array
    {
        return array_map(
            fn(\ReflectionParameter $param) => $this->resolveParameter($param, $class),
            $constructor->getParameters()
        );
    }

    private function resolveParameter(\ReflectionParameter $param, string $class): mixed
    {
        $type = $param->getType();
        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            return $this->make($type->getName());
        }
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }
        throw new \RuntimeException("Cannot autowire parameter \${$param->getName()} of {$class}.");
    }
}
