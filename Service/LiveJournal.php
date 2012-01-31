<?php
/**
 * @type Service
 * @description: Livejournal poster
 * @package Evil
 * @subpackage Services
 * @author Sergey Chuprunov
 * @version 0.0.1
 */
    class Evil_Service_LiveJournal
{
    public static function post($message)
    {
        // получаем конфиг из application.ini
        try
        {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'lj');
        }
        catch (Exception $e)
        {
            throw new Exception('Config load error. Check your application.ini. ' . $e);
        }
        // создаем новый объект класса Zend_XmlRpc_Client и передаем ему адрес сервера
	    $client = new Zend_XmlRpc_Client('http://www.livejournal.com/interface/xmlrpc');
	    // получаем объект прокси, при этом передаем ему пространство имен
	    $proxy = $client->getProxy('LJ.XMLRPC');

        try
        {
	        //получаем challenge для авторизации
	        $challenge = $proxy->getchallenge();
	    }
        catch(Exception $e)
        {
	        throw new Exception('Connection fail. Get challenge fail. ' . $e);
	    }

	    $data = array
        (
	        'username'       =>$config->username,
	        'auth_method'    =>$config->auth_method,
	        'auth_challenge' =>$challenge['challenge'],
	        'auth_response'  =>md5($challenge['challenge'].md5($config->password)),
	        'ver'            =>$config->protocol_version,
	        'lineendings'    =>$config->lineendings,
	        'subject'        =>$message['title'],
	        'event'          =>$message['text'],
	        'day'            =>date('d'),
	        'mon'            =>date('m'),
	        'year'           =>date('Y'),
	        'hour'           =>date('H'),
	        'min'            =>date('i'),
	        'security'       =>'public',
	        'props'          =>array
                            (
	                            'opt_preformatted'=>true,
	                            'opt_backdated'=>true,
	                            'taglist'=>$message['tags']								
	                        ),
			'usejournal' 	=>$config->community
	    );

	    //отправляем данные на сервер
	    try
        {
	        $p_data = $proxy->postevent($data);
	    }
        catch(Exception $e)
        {
	        throw new Exception('Post failed. ' . $e);
	    }

	    /*
	    если все нормально, то сервер вернет структуру с 3-мя переменными:
	    itemid - идентификатор поста
	    url - URL-адрес поста
	    anum - аутентификационный номер, созданный для этой записи
	    */
        if (empty($p_data))
        {
            echo 'Livejournal connection failed.';
            return $p_data;
        }
        else
        {
           return $p_data;
        }

    }
}