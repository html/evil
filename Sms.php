<?php
/**
 * 
 * Класс для отправки смс сообщений
 * поддерживает разные транспорты
 * @author nur
 *
 * evil.sms.transport ="Evil_Sms_Sms24x7"
    evil.sms.config.email = "example@mail.com"
    evil.sms.config.password = "******"
    evil.sms.config.sender_name = "example sender"
 * $config = array('transport);
 */
class Evil_Sms implements Evil_TransportInterface
{
    /**
     * 
     * траспорт который будет использоваться для отправки смс
     * @var string
     */
    protected $_transport = 'Evil_Sms_Sms24x7';
    /**
     * 
     * настройки траспорта
     * @var array
     */
    protected $_config = array();
    
    
    protected $_transportInstance = null;
    
    /**
     * 
     * Отправка сообщения
     * @param string $to номер телефона, в международном формате 
     * @example 7902xxxxxxx
     * @param string $message
     * @return bool
     * @throws Zend_Exception
     */
    public function send ($to, $message)
    {
        /**
         * ели телефон верный
         */
        if($this->_validate($to))
        {
        /**
         * отправка сообщения через транспорт
         */
         return $this->_transportInstance->send($to, $message);
        }
    }
    /**
     * инициализация транспорта класса
     * @var $config array
     * $config = array(
     * 'transport' => [string], 
     * 'config' = [array]
     * )
     */
    public function init (array $config)
    {
        $this->_config = isset($config['transportconfig']) ? $config['transportconfig'] : array();
        $this->_transport = isset($config['transport']) ? $config['transport'] : $this->_transport;
        
         $this->_transportInstance = new $this->_transport();
         if ($this->_transportInstance instanceof Evil_Sms_Interface) {
            $this->_transportInstance->init($this->_config);
         }else {
            throw new Zend_Exception(
            $this->_transport . ' not instace of Evil_Sms_Interface');
        }
    }
    
     /* валидация номера телефона
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