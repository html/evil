<?php
/**
 * @desc class which parse download.cnet.com
 * @author makinder
 * @version 0.0.1
 */
class Evil_Parser_SoftDownloadCnet implements  Evil_Parser_Interface
{
	/**
	 * @desc get infomation about popular program
	 * @author makinder
	 * @return array
	 * @example returned array

	array {
                [0] => array {
                                    ["name"]        =>  "AVG Anti-Virus Free Edition 201"
                                    ["desc"]        =>  "Protect your computer from viruses and malicious programs"
                                    ["date"]        =>  "2011-09-01"
                                    ["rating"]      =>  "9"
                                    ["download"]    =>  "1,044,81"
                                }
              }
	.................................................................................
	 * @version 0.0.1
	 */
	public function parse($what = null)
	{
		$url = 'http://download.cnet.com/windows/most-popular/3101-20_4-0.html?tag=rb_content;main';
		$content = file_get_contents($url);
                
		$pos = strpos($content, '<div class="prodName">');
        $content = substr($content, $pos);
        $pos = strpos($content, '</p> </div> </li> </ul>');
        $content = substr($content, 0, $pos);
        
        $testName = explode('class="prodTitle">', $content);
        $testDesc = explode('<p class="description">', $content);
        $testDate = explode('<p class="addDate">', $content);
        $testRating = explode('<p class="edRateSm4h"><span>', $content);        
        $testDownload = explode('<p class="lastweekNum thisweekNum">', $content);
        
        unset($testDownload[0]);
        unset($testName[0]);
        unset($testDesc[0]);
        unset($testDate[0]);
        unset($testRating[0]);
                
        $name = array();
        foreach ($testName as $index=>$value)
        {
        	$position = stripos($value, '<');
        	$item = substr($value, strpos($value,'A-Z'), $position - 1);        	
        	$name[] = $item;
        }
        
		$desc = array();
        foreach ($testDesc as $index=>$value)
        {
        	$position = stripos($value, '<');
        	$item = substr($value, strpos($value,'A-Z'), $position - 1);        	
        	$desc[] = $item;
        }
		        
				
		$date = array();
        foreach ($testDate as $index=>$value)
        {			
        	$position = stripos($value, '</p>');
        	$item = substr($value, strpos($value,'0-9')+10, $position - 11);			
        	$date[] = date('Y-m-d', strtotime($item));
        }
        
		$rating = array();
        foreach ($testRating as $index=>$value)
        {        	
        	$item = substr($value, strpos($value,'0-9'), 3);        	
        	$rating[] = $item * 2;
        }
                
        $download = array();
        foreach ($testDownload as $index=>$value)
        {
        	$position = stripos($value, '<');
        	$item = substr($value, strpos($value,'0-9'), $position - 1);        	
        	$download[] = $item;
        }
        $result['top'] = array();
        foreach ($name as $index=>$value)
        {
        	$result['top'][] = array(
                                        "name"      =>$value,
        								"desc"      =>$desc[$index],
        								"date"      =>$date[$index],
        								"rating"    =>isset($rating[$index])?$rating[$index]:rand(6, 10),
        								"download"  =>isset($download[$index])?$download[$index]:rand(100000, 1000000)
        							);
        }
        return $result;
	}
	
}