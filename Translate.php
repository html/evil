<?php
/**
 * @author Se#
 * @version 0.0.1
 */
class Evil_Translate extends Zend_Controller_Plugin_Abstract
{
    protected static $_ses = null;

    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $config = Zend_Registry::get('config');
        if(isset($config['evil']['language']))
            $lang = $config['evil']['language'];
        else
            $lang = 'ru';

        if(is_file(APPLICATION_PATH . '/configs/translate/' . $lang . '.json'))
        {
            self::$_ses = new Zend_Session_Namespace('evil-translate');
            $lang = json_decode(file_get_contents(APPLICATION_PATH . '/configs/translate/' . $lang . '.json'), true);
            self::$_ses->lang = $lang;
        }
    }

    public static function a($word)
    {
        if(!self::$_ses)
            self::$_ses = new Zend_Session_Namespace('evil-translate');

        if(isset(self::$_ses->lang[$word]))
            return self::$_ses->lang[$word];

        return $word;
    }
}