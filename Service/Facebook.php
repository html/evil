<?php 
/**
 * @desc class with working for Facebook API
 * @author makinder
 * @version 0.0.1
 */

class Evil_Service_Facebook
{
	
	/**
	 * @desc application ID
	 */
	protected static  $_appId = null;
	
	/**
	 * @desc application secret
	 */
	protected static $_secret = null;
	
	/**
	 * @desc send message 
	 * @param string $appId
	 * @param string $secret
	 * @param string $backUrl
	 * @param string $title
	 * @param string $message
	 * @author makinder
	 * @version 0.0.1
	 */
	public function send($appId, $secret, $backUrl, $title, $message)
	{
		$code = $_REQUEST["code"];
		self::$_appId = $appId;
		self::$_secret = $secret;
		if(empty($code))
			$this->prepareCode(self::$_appId, $backUrl);
		else 
			$this->postOnWall($code, $title, $message, $backUrl);	
	}
	/**
	 * @desc receive CODE
	 * @param string $appId
	 * @param string $backUrl
	 * @author makinder
	 * @version 0.0.1
	 */
	public function prepareCode($appId, $backUrl)
	{
		$scope = 'publish_stream,offline_access';
		$_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
		$dialogUrl = "https://www.Facebook.com/dialog/oauth?client_id=" 
		 	. $appId . "&redirect_uri=" . urlencode($backUrl) . "&scope=".$scope."&state="
		       . $_SESSION['state'];
			
	    echo("<script> top.location.href='" . $dialogUrl . "'</script>");
	}
	
	/**
	 * @desc post in wall Facebook
	 * @param string $code
	 * @param string $title
	 * @param string $message
	 * @param string $backUrl
	 * @author makinder
	 * @version 0.0.1
	 */
	public function postOnWall($code, $title, $message, $backUrl)
	{
	
	if($_REQUEST['state'] == $_SESSION['state']) 
   		{
		     $tokenUrl = "https://graph.Facebook.com/oauth/access_token?"
		       . "client_id=" . self::$_appId . "&redirect_uri=" . urlencode($backUrl)
		       . "&client_secret=" . self::$_secret . "&code=" . $code;
		
		     $response = file_get_contents($tokenUrl);
			
		     $params = null;
		     parse_str($response, $params);
		
		     $graphUrl = "https://graph.Facebook.com/me?access_token=" 
		       . $params['access_token'];
		
		     $userInfo = json_decode(file_get_contents($graphUrl));
						
		    
		    $uri =  'http://skill.teamrocketscience.ru/';
		    $pic = 	'http://perfect-code.narod.ru/st/logo_test2.jpg';
			$url = "https://graph.Facebook.com/".$userInfo->id."/feed";
			$attachment =  array(
							'access_token' => $params['access_token'],
							'message' => $message,
							'name' => $title,
							'link' => $uri,
							'picture'=>$pic,
								);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $attachment);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
			$result = curl_exec($ch);
			curl_close ($ch);	 
  		 }
   		else 
   		{
     		echo("The state does not match. You may be a victim of CSRF.");
   		}
	}
	
}
