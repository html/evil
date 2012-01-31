<?php
include_once '../bootstrap.php';

class testAsterisk extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {

    }

    public function testRunConv()
    {
        $jodConverter = new Evil_JodConverter();
        $ret = $jodConverter->convert('/tmp/5.txt', '/tmp/5.pdf');
        var_dump($ret);
    }
}
