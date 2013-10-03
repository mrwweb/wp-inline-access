<?php
/**
 * Plugin Name: WP Inline Access
 * Plugin URI: http://MRWweb.com/wp-inline-access/
 * Description: An alternative to front end editing. Click on an element, teleport to the right admin screen.
 * Version: 0.1.0
 * Author: Mark Root-Wiley
 * Author URI: http://MRWweb.com
 * License: GPLv2 or later
 */

/**
 * Load scripts for front end and back end
 */
add_action( 'wp_enqueue_scripts', 'wpia_enqueue_scripts_and_styles' );
add_action( 'admin_enqueue_scripts', 'wpia_enqueue_scripts_and_styles' );
function wpia_enqueue_scripts_and_styles() {

	if( is_user_logged_in() ) {
	
		wp_enqueue_style( 'wpia_css', plugins_url( '/css/wpia.css', __FILE__ ), null, '0.1.0' );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-position' );
		wp_enqueue_script( 'jquery-ui-tooltip' );

		wp_enqueue_script( 'wpia_js', plugins_url( '/js/wpia.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '0.1.0', true );

	}

}

/**
 * Add "Toggle Edit Mode" button to Admin Bar
 */
function wpia_toggle_mode_button( $admin_bar ) {

	// top level link to WP Help
	$toggle_button_args = array(
		'id'    => 'wpia-toggle-edit-mode',
		'title' => __( 'Toggle Edit Mode', 'wpinlineaccess' ),
		'href'  => '#'
	);
	$admin_bar->add_node( $toggle_button_args );
 
}
add_action('admin_bar_menu', 'wpia_toggle_mode_button', 1000);

/**
 * Add "Info Bar" after Admin bar for revealing when Edit Mode is toggled
 */
function wpia_output_info_bar() {

	$wpia_info_bar_list = wpia_info_bar_list();

	$wpia_info_bar_list_items = '';

	foreach ( $wpia_info_bar_list as $item ) {

		// HTML allowed in info bar values
		$allowed_html = array(
		    'a' => array(
		        'href' => array()
		    ),
		    'em' => array(),
		    'strong' => array()
		);

		$title = esc_attr( $item['title'] );
		$title_as_attribute = sanitize_title_with_dashes( $item['title'] );
		$value = wp_kses( $item['value'], $allowed_html );
		$tooltip = esc_attr( $item['tooltip'] );
		
		$item_output = sprintf(
			'<dt class="wpia-info-%1$s" data-wpia-tooltip="%3$s">%2$s</dt><dd class="wpia-info-%1$s">%4$s</dd>',
			$title_as_attribute,
			$title,
			$tooltip,
			$value
		);

		$wpia_info_bar_list_items .= $item_output;

	}

	echo '<div class="wpia-info-bar"><dl>' . $wpia_info_bar_list_items . '</dl></div> <!-- end .wpia-info-bar -->';

}
add_action( 'wp_after_admin_bar_render', 'wpia_output_info_bar' );

/**
 * Initialize and output array of Info Bar pieces of info
 * 
 * Each array should contain the keys: label, value, tooltip
 */
function wpia_info_bar_list() {

	// init array
	$wpia_info_bar_items = array();

	// allow others to filter
	$wpia_info_bar_items = apply_filters( 'wpia_info_bar_list', $wpia_info_bar_items );

	return $wpia_info_bar_items;

}

/**
 * Add default items to Info Bar
 */
function wpia_add_default_info_bar_items( $wpia_info_bar_list ) {

	// Add "Type"
	$wpia_info_bar_list[] = array(
		'title' => 'Type',
		'value' => 'The Type!',
		'tooltip' => 'WordPress uses a variety of web page types depending on the page being displayed.'
	);

	return $wpia_info_bar_list;

}
add_filter( 'wpia_info_bar_list', 'wpia_add_default_info_bar_items' );