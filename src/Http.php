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

    public function run(Request $request = null) {
        $this->initialize();

        $request = $request ?? $this->system->make("request", [], true);
        $this->system->bind_instance('request', $request);

        // TODO: 

        echo "</br>http server start";
    }

    // TODO: http server end
    public function end() {
        echo "</br>http server end";
    }


    public function initialize() {
        if (!$this->system->initialized()) $this->system->initialize();
    }

}
