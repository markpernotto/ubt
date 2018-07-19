<?php
/**
 * Template Name: UBT Template
 */
$city = urldecode(get_query_var( 'city' ));
$state = urldecode(get_query_var( 'state' ));
$city_state = $city.', '.$state;
get_header();
global $wpdb;
$result = $wpdb->get_results("SELECT template_content FROM ".$wpdb->prefix . 'ubt_templates');
foreach($result as $ubt){
    $php_formatted_output = $ubt->template_content;
    eval("\$php_formatted_output = \"$php_formatted_output\";");
    echo $php_formatted_output;
}
get_footer();
?>
