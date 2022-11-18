<?php

/**
 * Plugin Name: PeakRealty ZOHO CRM
 * Description: Insert, Update data of Properties in ZOHO CRM
 * Version: 1.0.0
 * Author: Dhara Talaviya
 *
 * Text Domain: Pr-zoho
 * Domain Path: /languages
 * 
 * Author URI: https://jem-products.com/
 *
 * @package PeakRealty ZOHO CRM
 * @category Core
 * @author: Dhara Talaviya
 */


// Exit if accessed directly
defined('ABSPATH') or die('Sorry!, You do not access the file directly');



// If this file is accessed directory, then abort.
if (!defined('WPINC')) {
    die;
}

if( !defined( 'ZOHOCRM_DIR' ) ) {
	define( 'ZOHOCRM_DIR', dirname( __FILE__ ) ); // plugin dir
}


//add action to load plugin
add_action( 'plugins_loaded', 'pr_zoocrm_plugin_loaded' );
	
 /**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package PeakRealty ZOHO CRM
 * @since 1.0.0
 */
function pr_zoocrm_plugin_loaded() {
	
		// // Script Class to manage all scripts and styles
		// include_once( ZOHOCRM_DIR . '/zoocrm-class.php' );

		// $PR_ZOOCRM_RestClient = new PR_ZOOCRM_RestClient();
		// $PR_ZOOCRM_RestClient -> setnewRecord();			
		
}//end if to check plugin loaded is called or not

function zohocrm(){
	// Script Class to manage all scripts and styles
		include_once( ZOHOCRM_DIR . '/zoocrm-class.php' );

		$PR_ZOOCRM_RestClient = new PR_ZOOCRM_RestClient();
		$properties = $PR_ZOOCRM_RestClient -> setnewRecord();
}
add_shortcode("zohocrm","zohocrm");

add_action( 'pr_zohocrm', 'zohocrm', 10);

function zohocrm_properties(){
	// Script Class to manage all scripts and styles
		include_once( ZOHOCRM_DIR . '/zoocrm-class.php' );

		$PR_ZOOCRM_RestClient = new PR_ZOOCRM_RestClient();
		$properties = $PR_ZOOCRM_RestClient -> setnewRecord_Buildings();
}
add_shortcode("zohocrm_properties","zohocrm_properties");

add_action( 'zohocrm_properties', 'zohocrm_properties', 10);