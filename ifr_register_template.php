<?php 
get_header();
date_default_timezone_set("America/New_York");
$date = getdate();
$early_discount = 0;
// early bird discount for people who pay before April 15 2014
/*if ( ($date['year'] <= 2014) && (($date['month'] < 4) || (($date['month'] == 4) && ($date['day'] < 16) && ($date['hour'] < 18)) ) ) {
  $early_discount = 300;
}*/
?>
<h2>Register</h2>
<?php
if (!isset($_REQUEST['email']) || $_REQUEST['email'] == "") {
?>
<h3>Please input the email address that you used when you applied to attend ITP Camp:</h3>
<form method="post" action="" id="email_form">
  <input type="text" id="email" name="email" size="35" value=""><br/>
  <input type="submit" name="submit" value="Go"/>
</form>
<?php
}
elseif (isset($_REQUEST['email'])) {
  $email = $_REQUEST['email'];
  global $wpdb;
  $applicant = array();

  // lookup user
  $user_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_2_ifr_user WHERE email = %s", $email));
  if ($user_result) {
    // lookup decision for that user
    $decision_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_2_ifr_decision WHERE user = %d AND (decision = 'approve' OR decision = 'comp') ", $user_result->id));
    $register_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_2_ifr_register WHERE user = %d", $user_result->id));
    $payment_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_2_ifr_payment WHERE form = %d AND entry = %s", array($register_result->form, $register_result->entry)));
    if (!$decision_result) {
?>
<h2>Applicant not found, or applicant not yet accepted. If you wish to apply, please <a href="https://itp.nyu.edu/camp/2014/apply">apply here</a></h2>
<h3>If you have applied to Camp already, please input your email address that you used in your application:</h3>
  <form method="post" action="" id="email_form">
    <input type="text" id="email" name="email" size="35" value=""><br/>
    <input type="submit" name="submit" value="Submit"/>
  </form>
<?php
    }
    else if ($register_result && $payment_result && 
             ($payment_result->cc_decision == "ACCEPT") && 
             ($payment_result->cc_amount >= ($decision_result->payment_due - $early_discount))) {
?>
<h2>Already Registered</h2>
<p>Our records show that you have already registered and paid. Thanks! Stay tuned for more info about Camp soon!</p>
<?php
    }
    else if ($decision_result->decision == 'comp') {
?>
<h2>Acceptance</h2>
<p>Congratulations! You sound like a great fit for ITP camp.  You are officially in.  <b>Your admission is complimentary</b> so we do not need any payment from you, but we do need you to let us know whether you are coming to Camp:   

<form action="https://itp.nyu.edu/camp/2014/registration-processing" method="POST">
  <input type="hidden" name="user" value="<?php echo $decision_result->user; ?>" />
  <input type="hidden" name="form" value="<?php echo $decision_result->form ?>" />
  <input type="hidden" name="entry" value="<?php echo $decision_result->entry ?>" />
  <input type="hidden" name="accept" value="1" />
  <input type="submit" value="Yes, I'm coming to Camp!" /> 
</form>
<form action="https://itp.nyu.edu/camp/2014/registration-processing" method="POST">
  <input type="hidden" name="user" value="<?php echo $decision_result->user; ?>" />
  <input type="hidden" name="form" value="<?php echo $decision_result->form ?>" />
  <input type="hidden" name="entry" value="<?php echo $decision_result->entry ?>" />
  <input type="hidden" name="accept" value="0" />
  <input type="submit" value="No, I'm not coming to Camp" />
</form>
<?php
    }
    else if ($decision_result->decision == 'approve' && ($decision_result->payment_due - $early_discount) == 0) {
?>
<h2>Acceptance</h2>
<p>Congratulations! You sound like a great fit for ITP camp.  You are officially in.  <b>Your admission is complimentary if you register before April 15.</b> We do not need any payment from you, but we do need you to let us know whether you are coming to Camp: 

<form action="https://itp.nyu.edu/camp/2014/registration-processing" method="POST">
  <input type="hidden" name="user" value="<?php echo $decision_result->user; ?>" />
  <input type="hidden" name="form" value="<?php echo $decision_result->form ?>" />
  <input type="hidden" name="entry" value="<?php echo $decision_result->entry ?>" />
  <input type="hidden" name="accept" value="1" />
  <input type="submit" value="Yes, I'm coming to Camp!" /> 
</form>
<form action="https://itp.nyu.edu/camp/2014/registration-processing" method="POST">
  <input type="hidden" name="user" value="<?php echo $decision_result->user; ?>" />
  <input type="hidden" name="form" value="<?php echo $decision_result->form ?>" />
  <input type="hidden" name="entry" value="<?php echo $decision_result->entry ?>" />
  <input type="hidden" name="accept" value="0" />
  <input type="submit" value="No, I'm not coming to Camp" /> 
</form>
<?php
    }
    else if ($decision_result->decision == 'approve') {
?>
<h2>Acceptance and Payment</h2>
<p>Congratulations! You sound like a great fit for ITP camp.  You are officially in.</p>
<p>Payment will reserve your spot. We will accept payments until June 1st, pending availability. Feel free to email <a href="mailto:campinfo@itp.nyu.edu">campinfo@itp.nyu.edu</a> if you have any questions.  We look forward to seeing you in June!</p>

<h3>Payment Amount: $<?php echo ($decision_result->payment_due - $early_discount); ?></h3>
<p>You will be charged <strong>$<?php echo ($decision_result->payment_due - $early_discount); ?></strong>. This amount already includes all applicable discounts.</p>

<h3>Contact/Billing Information</h3>
<em> (all fields are required)</em>
<form id="myform" action="<?php echo get_option('ifr_paygate_URL'); ?>" method="post"> 
  <input type="hidden" name="user" value="<?php echo $decision_result->user; ?>" />
  <input type="hidden" name="form" value="<?php echo $decision_result->form; ?>" />
  <input type="hidden" name="entry" value="<?php echo $decision_result->entry; ?>" />
  <input type="hidden" name="accept" value="1" />
  <input type="hidden" name="AMOUNT_PAID" value="<?php echo ($decision_result->payment_due - $early_discount); ?>" />
  <input type="hidden" name="AMOUNT_EVT" id="AMOUNT_EVT" value="<?php echo ($decision_result->payment_due - $early_discount); ?>" />
  <input type="hidden" name="FORM_ID" value="<?php echo get_option('ifr_paygate_FORM_ID'); ?>" />
  <input type="hidden" name="ACCOUNT_EVT" value="<?php echo get_option('ifr_paygate_ACCOUNT_EVT'); ?>" />
  <input type="hidden" name="FUND_CODE_EVT" value="<?php echo get_option('ifr_paygate_FUND_CODE_EVT'); ?>" />
  <input type="hidden" name="DEPTID_EVT" value="<?php echo get_option('ifr_paygate_DEPTID_EVT'); ?>" />
  <input type="hidden" name="PROGRAM_CODE_EVT" value="<?php echo get_option('ifr_paygate_PROGRAM_CODE_EVT'); ?>" />
  <input type="hidden" name="PROJECT_ID_EVT" value="<?php echo get_option('ifr_paygate_PROJECT_ID_EVT'); ?>" />
  <label for="FIRST_NAME">First Name:</label>
  <input type="text" name="FIRST_NAME" id="FIRST_NAME" required="true" size="25" />
  <label for="LAST_NAME">Last Name:</label>
  <input type="text" name="LAST_NAME" id="LAST_NAME" required="true" size="25" />
  <label for="EMAIL">Email:</label>
  <input type="text" name="EMAIL" id="EMAIL" size="35" readonly="true" value="<?php echo $user_result->email; ?>" />
  <label for="PHONE">Phone:</label>
  <input type="text" name="PHONE" id="PHONE" size="15" required="true" />
  <label for="ADDRESS_LINE_1">Address:</label>
  <input type="text" name="ADDRESS_LINE_1" id="ADDRESS_LINE_1" size="35" required="true" />
  <label for="CITY">City:</label>
  <input type="text" name="CITY" id="CITY" size="20" required="true" />
  <label for="STATELIST">State</label>
  <select id="STATELIST" required="true" >
    <option value="NA">Non-US/Canada</option>
    <option value="AL">Alabama</option>
    <option value="AK">Alaska</option>
    <option value="AZ">Arizona</option>
    <option value="AR">Arkansas</option>
    <option value="CA">California</option>
    <option value="CO">Colorado</option>
    <option value="CT">Connecticut</option>
    <option value="DE">Delaware</option>
    <option value="DC">District of Columbia</option>
    <option value="FL">Florida</option>
    <option value="GA">Georgia</option>
    <option value="HI">Hawaii</option>
    <option value="ID">Idaho</option>
    <option value="IL">Illinois</option>
    <option value="IN">Indiana</option>
    <option value="IA">Iowa</option>
    <option value="KS">Kansas</option>
    <option value="KY">Kentucky</option>
    <option value="LA">Louisiana</option>
    <option value="ME">Maine</option>
    <option value="MD">Maryland</option>
    <option value="MA">Massachusetts</option>
    <option value="MI">Michigan</option>
    <option value="MN">Minnesota</option>
    <option value="MS">Mississippi</option>
    <option value="MO">Missouri</option>
    <option value="MT">Montana</option>
    <option value="NE">Nebraska</option>
    <option value="NV">Nevada</option>
    <option value="NH">New Hampshire</option>
    <option value="NJ">New Jersey</option>
    <option value="NM">New Mexico</option>
    <option value="NY" selected>New York</option>
    <option value="NC">North Carolina</option>
    <option value="ND">North Dakota</option>
    <option value="OH">Ohio</option>
    <option value="OK">Oklahoma</option>
    <option value="OR">Oregon</option>
    <option value="PA">Pennsylvania</option>
    <option value="RI">Rhode Island</option>
    <option value="SC">South Carolina</option>
    <option value="SD">South Dakota</option>
    <option value="TN">Tennessee</option>
    <option value="TX">Texas</option>
    <option value="UT">Utah</option>
    <option value="VT">Vermont</option>
    <option value="VA">Virginia</option>
    <option value="WA">Washington</option>
    <option value="WV">West Virginia</option>
    <option value="WI">Wisconsin</option>
    <option value="WY">Wyoming</option>
    <option value="AB">Canada - Alberta</option>
    <option value="BC">Canada - British Columbia</option>
    <option value="MB">Canada - Manitoba</option>
    <option value="NB">Canada - New Brunswick</option>
    <option value="NL">Canada - Newfoundland and Labrador</option>
    <option value="NS">Canada - Nova Scotia</option>
    <option value="NT">Canada - Northwest Territories</option>
    <option value="NU">Canada - Nunavut</option>
    <option value="ON">Canada - Ontario</option>
    <option value="PE">Canada - Prince Edward Island</option>
    <option value="QC">Canada - Quebec</option>
    <option value="SK">Canada - Saskatchewan</option>
    <option value="YT">Canada - Yukon</option>
  </select>
  <input type="hidden" id="STATE" name="STATE" value="NY" required />
  <input type="text" id="STATEINPUT" placeholder="enter non-US state" readonly style="display:none;" />
  <label for="POSTAL_CODE">Postal Code</label>
  <input type="text" name="POSTAL_CODE" id="POSTAL_CODE" size="10" size="5" required="true" />
  <label for="COUNTRY">Country</label>
  <select id="COUNTRY" name="COUNTRY" required="true" >
    <option selected="selected" value="us">United States</option>
<option value="af">Afghanistan</option>
<option value="al">Albania</option>
<option value="dz">Algeria</option>
<option value="as">American Samoa (US)</option>
<option value="ad">Andorra</option>
<option value="ao">Angola</option>
<option value="ai">Anguilla (UK)</option>
<option value="ag">Antigua and Barbuda</option>
<option value="ar">Argentina</option>
<option value="am">Armenia</option>
<option value="aw">Aruba</option>
<option value="au">Australia</option>
<option value="at">Austria</option>
<option value="az">Azerbaijan</option>
<option value="bs">Bahamas</option>
<option value="bh">Bahrain</option>
<option value="bd">Bangladesh</option>
<option value="bb">Barbados</option>
<option value="by">Belarus</option>
<option value="be">Belgium</option>
<option value="bz">Belize</option>
<option value="bj">Benin</option>
<option value="bm">Bermuda (UK)</option>
<option value="bt">Bhutan</option>
<option value="bo">Bolivia</option>
<option value="ba">Bosnia and Herzegovina</option>
<option value="bw">Botswana</option>
<option value="br">Brazil</option>
<option value="vg">British Virgin Islands (UK)</option>
<option value="bn">Brunei Darussalam</option>
<option value="bg">Bulgaria</option>
<option value="bf">Burkina Faso</option>
<option value="bi">Burundi</option>
<option value="kh">Cambodia</option>
<option value="cm">Cameroon</option>
<option value="ca">Canada</option>
<option value="cv">Cape Verde</option>
<option value="ky">Cayman Islands (UK)</option>
<option value="cf">Central African Republic</option>
<option value="td">Chad</option>
<option value="cl">Chile</option>
<option value="cn">China</option>
<option value="cx">Christmas Island (AU)</option>
<option value="cc">Cocos (Keeling) Islands (AU)</option>
<option value="co">Colombia</option>
<option value="km">Comoros</option>
<option value="cd">Congo, Democratic Republic of the</option>
<option value="cg">Congo, Republic of the</option>
<option value="ck">Cook Islands (NZ)</option>
<option value="cr">Costa Rica</option>
<option value="ci">Cote d'Ivoire</option>
<option value="hr">Croatia</option>
<option value="cu">Cuba</option>
<option value="cy">Cyprus</option>
<option value="cz">Czech Republic</option>
<option value="dk">Denmark</option>
<option value="dj">Djibouti</option>
<option value="dm">Dominica</option>
<option value="do">Dominican Republic</option>
<option value="ec">Ecuador</option>
<option value="eg">Egypt</option>
<option value="sv">El Salvador</option>
<option value="gq">Equatorial Guinea</option>
<option value="er">Eritrea</option>
<option value="ee">Estonia</option>
<option value="et">Ethiopia</option>
<option value="fk">Falkland Islands (UK)</option>
<option value="fo">Faroe Islands (DK)</option>
<option value="fj">Fiji</option>
<option value="fi">Finland</option>
<option value="fr">France</option>
<option value="gf">French Guiana (FR)</option>
<option value="pf">French Polynesia (FR)</option>
<option value="ga">Gabon</option>
<option value="gm">Gambia</option>
<option value="ge">Georgia</option>
<option value="de">Germany</option>
<option value="gh">Ghana</option>
<option value="gi">Gibraltar (UK)</option>
<option value="gr">Greece</option>
<option value="gl">Greenland (DK)</option>
<option value="gd">Grenada</option>
<option value="gp">Guadeloupe (FR)</option>
<option value="gu">Guam (US)</option>
<option value="gt">Guatemala</option>
<option value="gn">Guinea</option>
<option value="gw">Guinea-Bissau</option>
<option value="gy">Guyana</option>
<option value="ht">Haiti</option>
<option value="va">Holy See (Vatican City)</option>
<option value="hn">Honduras</option>
<option value="hk">Hong Kong (CN)</option>
<option value="hu">Hungary</option>
<option value="is">Iceland</option>
<option value="in">India</option>
<option value="id">Indonesia</option>
<option value="ir">Iran</option>
<option value="iq">Iraq</option>
<option value="ie">Ireland</option>
<option value="il">Israel</option>
<option value="it">Italy</option>
<option value="jm">Jamaica</option>
<option value="jp">Japan</option>
<option value="jo">Jordan</option>
<option value="kz">Kazakstan</option>
<option value="ke">Kenya</option>
<option value="ki">Kiribati</option>
<option value="kp">Korea, Democratic People's Republic (North)</option>
<option value="kr">Korea, Republic of (South)</option>
<option value="kw">Kuwait</option>
<option value="kg">Kyrgyzstan</option>
<option value="la">Laos</option>
<option value="lv">Latvia</option>
<option value="lb">Lebanon</option>
<option value="ls">Lesotho</option>
<option value="lr">Liberia</option>
<option value="ly">Libya</option>
<option value="li">Liechtenstein</option>
<option value="lt">Lithuania</option>
<option value="lu">Luxembourg</option>
<option value="mo">Macau (CN)</option>
<option value="mk">Macedonia</option>
<option value="mg">Madagascar</option>
<option value="mw">Malawi</option>
<option value="my">Malaysia</option>
<option value="mv">Maldives</option>
<option value="ml">Mali</option>
<option value="mt">Malta</option>
<option value="mh">Marshall islands</option>
<option value="mq">Martinique (FR)</option>
<option value="mr">Mauritania</option>
<option value="mu">Mauritius</option>
<option value="yt">Mayotte (FR)</option>
<option value="mx">Mexico</option>
<option value="fm">Micronesia, Federated States of</option>
<option value="md">Moldova</option>
<option value="mc">Monaco</option>
<option value="mn">Mongolia</option>
<option value="me">Montenegro</option>
<option value="ms">Montserrat (UK)</option>
<option value="ma">Morocco</option>
<option value="mz">Mozambique</option>
<option value="mm">Myanmar</option>
<option value="na">Namibia</option>
<option value="nr">Nauru</option>
<option value="np">Nepal</option>
<option value="nl">Netherlands</option>
<option value="an">Netherlands Antilles (NL)</option>
<option value="nc">New Caledonia (FR)</option>
<option value="nz">New Zealand</option>
<option value="ni">Nicaragua</option>
<option value="ne">Niger</option>
<option value="ng">Nigeria</option>
<option value="nu">Niue</option>
<option value="nf">Norfolk Island (AU)</option>
<option value="mp">Northern Mariana Islands (US)</option>
<option value="no">Norway</option>
<option value="om">Oman</option>
<option value="pk">Pakistan</option>
<option value="pw">Palau</option>
<option value="pa">Panama</option>
<option value="pg">Papua New Guinea</option>
<option value="py">Paraguay</option>
<option value="pe">Peru</option>
<option value="ph">Philippines</option>
<option value="pn">Pitcairn Islands (UK)</option>
<option value="pl">Poland</option>
<option value="pt">Portugal</option>
<option value="pr">Puerto Rico (US)</option>
<option value="qa">Qatar</option>
<option value="re">Reunion (FR)</option>
<option value="ro">Romania</option>
<option value="ru">Russia</option>
<option value="rw">Rwanda</option>
<option value="sh">Saint Helena (UK)</option>
<option value="kn">Saint Kitts and Nevis</option>
<option value="lc">Saint Lucia</option>
<option value="pm">Saint Pierre and Miquelon (FR)</option>
<option value="vc">Saint Vincent and the Grenadines</option>
<option value="ws">Samoa</option>
<option value="sm">San Marino</option>
<option value="st">Sao Tome and Principe</option>
<option value="sa">Saudi Arabia</option>
<option value="sn">Senegal</option>
<option value="rs">Serbia</option>
<option value="cs">Serbia and Montenegro</option>
<option value="sc">Seychelles</option>
<option value="sl">Sierra Leone</option>
<option value="sg">Singapore</option>
<option value="sk">Slovakia</option>
<option value="si">Slovenia</option>
<option value="sb">Solomon Islands</option>
<option value="so">Somalia</option>
<option value="za">South Africa</option>
<option value="gs">South Georgia &amp; South Sandwich Islands (UK)</option>
<option value="es">Spain</option>
<option value="lk">Sri Lanka</option>
<option value="sd">Sudan</option>
<option value="sr">Suriname</option>
<option value="sz">Swaziland</option>
<option value="se">Sweden</option>
<option value="ch">Switzerland</option>
<option value="sy">Syria</option>
<option value="tw">Taiwan</option>
<option value="tj">Tajikistan</option>
<option value="tz">Tanzania</option>
<option value="th">Thailand</option>
<option value="tl">Timor-Leste</option>
<option value="tg">Togo</option>
<option value="tk">Tokelau</option>
<option value="to">Tonga</option>
<option value="tt">Trinidad and Tobago</option>
<option value="tn">Tunisia</option>
<option value="tr">Turkey</option>
<option value="tm">Turkmenistan</option>
<option value="tc">Turks and Caicos Islands (UK)</option>
<option value="tv">Tuvalu</option>
<option value="ug">Uganda</option>
<option value="ua">Ukraine</option>
<option value="ae">United Arab Emirates</option>
<option value="gb">United Kingdom</option>
<option value="uy">Uruguay</option>
<option value="uz">Uzbekistan</option>
<option value="vu">Vanuatu</option>
<option value="ve">Venezuela</option>
<option value="vn">Vietnam</option>
<option value="vi">Virgin Islands (US)</option>
<option value="wf">Wallis and Futuna (FR)</option>
<option value="eh">Western Sahara</option>
<option value="ye">Yemen</option>
<option value="zm">Zambia</option>
<option value="zw">Zimbabwe</option>
  </select>
  <p class="privacy_policy"><strong>Notice:</strong> New York University is committed to respecting your privacy. You can be assured that personal information will only be used by New York University to conduct official University business and personal information will never be disseminated to any unaffiliated third party.<br/> <a href="http://www.nyu.edu/about/notice.html">New York University's privacy policy</a></p>
  <input type="submit" name="submit" id="submit" value="Register" />
</form>
<?php
    }
  }
  else {
?>
<h2>Applicant not found, or applicant not yet accepted. If you wish to apply, please <a href="https://itp.nyu.edu/camp/2014/apply">apply here</a>.</h2>
<?php
  }
}
?>

