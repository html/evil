<?php
/**
 * @description Simplify for a registration action
 * @author Se#
 * @version 0.0.7
 * @changeLog
 * 0.0.7 see dispatch() v.0.0.4
 * 0.0.6 error log
 * 0.0.5 confirmPassword()
 * 0.0.4 notEmpty()
 * 0.0.3 see dispatch() v.0.0.2
 * 0.0.2 dispatch()
 */
class Evil_Registration
{
    /**
     * @description config extension
     * @var string
     * @author Se#
     * @version 0.0.1
     */
    public static $cfgExtension = 'json';

    /**
     * @description registration configuration
     * @var array|mixed
     * @author Se#
     * @version 0.0.1
     */
    protected $_cfg = array();

    /**
     * @description registration form
     * @var null
     * @author Se#
     * @version 0.0.1
     */
    protected $_form = null;

    /**
     * @description errors list
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    public $errors = array();

    /**
     * @throws Exception
     * @param string $path
     * @author Se#
     * @version 0.0.1
     */
    public function __construct($path = '')
    {
        $path = $path ? $path : APPLICATION_PATH . '/configs/forms/registration.' . self::$cfgExtension;
        if(!is_file($path))
            throw new Exception('Missed configuration for a registration');
        // todo fix by extension
        $this->_cfg = json_decode(file_get_contents($path), true);
    }

    /**
     * @description make registration form
     * @throws Exception
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function makeForm()
    {
        if(isset($this->_cfg['form']))
            $this->_form = new Zend_Form($this->_cfg['form']);
        else
            throw new Exception('Missed registration form configuration');
    }

    /**
     * @description echo or return form
     * @param bool $return
     * @return bool|object
     * @author Se#
     * @version 0.0.1
     */
    public function form($return = false)
    {
        if($return)
            return $this->_form;
        else
            echo $this->_form;

        return true;
    }

    /**
     * @description add CAPTCHA to a form
     * @param string $captchaName
     * @param array $args
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function useCaptcha($captchaName, $args)
    {
        if(method_exists('Evil_Captcha_' . ucfirst($captchaName), 'challenge'))
        {
            // todo 
        }
    }

    /**
     * @description dispatch the request for a registration
     * @param array $params
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     * @author Se#
     * @version 0.0.4
     * @changeLog
     * 0.0.4 log db and validation errors
     * 0.0.3 log errors, accept error messages for filters
     * 0.0.2 return bool
     */
    public function dispatch(array $params, $request)
    {
        if ($request->isPost())
        {
            $this->makeForm();
            if($this->_form->isValid($params))
            {
                $result = $params;

                if(isset($this->_cfg['dispatch']['filters']) && is_array($this->_cfg['dispatch']['filters']))
                {
                    $count  = count($this->_cfg['dispatch']['filters']);

                    for($i = 0; $i < $count; $i++)
                    {
                        $result = call_user_func_array($this->_cfg['dispatch']['filters'][$i], array($result));
                        if(!$result || is_string($result))
                        {
                            $this->error($this->_cfg['dispatch']['filters'][$i], $result);
                            return false;
                        }
                    }
                }

                if(!$this->_db($result))
                {
                    $this->error('Db', 'Can not create a row');
                    return false;
                }
            }
            else
            {
                $this->error('Form validation', 'Invalid data');
                return false;
            }
        }
        else
            return false;

        return true;
    }

    /**
     * @description log inside errors
     * @param string $from
     * @param string $message
     * @return void
     * @author Se#
     * @version 0.0.1
     */
    public function error($from, $message)
    {
        $this->errors[] = array(
            'failed'  => $from,
            'message' => $message,
            'time'    => time()
        );
    }

    /**
     * @description place data into a DB
     * @param array $result
     * @return bool
     * @author Se#
     * @version 0.0.1
     */
    protected function _db($result)
    {
        if(isset($this->_cfg['dispatch']['db']) && is_array($this->_cfg['dispatch']['db']))
        {
            $result = $this->_clear($result);
            if(isset($this->_cfg['dispatch']['db']['tableName']))
            {
                $table = new Zend_Db_Table(Evil_DB::scope2table($this->_cfg['dispatch']['db']['tableName']));
                $table->insert($result);
            }
            else
                return false;
        }

        return true;
    }

    /**
     * @description clear params from sys-info
     * @param array $params
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _clear(array $params)
    {
        if(isset($params['action']))
            unset($params['action']);

        if(isset($params['controller']))
            unset($params['controller']);

        if(isset($params['module']))
            unset($params['module']);

        if(isset($params['submit']))
            unset($params['submit']);

        return $params;
    }

    /**
     * @description check for an emptiness
     * @static
     * @param array $params
     * @return array|bool
     * @author Se#
     * @version 0.0.2
     * @changeLog
     * 0.0.2 return message on error
     */
    public static function notEmpty(array $params)
    {
        foreach($params as $i => $value)
        {
            if(empty($value))
                return 'Empty "' . $i . '"';
        }

        return $params;
    }

    /**
     * @description check password confirmation
     * @static
     * @param array $params
     * @return array|bool
     * @author Se#
     * @version 0.0.2
     * @changeLog
     * 0.0.2 return message on error
     */
    public static function confirmPassword(array $params)
    {
        if(isset($params['password']) && isset($params['confirmPassword']))
        {
            if($params['password'] == $params['confirmPassword'])
            {
                unset($params['confirmPassword']);
                return $params;
            }
            else
                return 'Password and password confirmation is different';
        }

        return $params;
    }
}