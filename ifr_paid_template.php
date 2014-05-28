<?php 
$register_data = array('user' => null, 'form' => null, 'entry' => null, 'accept' => null);
global $wpdb;
if (validate($register_data)) {
  // lookup user
  $user_result = $wpdb->get_row($wpdb->prepare("SELECT id FROM wp_2_ifr_user WHERE id = %d", $_POST['user']));
  if ($user_result) {
    // lookup decision for that user
    $decision_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_2_ifr_decision WHERE user = %d AND (decision = 'approve' AND payment_due > 0)) ", $user_result->id));
    if ($decision_result) {
      $register_data['user'] = $_POST['ifr_user'];
      $register_data['form'] = $_POST['ifr_form'];
      $register_data['entry'] = $_POST['ifr_entry'];
      $register_data['accept'] = $_POST['ifr_accept'];

      if ($wpdb->insert('wp_2_ifr_register', $register_data)) {
        $payment_data['form'] = $_POST['ifr_form'];
        $payment_data['entry'] = $_POST['ifr_entry'];
        $payment_data['cc_amount'] = $_POST['AMOUNT_PAID'];
        $payment_data['cc_time'] = $_POST['ccAuthReply_authorizedDateTime'];
        $payment_data['cc_seq_no'] = $_POST['req_reference_number'];
        $payment_data['cc_transaction_id'] = $_POST['pg_transaction_id'];
        $payment_data['cc_transaction_sig'] = $_POST['transactionSignature'];
        $payment_data['cc_decision'] = $_POST['decision'];

        if ($wpdb->insert('wp_2_ifr_payment', $payment_data)) {
          header('Location: https://itp.nyu.edu/camp/2014/payment-successful');
          exit;
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

<h2>Processing Error</h2>
<p>Uh oh! Something went wrong with your registration. We’re investigating the cause of the error, but please contact us at (212) 998-1880 or <a href="mailto:campinfo@itp.nyu.edu">campinfo@itp.nyu.edu</a> to get more information.</p>

<?php
get_footer();
?>
