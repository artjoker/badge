<?php

  $app->hook('slim.before.router', function () use ($app) {
    $config = $app->db->getAll("SELECT * FROM `config`");
    foreach ($config as $value)
      define($value['key'], $value['value']);
  });

  require PATH_ROUTE . "auth.php";


  $app->group('/admin', 'protect', function () use ($app) {

    $app->get('/', function () use ($app) {
      $app->redirect(URL_ROOT . 'admin/time');
    });

    $app->get('/fill', function () use ($app) {

      $app->db->query("TRUNCATE TABLE `timetable`");
      $users = $app->db->getAll("SELECT * FROM `users`");
      for ($m = 3; $m < 12; $m++) {

        $end = cal_days_in_month(CAL_GREGORIAN, $m, 2016);
        foreach ($users as $user) {
          $user_id = $user['user_id'];
          for ($i = 1; $i <= $end; $i++) {
            if (rand(0, 100) > 30) {
              $app->db->query("INSERT INTO `timetable` SET user_id = " . $user_id . ", day = '2016-" . str_pad($m, '0', STR_PAD_LEFT) . "-" . str_pad($i, '0', STR_PAD_LEFT) . "', time = '" . rand(8, 10) . ":00:00'");
              $app->db->query("INSERT INTO `timetable` SET user_id = " . $user_id . ", day = '2016-" . str_pad($m, '0', STR_PAD_LEFT) . "-" . str_pad($i, '0', STR_PAD_LEFT) . "', time = '" . rand(17, 19) . ":" . rand(0, 59) . ":" . rand(0, 59) . "'");

            }
          }
        }
      }
      die;
    });
    /**
     * Time frontend
     */
    $app->get('/time', function () use ($app) {
      $year  = (int)$app->request->get("y") > 0 ? (int)$app->request->get("y") : date("Y");
      $month = (int)$app->request->get("m") > 0 ? (int)$app->request->get("m") : date("m");
      $days  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
      //get timetable
      $query = "
        SELECT 
          user_id,
          day,
          MIN(time) AS 'start',
          MAX(time) AS 'end' 
        FROM `timetable` 
        WHERE day BETWEEN 
          '" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01' AND 
          '" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . $days . "'
        GROUP BY user_id, day";
      //echo $query;
      $timetable = $app->db->getAll($query);
      $data      = array();
      foreach ($timetable as $value) {
        $data[$value['user_id']][$value['day']] = $value;
        unset($data[$value['user_id']][$value['day']]['user_id']);
        unset($data[$value['user_id']][$value['day']]['day']);
      }
      $app->view->setData(array(
        "title"   => $app->lang->get('Timetable'),
        "menu"    => "time",
        "content" => $app->view->fetch('time.tpl', array(
          "app"   => $app,
          "days"  => $days,
          "year"  => $year,
          "month" => $month,
          "users" => $app->db->getAll("SELECT * FROM `users` WHERE user_active = 1 ORDER BY user_name ASC"),
          "time"  => $data,
        )),
      ));
    });
    /**
     * Config frontend
     */
    $app->get('/config', function () use ($app) {
      $app->view->setData(array(
        "title"   => $app->lang->get('Configuration'),
        "menu"    => "config",
        "content" => $app->view->fetch('config.tpl', array(
          "app" => $app,
        )),
      ));
    });
    /**
     * Config backend
     */
    $app->post('/config', function () use ($app) {
      foreach ($app->request->post() as $key => $value) {
        $query = "INSERT INTO `config` SET
          `key` = '" . strtoupper($key) . "',
          `value` = '" . $app->db->esc($value) . "'
        ON DUPLICATE KEY UPDATE
          `value` = '" . $app->db->esc($value) . "'
        ";
        $app->db->query($query);
      }
      $app->flash("success", $app->lang->get('Config updated'));
      $app->redirect(URL_ROOT . 'admin/config');
    });
    /**
     * Users frontend
     */
    $app->get('/users', function () use ($app) {
      $page  = '' != $app->request->get('p') ? $app->request->get('p') : 0;
      $query = "
       SELECT SQL_CALC_FOUND_ROWS *
       FROM `users`
       " . ($app->request->get('search') != '' ? "
       WHERE user_name LIKE '%" . $app->db->esc($app->request->get('search')) . "%'
       OR user_email LIKE '%" . $app->db->esc($app->request->get('search')) . "%'" : "") . "
       ORDER BY user_id DESC
       LIMIT " . $page . ", " . LIMIT;
      $users = $app->db->getAll($query);
      // pagination
      $pages = $app->db->getOne("SELECT FOUND_ROWS() AS 'cnt'");
      $get   = $app->request->get();
      unset($get['p']);
      $params = http_build_query($get);
      $app->view->setData(array(
        "title"   => $app->lang->get('Users'),
        "menu"    => "users",
        "content" => $app->view->fetch('users.tpl', array(
          "app"    => $app,
          "users"  => $users,
          "pages"  => ceil($pages['cnt'] / LIMIT),
          "page"   => $page,
          "params" => $params,
        )),
      ));
    });
    /**
     * Single user frontend
     */
    $app->get('/users/:id', function ($id) use ($app) {
      $user = $app->db->getOne("SELECT * FROM `users` WHERE user_id = '" . (int)$id . "'");
      $app->view->setData(array(
        "title"   => $app->lang->get('Edit user profile'),
        "menu"    => "users",
        "content" => $app->view->fetch('user.tpl', array(
          "app"  => $app,
          "user" => $user,
        )),
      ));
    });
    /**
     * Single user backend
     */
    $app->post('/users/:id', function ($id) use ($app) {
      $user = $app->request->post('user');
      $dob = strtotime($user['dob']);
      if ((int)$id == 0) {
        if ($user['pass'] == '')
          $user['pass'] = uniqid();
        $app->db->query("
          INSERT INTO `users` SET
            user_name = '" . $app->db->esc($user['name']) . "',
            user_email = '" . $app->db->esc($user['email']) . "',
            user_dob = '" . $app->db->esc(date("Y-m-d", $dob)) . "',
            user_active = '" . (isset($user['active']) ? 1 : 0) . "',
            user_pass = SHA2('".$user['pass']."', 512)
        ");
      } else {
        $app->db->query("
          UPDATE `users` SET
            user_name = '" . $app->db->esc($user['name']) . "',
            user_email = '" . $app->db->esc($user['email']) . "',
            user_dob = '" . $app->db->esc(date("Y-m-d", $dob)) . "',
            user_active = '" . (isset($user['active']) ? 1 : 0) . "'
            ".($user['pass'] != '' && $user['pass'] == $user['cfm'] ? ", user_pass = SHA2('".$user['pass']."', 512)" : "")."
          WHERE user_id = '" . (int)$id . "'
        ");
      }
      $app->flash("success", $app->lang->get('User data successfully updated'));
      $app->redirect(URL_ROOT . 'admin/users');
    });
    /**
     * Managers frontend
     */
    $app->get('/managers', function () use ($app) {
      $query    = "
         SELECT m.*
         FROM `managers` m
         ORDER BY manager_id DESC";
      $managers = $app->db->getAll($query);
      $app->view->setData(array(
        "title"   => $app->lang->get('Managers'),
        "menu"    => "users",
        "content" => $app->view->fetch('managers.tpl', array(
          "app"      => $app,
          "managers" => $managers,
        )),
      ));
    });
    /**
     * Single manager frontend
     */
    $app->get('/manager/:id', function ($id) use ($app) {
      $app->view->setData(array(
        "title"   => $app->lang->get('Edit manager profile'),
        "menu"    => "users",
        "content" => $app->view->fetch('manager.tpl', array(
          "app"     => $app,
          "manager" => $app->db->getOne(" SELECT * FROM `managers` WHERE manager_id = '" . (int)$id . "'"),
        )),
      ));
    });
    /**
     * Single manager backend
     */
    $app->post('/manager/:id', function ($id) use ($app) {
      $user = $app->request->post('manager');
      if ($user['pass'] == $user['cfm']) $pass = md5($user['pass']);
      else {
        $app->flash("error", $app->lang->get('Password mismatch'));
        $app->redirect(URL_ROOT . 'admin/managers/' . $id);
      }
      // create new manager or update existing
      if ($id > 0)
        $app->db->query("UPDATE `managers` SET
         manager_name = '" . $app->db->esc($user['name']) . "',
         user_email = '" . $app->db->esc($user['email']) . "',
         manager_active = '" . (isset($user['active']) ? 1 : 0) . "'
         " . ($pass != '' ? ", manager_pass = '" . $pass . "'" : "") . "
         WHERE manager_id = '" . (int)$id . "'
       ");
      else
        $app->db->query("INSERT INTO `managers` SET
         manager_name = '" . $app->db->esc($user['name']) . "',
         manager_email = '" . $app->db->esc($user['email']) . "',
         manager_active = '" . (isset($user['active']) ? 1 : 0) . "',
         manager_pass = '" . $pass . "'
       ");
      $app->flash("success", $app->lang->get('Manager data successfully updated'));
      $app->redirect(URL_ROOT . 'admin/managers');
    });
    /**
     * Delivery frontend
     */
    $app->get('/delivery', function () use ($app) {
      $app->view->setData(array(
        "title"   => $app->lang->get('Delivery'),
        "menu"    => "content",
        "content" => $app->view->fetch('delivery.tpl', array(
          "app"      => $app,
          "delivery" => $app->db->getAll("SELECT * FROM `delivery` ORDER BY delivery_id DESC"),
        )),
      ));
    });
    /**
     * Delivery backend
     */
    $app->post('/delivery', function () use ($app) {
      $delivery = $app->request->post('delivery');
      $app->db->query("INSERT INTO `delivery` SET
            delivery_active	= " . (isset($delivery['active']) ? 1 : 0) . ",
            delivery_name	= '" . $app->db->esc($delivery['name']) . "',
            delivery_cost =	" . $app->db->esc($delivery['cost']) . "
      ");
      $app->flash("success", $app->lang->get('Delivery type added successfully '));
      $app->redirect(URL_ROOT . 'admin/delivery');
    });
    /**
     * Exit admin panel
     */
    $app->get('/exit', function () use ($app) {
      unset($_SESSION['admin']);
      $app->redirect(URL_ROOT . '');
    });

    $app->hook('slim.after.router', function () use ($app) {
      $app->render('index.tpl', array("app" => $app));
    });
  });


