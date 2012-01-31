<?php
/**
 * @desc class wich parse gamespot.com
 * @author makinder
 * @version 0.0.1
 */

class Evil_Parser_GameSpot implements  Evil_Parser_Interface
{
	/**
	 * @desc get infomation about top games
	 * @author makinder
	 * @return array
	 * @example returned array
	 
	 array {
            {'category' =>
                [0] => array {
                                ["name"]    =>  "LIMBO"
                                ["desc"]    =>  "LIMBO is a black-and-white puzzle platforming adventure."
                                ["date"]    =>  "2011-08-02"
                                ["ganre"]   =>  "2D Platforme"
                                ["rating"]  =>  "9.0"
                                ["image"]   =>  "http://image.gamespotcdn.net/gamespot/images//2003/all/boxshots2/635383_218593.jpg"
                              }
                    }
            }
	 ......................................................................................
	 * @version 0.0.1
	 */
	public function parse($what = null)
	{
		$url = 'http://www.gamespot.com/games.html?mode=top&platform=5&type=top_rated';
		$content = file_get_contents($url);
		
		$pos = strpos($content, '<ul class="games">');
        $content = substr($content, $pos);        
        $pos = strpos($content, '<div id="side" class="col side_col">');
        $content = substr($content, 0, $pos);
		        
		$testName = explode('<h3 class="title">', $content);
        unset($testName[0]);        
        $name = array();
        foreach ($testName as $index=>$value)
        {
        	$startPosition = stripos($value, '>');
        	$endPosition = stripos($value, '</');
        	        	
        	$item = substr($value, strpos($value,'>')+1, (($endPosition-4) - $startPosition) );        	
        	$name[] = ltrim($item);
        }
        
        $testDesc = explode('<div class="deck">', $content);
        unset($testDesc[0]);        
		$desc = array();
        foreach ($testDesc as $index=>$value)
        {
        	$startPosition = stripos($value, '>');
        	$endPosition = stripos($value, '.');        	        
        	$item = substr($value, strpos($value,'>')+1, ($endPosition - $startPosition) );        	
        	$desc[] = $item;
        }
        
        $testDate = explode('<li class="first">R',$content);
        unset($testDate[0]);                        
		$date = array();
        foreach ($testDate as $index=>$value)
        {
        	$startPosition = stripos($value, ':');
        	$endPosition = stripos($value, '</') - 1;        	        
        	$item = substr($value, strpos($value,':')+1, ($endPosition - $startPosition) );			
        	$date[] = date('Y-m-d', strtotime($item));        	
        }
        
        $testGanre = explode('<li class="first">Genre: <strong>', $content);
        unset($testGanre[0]);        
		$ganre = array();
        foreach ($testGanre as $index=>$value)
        {        	
        	$position = stripos($value, '</') - 1;        	        
        	$item = substr($value, strpos($value,'A-Z'), $position );        	
        	$ganre[] = $item;
        }
        
        $testRating = explode('<dd class="value"><a class="pc"', $content);
        unset($testRating[0]);        
		$rating = array();
        foreach ($testRating as $index=>$value)
        {
        	$startPosition = stripos($value, '>');        	
        	$endPosition = stripos($value, '</a>') - 1;        	        
        	$item = substr($value, strpos($value,'>')+1, ($endPosition - $startPosition) );        	
        	$rating[] = $item;
        }
        
        $testImage = explode('<img src="', $content);
        unset($testImage[0]);		
        $image = array();
        foreach ($testImage as $index=>$value)
        {        	        	
        	$position = stripos($value, '.jpg');        	        
        	$item = substr($value, strpos($value,'A-Z'), $position+4 );        	
        	$image[] = $item;
        }
        
        $result['top'] = array();
        foreach ($name as $index=>$value)
        {
        	$result['top'][] = array(
                                        "name"  =>  $value,
        								"desc"  =>  $desc[$index],
        								"date"  =>  $date[$index],
        								"ganre" =>  $ganre[$index],
        								"rating"=>  $rating[$index],
        								"image" =>  $image[$index]
        							);
        }
        return ($result);
	}
}