<?php
/**
 * @description navigation
 * @author Se#
 * @version 0.0.1
 */
class Evil_Navigation
{
    /**
     * @description configuration
     * @var array
     * @author Se#
     * @version 0.0.1
     */
    protected $_config = array();

    /**
     * @description basic constructor
     * @param null $config
     * @author Se#
     * @version 0.0.1
     */
    public function __construct($config = null)
    {
        if(is_string($config) && is_file($config))
            $this->_config = json_decode(file_get_contents($config), true);
        elseif(is_array($config))
            $this->_config = $config;
    }
}