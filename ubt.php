<?php
/*
Plugin Name: URL Builder
Plugin URI: http://pernotto.com/
Description: Builds a URL from an uploaded CSV file to populate a template.
Version: 0.0.0.1
Author: mark.pernotto
Author URI: https://pernotto.com/
License: GPLv2 or later
*/

if ( !function_exists( 'add_action' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}
    global $wpdb;
    global $wp_rewrite;
    global $wp;
    global $locations_tbl = $wpdb->prefix . 'ubt_locations';
    global $templates_tbl = $wpdb->prefix . 'ubt_templates';
    register_activation_hook( __FILE__, 'ubt_create_db' );
    function ubt_create_db() {
        $version = get_option( 'ubt_version', '0.0.0.1' );
        $charset_collate = $wpdb->get_charset_collate();
        $locations_sql = "CREATE TABLE IF NOT EXISTS $locations_tbl (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            city varchar(255) NOT NULL,
            state varchar(255) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
        $template_meta_sql = "CREATE TABLE IF NOT EXISTS $templates_tbl (
            template_id mediumint(9) NOT NULL,
            template_content MEDIUMTEXT NOT NULL,
            UNIQUE KEY id (template_id)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $locations_sql );
        dbDelta( $template_meta_sql );
        $wp_rewrite->flush_rules();
    }

    register_uninstall_hook(__FILE__, 'ubt_uninstall_db');
    function ubt_uninstall_db() {
        $wpdb->query("DROP TABLE IF EXISTS ".$locations_tbl);
        $wpdb->query("DROP TABLE IF EXISTS ".$templates_tbl);
    }

    function support_menu() {
        add_menu_page('UBT Upload', 'Batch Upload', 'administrator', 'ubt_upload', 'ubt_init', 'dashicons-upload', 51); 
    }
    add_action('admin_menu', 'support_menu');

    function ubt_add_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
        $post_templates['page-ubt-template.php'] = __('UBT Template');
        return $post_templates;
    }
    add_filter( 'theme_page_templates', 'ubt_add_template_to_select', 10, 4 );

    function ubt_load_plugin_template( $template ) {
        if(  get_page_template_slug() === 'page-ubt-template.php' ) {
            $template = plugin_dir_path( __FILE__ ) . 'page-ubt-template.php';
        }
        if($template == '') {
            throw new \Exception('No template found');
        }
        return $template;
    }
    add_filter( 'template_include', 'ubt_load_plugin_template' );

    remove_filter('template_redirect','redirect_canonical');

    function custom_rewrite_rule() {
      $wp->add_query_var('city');
      $wp->add_query_var('state');
      add_rewrite_rule('([^/]*)/([^/]*)','index.php?page_id='.get_ubt_template_id().'&state=$matches[1]&city=$matches[2]','top');
      // if you were going to have the temdo a sub-page, it would go something like this:
      // add_rewrite_rule('^'.get_ubt_template_slug().'/([^/]*)/([^/]*)/?','index.php?page_id='.get_ubt_template_id().'&state=$matches[1]&city=$matches[2]','top');
    }
    add_action('init', 'custom_rewrite_rule', 1, 1);

    add_action('get_footer', 'ubt_template_output', 500, 200);
    function ubt_template_output(){
        $city = urldecode(get_query_var( 'city' ));
        $state = urldecode(get_query_var( 'state' ));
        if($city == ''){
            $city='New York City';
        }
        if($state == ''){
            $state='New York';
        }
        $city_state = $city.', '.$state;
        $result = $wpdb->get_results("SELECT template_content FROM ".$templates_tbl);
        foreach($result as $ubt){
            $php_formatted_output = $ubt->template_content;
            eval("\$php_formatted_output = \"$php_formatted_output\";");
            echo "<div style='float:left;'>".$php_formatted_output."</div>";
        }
    }

    add_action('wp_footer', 'ubt_location_output', 500, 200);
    function ubt_location_output(){
        $result = $wpdb->get_results("SELECT city,state FROM $locations_tbl");
        echo "<div style='width:99.6%;margin:10rem auto 0rem;background:#292929;height:20rem;clear:both;'></div>";
        echo "<table style='width:70%;margin:0rem auto 10rem;background:#213380;clear:both;'>";
        $count = 0;
        $subcount = 1;
        foreach($result as $ubt){
            if($subcount == 1)
                echo "<tr>";
            echo "<td style='padding:0.5rem 0.5rem 0 0.5rem'><a href='".site_url()."/".$ubt->state."/".$ubt->city."/' style='color:#FFF;font-size:0.8rem;text-decoration:none;text-align:center'>".$ubt->city.", ".$ubt->state."</a></td>";
            if($subcount == 4){
                $subcount=0;
                echo "</tr>";
            }
            $subcount++;
            $count++;
        }
        echo "</tr>";
        echo "</table>";
    }

    function get_ubt_template_id(){
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'page-ubt-template.php'
        ));
        foreach($pages as $page){
            return $page->ID;
        }
    }

    function get_ubt_template_slug(){
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'page-ubt-template.php'
        ));
        foreach($pages as $page){
            return $page->post_name;
        }
    }

    function ubt_init(){
        if(isset($_POST['ubt_upload_csv'])){
            $wpdb->query("TRUNCATE TABLE ".$locations_tbl);
            $file_tmp = $_FILES["file"]["tmp_name"];
            $handle = fopen($file_tmp, 'r+', 1);
            while (($data = fgetcsv($handle)) !== FALSE) {
                $wpdb->insert($locations_tbl, array(
                    'city' => $data[0],
                    'state' => $data[1]
                ));
            }
            fclose($handle);
            print('Import process Completed.');
        }
    ?>
        <h1>City State CSV UPLOADER</h1>
        <form enctype="multipart/form-data" accept-charset="utf-8" method="post" >
        <input type="hidden" name="MAX_FILE_SIZE" value="900000" />
        Upload city/state <strong>.csv</strong> file only: 
        <input name="file" type="file" />
        <input type="submit" name="ubt_upload_csv" value="Upload File" />
        </form>
        <?php

        if(isset($_POST['ubt_update_template'])){

                $wpdb->update( 
                    $templates_tbl, 
                    array( 
                        'template_content' => stripslashes($_POST['template_input'])
                    ), 
                    array( 'template_id' => get_ubt_template_id() ), 
                    array( 
                        '%s'
                    ), 
                    array( '%s' ) 
                );
            print('Template Updated.');
        }

        if(get_ubt_template_id()){
            $result = $wpdb->get_results("SELECT template_id FROM ".$templates_tbl." WHERE template_id=".get_ubt_template_id());
            $template_content="<h2>Hello World!</h2>";
            if(!$result){
                $wpdb->insert($templates_tbl,array('template_id' => get_ubt_template_id()));
            }
            else{
                $template_results=$wpdb->get_results("SELECT template_content FROM ".$templates_tbl." WHERE template_id=".get_ubt_template_id());
                foreach($template_results as $template_result)
                    $template_content=$template_result->template_content;
            }

            $url=site_url().'/New%20York%20City/New%20York/';
?>
            <h3>Preview template here: <a href="<?=$url?>" target="_blank"><?=$url?></a></h3>
            <form method="post" >
            <h4>Key:</h4>
            <ul>
                <li>$city to list a city.</li>
                <li>$state to list a state.</li>
                <li>$city_state to list as City, State layout</li>
            </ul>
            <textarea name="template_input" rows="30" cols="80"><?=$template_content?></textarea>
            <input type="submit" name="ubt_update_template" value="Update Template" />
            </form>
<?php
        }
        else{
            echo '<h3>Please apply the <strong>UBT Template</strong> to only one page.</h3>';
        }
    }
        ?>