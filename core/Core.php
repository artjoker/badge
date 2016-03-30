<?php 
namespace Slim;


class Core {
	var $db     = null;
	var $app    = null;
	var $mail   = null;
	var $head   = null;
	var $body   = null;
	var $env    = null;
	var $res    = null;
	var $user   = null;
	

	function __construct($app) 
	{
		$this->app  = $app;
		$this->db   = $app->db;
		$this->mail = $app->mail;
		$this->res  = array(
			'css'     => $this->getCSS(),
			'js'      => $this->getJS(),
			'content' => '',
		);
		$this->config();
	}

	function dd($var)
	{
		echo "<pre>";
		var_dump($var);
		echo "</pre>";
	}


	function getCSS()
	{
		$list = glob(ROOT . '/design/css/*.css');
		$res  = '';
		if (is_array($list)) 
			foreach($list as $file)
				$res .= '<link rel="stylesheet" href="/design/css/'.basename($file).'">'."\n";
		return $res;
	}

	function getJS() 
	{
		$list = glob(ROOT . '/design/js/*.js');
		$res  = '';
		if (is_array($list))
			foreach($list as $file)
				$res .= '<script src="/design/js/'.basename($file).'"></script>'."\n";
		return $res;
	}

	function parse()
	{
		// parse headers
		$this->head = $this->app->request->headers;
		$head = ($this->head->Authorization != '') ? $this->head->Authorization : '';
		$h = (array)$this->head;
		$h = array_values($h);
		$head .= (isset($h[0]['If-Modified-Since']) && $h[0]['If-Modified-Since'] != '') ? "<br> If-Modified-Since: ".$h[0]['If-Modified-Since'] : '';
		
		// parse body
		if ($this->app->request->isPut())
			$this->body = $this->app->request->put(ENTRY_POINT);
		if ($this->app->request->isPost())
			$this->body = $this->app->request->post(ENTRY_POINT);
		if ($this->app->request->isDelete())
			$this->body = $this->app->request->delete(ENTRY_POINT);
		if ($this->app->request->isPatch())
			$this->body = $this->app->request->patch(ENTRY_POINT);
		$this->body = json_decode(urldecode($this->body), true);
		// define environment
		$routes     = explode("/", $this->app->request->getResourceUri());
		$this->env  = $routes[1];
		$this->res['page'] = $routes[2];
		if($this->env == "api") {
			$this->db->query("INSERT INTO `logs` SET 
				`date` = '".date("Y-m-d H:m:s")."',
				head   = '".$this->db->esc($head)."', 
				body   = '".$this->db->esc(json_encode($this->body))."', 
				url    = '".$this->db->esc($this->res['page'])."'"
			);
		}
	}

	/**
	 * Вывод списка стран
	 * @param  $id [int] Индификатор для выбора одной страны
	 * @param  $search [string] поиск стран по первым буквам(ajax -> autocomplite)
	 * @param  $limit [int] поиск стран по первым буквам(ajax -> autocomplite)
	 * @return [array] Массив стран
	 */
	public function getCountries($id = '', $search = '', $limit = null) 
	{
		$where = array();
		if($id != '') $where[] = "country_id = ".$this->db->esc($id);
		if($search != '') $where[] = "name LIKE ('".$this->db->esc($search)."%')";
		$limit = $limit != '' ? " LIMIT ".$limit  : '';
		return $this->db->getAll("SELECT country_id as id, name as label FROM `countries`".(count($where) > 0 ? " WHERE ".implode(", ", $where) : ""));
	}

	/**
	 * Вывод списка город учитываю страну
	 * @param  [int] $country id страны
	 * @return [array]          Массив городов
	 */
	public function getCities($country, $search = '', $limit = null)
	{
		$where = '';
		if($search != '') $where = " AND city LIKE('".$this->db->esc($search)."%')";
		$limit = $limit != '' ? " LIMIT ".$limit  : '';
		return $this->db->getAll("SELECT id, city as 'label' FROM `cities` WHERE country_id = ".$this->db->esc($country).$where.$limit);
	}
	/**
	 * Вывод описание города
	 * @param  [int] $id id города
	 * @return [array]          Массив городов
	 */
	public function getCitiesOne($id)
	{
		return $this->db->getAll("SELECT * FROM `cities` WHERE id = ".$this->db->esc($id));
	}

