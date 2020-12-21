<?php
declare(strict_types=1);

namespace nowqs;

use Closure;

class MiddleWare {
    /**
     * queue array
     * @var    array
     */
    protected $queue = [];

    /**
     * System
     * @var    system
     */
    protected $system;

    public function __construct(System $system) {
        $this->system = $system;
    }

    /**
     * import middle ware array
     * @param    array
     * @param    string
     * @return   void
     */
    public function import($middleWares = [], string $type = 'global'): void {
        foreach ($middleWares as $vo) {
            $this->add($vo, $type);
        }
    }

    /**
     * add middle ware at the queue array
     * @param    mixed
     * @param    string
     * @return   void
     */
    public function add($middleWare, string $type = 'global'): void {
        $middleWare = $this->parse_middle_ware($middleWare, $type);

        if (!empty($middleWare)) {
            $this->queue[$type][] = $middleWare;
            $this->queue[$type] = array_unique($this->queue[$type], SORT_REGULAR);
        }
    }

    public function pipeline(string $type = "global") {
        return (new Pipeline)->through();
    }

    /**
     * parse middle ware
     * @param    mixed
     * @param    string
     * @return   array
     */
    public function parse_middle_ware($middleWare, string $type): array {
        if (is_array($middleWare)) {
            [$middleWare, $params] = $middleWare;
        }

        if ($middleWare instanceof Closure) {
            return [$middleWare, $params ?? []];
        }

        if (!is_string($middleWare)) {
            throw new InvalidArgumentException('the middle ware is invalid');
        }

        $alias = $this->app->config->get('middleware.alias', []);

        if (isset($alias[$middleWare])) {
            $middleWare = $alias[$middleWare];
        }

        if (is_array($middleWare)) {
            $this->import($middleWare, $type);
            return [];
        }

        return [[$middleWare, 'handle'], $params ?? []];
    }

    /**
     * sort middle ware
     * @param    mixed
     * @return   mixed
     */
    public function sort_middle_ware($middlewares) {
        $priority = $this->system->config->get('middleware.priority', []);
        uasort($middlewares, function ($a, $b) use ($priority) {
            $aPriority = $this->get_middle_ware_priority($priority, $a);
            $bPriority = $this->get_middle_ware_priority($priority, $b);
            return $bPriority - $aPriority;
        });
        return $middlewares;
    }

    /**
     * get middle ware priority
     * @param    mixed
     * @param    mixed
     * @return   mixed
     */
    public function get_middle_ware_priority($priority, $middleWare) {
        [$cell] = $middleWare;
        if (is_array($cell) && is_string($cell[0])) {
            $index = array_search($call[0], array_reverse($priority));
            return false == $index ? -1 : $index;
        }
        return -1;
    }
}
