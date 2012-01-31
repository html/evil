<?php
/**
 * Заменяет адреса JS файлов,
 * прописанных в application.ini на адреса файлов в сети CDN 
 * 
 * @author Sergey Bukharov
 *
 */

class Evil_Cdn_HeadScript extends Zend_View_Helper_HeadScript
{
	public function toString($indent = null)
	{
		$strings = parent::toString($indent);
		
		$headbase = new Evil_Cdn_HeadBase('js');
		return $headbase->toString($strings);	
	}
	
}