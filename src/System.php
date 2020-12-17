<?php
declare (strict_types = 1);

namespace nowqs;

Class System extends Container{

    public function __construct(string $systemPath = "") {

        static::setInstance($this);

        // $this->instances()

    }

}