<?php
interface Evil_Singleton {
    function __construct();
    function __clone();
    public static function getInstance();
}