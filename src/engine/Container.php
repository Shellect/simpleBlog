<?php

namespace engine;

use ReflectionClass;
use ReflectionException;

class Container
{
    private array $singletons = [];

    /**
     * @throws ReflectionException
     */
    public function get($class)
    {
        if (isset($this->singletons[$class])) {
            return $this->singletons[$class];
        }
        $classReflector = new ReflectionClass($class);
        $constructReflector = $classReflector->getConstructor();
        if ($constructReflector === null) {
            return new $class;
        }
        $constructArguments = $constructReflector->getParameters();
        if (empty($constructArguments)) {
            return new $class;
        }
        $args = [];
        foreach ($constructArguments as $argument) {
            $argumentType = $argument->getType()?->getName();
            $args[$argument->getName()] = $this->get($argumentType);
        }
        return new $class(...$args);
    }
}