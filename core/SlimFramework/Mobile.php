<?php

  namespace Slim;


  class Mobile
  {

    function curl($url, $data = array(), $type = 'POST') {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,0);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.16 Safari/537.36");
      switch ($type) {
        case "POST":
          if (0 < count($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
          }
          break;
      }
      $result = curl_exec($ch);
      curl_close($ch);
      return $result;
    }

    public function pushAndroid($device_id, $message)
    {
      $data = array(
          'data'             => array("message" => $message),
          'time_to_live'     => 86400,
          'registration_ids' => $device_id
      );
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

    public function pushIos($token, $mess)
  	{
  		require_once PATH_CORE.'/ApnsPHP/Autoload.php';
  		$push = new \ApnsPHP_Push(
  			\ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
  			PATH_DESIGN.'/ios.pem'
  		);
  		$push->connect();
  		$message = new \ApnsPHP_Message($token);
  		$message->setCustomIdentifier("Message-Badge-3");
  		$message->setBadge(3);
  		$message->setText($mess['description']);
  		$message->setSound();
  		$message->setCustomProperty('data', $mess);
  		$message->setExpiry(30);
  		$push->add($message);
  		$push->send();
  		$push->disconnect();

  	}

  }
