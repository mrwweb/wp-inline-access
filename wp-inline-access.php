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

WPIA_VERSION = '0.1.0';

/**
 * Load scripts for front end and back end
 */
add_action( 'wp_enqueue_scripts', 'wpia_enqueue_scripts_and_styles' );
add_action( 'admin_enqueue_scripts', 'wpia_enqueue_scripts_and_styles' );
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
 * Add default items to Edit Mode Info Bar
 * 
 * @param  array $wpia_info_bar_list list of elements in info bar
 * 
 * @return array array with defaults added
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
add_filter( 'wpia_info_bar_list', 'wpia_add_default_info_bar_items', -10 );

/**
 * output span used to define an editable region
 * 
 * @param  string  $path    admin path for editing, passed to admin_url()
 * @param  string|bool $tooltip Text for tooltip in hover mode
 * 
 * @return string           html string, opening span
 * 
 * @uses	admin_url
 */
function wpia_editable_span( $path, $tooltip = false ) {
	$wrapped = sprintf(
		'<span class="wpia-is-editable" data-wpia-edit="true" data-wpia-edit-href="%2$s" data-wpia-edit-tooltip="%3$s">',
		admin_url( $path ),
		$tooltip
	);
}

/**
 * Wrap an element with span for editing
 * 
 * @param  string  $element HTML element to output
 * @param  string  $path    admin path for editing, passed to admin_url()
 * @param  string|bool $tooltip Text for tooltip in hover mode
 * 
 * @return string	$element wrapped by span for edit mode
 * 
 * @uses wpia_editable_span()
 */
function wpia_editable_wrap( $element, $path, $tooltip = false ) {
	return wpia_editable_span( $path, $tooltip ) . $element . '</span>';
}

/**
 * Filter to make each menu editable
 * 
 * @param  string $menu html markup for menu
 * @param  array $args arguments for menu output
 * 
 * @return string       html output for menu, wit Edit Mode span wrapper
 */
function wpia_editable_nav_menu( $menu, $args ) {
	if( is_admin() || !current_user_can( 'edit_theme_options' ) )
		return $menu;

	$registered_menus = get_registered_nav_menus();
	$menu_locations = get_nav_menu_locations();
	$menu_location = $registered_menus[$args->theme_location];
	$menu_id = (int) $menu_locations[$args->theme_location];
	$menu_object = wp_get_nav_menu_object( $menu_id );

	$href = '/nav-menus.php?action=edit&menu=' . $menu_id;

	$tooltip = sprintf(
		'This is the &quot;%1$s&quot; Menu in the theme\'s &quot;%2$s&quot; Menu Location.',
		esc_attr( $menu_object->name ),
		esc_attr($menu_location)
	);

	return wpia_editable_wrap( $menu, $href, $tooltip );
}
add_filter( 'wp_nav_menu', 'wpia_editable_nav_menu', 99999, 2 );

/**
 * Filter to make each widget as editable in Edit Mode
 * 
 * @param  array $params parameters for sidebar set by theme or WP defaults
 * 
 * @return array         modified parameters for sidebar
 */
function wpia_editable_widget( $params ) {
	if( is_admin() || ! current_user_can( 'edit_theme_options' ) )
		return $params;

	$href = '/widgets.php';
	$tooltip = 'A &quot;Type&quot; of Widget with the title &quot;Title&quot; located in the &quot;Sidebar&quot; Widget Area.';

	$params[0]['before_widget'] =  wpia_editable_span( $href, $tooltip ) . $params[0]['before_widget'];
	$params[0]['after_widget'] =  $params[0]['after_widget'] . '</span>';

	return $params;
}
add_filter( 'dynamic_sidebar_params', 'wpia_editable_widget', 99999 );
