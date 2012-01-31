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
class Evil_Call_Asterisk implements Evil_TransportInterface
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


     public static function Call ($phone, $messageToSay)
    {
//        if (self::_validate($phone))
//        {
//            $tmpFile = tempnam('', __CLASS__ . '_');
            /**
             * перегоняем текст в mp3
             */
//            if (Evil_Speech::textToSpeech($messageToSay, $tmpFile)) {
                /**
                 *
                 * перегоняем mp3 в wav
                 * @var string
                 */
//                $prepearedFile = self::_prepareFile($tmpFile);
                /**
                 * если файл сконвертился успешно пытаемся позвонить
                 */
//                if (false !== $prepearedFile)
//                {
                    /**
                     * делаем звонок
                     */

                   self::_CallAsterisk($phone);
                    
                    /**
                     * удаляем файл
                     */
//                    unlink($prepearedFile);
//                }
//                else
//                {
///                    Evil_Log::log(
//                    __CLASS__ . ': не смогли сконвертировать mp3 в wav',
//                    Zend_Log::CRIT);
//                    return false;
//                }
//            }
//            else
//            {
//                Evil_Log::log(
//                __CLASS__ .
//                 ': не смогли сконвертировать текст в звуковое сообщение',
//                Zend_Log::CRIT);
//                return false;
//            }
//        }
    }

     /**
     * Соединяемся с Asterisk для совершения звонка
     * @return void
     */
    protected function _CallAsterisk($phone)
    {
//        var_dump(self::$_config);
        //echo 'i connect to:' . self::$_config['server'] . ' user:' . self::$_config['username'] . ' pass:' . self::$_config['password'] . PHP_EOL;

        $oSocket = fsockopen(self::$_config['server'], 5038, $errnum, $errdesc) or die("Connection to host failed");
        fputs($oSocket, "Action: login\r\n");
        fputs($oSocket, "Events: off\r\n");
        fputs($oSocket, "Username: " . self::$_config['username'] ."\r\n");
        fputs($oSocket, "Secret: " . self::$_config['password'] ."\r\n\r\n");

        fputs($oSocket, "Action: Originate\r\n");
        fputs($oSocket, "Channel: SIP/1001\r\n");
//       fputs($oSocket, "Channel: LOCAL/".$phone."@from-internal\r\n");
        fputs($oSocket, "WaitTime: ".self::$_config['waitTime']."\r\n");
        fputs($oSocket, "Exten: s\r\n");
        fputs($oSocket, "Context: default\r\n");
        fputs($oSocket, "Priority: 1\r\n\r\n");


        fputs($oSocket, "Action: Logoff\r\n\r\n");
        while (!feof($oSocket)) {
            echo fgets($oSocket, 128);
        }
        fclose($oSocket);
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

     /**
     * подготовка файла
     * перегонка из mp3 в wav например
     * @param string $file
     * @return string $file
     */
    private static function _prepareFile ($file)
    {
        $lameCmd = 'lame --decode --mp3input ' . escapeshellarg($file);
        ob_start();
        system($lameCmd, $status);
        $output = ob_get_clean();
        unlink($file);
        if (0 == $status) {
            return $file . '.wav';
        }
        return false;
    }
}
