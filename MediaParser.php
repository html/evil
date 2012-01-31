<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bukharov Sergey
 * Date: 14.09.11
 * Time: 20:36
 *
 * Управление парсерами
 * @desc Ищет файлы, соотвествующие категории контента,
 * если таковая задана и запускает их.
 *
 */
 
class Evil_MediaParser
{
    protected $classNamePrefix = 'Evil_Parser_';

    protected $directoryForParsers;
    //имена файлов с парерами
    private $parserFiles = array();

    //необходимые для текущего парсинга парсеры
    private $classParser = array();


    function __construct()
    {
        $this->directoryForParsers = __DIR__ . '/Parser';

        $this->_getFilesWithParsers();
    }

    /**
     * Распарсить контент с медиаСайтов
     * @desc Запускает парсеры, парсеры можно ограничить
     * арегментами
     * @param null $contentType (Video, Game, Music, Soft, ...)
     * @param null $category (top, new and etc..) into concret parser
     * @return array of 'content_type_1' => [
     *                                'category_1_name' => [
     *                                          '0' or 'name' =>
     *                                                      ['name' => name
     *                                                      'desc' => desc]
     *                                          '1' or 'name' =>
     *                                                      ['name' => name
     *                                                      'desc' => desc]
     *                                           ]
     *                                 'category_2_name' => [
     *                                          '0' or 'name' =>
     *                                                      ['name' => name
     *                                                      'desc' => desc]
     *                                          '1' or 'name' =>
     *                                                      ['name' => name
     *                                                      'desc' => desc]
     *                                           ]
     *                                      ]
     *                    'content_type_2' => [....]
     *
     */
    public function parse($contentType = null, $category = null)
    {
        //если парсеры для данного типа контента не найдены
        if (! $this->_getParsersClass($contentType)) return false;

        $category = strtolower($category);
        
        $content = array();
        //инстанцирование и запуск парсеров
        foreach ($this->classParser as $parser){
            $obj = new $parser();
            $res = $obj->parse($category);

            $content[$this->_getParserType($parser)] = $res;
        }
        return $content;
    }

    /**
     * Найти файлы, содержащие все парсеры
     * @throws Exception
     * @return array|null parsers file names
     */
    protected function _getFilesWithParsers()
    {
        $handle = opendir($this->directoryForParsers);
        if (!$handle)
            throw new Exception('Dir not exist');

        while (false !== ($fileName = readdir($handle))) {
            //пропустим заведомо не файлы (. и ..) и интерфейсы
            if (strlen($fileName) < 4 || strstr($fileName, 'Interface'))
                continue;

            array_push($this->parserFiles, $fileName);
        }
        if (empty($this->parserFiles)) return null;
        return $this->parserFiles;
    }

    /**
     * Найти в файлах необходимые для переданного Типа контента парсеры
     * Если Контент не передан,
     * @throws Exception
     * @param null $contentType
     * @return null|array of Parsers name
     */
    protected function _getParsersClass($contentType = null)
    {
        foreach ($this->parserFiles as $key => $parser) {
            //если парсер не нужного типа
            if ((!is_null($contentType)) && (stripos($parser, $contentType) === false))
                continue;

            list($className, $ext) = explode('.', $parser);
            $className = $this->classNamePrefix . $className;
            
            if (!class_exists($className))
                throw new Exception("Parser $className not exist, but file $parser is found");

            if (! in_array('Evil_Parser_Interface', class_implements($className))){             //не имплементирует интерфейс
                trigger_error('Class '. $className . " must be implements Evil_Parser_Interface.", E_USER_WARNING);
                continue;
            }
            
            array_push($this->classParser, $className);
        }

        if (empty($this->classParser)) return null;
        return $this->classParser;
    }

    protected static function _getParserType($parser_name)
    {
        preg_match('/^Evil_Parser_([A-Z][a-z]*)/', $parser_name ,$type);
        $type = strtolower($type[1]);

        if (empty($type)) return new Exception('Не могу определить тип парсера '. $parser_name);

        return $type;
    }
}