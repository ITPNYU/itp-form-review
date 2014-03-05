<?php
/**
 * Plugin Name: ITP Form Review
 * Plugin URI: http://github.com/ITPNYU/itp-form-review
 * Description: Wordpress plugin for reviewing form submissions
 * Version: 1.0
 * Author: NYU ITP
 * Author URI: http://itp.nyu.edu
 * License: GPLv3
 */

add_action('admin_menu', 'formReviewMenu');

function formReviewMenu() {
  add_management_page( 'Form Review', 'Form Review', 'manage_options', 'itp-form-review', 'formReviewPage');
}

function formReviewPage() {
  echo "<h2>Form Review</h2>";  
}

?>
