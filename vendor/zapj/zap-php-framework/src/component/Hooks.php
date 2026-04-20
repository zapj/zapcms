<?php
namespace zap\component;

class Hooks
{
    protected static $instance;

    protected $filter = [];
    protected $action = [];
    protected $tags = [];

    protected $lastFilterId;
    protected $currentHookName;

    private $filterOrAction;

    protected function __construct()
    {
    }
    protected function __clone()
    {
    }

    /**
     * @return Hooks
     */
    public static function instance(): Hooks
    {
        if(is_null(static::$instance)){
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function add_filter($hookName,$callback, int $priority = 10): Hooks
    {
        $this->add($hookName,$callback,$priority,'filter');
        return $this;
    }

    public function add_action($hookName,$callback, int $priority = 10): Hooks
    {
        $this->add($hookName,$callback,$priority,'action');
        return $this;
    }

    protected function add($hookName,$callback, $priority ,$type): Hooks
    {
        $this->$type[$hookName][$priority][] = $callback;
        $this->lastFilterId = array_key_last($this->$type[$hookName][$priority]);
        $this->filterOrAction = $type;
        $this->currentHookName = $hookName;
        return $this;
    }

    public function as($name){
        if(isset($this->tags[$this->filterOrAction][$this->currentHookName][$name])){
            return;
        }
        $this->tags[$this->filterOrAction][$this->currentHookName][$name] = $this->lastFilterId;
    }



    public function apply_filters($hookName,$value,...$args)
    {
        foreach($this->filter[$hookName] as $filters){
            foreach($filters as $filter){
                array_unshift($args,$value);
                if(is_callable($filter)){
                    $value = call_user_func_array($filter,$args);
                }else if($filter instanceof \Closure){
                    $value = $filter(...$args);
                }else if(is_string($filter) && stripos($filter, '@') !== false){ // new class
                    list($class,$method) = explode('@',$filter);
                    $value = call_user_func_array([new $class,$method],$args);
                }else if(is_string($filter) && stripos($filter, '::') !== false){ // call static
                    $value = call_user_func_array($filter,$args);
                }
                array_shift($args);
            }
        }
        return $value;
    }

    public function do_action($hookName,...$args)
    {
        foreach($this->action[$hookName] as $actions){
            foreach($actions as $action){
                if(is_callable($action)){
                    call_user_func_array($action,$args);
                }else if($action instanceof \Closure){
                    $action(...$args);
                }
            }
        }
    }

    public function do_action_ref_array($hookName,&$args){
        $this->do_action($hookName,...$args);
    }

    public function remove_action($hookName,$callback,$priority = 10): bool
    {
        return $this->_remove($hookName,$callback,$priority,'action');
    }

    public function remove_filter($hookName,$callback,$priority = 10): bool
    {
        return $this->_remove($hookName,$callback,$priority,'filter');
    }

    private function _remove($hookName,$callback,$priority,$type): bool
    {
        if(is_string($callback) && isset($this->tags[$type][$hookName][$callback])){
            $idx = $this->tags[$type][$hookName][$callback];
            unset($this->$type[$hookName][$priority][$idx]);
            unset($this->tags[$type][$hookName][$callback]);
            return true;
        }
        foreach ($this->$type[$hookName][$priority] ?? [] as $filters){
            foreach ($filters as $index => $filter){
                if(is_callable($filter) && $callback === $filter){
                    unset($this->$type[$hookName][$priority][$index]);
                    return true;
                }else if(is_string($filter)){
                    unset($this->$type[$hookName][$priority][$index]);
                    return true;
                }else if($callback === $filter){
                    unset($this->$type[$hookName][$priority][$index]);
                    return true;
                }
            }
        }
        return false;
    }

    public function remove_all_filter($hookName = null){
        $this->_remove_all($hookName,'filter');
    }

    public function remove_all_action($hookName = null){
        $this->_remove_all($hookName,'action');
    }

    protected function _remove_all($hookName,$type){
        if($hookName == null){
            $this->$type = [];
            $this->tags[$type] = [];
        }else{
            unset($this->$type[$hookName]);
            unset($this->tags[$type][$hookName]);
        }
    }

}