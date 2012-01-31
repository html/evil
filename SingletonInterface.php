<?php
interface Evil_SingletonInterface
{
    public static function getInstance();

    function __construct();

    function __clone();
}