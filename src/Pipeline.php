<?php
declare(strict_types=1);

namespace nowqs;

use Closure;
use Exception;
use Throwable;

Class Pipeline {

    /**
     * 
     */
    protected $pass;

    /**
     * @var array
     */
    protected $pipes = [];

    /**
     * 
     */
    protected $exceptionHandler;


    /**
     * initialize data
     * @param    mixed
     * @return   mixed
     */
    public function send($pass) {
        $this->pass = $pass;
        return $this;
    }

    public function through($pipes) {
        $this->pipes = is_array($pipes) ? $pipes : func_get_arg();
        return $this;
    }


    public function then(Closure $destination) {
        $pipeline = array_reduce(
            array_reduce($this->pipes),
            $this->carry(),
            function($pass) use ($destination) {
                try {
                    return $destination($pass);
                } catch (Throwable | Exception $e) {
                    return $this->handle_exception($pass, $e);
                }
            }
        );
    }

    public function with_exception($handle) {
        $this->exceptionHandler = $handle;
        return $this;
    }


    public function carry() {
        return function ($stack, $pipe) {
            return function ($pass) use ($stack, $pipe) {
                try {
                    return $pipe($pass, $stack);
                } catch (Throwable | Exception $e) {
                    return $this->handle_exception($pass, $e);
                }
            };
        };
    }

    public function handle_exception($pass, Throwable $e) {
        if ($this->exceptionHandler) {
            return call_user_func($this->exceptionHandler, $pass, $e);
        }
        throw $e;
    }

}