<?php
/**
 * CRON
 * Run daily 9am
 */
  define('ROOT', __DIR__ . '/../');
  include __DIR__ . "/Init.php";

  // birthday
  $users = $app->db->getAll("SELECT * FROM `users` WHERE RIGHT(user_dob, 5) = RIGHT(CURDATE(), 5) ");
  if (count($users) > 0) {
    $birthday = array();
    foreach ($users as $user) {
      $birthday[] = $user['user_name'];
    }
    $devices = $app->db->getAll("
      SELECT *
      FROM `devices`
      WHERE user_id NOT IN (
        SELECT user_id
        FROM `users`
        WHERE RIGHT(user_dob, 5) = RIGHT(CURDATE(), 5)
      )
    ");
    $message = "Сегодня день рождения у ".implode(", ", $birthday);
    foreach ($devices as $device) {
      switch ($device['dev_type']) {
        case 1: // ios
          $app->mobile->pushIos($device['apns_id'], $message);
          break;
        case 2: // android
          $app->mobile->pushAndroid($device['gcm_id'], $message);
          break;
      }
    }
  }
