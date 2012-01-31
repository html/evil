<?php
/**
 * @description Works with Redmine's issues
 * @throws Exception
 * @author Se#
 * @version 0.0.2
 * @changeLog
 * 0.0.2 see __construct() v.0.0.2
 */
class Evil_Rest_Redmine_Issue extends Evil_Rest_Client
{
    /**
     * @description default url
     * @var string
     * @author Se#
     * @version 0.0.1
     */
    protected $_site = 'http://redmine.teamrocketscience.ru/issues.';

    /**
     * @description default format
     * @var string
     * @author Se#
     * @version 0.0.1
     */
    protected $_requestFormat = 'json'; // REQUIRED!

    /**
     * @description set issue args
     * @throws Exception
     * @param array $args
     * @param string $key
     * @param bool $request
     * @author Se#
     * @version 0.0.2
     * @changeLog
     * 0.0.2 general/personal configs
     */
    public function __construct($args, $key, $request = false)
    {// get keys configuration
        $personal = APPLICATION_PATH . '/configs/redmine/keys.json';
        $general  = __DIR__ . '/default.json';

        $keys = array();

        if(is_file($personal))
            $keys = json_decode(file_get_contents($personal), true);
        elseif(is_file($general))
            $keys = json_decode(file_get_contents($personal), true);

        // if access is private, check rights for the current key
        if(isset($keys['private']) && $keys['private'] && isset($keys['keys']) && !isset($keys['keys'][$key]))
            throw new Exception(' Access denied ');

        if(isset($args['url']))
        {
            $this->_site = $args['url'];
            unset($args['url']);
        }

        $data = array(
            'url'    => $this->_site . $this->_requestFormat . '?key=' . $key,
            'params' => array('issue' => $this->_getParams($keys, $args))
        );

        parent::__construct($data, $request);
    }

    /**
     * @description get all needed parameters
     * @param array $keys
     * @param array $args
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    protected function _getParams($keys, $args)
    {
        $default = array();
        if(isset($keys['default']))
            $default = $keys['default'];

        $need  = isset($keys['need'])
                ? $keys['need']
                : array('project_id', 'subject', 'description', 'tracker_id', 'assigned_to_id');
        
        $data  = array();
        $count = count($need);
        for($i = 0; $i < $count; $i++)
        {
            $data[$need[$i]] = isset($args[$need[$i]]) ?// is there an attribute in the passed arguments?
                    $args[$need[$i]] :// yes, it is - get it
                    (isset($default[$need[$i]]) ?// no. Is there a default value for that attribute?
                            $default[$need[$i]] :// yes, get it
                            '');// no, pity
        }

        return $data;
    }
}