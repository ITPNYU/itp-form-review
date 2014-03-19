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

function ifr_form_query($route) {
  # From Gravity Forms API
  $public_key = get_option('ifr_gravity_public_key');
  $private_key = get_option('ifr_gravity_private_key');
  $method = "GET";
  date_default_timezone_set('America/New_York'); # FIXME: get from Wordpress
  $expires = strtotime("+60 mins");
  $paging = '250'; # limit API to first 250 results
  $string_to_sign = sprintf("%s:%s:%s:%s", $public_key, $method, $route, $expires);
  $sig = calculate_signature($string_to_sign, $private_key);
  $query_url = site_url() . "/gravityformsapi/" . $route . "?api_key=" . $public_key . "&signature=" . $sig . "&expires=" . $expires . "&paging[page_size]=" . $paging;
  return $query_url;
}

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

function ifr_menu() {
  # $hookname is something like tools_page_itp-form-review
  $page_hook = add_management_page( 'Form Review', 'Form Review', 'manage_options', 'itp_form_review', 'ifr_page');
  add_action('admin_print_scripts-' . $page_hook, 'ifr_script_load');
  #add_action('admin_enqueue_scripts-' . $page_hook, 'ifr_script_load'); # FIXME? is this right?
}

function ifr_page() {
  echo file_get_contents(plugin_dir_path(__FILE__) . '/html/ifrPage.html');
  echo '<script type="text/javascript">
    var ifrApp = angular.module("ifrApp", ["ngSanitize", "ui.bootstrap"]);

    ifrApp.controller("EntriesCtrl", function ($scope, $http) {
      $http.get("' . ifr_form_query("forms/2/entries") . '").success(function(data) {
        // augment data with affiliations field
        var fields = ["6.1", "6.2", "6.3", "6.4", "6.5", "6.6"];
        for (var e in data.response.entries) {
          var affiliations = [];
          for (var f in fields) {
            if (data.response.entries[e][fields[f]] != "") {
              affiliations.push(data.response.entries[e][fields[f]]);
            }
          }
          data.response.entries[e]["affiliations"] = affiliations;
        }
        // now copy the data into scope
        $scope.entries = data.response.entries;
      });
    });';

  echo file_get_contents(plugin_dir_path(__FILE__) . '/js/reviewCtrl.js');
  echo file_get_contents(plugin_dir_path(__FILE__) . '/js/decisionCtrl.js');

  echo '</script>';
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
