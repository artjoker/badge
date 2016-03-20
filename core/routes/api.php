<?php

  $app->group('/api', function() use($app){

    $app->post('/auth', function() use($app){
      $data = json_decode($app->request->getBody());
      $user = $app->db->getOne("SELECT * FROM `users` WHERE user_email = '".$app->db->esc($data->login)."'");
      if ($user['user_pass'] != $data->password)
        $app->response->setStatus(403);
      else {
        $uuid = md5(time()."Artjoker".$user['user_email']);
        $app->db->query("INSERT INTO `session` SET user_id = '".$user['user_id']."', sess_id = '".$uuid."' ON DUPLICATE KEY UPDATE sess_id = '".$uuid."'");
        $app->response->setStatus(200);
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody(json_encode(array("data"=>array("token" => $uuid))));
      }
      $app->stop();
    });

    $app->put("/time", function() use($app) {
      $data = json_decode($app->request->getBody());
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

