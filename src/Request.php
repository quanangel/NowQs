<?php
declare(strict_types=1);

namespace nowqs;

use ArrayAccess;

class Request implements ArrayAccess {


    /**
     * valid method
     */
    protected $validMethod = ['GET', 'POST', 'PUT', 'POST', 'OPTIONS', 'HEAD', 'PATCH'];

    /**
     * input stream
     */
    protected $input;

    /**
     * header array
     * @var    array
     */
    protected $header = [];

    /**
     * server array
     * @var    array
     */
    protected $server = [];

    /**
     * env array
     * @var    array
     */
    protected $env = [];

    /**
     * method
     * @var    string
     */
    protected $method;

    /**
     * Host
     * @var    string
     */
    protected $host;

    /**
     * Domain
     * @var    string
     */
    protected $domain;

    /**
     * Root Domain
     * @var    string
     */
    protected $rootDomain;

    /**
     * Sub Domain
     * @var    string
     */
    protected $subDomain;

    /**
     * Pan Domain
     * @var    string
     */
    protected $panDomain;

    /**
     * URL
     * @var    string
     */
    protected $url;

    /**
     * base URL
     * @var    string
     */
    protected $baseUrl;

    /**
     * base File
     * @var    string
     */
    protected $baseFile;

    /**
     * root URL
     * @var    string
     */
    protected $root;

    /**
     * compatible pathinfo fetch
     */
    protected $pathinfoFetch = ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'];

    /**
     * compatible pathinfo var
     */
    protected $varPathinfo = 's';

    /**
     * Pathinfo
     * @var string
     */
    protected $pathinfo;

    /**
     * middle ware array
     */
    protected $middleWare = [];

    protected $request = [];

    protected $get = [];

    protected $post = [];

    protected $put = [];

    /**
     * mime type
     * @var    array
     */
    protected $mimeType = [
        'xml' => 'application/xml,text/xml,application/x-xml',
        'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js' => 'text/javascript,application/javascript,application/x-javascript',
        'css' => 'text/css',
        'rss' => 'application/rss+xml',
        'yaml' => 'application/x-yaml,text/yaml',
        'atom' => 'application/atom+xml',
        'pdf' => 'application/pdf',
        'text' => 'text/plain',
        'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
        'csv' => 'text/csv',
        'html' => 'text/html,application/xhtml+xml,*/*',
    ];

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

        $request->header = array_change_key_case($header);
        $request->server = $_SERVER;
        $request->env = $system->env;

