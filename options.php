<?php

?>
<div class="wrap">
<h2>Form Review Settings</h2>

<form method="post" action="options.php">

<?php 
settings_fields( 'ifr_gravity_section' ); 
do_settings_sections( 'ifr_gravity_section' );
submit_button();
?>

</form>
</div>

</form>

