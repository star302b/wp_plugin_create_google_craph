<?php
/*
Plugin Name: Custom Plugin Create Graph
Description: Create graph
Version: 1.0
Author: Denis Myagkii
Author URI: https://ua.linkedin.com/in/�����-������-80b50111a
*/
define('WP_CREATE_GRAPH_DIR', plugin_dir_path(__FILE__));
define('WP_CREATE_GRAPH_URL', plugin_dir_url(__FILE__));
register_activation_hook(__FILE__, 'wp_create_graph_activation');
register_deactivation_hook(__FILE__, 'wp_create_graph_deactivation');
//Register shortcode
function wp_create_graph_user_shortcode ()
{
    $array_finish = creat_chart();
    include_once(WP_CREATE_GRAPH_DIR.'includes/view.php');
}
add_shortcode('product-chart', 'wp_create_graph_user_shortcode');
//Action on activation plugin
function wp_create_graph_activation() {
    add_option('wp_create_graph_host', 'host');
    add_option('wp_create_graph_database', 'database');
    add_option('wp_create_graph_user', 'user');
    add_option('wp_create_graph_password', 'password');
    add_option('wp_create_graph_connection', 'local');
    register_uninstall_hook(__FILE__, 'wp_create_graph_uninstall');
}
function wp_create_graph_uninstall(){
}
function wp_create_graph_deactivation() {
}
function wp_create_graph_add_top_menu() {
    add_menu_page( 'Charts', 'Charts', '','wp-create-graph/admin.php', '', plugins_url( 'assets/img/bars-chart.png', __FILE__ ) );
}
add_action('admin_menu', 'wp_create_graph_add_top_menu');
//Add submenu
function wp_create_graph_add_submenu_about() {
    add_submenu_page('wp-create-graph/admin.php', 'Settings', 'Settings', 'manage_options', WP_CREATE_GRAPH_DIR.'includes/settings.php');
}
add_action( 'admin_init', 'wp_create_graph_admin_init' );
add_action( 'admin_menu', 'wp_create_graph_admin_menu' );
add_action( 'wp_enqueue_scripts', 'wp_create_graph_scripts' );
//Add script
function wp_create_graph_scripts()
{
    wp_enqueue_script('wp_create_graph_scripts','https://www.gstatic.com/charts/loader.js');
}
//Register script
function wp_create_graph_admin_init() {
    wp_register_script( 'wp_create_graph-script', 'https://www.gstatic.com/charts/loader.js' );

}
function wp_create_graph_admin_menu() {
    /* Register page plugon */
    $page = add_submenu_page( 'wp-create-graph/admin.php',
        __( 'View Charts', 'myPlugin' ),
        __( 'View Charts', 'myPlugin' ),
        'manage_options',
        'wp_create_graph-options',
        'wp_create_graph_manage_menu'
    );

    /* Use this page to load the registered script */
    add_action( 'admin_print_scripts-' . $page, 'wp_create_graph_admin_scripts' );
}
add_action('admin_menu', 'wp_create_graph_add_submenu_about');
function wp_create_graph_admin_scripts() {
    /*
     * This function will be called only on the plug-in page, we connect our script
     */
    wp_enqueue_script( 'wp_create_graph-script' );
}
function wp_create_graph_manage_menu() {
    $array_finish = creat_chart();
    include_once(WP_CREATE_GRAPH_DIR.'includes/view.php');
}
//Orders calculation function
function creat_chart(){
    if(get_option('wp_create_graph_connection')==='remote'){
        $wpdb = new wpdb(get_option('wp_create_graph_user'),get_option('wp_create_graph_password'),get_option('wp_create_graph_database'),get_option('wp_create_graph_host'));
    }
    else {
        global $wpdb;
    }
    //Construction of the category tree
    function ShowTree($ParentID, $lvl) {
        if(get_option('wp_create_graph_connection')==='remote'){
            $wpdb = new wpdb(get_option('wp_create_graph_user'),get_option('wp_create_graph_password'),get_option('wp_create_graph_database'),get_option('wp_create_graph_host'));
        }
        else {
            global $wpdb;
        }
        $arr = [];
        $lvl++;
        $sSQL="SELECT * FROM productgroups WHERE pg_supergroupid=".$ParentID."";
        $result=$wpdb->get_results($sSQL);
        if ($wpdb->get_var("SELECT COUNT(pg_id) FROM productgroups WHERE pg_supergroupid=".$ParentID."") > 0) {
            foreach ($result as $value){

                $ID1 = $value->pg_id;
                if($ParentID != $ID1){
                    $arr[$ParentID][$value->pg_id] = ShowTree($ID1, $lvl);
                    $lvl--;
                }
            }
        }
        return $arr;
    }
    //Function search key in array of tree category
    function search($array, $key)
    {
        $results = [];
        if (is_array($array)) {
            if (array_key_exists($key,$array)) {
                $results[] = $key;
            }
            foreach ($array as $subarray) {
                $results = array_merge($results, search($subarray, $key));
            }
        }
        elseif (array_keys($array,$key,true)){
            return $key;
        }
        return $results;
    }
    $array_group = [];
    $rew = $wpdb->get_results("SELECT * FROM productgroups GROUP BY pg_supergroupid ASC");
    foreach ($rew as $value) {
        if(!search($array_group,$value->pg_supergroupid))
            $array_group[$value->pg_supergroupid] = ShowTree($value->pg_supergroupid, 0);

    }
    $row = $wpdb->get_results("SELECT o_creationdate FROM orders ORDER BY o_creationdate ASC");
    $date = [];
    $date[] = substr($row[0]->o_creationdate,0,10);
    foreach ($row as $value){
        if(in_array(substr($value->o_creationdate,0,10),$date)){}
        else
            $date[] = substr($value->o_creationdate,0,10);
    }
    $arr_finish = [];
    for($i=0;$i<count($date);$i++) {
        $row = $wpdb->get_results("SELECT o_productid FROM orders WHERE o_creationdate LIKE '{$date[$i]}%'");
        foreach ($row as $value){
            $row2 = $wpdb->get_results("SELECT p_productgroupid FROM products WHERE p_id=$value->o_productid");
            foreach($array_group as $key => $value2){
                if ( search($value2,$row2[0]->p_productgroupid) || $key==$row2[0]->p_productgroupid|| array_key_exists($row2[0]->p_productgroupid,$value2) || $key === $row2[0]->p_productgroupid  ) {
                    $arr_finish[$key][$date[$i]] ++;
                }
            }
        }
    }
    return $arr_finish;
}