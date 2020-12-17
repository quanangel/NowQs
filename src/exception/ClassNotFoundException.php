<?php
declare (strict_types = 1);

namespace nowqs\exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

Class ClassNotFoundException extends RuntimeException implements NotFoundExceptionInterface {

    protected $class;

    public function __construct(string $message, string $class ="", int $code = 0, Throwable $previous = null){
        $this->class = $class;
        parent::__construct($message, $code, $previous);
    }

    /**
     * get class name
     * @return    string
     */
    public function getClass(): string{
        return $this->class;
    }

}