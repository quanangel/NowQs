<?php
declare(strict_types=1);

namespace nowqs;

use ArrayAccess;
use Closure;
use IteratorAggregate;
use ArrayIterator;
use Countable;

use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionFunctionAbstract;

use ReflectionException;
use InvalidArgumentException;
use nowqs\exception\ClassNotFoundException;
use nowqs\exception\FuncNotFoundException;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface, ArrayAccess, IteratorAggregate, Countable {
    /**
     * container instance
     * @var    Container|Closure
     */
    protected static $instance;

    /**
     * container instance array
     * @var    array
     */
    protected $instances = [];

    /**
     * bind container array
     * @var    array
     */
    protected $bind = [];

    /**
     * call back array
     */
    protected $reflectCallBack = [];

    /**
     * bind Class/Closure/Instance/Interface at container
     */
    public function bind($tag, $container = null) {
        if (is_array($tag)) {
            foreach ($tag as $k => $v) {
                $this->bind($k, $v);
            }
        } elseif ($container instanceof Closure) {
            $this->bind[$tag] = $container;
        } elseif (is_object($container)) {
            $this->bind_instance($tag, $container);
        } else {
            $tag = $this->getRealAlias($tag);
            if ($tag != $container) {
                $this->bind[$tag] = $container;
            }
        }
        return $this;
    }

    /**
     * bind instance as tag in instance array
     * @param    string
     * @param    object
     * @return   $this
     */
    public function bind_instance(string $tag, $instance) {
        $tag = $this->getRealAlias($tag);
        $this->instances[$tag] = $instance;
        return $this;
    }

    /**
     * get real alias
     * @param    string
     * @return   string
     */
    public function getRealAlias(string $tag): string {
        if (isset($this->bind[$tag])) {
            $bind = $this->bind[$tag];
            if (is_string($bind)) {
                return $this->getRealAlias($bind);
            }
        }

        return $tag;
    }

    /**
     * get this instance container
     * @return    static
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        if (static::$instance instanceof Closure) {
            return (static::$instance)();
        }

        return static::$instance;
    }

    /**
     * set this instance container
     * @param    object
     */
    public static function setInstance($instance): void {
        static::$instance = $instance;
    }

    /**
     * judgment the tag does is exist in the container instance or the bind array
     * @return    boolean
     */
    public function is_has($tag): bool {
        return (isset($this->bind[$tag]) || isset($this->instances[$tag]));
    }

    /**
     * judgment the tag does is exist in the container instance
     */
    public function exist($tag): bool {
        $tag = $this->getRealAlias($tag);
        return isset($this->instances[$tag]);
    }

    /**
     * make class instance, if exits return instance
     */
    public function make(string $tag, array $vars = [], bool $isNew = false) {
        $tag = $this->getRealAlias($tag);
        if (isset($this->instances[$tag]) && !$isNew) {
            return $this->instances[$tag];
        }

        $object = null;

        if (isset($this->bind[$tag]) && $this->bind[$tag] instanceof Closure) {
            $object = $this->reflect_function($this->bind[$tag], $vars);
        } else {
            $object = $this->reflect_class($tag, $vars);
        }

        if ($isNew) {
            $this->instances[$tag] = $object;
        }

        return $object;
    }

    /**
     * delete container at instance object
     * @param    string
     */
    public function delete($tag) {
        $tag = $this->getRealAlias($tag);
        if (isset($this->instances[$tag])) {
            unset($this->instances[$tag]);
        }
    }


    public function reflect($callable, array $vars =[], bool $accessible = false) {
        if ($callable instanceof Closure) {
            return $this->reflect_function($callable, $vars);
        } else if (is_string($callable) && false === strpos($callable, '::')) {
            return $this->reflect_function($callable, $vars);
        }
        return $this->reflect_method($callable, $vars, $accessible);
    }

    /**
     * reflect execute function
     * @param    mixed
     * @param    array
     * @return   mixed
     */
    public function reflect_function($function, array $vars = []) {
        try {
            $reflect = new ReflectionFunction($function);
        } catch (ReflectionException $e) {
            throw new FuncNotFoundException("function not exist: {$function}()", $function, 0, $e);
        }
        $args = $this->bind_params($reflect, $vars);
        return $function(...$args);
    }

    /**
     * reflect execute class function instance and dependency injection
     * @param    mixed
     * @param    array
     * @param    boolean
     * @return   mixed
     */
    public function reflect_method($method, array $vars = [], bool $accessible = false) {
        if (is_array($method)) {
            [$class, $method] = $method;
            $class = is_object($class) ? $class : $this->reflect_class($class); 
        } else {
            [$class, $method] = explode('::', $method);
        }

        try {
            $reflect = new ReflectionMethod($class, $method);
        } catch (ReflectionException $e) {
            $class = is_object($class) ? get_class($class) : $class;
            new FuncNotFoundException("method not exist: {$class}::{$method}()", "{$class}::{$method}", 0, $e);
        }

        $args = $this->bind_params($reflect, $vars);

        if ($accessible) $reflect->setAccessible($accessible);

        return $reflect->invokeArgs(is_object($class) ? $class : null, $args);
    }

    /**
     * reflect execute class function instance and dependency injection
     * @param    mixed
     * @param    mixed
     * @param    array
     * @return   mixed
     */
    public function reflect_method_instance($instance, $reflect, array $vars = []) {
        $args = $this->bind_params($instance, $vars);
        return $reflect->invokeArgs($instance, $args);
    }

    /**
     * reflect execute class instance and dependency injection
     * @param    string
     * @param    array
     * @return   mixed
     */
    public function reflect_class(string $class, array $vars = []) {
        try {
            $reflect = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new ClassNotFoundException('class not exist: ' . $class, $class, 0, $e);
        }

        if ($reflect->hasMethod('__creater')) {
            $method = $reflect->getMethod('__creater');
            if ($method->isPublic() && $method->isStatic()) {
                $args = $this->bind_params($method, $vars);
                return $method->invokeArgs(null, $args);
            }
        }

        $constructor = $reflect->getConstructor();

        $args = $constructor ? $this->bind_params($constructor, $vars) : [];

        $object = $reflect->newInstanceArgs($args);

        $this->reflect_after($class, $object);

        return $object;
    }

    /**
     * execute call back
     * @param    string
     * @param    mixed
     * @return   void
     */
    public function reflect_after(string $class, $object): void {
        if (isset($this->reflectCallBack['*'])) {
            foreach ($this->reflectCallBack['*'] as $callback) {
                $callback($object, $this);
            }
        }

        if (isset($this->reflectCallBack[$class])) {
            foreach ($this->reflectCallBack[$class] as $callback) {
                $callback($object, $this);
            }
        }
    }

    /**
     * bind params
     * @param    ReflectionFunctionAbstract
     * @param    array
     * @return   array
     */
    public function bind_params(ReflectionFunctionAbstract $reflect, array $vars = []): array {
        if (0 == $reflect->getNumberOfParameters()) {
            return [];
        }

        $params = $reflect->getParameters();
        $args = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $class = $param->getClass();

            // TODO:
            if ($class) {
                $args[] = $this->get_object_param($class->getName(), $vars);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException('method param miss: ' . $name);
            }
        }

        return $args;
    }

    /**
     * get object param
     * @param    string
     * @param    array
     */
    public function get_object_param(string $className, array &$vars) {
        $array = $vars;
        $value = array_shift($array);

        $result = null;
        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }

    /**
     * get instance, if not, create instance
     * @param    string
     * @param    array
     * @param    boolean
     * @return   mixed
     */
    public static function pull(string $tag, array $vars = [], bool $isNew = false) {
        return static::getInstance()->make($tag, $vars, $isNew);
    }


    /**
     * register callback function at reflect callback array
     * @param    mixed
     * @param    Closure
     * @return   void
     */
    public function register_reflect_callback($tag, Closure $callback = null): void {
        if ($tag instanceof Closure) {
            $this->reflectCallBack['*'][] = $tag;
            return;
        }
        $tag = $this->getRealAlias($tag);
        $this->reflectCallBack[$tag][] = $callback;
    }

    /**
     * implements ContainerInterface function
     * get the tag at container
     */
    public function get($tag) {
        if ($this->has($tag)) {
            return $this->make($tag);
        }
        throw new ClassNotFoundException('Class not exist: ' . $tag, $tag);
    }

    /**
     * implements ContainerInterface function
     * judgment the tag does is exist in the container
     * @param    string
     * @return   boolean
     */
    public function has($tag): bool {
        return $this->is_has($tag);
    }

    /**
     * implements ArrayAccess function
     * judgment some container exist in container instance array
     */
    public function offsetExists($name): bool {
        return $this->exist($name);
    }

    /**
     * implements ArrayAccess function
     * get something
     *
     */
    public function offsetGet($name) {
        return $this->make($name);
    }

    /**
     * implements ArrayAccess function
     * set something at bind array
     */
    public function offsetSet($name, $value) {
        $this->bind($name, $value);
    }

    /**
     * implements ArrayAccess function
     */
    public function offsetUnset($name) {
        $this->delete($name);
    }

    /**
     * implements IteratorAggregate function
     */
    public function getIterator() {
        return new ArrayIterator($this->instances);
    }

    /**
     * implements Countable function
     */
    public function count() {
        return count($this->instances);
    }

    /**
     * magic function
     * set something at bind array
     */
    public function __set($name, $value) {
        $this->bind($name, $value);
    }

    /**
     * magic function
     * get something
     * @param    string
     */
    public function __get($name) {
        return $this->get($name);
    }

    /**
     * magic function
     * judgment some container exist in container instance array
     */
    public function __isset($name): bool {
        return $this->exist($name);
    }

    /**
     * magic function
     * delete some container at container instance array
     */
    public function __unset($name) {
        $this->delete($name);
    }
}
