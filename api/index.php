<?php
require '../lib/Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if (current_user_can('activate_plugins')) { // indicates an administrator
  global $wpdb;
  $review_table = $wpdb->prefix . "ifr_review";

  /**
   * Step 2: Instantiate a Slim application
   *
   * This example instantiates a Slim application using
   * its default settings. However, you will usually configure
   * your Slim application now by passing an associative array
   * of setting names and values into the application constructor.
   */
  $app = new \Slim\Slim();

  $app->get(
      '/review',
      function () {
        $app->response->header('Content-Type', 'application/json');
        $results = $wpdb->get_results($wpdb->prepare("SELECT ALL FROM $review_table"), OBJECT);
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
