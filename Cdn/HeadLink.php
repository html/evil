<?php
/**
 * Заменяет адреса CSS файлов,
 * прописанных в application.ini на адреса файлов в сети CDN 
 * 
 * @author Sergey Bukharov
 *
 */

class Evil_Cdn_HeadLink extends Zend_View_Helper_HeadLink
{
	public function toString($indent = null)
	{
		$strings = parent::toString($indent);
		
		$headbase = new Evil_Cdn_HeadBase('css');
		return $headbase->toString($strings);	
	}
}