<h3>Questions? Problems?</h3>
<p>If you need help, please contact us at (212) 998-1880 or email <a href="mailto:campinfo@itp.nyu.edu">campinfo@itp.nyu.edu</a>.</p>

<script type="text/javascript">
jQuery(document).ready(function() {
  function nonUS() {
    jQuery('select#STATELIST').attr('style', 'display:none;');
    jQuery('input#STATEINPUT')
      .attr('readonly', null)
      .attr('style', null);
    jQuery('input#STATE').val(jQuery('input#STATEINPUT').val());
  };
  
  function US() {
    jQuery('input#STATEINPUT')
      .attr('readonly', true)
      .attr('style', 'display:none;');
    jQuery('select#STATELIST').attr('style', null);
    jQuery('input#STATE').val(jQuery('select#STATELIST').val());
  };
  
  jQuery('select#STATELIST').on('change', function() {
    if (jQuery(this).val() == 'NA') {
      nonUS();
    }
    else {
      US();
    }
    jQuery(this).trigger('blur');
  });
  jQuery('select#COUNTRY').on('change', function() {
    if ((jQuery(this).val() == 'us') || (jQuery(this).val() == 'ca')) {
      US();
    }
    else {
      nonUS();
    }
    jQuery(this).trigger('blur');
  });
  jQuery('input#STATEINPUT').on('change', function() {
    jQuery('input#STATE').val(jQuery('input#STATEINPUT').val());
  });

  US();
});
</script>

<?php
get_footer();
?>
