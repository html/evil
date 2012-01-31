<?php
/**
 *
 * Класс для осуществления звонков по VOIP через Asterisk
 *
 * @depends asterisknow server
 * @depends lame
 * @author Adel Shigabutdinov
 *
 */
class Evil_Call_Asterisk// implements Evil_TransportInterface
{
    protected static $_config = array();

    public function __construct()
    {
        $confFile = pathinfo(__FILE__, PATHINFO_DIRNAME) . '/asterisk.json';
        self::$_config = Zend_Json_Decoder::decode(file_get_contents($confFile));
    }

    /**
     * инит траспорта
     * @param array $config
     */
    public function init(array $config)
    {

    }


    /**
     * функция отправки сообщения
     * @param string $to
     * @param string $message
     */
    public function send($to, $message)
    {
        // TODO: Implement send() method.
    }


    /**
     * @static
     * @param  $phone
     * @param  $messageToSay
     * @return array
     */
    public static function Call ($phone, $messageToSay)
    {
        //var_dump(self::$_config);
        $result = false;
        $oSocket = fsockopen(self::$_config['server'], self::$_config['port'], $errnum, $errdesc);

        if (!$oSocket)
	    return false;
        else
        {
            fputs($oSocket, "Action: login\r\n");
            fputs($oSocket, "Events: off\r\n");
            fputs($oSocket, "Username: " . self::$_config['username'] . "\r\n");
            fputs($oSocket, "Secret: " . self::$_config['password'] . "\r\n\r\n");

            fputs($oSocket, "Action: Originate\r\n");
            fputs($oSocket, "Channel: LOCAL/" . $phone . "@from-internal\r\n");
//            fputs($oSocket, "Channel: SIP/1002\r\n");
            fputs($oSocket, "WaitTime: " . self::$_config['wait'] ."\r\n");
            fputs($oSocket, "CallerId: open.kzn.ru\r\n");
            fputs($oSocket, "Exten: s\r\n");
            fputs($oSocket, "Variable: Variable1=". $messageToSay ."\r\n");
            fputs($oSocket, "Context: default\r\n");
            fputs($oSocket, "Priority: 1\r\n\r\n");
	    
            fputs($oSocket, "Action: Logoff\r\n\r\n");
	    $data='';
            while (!feof($oSocket))
            {
                $data.= fgets($oSocket, 128);
            }
	    $result=(preg_match('/Originate succes/',$data)==1);
            fclose($oSocket);
        }

        return $result;

    }

     /**
     *
     * Звонок для сообщения что осталось жить 7 дней
     * @author NuR
     * @param string $phone
     * @return bool
     */

    public static function sevenDays ($phone)
    {
        return self::Call($phone, 'Тебе осталось жить 7 дней');
    }

     /**
     * валидация номера телефона
     * @param string $phoneNumber
     * @return bool
     */
    private function _validate ($phoneNumber)
    {
        $pattern = '/^([0-9]+)([0-9]+)$/';
        $vlidator = new Zend_Validate_Regex($pattern);
        return $vlidator->isValid($phoneNumber);
    }




}
