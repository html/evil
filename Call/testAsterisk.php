<?php

include_once 'Asterisk.php';

class testAsterisk
{
    public function __construct()
    {
        //echo 'aaa';
    }

    public function testConnection()
    {
        $asterisk = new Evil_Call_Asterisk();
        $asterisk->Call('89179273515', 'я тестирую астериск');
        return $asterisk;
    }

    public function testConn()
    {
        $server = '192.168.100.106';
        $user = 'admin';
        $pass = 'amp111';

        $oSocket = fsockopen($server, 5038, $errnum, $errdesc) or die("Connection to host failed");
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

            
            /*
            fputs($oSocket, "Action: originate\r\n");
            fputs($oSocket, "Channel: SIP/1001\r\n");
            fputs($oSocket, "WaitTime: 120\r\n");
            fputs($oSocket, "CallerId: open.kzn.ru\r\n");
            fputs($oSocket, "Exten: 89179273515\r\n");
            fputs($oSocket, "Context: sipnet\r\n");
            fputs($oSocket, "Priority: 1\r\n\r\n");
            fputs($oSocket, "Action: Logoff\r\n\r\n"); */
            
           fputs($oSocket, "Action: Originate\r\n");
           fputs($oSocket, "Channel: LOCAL/79179273515@from-internal\r\n");
           fputs($oSocket, "WaitTime: 120\r\n");
           fputs($oSocket, "CallerId: open.kzn.ru\r\n");
           fputs($oSocket, "Exten: s\r\n");
           fputs($oSocket, "Variable: Variable1=��иве�. Э�о а��е�и�к. Я �ме� звони�� и гово�и�� �о ��о мне п�икаж��.\r\n");
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
}
$Ast=new testAsterisk();
$Ast->testConn();

