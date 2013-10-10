<?php
/**
 * Plugin Name: WP Inline Access
 * Plugin URI: http://MRWweb.com/wp-inline-access/
 * Description: An alternative to front end editing. Click on an element, teleport to the right admin screen.
 * Version: 0.2.0
 * Author: Mark Root-Wiley
 * Author URI: http://MRWweb.com
 * License: GPLv2 or later
 */

/* Version constant */
define( 'WPIA_VERSION', '0.2.0' );

/**
 * Load scripts for front end and back end
 */
function wpia_enqueue_scripts_and_styles() {
	if( is_user_logged_in() ) {	
		wp_enqueue_style( 'wpia_css', plugins_url( '/css/wpia.css', __FILE__ ), null, WPIA_VERSION );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-position' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'wpia_js', plugins_url( '/js/wpia.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), WPIA_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'wpia_enqueue_scripts_and_styles' );
add_action( 'admin_enqueue_scripts', 'wpia_enqueue_scripts_and_styles' );

// init the info bar & helper functions
include( 'wpia-info-bar.php' );
// add default items to the info bar
include( 'wpia-info-bar-defaults.php' );
// filter WP functions to make editable in Edit Mode
include( 'wpia-editable-items.php' );