<?php
/**
 *
 */
class Evil_Cache_Pull implements Evil_Cache_Interface
{

    protected static $_instances = array();

    protected $_pull = array();

    protected function __construct($params)
    {
        ;
    }

    /**
     * @description этот класс - синглтон
     * @static
     * @param  $params
     * @return self
     */
    public static function getInstance($params)
    {
        $hash = Evil_Cache2::getHash($params);

        if (!isset(self::$_instances[$hash]))
        {
           self::$_instances[$hash] = new self($params);
        }

        return self::$_instances[$hash];
    }

    /**
     * @description вынимает значение из кеша
     * @param  $key
     * @return array|null
     */
    public function get($key)
    {
         return isset($this->_pull[$key]) ? $this->_pull[$key] : null;
    }

    /**
     * @description кладет значение в кеш
     * @param  $key
     * @param  $object
     * @return void
     */
    public function put($key, $object)
    {
        $this->_pull[$key] = $object;
    }
}