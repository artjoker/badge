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

  }