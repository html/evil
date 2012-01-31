<?php
/**
 * CDN - Content Delivery Network
 * 
 * @desc Заменяет ссылки на котент, расположеный на текущем сервере, ссылками, расположеными на
 * удаленном сервере CDN.
 * 
 * @see как использовать см. HeadScript и HeadLink
 * 
 * Пути к файлам и папкам задаются в application/configs/cdn.json
 * в виде
 *{
 *  "js":{
 *   "jQuery":{
 *     "src_address":"/js/jquery-1.5.1.min.js",
 *     "cdn_address":[
 *       "//yandex.st/jquery/1.5.1/jquery.js",
 *       "//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js",
 *       "//ajax.aspnetcdn.com/ajax/jQuery/jquery-1.5.1.min.js",
 *       "//code.jquery.com/jquery-1.5.1.min.js"
 *     ],
 *     "actual_cdn":1
 * 	  }
 *  }
 *}  	
 * Где 
 * 	   js - указывает тип файлов для замены (JS, CSS)
 *     JQuery  - идентификатор паттерна. Если необходимо указать несколько паттернов 
 *     		замены каждый новый паттерн должен иметь свой идентификатор.
 *     		идентифкатор может быть задан символьным именем
 *     src_address - тот адрес который необходимо заменить
 *     cdn_address - адрес на CDN сервере. Тоесть НА который нужно заменять
 *     	  * В качестве адресов можно указывать папки и файлы.
 *     		Если в src_address задан путь к файлу или папке, то в
 *     		cdn_address тоже должен быть указан путь к файлу или папке соотвественно
 *        * После названия директории рекомендуется ставить '/'
 *        	в ином случае паттерн  '/js/extjs' будет соотвествовать строке  '/js/extjsFinal'
 *          и будет заменен на "http://d30tk8m4gt6k7.cloudfront.net/js/extjsFinal".
 *        * Может содержать несколько адресов. Указываются в порядке приоритета начиная с первого
 *        	В случае недоступности первого, будет использован второй и т.д.
 *          если ни одна из ссылок недоступна CDN не будет использоватьсяи файлы будут отдаватся с этого сервера
 * 		"actual_cdn":может принимать несколько значений:
 * 			 - disabled - отключено пользователем. Автоматически не будет включатся при проверки доступности CDN
 *           - 0 выключен CDN автоматом в следствии недоступности ни одого CDN сервера
 *           - 1 включено перенаправлление на первый по счету CDN сервер
 *           - 2 включено перенаправлление на второй по счету CDN сервер
 *           - и т.д.
 *           
 * @author Sergey Bukharov
 * @date 21.06.2011
 * 
 */

class Evil_Cdn_HeadBase
{

	/**
	* паттерн для поиска адресов JavaScript файлов
	* @var string
	*/
	const JS_PREFIX = 'src="';

	/**
	* паттерн для поиска адресов CSS файлов
	* @var string
	*/	
	const CSS_PREFIX =  'href="';
		
	/**
	 * содержат строки необходимые для замены
	 * Паралельные массивы. При добавлении в один необходимо
	 * добавить и в другой
	 */
	private $_address_inside = array();  //search
	private $_address_outside = array(); //destination
	
	/**
	 * application.ini
	 * @var array
	 */
	private $_config;
	
	/**
	 * js or css or ...
	 * @var string
	 */
	private $_content_type;

	public function __construct($content_type)
	{
		//если Json не корректный или файл настроек не найден
		//тихонько выходим, в дальнейшем никаких замен не будет
		try{
			$this->_getConfig();
		}catch (Exception $e){
			//TODO если будет использоваться логирование записать исключение в лог
			return false;
		}
					
		$content_type = strtolower($content_type);
		if ($content_type != 'css' || $content_type !='js'){
		//	throw new Exception("Указан не верный тип заменяемых файлов ($content_type)");
		}		
		
		$this->_content_type = $content_type;
		
		//проверяем адреса на корректность и правим
		$this->_validateAdress();		
	}
	

