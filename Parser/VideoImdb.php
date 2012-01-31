<?php
/**
 * @desc class which working for IMDB.com
 * @author makinder
 * @version 0.0.1
 */

class Evil_Parser_VideoImdb implements   Evil_Parser_Interface
{
	/**
     * @desc what we need(top|new|tv)
     */
    protected $_whatWeNeed = '';


    /**
	 * @desc parse IMDB.com
	 * @author makinder
	 * @param string $whatWeNeed(new|top|tv);
	 * @return Array
	 * @version 0.0.1
	 */
	public function parse($whatWeNeed = null)
	{
        $this->_whatWeNeed = $whatWeNeed;

        switch ($this->_whatWeNeed)
        {
            case 'new': $url['new'] = 'http://www.imdb.com/nowplaying/'; break;
            case 'top': $url['top'] = 'http://www.imdb.com/chart/';break;
            case 'tv' : $url['tv']  = 'http://www.imdb.com/search/title?num_votes=5000,&sort=user_rating,desc&title_type=tv_series';break;
            case null : $url        = array(
                                        'new' => 'http://www.imdb.com/nowplaying/',
                                        'top' => 'http://www.imdb.com/chart/',
                                        'tv'  => 'http://www.imdb.com/search/title?num_votes=5000,&sort=user_rating,desc&title_type=tv_series'
                                    );break;
            default   : throw new Exception('we get undefined format'.$this->_whatWeNeed);break;
        }

        $result = $this->_findId($url);
        return $this->_getDescription($result);
    }


    /**
     * @desc find id
     * @author makinder
     * @param string|array $url;
     * @version 0.0.1
     */

    protected function _findId($urls)
    {
 		if(!is_array($urls))
             throw new Exception('url must be an array'.$urls);

        foreach($urls as $what=>$url)
        {
            $total_info = file_get_contents($url);
            $dom = new Zend_Dom_Query($total_info);
            $links = $dom->query('a');

            $data = array();
            foreach ($links as $item)
                $data[] = $item->getAttribute('href');

            $find = '/title/tt';
            $result = array();
            foreach ($data as $index=>$value)
            {
                if (strpos($value, $find) !== false)
                {
                    $item = substr($value, strpos($value, '/tt')+1, 9);
                    $result[] = $item;
                }
            }

            $request = array();
            $hoho[$what] = array_unique($result);
        }

        return $hoho;
	}


    /**
     * @desc get personal infomation
     * @author makinder
     * @param array $hoho;
     * @return array
     * @example returned array

	 array(26) {
  				[0] => array(15)
  							{
							    ["Title"] 		=> "Drive"
							    ["Year"] 		=> "2011"
							    ["Rated"] 		=> "N/A"
							    ["Released"] 	=> "2011-05-24"
							    ["Genre"] 		=> "Action, Drama"
							    ["Director"] 	=> "Nicolas Winding Refn"
							    ["Writer"] 		=> "Hossein Amini, James Sallis"
							    ["Actors"] 		=> "Ryan Gosling, Carey Mulligan, Bryan Cranston, Christina Hendricks"
							    ["Plot"] 		=> "A Hollywood stunt performer who moonlights as a wheelman discovers that a contract has been put on him after a heist gone wrong."
							    ["Poster"] 		=> "N/A"
							    ["Runtime"] 	=> "1 hr 35 mins"
							    ["Rating"] 		=> "N/A"
							    ["Votes"] 		=> "N/A"
							    ["ID"] 			=> "tt0780504"
							    ["Response"] 	=> "True"
							  }
				}
	 ..................................................................................................................................
     * @version 0.0.1
     */
    protected function _getDescription($hoho)
    {
        if(!is_array($hoho))
            throw new Exception('we don`t get array contained id');

        foreach ($hoho as $category=>$content)
		{
            foreach($content as $index=>$param)
            {
                $requesturl = 'http://www.imdbapi.com/?t=' .$param;
			    $request[] = json_decode(file_get_contents($requesturl),true);
            }

            foreach ($request as $index=>$mas)
            {
                if($request[$index]['Response'] == 'True')
                    $request[$index]['Released'] = date("Y-m-d", strtotime($mas['Released']));
                
                else
                    unset($index);

            }
            $result[$category] = $request;
		}
		return $result;
    }

}