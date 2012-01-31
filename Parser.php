<?php
/**
 * @description parser as it is
 * @author Se#
 * @version 0.0.3
 * @changeLog
 * 0.0.3 insertString method and self::$urls are added
 * 0.0.2 see links()
 */
class Evil_Parser
{
    /**
     * @description known url for searching
     * @var array
     * @author makinder, Se#
     * @version 0.0.1
     */
    public static $urls = array(
        'google'  => 'http://www.google.ru/search?source=ig&hl=ru&rlz=&q=$string&aq=f&aqi',
        'yandex'  => 'http://yandex.ru/yandsearch?text=$string&lr=11000',
        'rambler' => 'http://nova.rambler.ru/search?btnG=%D0%9D%D0%B0%D0%B9%D1%82%D0%B8%21&query=$string',
        'mail'    => 'http://go.mail.ru/search?mailru=1&drch=e&q=$string&rch=e'
    );

    /**
    * @description Searches for a line in the searcher
    * @param string $lookIn
    * @param string $searchString ('google','yandex','rambler','mail')
    * @author makinder, Se#
    * @version 0.0.2
     * @changeLog
     * 0.0.2 configurable urls
    */
    public function links($resource, $string, $urls = array())
    {
        $urls = empty($urls) ? self::$urls : $urls;
        $urls = self::insertString($urls, $string, '$string');

        if(isset($urls[$resource]))
            $url = $urls[$resource];
        else
            return false;

        $dom = new Zend_Dom_Query(file_get_contents($url));

        return $dom->query('a');
    }

    /**
     * @description insert a string into text
     * @static
     * @param array $texts
     * @param string $string
     * @param string $label
     * @return array
     * @author Se#
     * @version 0.0.1
     */
    public static function insertString(array $texts, $string, $label = '$string')
    {
        foreach($texts as $index => $text)
            $texts[$index] = str_replace($label, $string, $text);

        return $texts;
    }
}