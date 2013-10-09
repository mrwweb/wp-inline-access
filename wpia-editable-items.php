<?php
/**
 * Edit Mode Editable Items
 *
 * Provides code for generating "editable span" tags and filters various functions to add editability
 */

/* ===============================================
 * HELPER FUNCTIONS
 * ===============================================*/

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
		'<span class="wpia-is-editable" data-wpia-edit="true" data-wpia-edit-href="%1$s" data-wpia-edit-tooltip="%2$s">',
		admin_url( $path ),
		$tooltip
	);

	return $wrapped;
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

/* ===============================================
 * FILTER WP FUNCTIONS
 * ===============================================*/

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

	$href = '/widgets.php#' . $params[0]['widget_id'];
	$tooltip = sprintf(
		'A &quot;%1$s&quot; Widget located in the &quot;%2$s&quot; Widget Area.',
		$params[0]['widget_name'],
		$params[0]['name']
	);

	$params[0]['before_widget'] =  wpia_editable_span( $href, $tooltip ) . $params[0]['before_widget'];
	$params[0]['after_widget'] =  $params[0]['after_widget'] . '</span>';

	return $params;
}
add_filter( 'dynamic_sidebar_params', 'wpia_editable_widget', 99999 );