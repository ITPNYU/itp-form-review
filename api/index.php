<?php
require '../lib/Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

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
      echo "review GET";
    }
);

$app->post(
    '/review',
    function () {
        echo 'review POST';
    }
);

$app->run();
?>
