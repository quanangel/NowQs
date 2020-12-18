<?php
declare (strict_types = 1);

namespace nowqs;

Class Http{

    /**
     * @var System
     */
    protected $system;

    /**
     * route path
     * @var string
     */
    protected $routePath;

    public function __construct(System $system) {
        $this->system = $system;
        $this->routePath = $this->system->get_root_path() . 'route' . DIRECTORY_SEPARATOR;
    }

    public function run() {
        echo "123";
    }

}
