<?php
require '../lib/Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if (current_user_can('activate_plugins')) { // indicates an administrator
  global $wpdb;
  $review_table = $wpdb->prefix . "ifr_review";

  $app = new \Slim\Slim();
  $app->response->headers->set('Content-Type', 'application/json');

  $app->get(
      '/review',
      function () {
        global $wpdb;
        $db_prefix = $wpdb->prefix;
        if (get_current_site()->id > 0) {
          $db_prefix = $db_prefix . get_current_site()->id . "_";
        }
        $review_table = $db_prefix . "ifr_review";
        var_dump($review_table);
        $results = $wpdb->get_results("SELECT * FROM $review_table", ARRAY_A);
        echo json_encode($results);
      }
  );

  $app->post(
      '/review',
      function () {
          echo 'review POST';
      }
  );

  $app->run();
}
else {
  header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden"); 
  echo "403 Forbidden";
}
?>
