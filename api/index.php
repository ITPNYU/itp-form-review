<?php
require '../lib/Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

function ifr_create_user($fname, $lname, $email, $blog) {
  $user_login_prefix = preg_replace('/\W/', '', strtolower(substr($fname, 0, 1) . $lname));
  $user_pass = wp_generate_password( $length=12, $include_standard_special_chars=false );
                
  $user_id = email_exists($user_email);
  if ($user_id) { // user already exists
    $user_info = get_userdata($user_id);
    $user_login = $user_info->user_login;
    if ($user_login != 'admin') {
      wp_update_user(array( 'ID' => $user_id, 'user_pass' => $user_pass));
      add_user_to_blog( $blog, $user_id, "author" ) ;
    }
  } 
  else { // user does not exist
    if (username_exists( $user_login )) { // but user name is in use already
      do {
        $user_login = $user_login_prefix . rand(1, 99);
      }
      while (username_exists($user_login));
    }
    else {
      $user_login = $user_login_prefix;
    }

    $user_info = array(
      'user_login' => $user_login,
      'user_pass' => $user_pass,
      'user_email' => $email,
      'first_name' => $fname,
      'last_name' => $lname,
      'nickname' => $fname . " " . $lname
    );
    
    $user_id = wp_insert_user( $user_info );
    if (is_wp_error($user_id)) {
      return null;
    }
    else {
      $user_info['wpid'] = $user_id;
      add_user_to_blog( $blog, $user_id, "author" ) ;
      remove_user_from_blog($user_id, 1); // hack, must manually remove from main blog
    }
}
  return $user_info;
}

// FIXME: will need to change this for special authorization
if (current_user_can('activate_plugins')) { // indicates an administrator
  global $wpdb;
  $review_table = $wpdb->prefix . "ifr_review";

  $app = new \Slim\Slim();
  $app->response->headers->set('Content-Type', 'application/json');

  $app->get(
      '/review',
      function () use ($app) {
        global $wpdb;
        $blog_id = $app->request->params('blog');
        $db_prefix = $wpdb->prefix;
        if ($blog_id != null) {
          $db_prefix = $db_prefix . $blog_id . "_";
        }
        $table = $db_prefix . "ifr_review";
        $query = "SELECT * FROM $table";
        $results = $wpdb->get_results($query, ARRAY_A);
        $res["num_objects"] = count($results);
        $res["objects"] = $results;
        echo json_encode($res);
      }
  );

  $app->get(
      '/decision',
      function () use ($app) {
        global $wpdb;
        $blog_id = $app->request->params('blog');
        $db_prefix = $wpdb->prefix;
        if ($blog_id != null) {
          $db_prefix = $db_prefix . $blog_id . "_";
        }
        $table = $db_prefix . "ifr_decision";
        $query = "SELECT * FROM $table";
        $results = $wpdb->get_results($query, ARRAY_A);
        $res["num_objects"] = count($results);
        $res["objects"] = $results;
        echo json_encode($res);
      }
  );

  $app->get(
      '/payment',
      function () use ($app) {
        global $wpdb;
        $blog_id = $app->request->params('blog');
        $db_prefix = $wpdb->prefix;
        if ($blog_id != null) {
          $db_prefix = $db_prefix . $blog_id . "_";
        }
        $table = $db_prefix . "ifr_payment";
        $query = "SELECT * FROM $table";
        $results = $wpdb->get_results($query, ARRAY_A);
        $res["num_objects"] = count($results);
        $res["objects"] = $results;
        echo json_encode($res);
      }
  );

  $app->get(
      '/user',
      function () use ($app) {
        global $wpdb;
        $blog_id = $app->request->params('blog');
        $db_prefix = $wpdb->prefix;
        if ($blog_id != null) {
          $db_prefix = $db_prefix . $blog_id . "_";
        }
        $table = $db_prefix . "ifr_user";
        $query = "SELECT * FROM $table";
        $results = $wpdb->get_results($query, ARRAY_A);
        $res["num_objects"] = count($results);
        $res["objects"] = $results;
        echo json_encode($res);
      }
  );

  $app->post(
      '/review',
      function () use ($app) {
        global $wpdb;
        global $user_login;
        $blog_id = $app->request->params('blog'); // FIXME: redundant
        $db_prefix = $wpdb->prefix;
        if ($blog_id != null) {
          $db_prefix = $db_prefix . $blog_id . '_';
        }
        $table = $db_prefix . 'ifr_review'; // END FIXME
        $req = json_decode($app->request->getBody(), true);
        $req['reviewer'] = $user_login;

        $status = $wpdb->insert($table, $req);
        if ($status == false) {
          $app->response->setStatus(400); // bad request
        }
        else {
          $app->response->setStatus(201); // created
          echo json_encode($req);
        }
      }
  );

  // required args: form, entry, decision
  $app->post(
      '/decision',
      function () use ($app) {
        global $wpdb;
        global $user_login;
        $blog_id = $app->request->params('blog'); // FIXME: redundant
        $db_prefix = $wpdb->prefix;
        if ($blog_id != null) {
          $db_prefix = $db_prefix . $blog_id . '_';
        }
        $table = $db_prefix . 'ifr_decision'; // END FIXME
        $req = json_decode($app->request->getBody(), true);

        $payment_due = 0.0;
        $user_db_id = null;
        if (($req['decision'] === 'approve') || ($req['decision'] === 'comp')) {
          $base_price = 1200.0;
          if ($req['decision'] === 'comp') {
            $payment_due = 0;
          }
          else {
            $payment_due = $base_price * (1 - $req['discount']);
          }
          $user_info = ifr_create_user(
            $req['fname'],
            $req['lname'],
            $req['email'],
            $blog_id
          );
          $user_table = $db_prefix . 'ifr_user';
          $user_data = array(
            'username' => $user_info['user_login'],
            'password' => $user_info['user_pass'],
            'fname' => $user_info['first_name'],
            'lname' => $user_info['last_name'],
            'email' => $user_info['user_email']
          );
          if ($wpdb->insert($user_table, $user_data)) {
            $user_db_id = $wpdb->insert_id;
          }
        }

        $decision_data = array(
          'form' => $req['form'],
          'entry' => $req['entry'],
          'decision' => $req['decision'],
          'reviewer' => $user_login,
          'user' => $user_db_id,
          'payment_due' => $payment_due
        );
        $status = $wpdb->insert($table, $decision_data);
        if ($status == false) {
          $app->response->setStatus(400); // bad request
          echo json_encode($decision_data);
        }
        else {
          $app->response->setStatus(201); // created
          echo json_encode($decision_data);
        }
      }
  );

  // FIXME: will need to change this for special authorization
  $app->post(
      '/payment',
      function () use ($app) {
        global $wpdb;
        global $user_login;
        $blog_id = $app->request->params('blog'); // FIXME: redundant
        $db_prefix = $wpdb->prefix;
        if ($blog_id != null) {
          $db_prefix = $db_prefix . $blog_id . '_';
        }
        $table = $db_prefix . 'ifr_payment'; // END FIXME
        $req = json_decode($app->request->getBody(), true);

        $status = $wpdb->insert($table, $req);
        if ($status == false) {
          $app->response->setStatus(400); // bad request
        }
        else {
          $app->response->setStatus(201); // created
          echo json_encode($req);
        }
      }
  );

  $app->run();
}
else {
  header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden'); 
  echo "403 Forbidden";
}
?>
