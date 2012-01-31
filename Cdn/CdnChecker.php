<?php
/**
 * Переключатель CDN
 * Проверяет на доступность CDN адреса,
 * в случае проболемм если определены другие CDN провайдеры - переключается на них,
 * если не определены - отключает текущую CDN до следующий проверки
 * 
 * @author Sergey Bukharov
 *
 */

class Evil_Cdn_CdnChecker
{
	/**
	 * Адреса CDN провайдеров
	 * @var array
	 */
	private $_config;
	
	/**
	 * Путь к адресам CDN провайдеров
	 * @var String
	 */
	private $cdn_json_path = "../ocweb/application/configs/cdn.json";
	
	private $_json;
	
	public function __construct()
	{
		$json = $this->_getConfig();		
	}
	
	/**
	 * Получает настройки CDN плагина с адресами
	 * @throws Invalid json
	 * @throws Input file does not exist
	 */
	public function _getConfig()
	{
		$json = new Evil_Json($this->cdn_json_path);
		$this->_config = $json->toArray();
		$this->_json = $json;
		
/*		$path = "../ocweb/application/configs/cdn.json";
		$json_content = file_get_contents($path);
		$json = new Zend_Json();
		$this->_config = $json->decode($json_content);	
		return $json;*/
	}
	
	/**
	 * Проверяет CDN на доступность
	 * Если не доступен - переключает на другой или вырубает
	 * TODO если появится логирование, добавить в лог инфомрацию по поводу отключения серверов CDN
	 */
		public function checkCdn(){
			//Пробегаемся по адресам для замены
			foreach ($this->_config as &$content_type) {
				foreach ($content_type as &$numb) {	
					
					//если отключено пользователем не проверяем на доступность и не включаем автоматом
					if ('disabled' == strtolower($numb['actual_cdn'])){
						continue;
					}
					
					//если имеем один адрес CDN
					if (is_string($numb['cdn_address'])) {
						if ($this->checkAdress($numb['cdn_address'])) {
							$numb['actual_cdn'] = 1;
						} else {
							$numb['actual_cdn'] = 0;
						}
					}
					
					//если имеем несколько адресов CDN
					if (is_array($numb['cdn_address'])) {
						$numb['actual_cdn'] = 0;
						$address_count = count($numb['cdn_address']);
						//ищем первый работающий CDN
						for ($i = 0; $i < $address_count; $i++){
							if ($this->checkAdress($numb['cdn_address'][$i])) {	
								$numb['actual_cdn'] = $i+1;
								break;
							}
						}
					}
					
				} //foreach
			} //foreach
			
			$this->saveJsonConfig();
		}
		
		protected function saveJsonConfig()
		{
			$this->_json->__setNewJson($this->_config);
			$this->_json->save($this->cdn_json_path, true);			
		}
		
		/**
		 * Проверяет переданный адрес на доступность
		 * @param URL $address
		 * @return bool
		 */
		public static function checkAdress($address){
			//если не указана схема (http) подставить ее
			if (is_null(parse_url($address, PHP_URL_SCHEME))){
				$address = 'http:' . $address;
			};
				try {
					$headers = get_headers($address);
				} catch (Exception $e) {
					return false;
				}
				
				//Удовлетворительными ответами удаленного сервера считаются 200 и 403
				if (strpos($headers[0], '200') || strpos($headers[0], '403')) {
					return true;
				} else {
					return false;
				}	
		}
		
}