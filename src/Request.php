<?php
declare(strict_types=1);

namespace nowqs;

use ArrayAccess;

class Request implements ArrayAccess {
    /**
     * input stream
     */
    protected $input;

    /**
     * method
     * @var string
     */
    protected $method;

    public function __construct() {
        $this->input = file_get_contents('php://input');
    }

    public function __make(System $system) {
        $request = new static();

        $header = [];
        if (function_exists('apache_request_headers') && apache_request_headers()) {
            $header = apache_request_headers();
        } else {
            $server = $_SERVER;
            $server = $_SERVER;
            foreach ($server as $key => $val) {
                if (0 === strpos($key, 'HTTP_')) {
                    $key = str_replace('_', '-', strtolower(substr($key, 5)));
                    $header[$key] = $val;
                }
            }
            if (isset($server['CONTENT_TYPE'])) {
                $header['content-type'] = $server['CONTENT_TYPE'];
            }
            if (isset($server['CONTENT_LENGTH'])) {
                $header['content-length'] = $server['CONTENT_LENGTH'];
            }
        }
    }

    /**
     * magic function
     */
    public function __set($name, $value) {
        // TODO:
    }

    /**
     * magic function
     */
    public function __isset($name) {
        // TODO:
    }

    /**
     * magic function
     */
    public function __get($name) {
        // TODO:
    }

    /**
     * implements ArrayAccess function
     */
    public function offsetExists($offset) {
        // TODO:
    }

    /**
     * implements ArrayAccess function
     */
    public function offsetGet($offset) {
        // TODO:
    }

    /**
     * implements ArrayAccess function
     */
    public function offsetSet($offset, $value) {
        // TODO: don't
    }

    /**
     * implements ArrayAccess function
     */
    public function offsetUnset($offset) {
        // TODO: don't
    }
}
