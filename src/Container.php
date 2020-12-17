<?php
declare (strict_types = 1);

namespace nowqs;

use ArrayAccess;
use Closure;
use IteratorAggregate;
use ArrayIterator;
use Countable;

use Psr\Container\ContainerInterface;

use nowqs\exception\ClassNotFoundException;

class Container implements ContainerInterface, ArrayAccess, IteratorAggregate, Countable{

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
     * bind Class/Closure/Instance/Interface at container
     */
    public function bind($tag, $container = null) {
        if (is_array($tag)) {
            foreach ($tag as $k => $v) {
                $this->bind($k, $v);
            }
        } else if ($container instanceof Closure) {
            $this->bind[$tag] = $container;
        } else if (is_object($container)) {
            $this->bind_instance($tag, $container);
        } else {
            $tag = $this->getRealAlias($tag);
            if ($tag != $container) $this->bind[$tag] = $container;
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
            if (is_string($bind)) return $this->getRealAlias($bind); 
        }

        return $tag;
    }


    /**
     * get this instance container
     * @return    static
     */
    public static function getInstance() {

        if (is_null(static::$instance)) static::$instance = new static;

        if (static::$instance instanceof Closure) return (static::$instance)();
        
        return static::$instance;
    }

    /**
     * set this instance container
     * @param    object
     */
    public static function setInstance($instance): void{
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
    public function make() {
        // TODO: 
    }

    /**
     * delete container at instance object
     * @param    string
     */
    public function delete($tag) {
        $tag = $this->getRealAlias($tag);
        if (isset($this->instances[$tag])) unset($this->instances[$tag]);
    }

    /**
     * implements ContainerInterface function
     * get the tag at container
     */
    public function get($tag) {
        if ($this->has($tag)) return $this->make($tag);
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
    public function offsetExists($key): bool {
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
    public function offsetUnset($key) {
        $this->delete($name);
    }

    /**
     * implements IteratorAggregate function
     */
    public function getIterator(){
        return new ArrayIterator($this->instances);
    }

    /**
     * implements Countable function
     */
    public function count() {
        return count($this->instances);
    }

    /**
     * magic method
     * set something at bind array
     */
    public function __set($name, $value) {
        $this->bind($name, $value);
    }

    /**
     * magic method
     * get something
     * @param    string
     */
    public function __get($name){
        return $this->get($name);
    }

    /**
     * magic method
     * judgment some container exist in container instance array
     */
    public function __isset($name): bool {
        return $this->exist($name);
    }

    /**
     * magic method
     * delete some container at container instance array
     */
    public function __unset($name) {
        $this->delete($name);
    }


}