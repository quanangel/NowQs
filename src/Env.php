<?php
declare (strict_types = 1);

namespace nowqs;

use ArrayAccess;
use Exception;

Class Env implements ArrayAccess{


    /**
     * it's ENV data array
     */
    protected $data = [];


    public function __construct() {
        $this->data = $_ENV;
    }


    public function load(string $file):void{
        $env = parse_ini_file($file) ?: [];
        $this->set($env);
    }

    /**
     * set ENV value
     * @param    mixed
     * @param    mixed
     * @return   void
     */
    public function set($env, $value = null):void {
        if (is_array($env)) {
            $env = array_change_key_case($env, CASE_UPPER);
            
            foreach ($env as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $this->data[$key . '_' . strtoupper($k)] = $v;
                    }
                } else {
                    $this->data[$key] = $val;
                }
            }

        } else {
            $key = strtoupper(str_replace('.', '_', $env));
            $this->data[$key] = $value;
        }
    }

    /**
     * get some ENV message
     * @param    string|null
     * @param    mixed
     * @return   mixed
     */
    public function get(string $name = null, $default = null) {
        if (is_null($name)) return $this->data;
        $name = strtoupper(str_replace('.','_', $name));
        if (isset($this->data[$name])) return $this->data[$name];

        return $this->get_env($name, $default);
    }

    /**
     * get System ENV
     * @param    string|null
     * @param    mixed
     * @return   mixed
     */
    public function get_env(string $name = null, $default = null){
        $result = getenv("PHP_" . $name);
        if (false == $result) return $default;

        if ('false' == $result) {
            $result = false;
        } else if('true' == $result) {
            $result = true;
        }

        if (!isset($this->data[$name])) $this->data[$name] = $result;

        return $result;
    }

    /**
     * judgment the ENV value exist
     */
    public function has(string $name): bool {
        return !is_null($this->get($name));
    }


    /**
     * implements ArrayAccess function
     * judgment the ENV value exist
     */
    public function offsetExists($name): bool {
        return $this->__isset($name);
    }

    /**
     * implements ArrayAccess function
     * get some ENV value
     * @return   mixed
     */
    public function offsetGet($name) {
        return $this->get($name);
    }

    /**
     * implements ArrayAccess function
     * set ENV value
     */
    public function offsetSet($name, $value) {
        $this->set($name, $value);
    }

    /**
     * implements ArrayAccess function
     * not exist function
     */
    public function offsetUnset($name) {
        throw new Exception("not suppore: unset");
    }


    /**
     * magic function
     * set ENV value
     * @param    string 
     * @param    mixed
     * @return   void
     */
    public function __set(string $name, $value):void {
        $this->set($name, $value);
    }

    /**
     * magic function
     * get ENV value
     * @param    string
     * @return   mixed
     */
    public function __get(string $name) {
        return $this->get($name);
    }

    /**
     * magic function
     * judgment the ENV value exist
     */
    public function __isset(string $name): bool {
        return $this->has($name);
    }
    
}