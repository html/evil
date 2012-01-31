<?php
/**
 * @description Класс для хранения кеша в редисе
 */
class Evil_Cache_Redis implements Evil_Cache_Interface
{
    protected static $_instances = array();

    protected static $_cache = null;

    protected function __construct($params)
    {
        self::$_cache = Zend_Cache::factory(
                'Core',
                'Rediska_Zend_Cache_Backend_Redis',
                array(
                    'lifetime' =>  120,
                    'automatic_serialization' => true,
                    ),
                array(
                    'rediska' => new Rediska()
                    ),
                false,
                true
            );
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
        //var_dump($key);
      //  die();
      //  file_put_contents('/tmp/red.log',$key . PHP_EOL,FILE_APPEND);
        
        if (($result = self::$_cache->load($key)) !== false)
        {
          //  $exportData = var_export($result,true) . PHP_EOL;
          //  file_put_contents('/tmp/red.log',$exportData,FILE_APPEND);
            return $result;
        }
        else
        {
            return null;
        }
    }

    /**
     * @description кладет значение в кеш
     * @param  $key
     * @param  $object
     * @return void
     */
    public function put($key, $object)
    {
    //  var_dump($key);
     //   file_put_contents('/tmp/put.log',var_export($key,true),FILE_APPEND);
      $data = explode(':',$key,2);
      $key = $data[1];

      self::$_cache->save($object, $key);
    }


}