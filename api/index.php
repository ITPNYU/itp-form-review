<?php
require '../lib/Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

require('../ifr_gravity.php');

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
        $req['reviewer'] = $user_login;

        $ifr_entries = ifr_form_query("forms/2/entries");

        $base_price = 1200;
        $discount_sum = 0;

        $req['payment_due'] = $base_price * (1 - $discount_sum);

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
