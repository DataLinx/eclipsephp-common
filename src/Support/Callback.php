<?php

namespace Eclipse\Common\Support;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

/**
 * Represents a callable entity that can encapsulate a class method, an invokable class, or a function.
 *
 * This class provides a serializable wrapper around callables, allowing them to be
 * stored in configuration files or serialized for later use.
 *
 * Example usage:
 * ```
 * // Create from a static class method
 * $callback = new Callback(ConfigLocaleCallback::class, 'getLocales');
 * $result = $callback(); // Invoke the callback
 *
 * // Create from an invokable class
 * $callback = new Callback(InvokableLocaleCallback::class);
 * $result = $callback(); // Invoke the callback
 *
 * // Create from a function
 * $callback = new Callback('array_filter');
 * $result = $callback($array, fn($item) => $item > 0);
 *
 * // Convert to a native callable
 * $callable = $callback->toCallable();
 *
 * // Use in configuration
 * 'available_locales' => new Callback(MyClass::class, 'getLocales'),
 * ```
 */
class Callback
{
    private ?string $class_name = null;

    private ?string $method_name = null;

    private ?string $function_name = null;

    public function __construct(string $callable_or_class, ?string $method_name = null)
    {
        if ($method_name !== null) {
            if (! class_exists($callable_or_class) || ! method_exists($callable_or_class, $method_name)) {
                throw new InvalidArgumentException('Specified class name or method name does not exist');
            }

            $this->class_name = $callable_or_class;
            $this->method_name = $method_name;
        } elseif (class_exists($callable_or_class)) {
            if (! method_exists($callable_or_class, '__invoke')) {
                throw new InvalidArgumentException('Specified class is not invokable');
            }

            $this->class_name = $callable_or_class;
        } elseif (is_callable($callable_or_class)) {
            $this->function_name = $callable_or_class;
        } else {
            throw new InvalidArgumentException('Specified function name is not callable');
        }
    }

    public function __invoke(...$arguments): mixed
    {
        if (isset($this->class_name)) {
            if (isset($this->method_name)) {
                return call_user_func_array([$this->class_name, $this->method_name], $arguments);
            }

            return (new $this->class_name)(...$arguments);
        }

        if (isset($this->function_name)) {
            return call_user_func_array($this->function_name, $arguments);
        }

        throw new RuntimeException('Callback is not properly initialized');
    }

    public function __serialize(): array
    {
        return [
            'class_name' => $this->class_name,
            'method_name' => $this->method_name,
            'function_name' => $this->function_name,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->class_name = $data['class_name'] ?? null;
        $this->method_name = $data['method_name'] ?? null;
        $this->function_name = $data['function_name'] ?? null;
    }

    public static function __set_state(array $properties): self
    {
        $callback = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();

        $callback->class_name = $properties['class_name'] ?? null;
        $callback->method_name = $properties['method_name'] ?? null;
        $callback->function_name = $properties['function_name'] ?? null;

        return $callback;
    }

    public function toCallable(): callable
    {
        if (isset($this->class_name)) {
            if (isset($this->method_name)) {
                return [$this->class_name, $this->method_name];
            }

            return new $this->class_name;
        }

        if (isset($this->function_name)) {
            return $this->function_name;
        }

        throw new RuntimeException('Callback is not properly initialized');
    }
}
