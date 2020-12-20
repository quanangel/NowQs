<?php
declare(strict_types=1);

namespace nowqs;

class Config {
    /**
     * config data array
     * @var array
     */
    protected $config = [];

    /**
     * config path
     * @var string
     */
    protected $path;

    /**
     * config extension
     * @var string
     */
    protected $ext;

    public function __construct(string $path = null, string $ext = '.php') {
        $this->path = $path ?: '';
        $this->ext = $ext;
    }

    /**
     * load config file
     * @param    string
     * @param    string
     * @return   array
     */
    public function load(string $file, string $key): array {
        $fileName = '';
        if (is_file($file)) {
            $fileName = $file;
        } else {
            $fileName = $this->path . $file . $this->ext;
        }

        if ('' != $fileName) {
            return $this->parse($fileName, $key);
        }

        return $this->config;
    }

    /**
     * parse config file
     * @param    string
     * @param    string
     * @return   array
     */
    protected function parse(string $file, string $key): array {
        $type = pathinfo($file, PATHINFO_EXTENSION);
        $config = [];
        switch ($type) {
            case 'php':
                $config = include $file;
                break;
        }

        return (is_array($config) ? $this->set($config, strtolower($key)) : []);
    }

    /**
     * judgment the key exist at the config array
     * @param    string
     * @return   boolean
     */
    public function has(string $key): bool {
        if (!isset($this->config[strtolower($key)]) && false == strpos($key, '.')) {
            return false;
        }

        return !is_null($this->get($key));
    }

    /**
     * get level one at the config array
     * @param    string
     * @return   array
     */
    public function pull(string $key): array {
        return ($this->config[strtolower($key)] ?? []);
    }

    /**
     * get config by key at the config array
     * @param    string|null
     * @param    string|null
     */
    public function get(?string $key = null, ?string $default = null) {
        if (empty($key)) {
            return $this->config;
        }

        if (false == strpos($key, '.')) {
            return $this->pull($key);
        }

        $key = explode('.', $key);
        $key[0] = strtolower($key[0]);
        $config = $this->config;

        foreach ($key as $val) {
            if (isset($config[$val])) {
                $config = $config[$val];
            } else {
                return $default;
            }
        }

        return $config;
    }

    /**
     * set config by key at the config array
     * @param    array
     * @param    string|null
     * @return   array
     */
    public function set(array $config, ?string $key = null): array {
        $result = [];
        if (empty($key)) {
            $result = $this->config = array_merge($this->config, array_change_key_case($config));
        } else {
            if (isset($this->config[$key])) {
                $result = array_merge($this->config[$key], $config);
            } else {
                $result = $config;
            }
            $this->config[$key] = $result;
        }

        return $result;
    }
}
