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
      function () use ($app) {
        global $wpdb;
        $blog_id = $app->request->params('blog');
        $db_prefix = $wpdb->prefix;
        if ($blog_id != null) {
          $db_prefix = $db_prefix . $blog_id . "_";
        }
        $review_table = $db_prefix . "ifr_review";
        $query = "SELECT * FROM $review_table";
        $results = $wpdb->get_results($query, ARRAY_A);
        $res["num_objects"] = count($results);
        $res["objects"] = $results;
        echo json_encode($res);
      }
  );

  $app->post(
      '/review',
      function () use ($app) {
          //echo 'review POST';
      }
  );

  $app->run();
}
else {
  header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden"); 
  echo "403 Forbidden";
}
?>