	/**
	 * вызывается при рендеринге контента.
	 * Генерирует пути к JS файлам
	 * @override
	 * @see Zend_View_Helper_HeadScript::toString()
	 */
	public function toString($strings){
		//если строки для замены пусты
		if (empty($strings)) return $strings;
		
		//ничего не делаем, если пути замены не определены
		if (empty($this->_config)) return $strings;
		
		/* Заполяем массивы $_address_inside и $_address_outside
		 * адресами путей, которые необходимо переписать
		 */ 				
		$this->_addPathToReplacmentArrays();
			
		// заменяем в документе строки полуичившимися паттернами
		$strings = $this->_replaceSrc($strings);
		
		return $strings;
	}
	
	
		/**
		 * Инициализация
		 * Считывает настрокйи application.ini
		 */
	public function _getConfig()
	{
/*		//получаем ссылку на хранилище настроек
		try {
			$config = Zend_Registry::get('config');
		} catch (Exception $e) {
			throw new Exception('Helper CDN не смог получить доступ к настройкам в Application.ini', 500);
		}	
		
		$this->_config = $config['evil']['CDN'];	*/		
		
		//$json = file_get_contents(APPLICATION_PATH . "/config/cdn.json");
		$path = APPLICATION_PATH . "/configs/cdn.json";
		$json = new Evil_Json($path);
		$this->_config = $json->toArray();
		
		return $json;
	}
	
	
		/**
		 * корректирует и проверяет адрес
		 */
		private function _validateAdress()
		{
			foreach ($this->_config as &$content_type){
				foreach ($content_type as &$content_number) {					
					//удаляет символ "*" в конце строки
					$content_number['src_address'] = rtrim($content_number['src_address'], '*');
				}
			}
		}

		
		/**
		 * Создание Паттернов путей для поиска и замены 
		 * Добавление их в address_inside и address_outside
		 * @param $type css|js
		 */
		private function _addPathToReplacmentArrays()
		{	
			switch ($this->_content_type){
			case 'css':
				$prefix = self::CSS_PREFIX;
				break;
			case 'js':
				$prefix = self::JS_PREFIX;
				break;
			default:
				return;
			}	
			
			//Пробегаемся по адреса для замены и добавляем их
			foreach ($this->_config[$this->_content_type] as $numb){
				//если фалага включения CDN Не обранужено
				if (!isset($numb['actual_cdn'])){
					continue;	
				}
				
				//если перенаправление по текущему CDN выключено 
				if (false == $numb['actual_cdn'] || 'disabled' == $numb['actual_cdn'] ||
					0 == $numb['actual_cdn'] ){
					continue;	
				}
				
				//если CDN адрес только один записываем его
				if (!is_array($numb['cdn_address'])){
					array_push($this->_address_inside,  $prefix . $numb['src_address']);
					array_push($this->_address_outside, $prefix . $numb['cdn_address']);
					continue;					
				}
				
				//указатель на  рабочий CDN должен быть цифрой
				if (!is_numeric($numb['actual_cdn'])){
					continue;
				}
				//если адресов CDN несколько, записываем тот, на который указывает опция actual_cdn
				$cdn_number = ((int) $numb['actual_cdn']) - 1;
							  //[cdn_address][2] к примеру 	
				if (isset($numb['cdn_address'][$cdn_number])){
					array_push($this->_address_inside,  $prefix . $numb['src_address']);
					array_push($this->_address_outside, $prefix . $numb['cdn_address'][$cdn_number ]);
					continue;						
				}
			}		
		}
		
		/**
		 * Парся адреса JS скриптов заменяет их на амазонавские	
		 * @return void
		 */	
		private function _replaceSrc($str_paths = null){
			
			if (is_null($str_paths)) return;
			
			//количество элементов в массивах, содержащих адреса элементов для замены должно совпадать
			if(count($this->_address_inside) != 
				count($this->_address_outside)){
					throw new Exception("Helper Cdn. Количество  элементов в массивах адресов для замены не совпадает", 500);
				}
			
			$str_paths = str_ireplace($this->_address_inside, 
								$this->_address_outside, 
								$str_paths);
			return $str_paths;
										
		} 	
			
}