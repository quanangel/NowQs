<?php

declare(strict_types=1);

namespace nowqs;

class System extends Container {
    const VERSION = 'dev';

    /**
     * debug status
     * @var    boolean
     */
    protected $debug = false;

    /**
     * start time
     * @var    float
     */
    protected $beginTime;

    /**
     * start memory size
     */
    protected $beginMemory;

    /**
     * root path
     * @var    string
     */
    protected $rootPath = '';

    /**
     * nowqs frame patch
     * @var    string
     */
    protected $nowqsPath = '';

    /**
     * app path
     * @var    string
     */
    protected $appPath = '';

    /**
     * run time path
     * @var    string
     */
    protected $runtimePath = '';

    /**
     * route path
     * @var    string
     */
    protected $routePath = '';

    /**
     * config file ext
     * @var    string
     */
    protected $configExt = '.php';


    /**
     * is it initialize
     * @var    boolean
     */
    protected $initialized = false;

    /**
     * bind tag
     * @var array
     */
    protected $bind = [
        'system' => System::class,
        'env' => Env::class,
        'http' => Http::class,
    ];

    public function __construct(string $systemPath = '') {
        $this->nowqsPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->rootPath = $systemPath ? rtrim($this->nowqsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : $this->get_default_root_path();
        $this->appPath = $this->rootPath . 'app' . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;

        static::setInstance($this);
        $this->bind_instance('system', $this);
        $this->bind_instance('nowqs/Container', $this);

        // $this->instances()
    }

    // TODO:
    public function initialize() {
        $this->beginTime = microtime(true);
        $this->beginMemory = memory_get_usage();

        $this->initialized = true;

        // load env
        if (is_file($this->rootPath . ".env")) {
            $this->env->load($this->rootPath . ".env");
        }

        // get config extension
        $this->configExt = $this->env->get('config_ext', '.php');

        $this->debug_mode_init();

        $this->load();

        return $this;
    }

    /**
     * debug mode initialize
     */
    public function debug_mode_init(){
        $this->debug = $this->env->get('debug') ? true : false;
        if (!$this->debug) {
            ini_set('display_errors', 'Off');
        }

        // TODO: something
    }


    public function load():void {
        echo "</br>is system load";
        $appPath = $this->get_app_path();

        echo "<br>";
        echo $appPath;
        echo "<br>";
        echo $this->get_nowqs_path();
    }

    /**
     * get root path
     * @return    string
     */
    public function get_root_path(): string {
        return $this->rootPath;
    }

    /**
     * get default root path
     * @return    string
     */
    protected function get_default_root_path(): string {
        return dirname($this->nowqsPath) . DIRECTORY_SEPARATOR;
    }

    /**
     * get nowqs path
     * @return    string
     */
    public function get_nowqs_path(): string{
        return $this->nowqsPath;
    }

    /**
     * get app path
     * @return    string
     */
    public function get_app_path(): string{
        return $this->appPath;
    }

    /**
     * set app path
     * @param    string
     */
    public function set_app_path(string $path) {
        $this->appPath = $path;
    }

    /**
     * get runtime path
     * @return    string
     */
    public function get_runtime_path(): string{
        return $this->appPath;
    }

    /**
     * set runtime path
     * @param    string
     */
    public function set_runtime_path(string $path) {
        $this->runtimePath = $path;
    }


    /**
     * get initialize status
     * @return    boolean
     */
    public function initialized(): bool {
        return $this->initialized;
    }




    /**
     * get begin time
     * @return    float
     */
    public function get_begin_time(): float{
        return $this->beginTime;
    }

    
}