	/**
	 * Пагинация
	 * @param  [int] $count Кол-во всего эл-тов
	 * @param  [string] $url   урл для пагинации
	 * @return [html]        верстка пагинации
	 */
	public function getPaginate($count, $url)
	{      
		$res = '';
    	$pages['all']   = ceil($count / LIMIT_PAGIN);
      	$prev           = '&laquo;';
      	$next           = '&raquo;';
      	$classPrev      = 'prev';
      	$classNext      = 'next';
      	$classPage      = 'page';
      	$classCurrent   = 'active';
      	$classSeparator = 'separator';
      	$separator      = '...';
      	$tpl_enable = "/pagination/enable.tpl";
      	$tpl_disable = "/pagination/disable.tpl";
      	$url            = $url != '' ? $url : '';
      	if($pages['all'] > 1){
        	$limit_pagination = 2;
			$curent = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
			$start  = $curent - $limit_pagination;
			$end    = $curent + $limit_pagination;
			$start  = $start < 1 ? 1 : $start;
			$end    = $end > $pages['all'] ? $pages['all'] : $end;

	        if($curent > 1) {
	          	$res = $this->app->view->fetch($tpl_enable , array('url' => $url.($curent-1), 'class' => $classPrev, 'title' => $prev));
	        }
	        if($curent > $limit_pagination+1)
	          	$res .= $this->app->view->fetch($tpl_enable , array('url' => $url.'1', 'class' => $classPage, 'title' => '1')).$this->app->view->fetch($tpl_enable, array('class' => $classSeparator, 'title' => $separator, "url" => $url));
	        
	        for($i = $start; $i < $end + 1; $i++){                         
	          	if($i == $curent)
	            	$res .= $this->app->view->fetch($tpl_disable, array('class' => $classCurrent, 'title' => $i));
	          	else
		            $res .= $this->app->view->fetch($tpl_enable , array('url' => $url.$i, 'class' => $classPage, 'title' => $i));
	        }
	        if($curent < ($pages['all'] - $limit_pagination))
	          	$res .= $this->app->view->fetch($tpl_disable, array('class' => $classSeparator, 'title' => $separator)).$this->app->view->fetch($tpl_enable , array('url' => $url.$pages['all'], 'class' => $classPage, 'title' => $pages['all']));
	        if($curent != $pages['all'])
	          	$res .= $this->app->view->fetch($tpl_enable , array('url' => $url.($curent+1), 'class' => $classNext, 'title' => $next));        
	    }
	    return $res;
    }

    /**
     * Удаление всех файлов из папки
     * @param  [string] $dir путь к директории
     */
    public function cleanDir($dir) 
    {
    	$files = glob($dir."/*");
    	$c = count($files);
    	if (count($files) > 0) {
        	foreach ($files as $file) {      
            	if (file_exists($file)) {
            		unlink($file);
            	}   
        	}
    	}
	}

