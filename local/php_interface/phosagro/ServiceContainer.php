<?php

declare(strict_types=1);

namespace Phosagro;

use Phosagro\System\Injector;

final class ServiceContainer
{
    /** @var array<int,array{object,\ReflectionMethod}> */
    private array $injectors = [];

    private static ?self $instance = null;

    /** @var array<string,null|mixed> */
    private array $map = [];

    private \stdClass $uninitialized;

    public function __construct()
    {
        $this->uninitialized = new \stdClass();
        $this->map['~'.self::class] = $this;
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function get(string $class): object
    {
        $instance = $this->getInternal($class);

        while ([] !== $this->injectors) {
            [$object, $method] = array_pop($this->injectors);
            $method->invokeArgs($object, $this->inject($method->getParameters()));
        }

        return $instance;
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function invoke(\Closure $function): mixed
    {
        $metadata = new \ReflectionFunction($function);

        $arguments = $this->inject($metadata->getParameters());

        while ([] !== $this->injectors) {
            [$object, $method] = array_pop($this->injectors);
            $method->invokeArgs($object, $this->inject($method->getParameters()));
        }

        return $metadata->invokeArgs($arguments);
    }

    public function set(string $name, mixed $value): void
    {
        if (class_exists($name) && !($value instanceof $name)) {
            throw new \InvalidArgumentException(sprintf('Trying to set "%s" as a service "%s".', get_debug_type($value), $name));
        }

        $this->map["~{$name}"] = $value;
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function getInternal(string $class): object
    {
        $key = "~{$class}";

        if (\array_key_exists($key, $this->map)) {
            $value = $this->map[$key];
            if ($value === $this->uninitialized) {
                throw new \LogicException(sprintf('Recursive dependency detected for "%s".', $class));
            }

            return $value;
        }

        $this->map[$key] = $this->uninitialized;

        try {
            $classMetadata = new \ReflectionClass($class);
            $constructorMetadata = $classMetadata->getConstructor();
            if (null === $constructorMetadata) {
                $argumentList = [];
            } else {
                $argumentList = $this->inject($constructorMetadata->getParameters());
            }
            $value = $classMetadata->newInstanceArgs($argumentList);
        } catch (\Throwable $error) {
            unset($this->map[$key]);

            throw $error;
        }

        $this->map[$key] = $value;

        foreach ($classMetadata->getMethods() as $methodMetadata) {
            if ([] !== $methodMetadata->getAttributes(Injector::class)) {
                $this->injectors[] = [$value, $methodMetadata];
            }
        }

        return $value;
    }

    /**
     * @param \ReflectionParameter[] $parameterList
     *
     * @return mixed[]
     */
    private function inject(array $parameterList): array
    {
        $valueList = [];

        foreach ($parameterList as $parameterMetadata) {
            $value = $this->uninitialized;
            $type = $parameterMetadata->getType();
            if ($type instanceof \ReflectionNamedType) {
                if (!$type->isBuiltin()) {
                    $value = $this->getInternal($type->getName());
                }
            }
            if ($value === $this->uninitialized) {
                $value = $this->getInternal($parameterMetadata->getName());
            }
            $valueList[] = $value;
        }

        return $valueList;
    }
}
