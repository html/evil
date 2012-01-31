<?php

/**
 * Класс который будет кешировать
 */
class Evil_Cache2
{
    /**
     * @description Получение объекта из кеша
     * @param $hash - md5 hash объекта
     * @static
     * @return object
     */

    public static function get($hash)
    {
        $data = explode(':', $hash,2);

        $back = $data[0];
        $hash = $data[1];

        $backend = $back::getInstance(array());
      //  file_put_contents('/tmp/hash.log', var_export($hash,true) . PHP_EOL, FILE_APPEND);
        return $backend->get($hash);
    }

    /**
     * @description Помещает объект в кеш     *
     * @param $object - объект
     * @static
     * @return md5 hash объекта
     */
    public static function put(&$object,$hash = null)
    {
      //  var_dump($hash);
        $hash = (null == $hash) ? self::getHash(&$object) : $hash;

       // var_dump($object);
        //die('23');
        $data =explode(':' ,$hash,2);
        $key = $data[1];
        $backend = self::_getBackend(&$object);
       // var_dump($backend);
       // die();
        $backend->put($key,&$object);
       // self::_saveToCache($hash, &$object, $backend);
    }

    protected static function _getBackendClass(&$object)
    {

       // file_put_contents('/tmp/getbackend.log',var_export($object,true),FILE_APPEND);

        if (gettype($object) == 'array' || gettype($object) == 'string')
        {
            return 'Evil_Cache_Redis';
        }

        return 'Evil_Cache_Pull';
    }

    protected static function _getBackend(&$object)
    {
       $backendClass = self::_getBackendClass(&$object);
       return $backendClass::getInstance(array());
    }

    /**
     * @description Сохраняет в кеше хеш объекта как ключ, объект как значение
     * @param $hash - md5 хеш объекта
     * @param $object - объект
     * @param $backend - бекенд
     * @return bool
     */
    protected function _saveToCache($hash, &$object, &$backend)
    {
        $backend->put($hash, &$object);
        return true;
    }


    /**
     * @static
     * @param  $object
     * @return string
     */
    public static function getHash(&$object)
    {
        $backend = self::_getBackendClass(&$object);
        return $backend . ':' . md5(json_encode($object, true));
        //return $backend . ':' . md5(serialize($object));
    }

}