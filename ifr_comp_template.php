<?php 
$register_data = array('user' => null, 'form' => null, 'entry' => null, 'accept' => null);
global $wpdb;
if (validate()) {
  // lookup user
  $user_result = $wpdb->get_row($wpdb->prepare("SELECT id FROM wp_2_ifr_user WHERE id = %d", $_POST['user']));
  if ($user_result) {
    // lookup decision for that user
    $decision_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_2_ifr_decision WHERE user = %d AND (decision = 'comp' OR (decision = 'approve' AND payment_due = 0)) ", $user_result->id));
    if ($decision_result) {
      $accept_data['user'] = $_POST['user'];
      $accept_data['form'] = $_POST['form'];
      $accept_data['entry'] = $_POST['entry'];
      $accept_data['accept'] = $_POST['accept'];
     
      if ($wpdb->insert('wp_2_ifr_register', $accept_data)) {
        header('Location: https://itp.nyu.edu/camp/2014/registration-successful');
        exit;
      }
    }
  }
}

get_header();

function validate($arg) {
  foreach ($arg as $key => $value) {
    if (!isset($_POST[$key]) || !is_numeric($_POST[$key])) {
      return false;
    }
  }
  return true;
}

?>

<h2>Processing Error</h2>
<p>Uh oh! Something went wrong with your registration. We’re investigating the cause of the error, but please contact us at (212) 998-1880 or <a href="mailto:campinfo@itp.nyu.edu">campinfo@itp.nyu.edu</a> to get more information.</p>

<?php
get_footer();
?>