<?php

include_once '../bootstrap.php';

class testAsterisk //extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        //echo 'aaa';
    }

    public function testConnection()
    {
        $asterisk = new Evil_Call_Asterisk();
        $asterisk->Call('89178962496', 'я тестирую астериск');
        return $asterisk;
    }

    public function testSevenDays()
    {
        $asterisk = new Evil_Call_Asterisk();
        $r = $asterisk->sevenDays('89179273515');
        var_dump($r);
        return $r;
    }

    public function testConn()
    {

        $server = '192.168.100.106';
        //$server = '192.168.1.188';
        $port = "5038";
        $user = 'admin';
        $pass = 'amp111';
        //$phone = "89178962496";
        $phone = "89179273515";
        $oSocket = fsockopen($server, $port, $errnum, $errdesc) or die("Connection to host failed");
        if (!$oSocket)
        {
            echo $errdesc . '(' . $errnum . '.)'. PHP_EOL;
        }
        else
        {
            echo 'conn ok', PHP_EOL;


            fputs($oSocket, "Action: login\r\n");
            fputs($oSocket, "Events: on\r\n");
            fputs($oSocket, "Username: " . $user ."\r\n");
            fputs($oSocket, "Secret: " . $pass ."\r\n\r\n");

            fputs($oSocket, "Action: Originate\r\n");

            fputs($oSocket, "Channel: LOCAL/".$phone."@from-internal\r\n");
          //  fputs($oSocket, "Channel: SIP/1001\r\n");
            fputs($oSocket, "WaitTime: 120\r\n");
            fputs($oSocket, "CallerId: open.kzn.ru\r\n");
            fputs($oSocket, "Exten: s\r\n");
            fputs($oSocket, "Variable: Variable1=Привет. Это астериск. Я умею звонить и говорить то что мне прикажут.\r\n");

            fputs($oSocket, "Context: default\r\n");

            fputs($oSocket, "Priority: 1\r\n\r\n");


            fputs($oSocket, "Action: Logoff\r\n\r\n");
            while (!feof($oSocket))
            {

                echo fgets($oSocket, 128);
            }
            fclose($oSocket);
        }

        var_dump('aaa');
    }

    public function testEvilCall()
    {
        $evilCall = new Evil_Call();
        $evilCall->Call('89179273515', 'привет я киберзло мухахахаха');
    }
}


$a = new testAsterisk();
$a->testSevenDays();
var_dump($a);
