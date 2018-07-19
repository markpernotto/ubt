<?php
/**
 * Template Name: UBT Template
 */

//These are used as variables within the Plugin template
$city = urldecode(get_query_var( 'city' ));
$state = urldecode(get_query_var( 'state' ));
$city_state = $city.', '.$state;

//So that we can still edit via CSS in theme Customizer
get_header();

global $wpdb;
//This is gross - if there's more than one than page assigned this template then ¯\_(ツ)_/¯
$result = $wpdb->get_results("SELECT template_content FROM ".$wpdb->prefix . 'ubt_templates');
foreach($result as $ubt){
    $php_formatted_output = $ubt->template_content;
    eval("\$php_formatted_output = \"$php_formatted_output\";");
    echo $php_formatted_output;
}
get_footer();
?>
