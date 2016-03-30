<?php

  $app->group('/api', function() use($app){

    $data = json_decode($app->request->getBody());

    $app->post('/auth', function() use($app, $data){
      $user = $app->db->getOne("SELECT * FROM `users` WHERE user_email = '".$app->db->esc($data->login)."'");
      if ($user['user_pass'] != $data->password)
        $app->response->setStatus(403);
      else {
        $uuid = md5(time()."Artjoker".$user['user_email']);
        $app->db->query("INSERT INTO `session` SET
          user_id = '".$user['user_id']."',
          sess_id = '".$uuid."',
          dt      = NOW()
          ON DUPLICATE KEY UPDATE sess_id = '".$uuid."'");
        $app->response->setStatus(200);
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array("data"=>array("token" => $uuid))));
      }
      $app->stop();
    });

    $app->put("/gcm", function() use($app, $data) {
      $app->db->query("
        INSERT INTO `devices` SET
          user_id = (SELECT user_id FROM `session` WHERE sess_id = '".$app->db->esc($data->token)."'),
          device_id = '".$app->db->esc($data->device_id)."',
          gcm_id = '".$app->db->esc($data->reg_id)."'
      ");
      $app->response->setStatus(200);
      $app->stop();
    });

    $app->post("/sos", function() use($app, $data) {
      echo "SELECT * FROM `users` WHERE user_id = (SELECT user_id FROM `session` WHERE sess_id = '".$app->db->esc($data->token)."')";
      $user = $app->db->getOne("SELECT * FROM `users` WHERE user_id = (SELECT user_id FROM `session` WHERE sess_id = '".$app->db->esc($data->token)."')");
      if ($user['user_name'] == '')
        $app->response->setStatus(403);
      else {
        $app->response->setStatus(200);
        $app->mail->send(ALARM, "SOS", "<h3 style='color:red'>".$user['user_name']." нажал паническую кнопку! В офисе маски шоу!</h3>");
      }
      $app->stop();
    });

    $app->put("/time", function() use($app, $data) {
      // $data = json_decode($app->request->getBody());
      var_dump($data);
      die;
      $app->db->query("
        INSERT INTO `timetable` SET
          user_id = (SELECT user_id FROM `session` WHERE sess_id = '".$app->db->esc($data->token)."'),
          day = CURRENT_DATE(),
          time= CURTIME()
      ");
      $app->response->setStatus(200);
      $app->stop();
    });
  });
