<?php

interface Evil_Cache_Interface
{
    public static function getInstance($params);

    public function put($key, $object);

    public function get($key);
}