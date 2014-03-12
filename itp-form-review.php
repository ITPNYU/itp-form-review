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
  add_management_page( 'Form Review', 'Form Review', 'manage_options', 'itp-form-review', 'ifr_page');
}

function ifr_form_query($route) {
  # From Gravity Forms API
  $public_key = get_option('ifr_gravity_public_key');
  $private_key = get_option('ifr_gravity_private_key');
  $method = "GET";
  date_default_timezone_set('America/New_York'); # FIXME: get from Wordpress
  $expires = strtotime("+60 mins");
  $paging = '200';
  $string_to_sign = sprintf("%s:%s:%s:%s", $public_key, $method, $route, $expires);
  $sig = calculate_signature($string_to_sign, $private_key);
  $query_url = site_url() . "/gravityformsapi/" . $route . "?api_key=" . $public_key . "&signature=" . $sig . "&expires=" . $expires . "&paging[page_size]=" . $paging;
  return $query_url;
}

function ifr_page() {
  echo '<h2>Form Review</h2>';
  echo ifr_form_query("forms/2/entries");

  echo '<div ng-app="ifrApp">';

  echo '<div ng-controller="EntriesCtrl">';

  echo '<input type="search" ng-model="entryFilter" placeholder="search" />';
  echo '<div class="ifrEntry" ng-repeat="e in entries | orderBy:date_created:reverse | filter:entryFilter">
  <h3 class="ifrEntryHeader">{{e["1"]}} {{e["2"]}}</h3>
  <ul>
    <li><b>Email</b>: {{e["3"]}}</li>
    <li><b>Location</b>: {{e["10"]}}</li>
    <li><b>Work</b>: {{e["4"]}}</li>
    <li><b>Links</b>: <span ng-bind-html="{{e.5}} | linky"</span></li>
    <li><b>Affiliation</b>: {{e["6"]}}</li>
    <li><b>Goals for Camp</b>: {{e["7"]}}</li>
    <li><b>Skills/Contributions</b>: {{e["8"]}}</li>
    <li><b>Proposed Session</b>: {{e["9"]}}</li>
    <li><b>Anything Else</b>: {{e["11"]}}</li>
  </ul>

</div><!-- .ifrEntry -->';

  echo '</div> <!-- ng-app -->';

  echo '<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.14/angular.min.js"></script>';
  echo '<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.14/angular-sanitize.min.js"></script>';
  echo '<script type="text/javascript">
      var ifrApp = angular.module("ifrApp", ["ngSanitize"]);
 
      ifrApp.controller("EntriesCtrl", function ($scope, $http) {

        $http.get("' . ifr_form_query("forms/2/entries") . '").success(function(data) {
          $scope.entries = data.response.entries;
        });
      });
    </script>';
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