    /**
     * Проверка заголовков Authorization и аccess_token user
     * @return [boolen] Возращаем статус заголовков, и записуем данные пользователя в переменую $app->core->user
     */
    public function auth($type)
    {
    	switch ($type) {
    		case 'Bearer':
    			if($this->app->core->head['Authorization'] != '') {
					$auth = explode(" ", $this->app->core->head['Authorization']);
					// неправильный токен
					if(!isset($auth[1])) {
						$this->app->response->setStatus(400);
						$this->app->response->setBody(json_encode(array("status_code" => 13, "data" => ""))); 
						return false;
					}

					$this->user = $this->db->getOne("SELECT *,
						(select sum(biling_data) from `biling` where biling_user = user_id and biling_type = 1) as 'biling_plus',
						(select sum(biling_data) from `biling` where biling_user = user_id and biling_type IN(2,3)) as 'biling_minus',
						(select activ_date from `activity` where activ_user = user_id order by activ_date desc limit 1) as 'active'
						FROM `users` 
						WHERE user_access_token = '".$this->db->esc($auth[1])."'");

					//если пользователь заходил больше недели назад отдаем что только устарел
					$last_active = date("d.m.Y 00:00:00", strtotime($this->user['active']));
					$date_a      = new \DateTime($last_active);
					$date_b      = new \DateTime();
					$interval    = $date_b->diff($date_a);
					if($interval->days < 7) {
						//активность пользователя в разные дни
						if($this->user['active'] != date("Y-m-d"))
							$this->db->insertTable(array("activ_user" => $this->user['user_id'], "activ_date" => date("Y-m-d")), "activity");
					
						if($this->user != '' && $auth[0] == "Bearer") {
							$this->user['user_energy'] = $this->user['biling_plus'] - $this->user['biling_minus'];
							return true;
						} else {
							//плохой токен
							$this->app->response->setStatus(400);
							$this->app->response->setBody(json_encode(array("status_code" => 13, "data" => ""))); 
						}
					} else {
						//устарел токен
						$this->app->response->setStatus(400);
						$this->app->response->setBody(json_encode(array("status_code" => 14, "data" => ""))); 
					}
				} else {
					//плохой токен
					$this->app->response->setStatus(400);
					$this->app->response->setBody(json_encode(array("status_code" => 13, "data" => ""))); 
				}
				return false;
    		break;
    	}
    }

    /**
     * Записуем в констаты все конфиги из админки
     * @return [define] контакты
     */
    public function config()
    {
    	$config = $this->db->getAll("SELECT * FROM `config`");
    	foreach ($config as $v)
    		define(strtoupper($v['key']), $v['value']);
    }

    /**
     * Записуем изменение энергии в DB
     * @param [int] $user_id Id пользователя
     * @param [int] $energy  кол-во энергии
     * @param [int] $type    Статус эрегии(1-начисление, 2-снятие, 3-заморозка)
     * @param [string] $comment Комментарий
     */
    public function setEnergy($user_id, $energy, $type, $comment)
    {
    	$this->db->insertTable(array(
			"biling_user" => $user_id,
			"biling_data" => $energy,
			"biling_type" => $type,
			"biling_dt"   => date("Y-m-d H:i:s"),
			"biling_comment" => $comment
		), "biling");
    }
    /**
     * формируем массив для оптравки пушей
     * @param  [int] $user_id ID пользователя
     * @param  [int] $type тип push
     */
    public function sendAndroidNotifyConfig ($user_id, $desc) 
    {
	    $android = $this->db->getAll("SELECT gcm_id FROM `devices` WHERE device_user IN (".(is_array($user_id) ? implode(",", $user_id) : $user_id).")");
	    if (0 == count($android)) return false;
	    foreach ($android as $key => $value)
	        $devices[] = $value['gcm_id'];
	    $response = array(
			"message" => $desc
	    );
	  	$data = array(
	        'data' => $response,
	        'time_to_live' => 86400,
	        'registration_ids' => $devices
	    );
	    $this->pushAndriod($data);
	}
	/**
	 * отправка push
	 * @param  [int/array] $data [description]
	 * @return [type]       [description]
	 */
	public function pushAndriod($data) 
	{  
	    $headers = array("Content-Type:"."application/json", "Authorization:"."key=".API_PUSH_KEY); 
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_NUMERIC_CHECK));
	    $response = curl_exec($ch);
	    curl_close($ch);
	}

	/**
	 * формируем кому отпрвалять пуши
	 * @param  [int] $id   id пользователя
	 * @param  [string] $desc текст
	 */
	public function sendiOSNotifyConfig($user_id, $desc)
	{
		$ios = $this->db->getAll("SELECT apns_id FROM `devices` WHERE device_user IN (".(is_array($user_id) ? implode(",", $user_id) : $user_id).")");
		if (0 == count($ios)) return false;
		foreach ($ios as $key => $value)
			if($value['apns_id'] != '')
		 		$this->pushIos($value['apns_id'], $desc);
	}
		
	/**
	 * Отправка пушей на iOs
	 */
	public function pushIos($token, $mess)
	{

		// Using Autoload all classes are loaded on-demand
		require_once ROOT.'/Slim/ApnsPHP/Autoload.php';

		// Instantiate a new ApnsPHP_Push object
		//ENVIRONMENT_PRODUCTION
		$push = new \ApnsPHP_Push(
			\ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
			ROOT.'/pushcert.pem'
		);

		// Set the Provider Certificate passphrase
		// $push->setProviderCertificatePassphrase('test');

		// Set the Root Certificate Autority to verify the Apple remote peer
		// $push->setRootCertificationAuthority('pushcert.pem');

		// Connect to the Apple Push Notification Service
		$push->connect();

		// Instantiate a new Message with a single recipient
		//$token = '6b67b9e86592fe23f268a9c60a66b81e5515614775b07f84b2b40da62a7b5cb0';
		$message = new \ApnsPHP_Message($token);

		// Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
		// over a ApnsPHP_Message object retrieved with the getErrors() message.
		$message->setCustomIdentifier("Message-Badge-3");

		// Set badge icon to "3"
		$message->setBadge(3);

		// Set a simple welcome text
		$message->setText($mess['description']);

		// Play the default sound
		$message->setSound();

		// Set a custom property
		$message->setCustomProperty('data', $mess);

		// Set another custom property
		//$message->setCustomProperty('acme3', array('bing', 'bong'));

		// Set the expiry value to 30 seconds
		$message->setExpiry(30);

		// Add the message to the message queue
		$push->add($message);

		// Send all messages in the message queue
		$push->send();

		// Disconnect from the Apple Push Notification Service
		$push->disconnect();

		// Examine the error message container
		// $aErrorQueue = $push->getErrors();
		// if (!empty($aErrorQueue)) {
		// 	var_dump($aErrorQueue);
		// }
		// die;
	}

	/**
	 * оплата андроид маркет
	 */
	public function verify_market_in_app($signed_data, $signature, $public_key_base64) 
	{
		require_once 'AndroidMarket/Licensing/ResponseData.php';
		require_once 'AndroidMarket/Licensing/ResponseValidator.php';
		// $response = $signed_data;
		// var_dump($response);die;
		$response = json_decode($signed_data,true);

		//if you wish to inspect or use the response data, you can create
		//a response object and pass it as the first argument to the Validator's verify method
		// $response = new AndroidMarket_Licensing_ResponseData($responseData);
		// $valid = $validator->verify($response, $signature);
		// var_dump($signature);die;
		$validator = new \AndroidMarket_Licensing_ResponseValidator($public_key_base64, $response['packageName']);
		$valid = $validator->verify($signed_data, $signature);
		return $valid;
	}
}