<?php
/**
 * @throws Exception
 * @author Se#
 * @version 0.0.1
 * @example
 * $json = '{"field" :
 *              {
 *                  "#" : "test field",
 *                  "property" :
 *                  {
 *                      "#value" : "personal for property comment",
 *                      "value"   : "some",
 *
 *                      "#desc" : "property description",
 *                      "desc"   : "some another property"
 *                  }
 *              }
 *          }';
 *
 * $evilJson = new Evil_Json($json, '$');
 * $field = $evilJson->field->toArray(); // if we need exactly array; it will not be contain comments
 *
 * $field => Array('property' => array('value' => 'some'))
 *
 * All fields which marked with $comment will be removed to the additional array.
 * You can get it by Evil_Json::getComments() or ::getComment($field), where $field - full path to the needed field,
 * for example, to get comment for property value (see @example),
 * $comment = $evilJson->getComment('field.property.#value');
 *
 */
class Evil_Json
{
    /**
     * Contain source path if it passed (not json itself)
     *
     * @var string
     */
    protected $_path = '/default.json';

    /**
     * Represent current json-array
     *
     * @var array
     */
    protected $_jsonArray  = array();

    /**
     * Contain comments for current JSON (only for root json)
     *
     * @var array
     */
    protected $_comments   = array();

    /**
     * JSON configuration for current object
     *
     * @var array
     */
    protected $_config = array(
        'cm'       => '$', // comment marker : {"$" : "general comment"}, $ - cm
        'readOnly' => false
    );

    /**
     * Construct an object.
     * Apply path to the json file, json-string, array, object.
     *
     * @throws Exception
     * @param string|array|object $json
     * @param string $commentMarker
     */
    public function __construct($json, $config = array())
    {
        // Detect path to the file
        if(is_string($json) && (strpos('{', $json) === false)){
            if(file_exists($json)){
                $this->_path = $json;
                $json = file_get_contents($json);
            }
            else
                throw new Exception(' Unknown input "' . $json . '" ');
        }

        if(is_string($json))// at this stage string is definitely not a path
            $this->_jsonArray  = json_decode($json, true);
        elseif(is_array($json))
            $this->_jsonArray  = $json;
        else
            $this->_jsonArray  = json_decode(json_encode($json), true);
		
        if (is_null($this->_jsonArray))
        	throw new Exception('Input Json is not valid'. $json);    
        $this->_config = empty($config) ? $this->_config : $config + $this->_config;
        // fetch comments
        $this->_fetchComments();
    }

    /**
     * Convert current json to the String
     *
     * @return string
     */
    public function toString()
    {
        return json_encode($this->_jsonArray);
    }

    /**
     * Convert current json to the object
     *
     * @return stdClass
     */
    public function toObject()
    {
        return json_decode(json_encode($this->_jsonArray));
    }

    /**
     * Return current json as an array
     *
     * @return array|string
     */
    public function toArray()
    {
        return $this->_jsonArray;
    }

    /**
     * Set new value for the property of the current json
     *
     * @param string $field
     * @param string|array|object $value
     * @return null|mixed
     */
    public function __set($field, $value)
    {
        if(true == $this->_config['readOnly'])
            return null;
        
        return isset($this->_jsonArray[$field]) ? $this->_jsonArray[$field] = $value : null;
    }
    
    /**
     * Заменяет текущий Json новым,
     * переданым в качестве массива
     * @param $array
     * @return Successful bool 
     * @author Sergey Bukharov
     */
    public function __setNewJson($array)
    {
        if(true == $this->_config['readOnly'])
            return null;
        
       	if (!is_array($array)) return null;
       	$this->_jsonArray = $array;
       	return true;	
    }

    /**
     * Save current json to the file
     *
     * @param string $path
     * @param bool Save to human friend view
     * @return void
     */
    public function save($path = '', $human_friend_format = false)
    {
        // TODO: save comments too!
        $path = empty($path) ? $this->_path : $path;

        $f = fopen($path, "w+t");
		
        //конвертировать в человекочитаемый вид?
        $json_content = $this->toString();
        if ($human_friend_format){
        	$json_content = $this->indent($json_content);
        }
        return fputs($f, $json_content) && fclose($f) ? true : false;
    }

    /**
     * Get a param from the current json
     *
     * @param string $name
     * @return Evil_Json|null
     */
    public function __get($name)
    {
        return isset($this->_jsonArray[$name]) ? new self($this->_jsonArray[$name]) : null;
    }

    /**
     * Return comments for current json, works only for the root json.
     *
     * @return array
     */
    public function getComments()
    {
        // TODO: pass comments to the children
        return $this->_comments;
    }

    /**
     * Get comment for passed parameter from the current json
     *
     * @param string $fieldPath
     * @return array|null
     */
    public function getComment($fieldPath)
    {
        return isset($this->_comments[$fieldPath]) ? $this->_comments[$fieldPath] : null;
    }

    /**
     * Fetch comments for current json
     *
     * @param string $field
     * @param array|string $object
     * @param string $key
     * @return null
     */
    protected function _fetchComments($field = null, $object = null, $key = '')
    {
        if(empty($object) && empty($field)){// means method is launched first time
            foreach($this->_jsonArray as $attr => $value)
                $this->_jsonArray[$attr] = $this->_fetchComments($attr, $value, $attr);
        }
        else{// means method is launched at least second time
            if(!is_array($object))// string property is reached
                return $object;

            // check for comments
            foreach($object as $name => $value){
                if(substr($name, 0, 1) == $this->_config['cm']){// save comment
                    $this->_comments[$key . '.' . $name] = $object[$name];
                    unset($object[$name]);// delete comment from the json
                }
            }

            $tmp = $object;// for working with the current part of json

            foreach($object as $attr => $subVal)
                $tmp[$attr] = $this->_fetchComments($attr, $subVal, $key. '.' .$attr);

            return $tmp;
        }
    }
    
    /**
     * Форматирует JSON код из портянки в человекочитаемый
     * @param string of JSON $json
     * @author Sergey Bukharov
     */
	protected function indent($json) {
	
	    $result      = '';
	    $pos         = 0;
	    $strLen      = strlen($json);
	    $indentStr   = '  ';
	    $newLine     = "\n";
	    $prevChar    = '';
	    $outOfQuotes = true;
	
	    for ($i=0; $i<=$strLen; $i++) {
	
	        // Смотрим следующий символ в строке
	        $char = substr($json, $i, 1);
	
	        // Are we inside a quoted string?
	        if ($char == '"' && $prevChar != '\\') {
	            $outOfQuotes = !$outOfQuotes;
	        
	        // If this character is the end of an element, 
	        // output a new line and indent the next line.
	        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
	            $result .= $newLine;
	            $pos --;
	            for ($j=0; $j<$pos; $j++) {
	                $result .= $indentStr;
	            }
	        }
	        
	        // Add the character to the result string.
	        $result .= $char;
	
	        // If the last character was the beginning of an element, 
	        // output a new line and indent the next line.
	        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
	            $result .= $newLine;
	            if ($char == '{' || $char == '[') {
	                $pos ++;
	            }
	            
	            for ($j = 0; $j < $pos; $j++) {
	                $result .= $indentStr;
	            }
	        }
	        
	        $prevChar = $char;
	    }
	
	    return $result;
		}    
}