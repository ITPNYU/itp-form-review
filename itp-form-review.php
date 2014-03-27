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

require 'ifr_gravity.php';

global $ifr_db_version;
$ifr_db_version = "8";

register_activation_hook( __FILE__, 'ifr_setup');
register_activation_hook( __FILE__, 'ifr_db_install');

add_action('admin_init', 'ifr_settings');
add_action('admin_menu', 'ifr_menu');
add_action('plugins_loaded', 'ifr_db_upgrade');

add_filter( 'template_redirect', 'ifr_register_template');

function ifr_gravity_private_key_callback() {
  $private_key = get_option('ifr_gravity_private_key');
  $val = '';
  if (isset($private_key)) {
    $val = 'value="' . $private_key . '"';
  }
  echo '<input name="ifr_gravity_private_key" id="ifr_gravity_private_key" type="text" ' . $val . ' />';
}

function ifr_gravity_public_key_callback() {
  $public_key = get_option('ifr_gravity_public_key');
  $val = '';
  if (isset($public_key)) {
    $val = 'value="' . $public_key . '"';
  }
  echo '<input name="ifr_gravity_public_key" id="ifr_gravity_public_key" type="text" ' . $val . ' />';
}

function ifr_db_create() {
  global $wpdb;
  global $ifr_db_version;
  $review_table = $wpdb->prefix . "ifr_review";
  $user_table = $wpdb->prefix . "ifr_user";
  $decision_table = $wpdb->prefix . "ifr_decision";
  $payment_table = $wpdb->prefix . "ifr_payment";

  $review_sql = "CREATE TABLE $review_table (
id INT NOT NULL AUTO_INCREMENT,
form INT NOT NULL,
entry VARCHAR(20) NOT NULL,
reviewer VARCHAR(20) NOT NULL,
recommendation VARCHAR(20) NOT NULL,
comment VARCHAR(1000),
PRIMARY KEY  (id)
);";

  $user_sql = "CREATE TABLE $user_table (
id INT NOT NULL AUTO_INCREMENT,
fname VARCHAR(50) NOT NULL,
lname VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL,
username VARCHAR(50) NULL,
password VARCHAR(50) NULL,
PRIMARY KEY  (id)
);";

  $decision_sql = "CREATE TABLE $decision_table (
id INT NOT NULL AUTO_INCREMENT,
user INT NULL,
form INT NOT NULL,
entry VARCHAR(20) NOT NULL,
reviewer VARCHAR(20) NOT NULL,
decision VARCHAR(20) NOT NULL,
payment_due VARCHAR(20) NOT NULL,
PRIMARY KEY  (id),
FOREIGN KEY (user) REFERENCES $user_table(id)
);";

  $payment_sql = "CREATE TABLE $payment_table (
id INT NOT NULL AUTO_INCREMENT,
form INT NOT NULL,
entry VARCHAR(20) NOT NULL,
session_id VARCHAR(20) NOT NULL,
cc_amount VARCHAR(20) NULL,
cc_time VARCHAR(30) NULL,
cc_seq_no VARCHAR(20) NULL,
cc_transaction_id VARCHAR(20) NULL,
cc_transaction_sig VARCHAR(50) NULL,
cc_decision VARCHAR(20) NULL,
PRIMARY KEY  (id)
);";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta($review_sql);
  dbDelta($user_sql);
  dbDelta($decision_sql);
  dbDelta($payment_sql);

  add_option("ifr_db_version", $ifr_db_version);
}

function ifr_db_upgrade() {
  global $ifr_db_version;
  if (get_option('ifr_db_version') != $ifr_db_version) {
    ifr_db_create();
  }
}

function ifr_menu() {
  # $hookname is something like tools_page_itp-form-review
  $page_hook = add_management_page( 'Form Review', 'Form Review', 'manage_options', 'itp_form_review', 'ifr_page');
  add_action('admin_print_scripts-' . $page_hook, 'ifr_script_load');
  #add_action('admin_enqueue_scripts-' . $page_hook, 'ifr_script_load'); # FIXME? is this right?
}

function ifr_page() {
  echo file_get_contents(plugin_dir_path(__FILE__) . '/html/ifrPage.html');
  echo '<script type="text/javascript">
  var ifr_form_query = "' . ifr_form_query("forms/2/entries") . '";
  var ifr_api = "' . network_site_url() . 'wp-content/plugins/itp-form-review/api/";
</script>';
  echo '<script type="text/javascript">';
  echo file_get_contents(plugin_dir_path(__FILE__) . '/js/entryCtrl.js');
  echo file_get_contents(plugin_dir_path(__FILE__) . '/js/reviewCtrl.js');
  echo file_get_contents(plugin_dir_path(__FILE__) . '/js/decisionCtrl.js');
  echo file_get_contents(plugin_dir_path(__FILE__) . '/js/paymentCtrl.js');

  echo '</script>';
}

function ifr_register_template() {
  if (is_page('register')) {
    $location = plugin_dir_path(__FILE__) . 'ifr_register_template.php';
    //echo "plugin " . $location;
    if ( file_exists( $location ) ) {
      load_template($location); 
      exit();
    }
  }
}

function ifr_script_load($hook) {
  wp_enqueue_style('bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css');
 
  wp_register_script('angular', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.14/angular.min.js');
  wp_register_script('angular-sanitize', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.14/angular-sanitize.min.js', array('angular'));
  wp_register_script('angular-ui-bootstrap', '//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.10.0/ui-bootstrap-tpls.min.js', array('angular'));
  wp_enqueue_script('angular');
  wp_enqueue_script('angular-sanitize');
  wp_enqueue_script('angular-ui-bootstrap');
}

function ifr_settings() {
  add_settings_section(
    'ifr_gravity_section',
    'ITP Form Review Plugin',
    'ifr_section',
    'general'
  );

  add_settings_field(
    'ifr_gravity_public_key',
    'Gravity Forms API Public Key',
    'ifr_gravity_public_key_callback',
    'general',
    'ifr_gravity_section'
  );
  
  add_settings_field(
    'ifr_gravity_private_key',
    'Gravity Forms API Private Key',
    'ifr_gravity_private_key_callback',
    'general',
    'ifr_gravity_section'
  );

  register_setting( 'general', 'ifr_gravity_public_key');
  register_setting( 'general', 'ifr_gravity_private_key');
}

function ifr_setup() {
  add_option('ifr_gravity_public_key');
  add_option('ifr_gravity_private_key');
} 

?>
