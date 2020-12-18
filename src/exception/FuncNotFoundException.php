<?php
declare (strict_types = 1);

namespace nowqs\exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

Class FuncNotFoundException extends RuntimeException implements NotFoundExceptionInterface {

    protected $func;

    public function __construct(string $message, string $func ="", int $code = 0, Throwable $previous = null){
        $this->func = $func;
        parent::__construct($message, $code, $previous);
    }

    /**
     * get func name
     * @return    string
     */
    public function getFunc(): string{
        return $this->func;
    }

}