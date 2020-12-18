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
     * root path
     * @var string
     */
    protected $rootPath = '';

    /**
     * nowqs frame patch
     * @var string
     */
    protected $nowqsPath = '';

    /**
     * app path
     * @var string
     */
    protected $appPath = "";

    /**
     * run time path
     * @var string
     */
    protected $runtimePath = '';

    /**
     * route path
     * @var string
     */
    protected $routePath = '';

    /**
     * config file ext
     * @var string
     */
    protected $configExt = '.php';

    /**
     * bind tag
     * @var array
     */
    protected $bind = [
        'system' => System::class,
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

    /**
     * get root path
     * @return    string
     */
    public function get_root_path(): string{
        return $this->rootPath;
    }

    /**
     * get default root path
     * @return    string
     */
    protected function get_default_root_path(): string {
        return dirname($this->nowqsPath, 4) . DIRECTORY_SEPARATOR;
    }
}