        return $request;
    }

    /**
     * set domain
     * @param    string
     * @return   mixed
     */
    public function set_domain(string $domain) {
        $this->domain = $domain;
        return $this;
    }

    /**
     * get domain
     * @param    boolean
     * @return   string
     */
    public function domain(bool $isPort = false): string {
        return $this->scheme() . '://' . $this->host($isPort);
    }

    /**
     * get root domain
     * @return    string
     */
    public function root_domain(): string {
        $root = $this->rootDomain;
        if (!$root) {
            $item = explode('.', $this->host());
            $count = count($item);
            $root = $count > 1 ? $item[$count - 2] . '.' . $item[$count - 1] : $item[0];
        }
        return $root;
    }

    /**
     * set sub domain
     * @param    string
     * @return   mixed
     */
    public function set_sub_domain(string $subDomain) {
        $this->subDomain = $subDomain;
        return $this;
    }

    /**
     * get sub domain
     * @return    string
     */
    public function sub_domain(): string {
        if (is_null($this->subDomain)) {
            $rootDomain = $this->root_domain();

            if ($rootDomain) {
                $this->subDomain = rtrim(stristr($this->host(), $rootDomain, true), '.');
            } else {
                $this->subDomain = '';
            }
        }
        return $this->subDomain;
    }

    /**
     * set pan domain
     * @param    string
     * @return   mixed
     */
    public function set_pan_domain(string $panDomain) {
        $this->panDomain = $panDomain;
        return $this;
    }

    /**
     * get pan domain
     * @return    string
     */
    public function pan_domain(): string {
        return $this->panDomain ?: '';
    }

    /**
     * set url
     * @param    string
     * @return   mixed
     */
    public function set_url(string $url) {
        $this->url = $url;
        return $this;
    }

    /**
     * get url
     * @param    boolean
     * @return   string
     */
    public function url(bool $complete = false): string {
        $url = '';
        if ($this->url) {
            $url = $this->url;
        } elseif ($this->server('HTTP_X_REWRITE_URL')) {
            $url = $this->server('HTTP_X_REWRITE_URL');
        } elseif ($this->server('REQUEST_URI')) {
            $url = $this->server('REQUEST_URI');
        } elseif ($this->server('ORIG_PATH_INFO')) {
            $url = $this->server('ORIG_PATH_INFO') . (!empty($this->server('QUERY_STRING')) ? '?' . $this->server('QUERY_STRING') : '');
        } elseif (isset($_SERVER['argv'][1])) {
            $url = $_SERVER['argv'][1];
        }
        return $complete ? $this->domain() . $url : $url;
    }

    /**
     * set base URL
     * @param    string
     * @return   mixed
     */
    public function set_base_url(string $baseUrl) {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * get base URL
     * @param    boolean
     * @return   string
     */
    public function base_url(bool $complete = false): string {
        if (!$this->baseUrl) {
            $str = $this->url();
            $this->baseUrl = strpos($str, '?') ? strstr($str, '?', true) : $str;
        }

        return $complete ? $this->domain() . $this->baseUrl : $this->baseUrl;
    }

    /**
     * get base file
     * @param    boolean
     * @return   string
     */
    public function base_file(bool $complete = false): string {
        if (!$this->baseFile) {
            $url = '';
            if (!$this->is_cli()) {
                $script_name = basename($this->server('SCRIPT_FILENAME'));
                if (basename($this->server('SCRIPT_NAME')) === $script_name) {
                    $url = $this->server('SCRIPT_NAME');
                } elseif (basename($this->server('PHP_SELF')) === $script_name) {
                    $url = $this->server('PHP_SELF');
                } elseif (basename($this->server('ORIG_SCRIPT_NAME')) === $script_name) {
                    $url = $this->server('ORIG_SCRIPT_NAME');
                } elseif (($pos = strpos($this->server('PHP_SELF'), '/' . $script_name)) !== false) {
                    $url = substr($this->server('SCRIPT_NAME'), 0, $pos) . '/' . $script_name;
                } elseif ($this->server('DOCUMENT_ROOT') && strpos($this->server('SCRIPT_FILENAME'), $this->server('DOCUMENT_ROOT')) === 0) {
                    $url = str_replace('\\', '/', str_replace($this->server('DOCUMENT_ROOT'), '', $this->server('SCRIPT_FILENAME')));
                }
            }
            $this->baseFile = $url;
        }

        return $complete ? $this->domain() . $this->baseFile : $this->baseFile;
    }

    /**
     * set root URL
     * @param    string
     * @return   mixed
     */
    public function set_root(string $root) {
        $this->root = $root;
        return $this;
    }

    /**
     * get root URL
     * @param    boolean
     * @return   string
     */
    public function root(bool $complete = false): string {
        if (!$this->root) {
            $file = $this->base_file();
            if ($file && 0 !== strpos($this->url(), $file)) {
                $file = str_replace('\\', '/', dirname($file));
            }
            $this->root = rtrim($file, '/');
        }

        return $complete ? $this->domain() . $this->root : $this->root;
    }

    /**
     * get root URL dir
     * @return    string
     */
    public function root_url(): string {
        $base = $this->root();
        $root = strpos($base, '.') ? ltrim(dirname($base), DIRECTORY_SEPARATOR) : $base;

        if ('' != $root) {
            $root = '/' . ltrim($root, '/');
        }

        return $root;
    }

    /**
     * get scheme
     * @return    string
     */
    public function scheme(): string {
        return $this->is_ssl() ? 'https' : 'http';
    }

    /**
     * judgment is has ssl
     */
    public function is_ssl(): bool {
        if ($this->server('HTTPS') && ('1' == $this->server('HTTPS') || 'on' == strtolower($this->server('HTTPS')))) {
            return true;
        } elseif ('https' == $this->server('REQUEST_SCHEME')) {
            return true;
        } elseif ('443' == $this->server('SERVER_PORT')) {
            return true;
        } elseif ('https' == $this->server('HTTP_X_FORWARDED_PROTO')) {
            return true;
        } elseif ($this->httpsAgentName && $this->server($this->httpsAgentName)) {
            return true;
        }

        return false;
    }

    /**
     * judgment is CLI mode
     * @return    boolean
     */
    public function is_cli(): bool {
        return strpos(PHP_SAPI, 'cli') === 0;
    }

    /**
     * judgment is CGI mode
     * @return    boolean
     */
    public function is_cgi(): bool{
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * set valid method
     */
    public function set_valid_method(array $method = []) {
        $this->validMethod = $method;
    }

    /**
     * get valid method
     * @return    array
     */
    public function valid_method(): array {
        return $this->validMethod;
    }

    /**
     * judgment the method is valid
     * @return    boolean
     */
    public function is_valid_method(string $method = ""): bool {
        if (!in_array($method, $this->valid_method())) return false;
        return $this->method() == strtoupper($method);
    }


    /**
     * get server message
     * @return    mixed
     */
    public function server(string $key = '', $default = null) {
        if (empty($key)) {
            return $this->server;
        } else {
            $key = strtoupper($key);
        }
        return $this->server[$key] ?? $default;
    }

    /**
     * get the request mime type
     */
    public function type(): string {
        $accept = $this->server('HTTP_ACCEPT');
        if (empty($accept)) {
            return '';
        }

        foreach ($this->mimeType as $key => $val) {
            $array = explode(',', $val);
            foreach ($array as $v) {
                if (stristr($accept, $v)) {
                    return $key;
                }
            }
        }

        return '';
    }

    /**
     * judgment key exits at the param
     * @param    string
     * @param    string
     * @param    boolean
     * @return   boolean
     */
    public function has(string $key, string $type = 'param', bool $checkEmpty = false): bool {
        if (!in_array($type, [
            'param', 'get', 'post', 'put', 'patch', 'route', 'delete', 'cookie', 'session', 'env', 'request', 'server', 'header', 'file'
        ])) {
            return false;
        }

        $param = empty($this->$type) ? $this->$type() : $this->$type;

        if (is_object($param)) {
            return $param->has($key);
        }

        foreach (explode('.', $key) as $val) {
            if (isset($param[$val])) {
                $param = $param[$val];
            } else {
                return false;
            }
        }

        return ($checkEmpty && '' == $param) ? false : true;
    }

    /**
     * set Host
     * @param    string
     */
    public function set_host(string $host) {
        $this->host = $host;
    }

    /**
     * set Port
     * @return    integer
     */
    public function port():int {
        return (int) ($this->server('HTTP_X_FORWARDED_PORT') ?: $this->server('SERVER_PORT', ''));
    }

    /**
     * get Protocol
     * @return    string
     */
    public function protocol(): string {
        return $this->server('SERVER_PROTOCOL', '');
    }

    /**
     * get Host
     * @param    boolean
     * @return   string
     */
    public function host(bool $strict = false): string {
        $host = null;
        if ($this->host) {
            $host = $this->host;
        } else {
            $host = strval($this->server('HTTP_X_FORWARDED_HOST') ?: $this->server('HTTP_HOST'));
        }

        return (true == $strict && strpos($host, ':')) ? strstr($host, ':', true) : $host;
    }

    /**
     * get method
     * @param    boolean
     * @return   string
     */
    public function method(bool $origin = false): string {
        if ($origin) {
            return $this->server('REQUEST_METHOD') ?: 'GET';
        } else {
            if ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->method = strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
            } else {
                $this->method = $this->server('REQUEST_METHOD') ?: 'GET';
            }
        }
        return $this->method;
    }

    /**
     * set method
     * @param    string
     * @return   mixed
     */
    public function set_method(string $method) {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * set mime type
     * @param    mixed
     * @param    mixed
     */
    public function set_mime_type($type, $value = '') {
        if (is_array($type)) {
            $this->mimeType = array_merge($this->mimeType, $type);
        } else {
            $this->mimeType[$type] = $value;
        }
    }

    /**
     * get the request time
     * @param    boolean
     * @return   integer|float
     */
    public function time(bool $isFloat = false) {
        return $isFloat ? $this->server('REQUEST_TIME_FLOAT') : $this->server('REQUEST_TIME');
    }

    /**
     * get pathinfo message
     * @return string
     */
    public function pathinfo(): string {
        if (is_null($this->pathinfo)) {
            $pathinfo = null;
            if (isset($_GET[$this->varPathinfo])) {
                $pathinfo = $_GET[$this->varPathinfo];
                unset($_GET[$this->varPathinfo], $this->get[$this->varPathinfo]);
            } elseif ($this->server('PATH_INFO')) {
                $pathinfo = $this->server('PATH_INFO');
            } elseif (false !== strpos(PHP_SAPI, 'cli')) {
                $pathinfo = strpos($this->server('REQUEST_URI'), '?') ? strstr($this->server('REQUEST_URI'), '?', true) : $this->server('REQUEST_URI');
            }

            if (is_null($pathinfo)) {
                foreach ($this->pathinfoFetch as $type) {
                    if ($this->server($type)) {
                        $pathinfo = (0 === strpos($this->server($type), $this->server('SCRIPT_NAME'))) ?
                        substr($this->server($type), strlen($this->server('SCRIPT_NAME'))) :
                        $this->server($type);
                        break;
                    }
                }
            }

            if (!empty($pathinfo)) {
                unset($this->get[$pathinfo], $this->request[$pathinfo]);
            }

            $this->pathinfo = (empty($pathinfo) || '/' == $pathinfo) ? '' : ltrim($pathinfo, '/');
        }

        return $this->pathinfo;
    }

    /**
     * get the URL extension
     * @return    string
     */
    public function ext(): string {
        return pathinfo($this->pathinfo(), PATHINFO_EXTENSION);
    }

    public function param($key = "", $default = null, $filter = "") {
        // TODO: 
    }

    /**
     * get middleWare array message
     * @param    mixed
     * @param    mixed
     * @return   mixed
     */
    public function middle_ware($key, $default = null) {
        return $this->middleWare[$key] ?? $default;
    }

    /**
     * magic function
     * set value by key at middleWare array
     * @param    mixed
     * @param    mixed
     */
    public function __set($key, $value) {
        $this->middleWare[$key] = $value;
    }

    /**
     * magic function
     * judgment key exist at middleWare array
     * @param    mixed
     * @return   boolean
     */
    public function __isset($key): bool {
        return isset($this->middleWare[$key]);
    }

    /**
     * magic function
     * get value by key at middleWare array
     * @param    mixed
     * @return   mixed
     */
    public function __get($key) {
        return $this->middle_ware($key);
    }

    /**
     * implements ArrayAccess function
     */
    public function offsetExists($key) {
        return $this->has($key);
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
