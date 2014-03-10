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

register_activation_hook( __FILE__, 'ifr_setup');

add_action('admin_init', 'ifr_settings');
add_action('admin_menu', 'ifr_menu');

# From Gravity Forms API documentation
function calculate_signature($string, $private_key) {
  $hash = hash_hmac("sha1", $string, $private_key, true);
  $sig = rawurlencode(base64_encode($hash));
  return $sig;
}

function ifr_gravity_private_key_callback() {
  echo '<input name="ifr_gravity_private_key" id="ifr_gravity_private_key" type="text" />';
}

function ifr_gravity_public_key_callback() {
  echo '<input name="ifr_gravity_public_key" id="ifr_gravity_public_key" type="text" />';
}

function ifr_menu() {
  add_management_page( 'Form Review', 'Form Review', 'manage_options', 'itp-form-review', 'ifr_page');
}

function ifr_page() {
  echo '<h2>Form Review</h2>';

  # From Gravity Forms API, should be rolled into a function
  $public_key = get_option('ifr_gravity_public_key');
  $private_key = get_option('ifr_gravity_private_key');
  $method = "GET";
  $route = "forms/2/entries";
  date_default_timezone_set('America/New_York');
  $expires = strtotime("+60 mins");
  $string_to_sign = sprintf("%s:%s:%s:%s", $public_key, $method, $route, $expires);
  $sig = calculate_signature($string_to_sign, $private_key);
  echo site_url() . "/gravityformsapi/" . $route . "?api_key=" . $public_key . "&signature=" . $sig . "&expires=" . $expires;
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
