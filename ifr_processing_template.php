<?php 
date_default_timezone_set("America/New_York");
$date = getdate();
$early_discount = 0;
// early bird discount for people who pay before April 15 2014
/*if ( ($date['year'] <= 2014) && (($date['month'] <= 4) || (($date['month'] == 4) && ($date['day'] < 15)) ) ) {
  $early_discount = 300;
}*/

$register_data = array('user' => null, 'form' => null, 'entry' => null, 'accept' => null);
global $wpdb;
if (validate($register_data)) {
  $accept_data['user'] = $_POST['user'];
  $accept_data['form'] = $_POST['form'];
  $accept_data['entry'] = $_POST['entry'];
  $accept_data['accept'] = $_POST['accept'];
  // lookup user
  $user_result = $wpdb->get_row($wpdb->prepare("SELECT id FROM wp_2_ifr_user WHERE id = %d", $_POST['user']));
  if ($user_result) {
    // lookup decision for that user
    $decision_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_2_ifr_decision WHERE user = %d", $user_result->id));
    if ($decision_result) {
      if (($decision_result->decision == 'comp') 
          || (($decision_result->decision == 'approve') && (($decision_result->payment_due - $early_discount) == 0))) {
        if ($wpdb->insert('wp_2_ifr_register', $accept_data)) {
          if ($_POST['accept'] == 1) {
            header('Location: https://itp.nyu.edu/camp/2014/registration-successful');
          }
          else if ($_POST['accept'] == 0) {
            header('Location: https://itp.nyu.edu/camp/2014/registration-declined');
          }
          exit;
        }
      }
      else if ($decision_result->decision == 'approve') {
        $payment_data['form'] = $_POST['form'];
        $payment_data['entry'] = $_POST['entry'];
        $payment_data['cc_amount'] = $_POST['AMOUNT_PAID'];
        $payment_data['cc_time'] = $_POST['ccAuthReply_authorizedDateTime'];
        $payment_data['cc_seq_no'] = $_POST['orderNumber'];
        $payment_data['cc_transaction_id'] = $_POST['pg_transaction_id'];
        $payment_data['cc_transaction_sig'] = $_POST['transactionSignature'];
	$payment_data['cc_decision'] = $_POST['decision'];

        if (($decision_result->payment_due - $early_discount) == $payment_data['cc_amount']) {
          if ($wpdb->insert('wp_2_ifr_payment', $payment_data)) {
            if ($wpdb->insert('wp_2_ifr_register', $accept_data)) {
              header('Location: https://itp.nyu.edu/camp/2014/payment-successful');
              exit;
            }
          }
        }
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

<h3>Processing Error</h3>
<p>Uh oh! Something went wrong with your registration. We’re investigating the cause of the error, but please contact us at (212) 998-1880 or <a href="mailto:campinfo@itp.nyu.edu">campinfo@itp.nyu.edu</a> to get more information.</p>

<?php
get_footer();
?>
