<?php 
get_header();

$ifr_content = file_get_contents(plugin_dir_path(__FILE__) . 'html/ifr_register_form.html');

echo $ifr_content;

get_footer();
?>
