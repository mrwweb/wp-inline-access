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

/* Version constant */
define( 'WPIA_VERSION', '0.1.0' );

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

/**
 * Add "Toggle Edit Mode" button to Admin Bar
 */
function wpia_toggle_mode_button( $admin_bar ) {

	// for now, no edit button in admin
	if( !is_admin() )
		return;

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

	// for now, no infobar in admin
	if( is_admin() )
		return;

	echo '<div class="wpia-info-bar"><dl>';
	do_action( 'wpia_info_bar' );
	echo '</dl></div> <!-- end .wpia-info-bar -->';

}
add_action( 'wp_after_admin_bar_render', 'wpia_output_info_bar' );

/**
 * Creates html for a single item in the Info Bar
 * 
 * @param  string $label         label for info bar item
 * @param  string $value         value for info bar item
 * @param  string $label_tooltip hover tooltip for $label value
 * @param  string $value_tooltip hover tooltip for $value value
 * 
 * @return string                html for single item in info bar
 */
function wpia_info_bar_item( $label, $value, $label_tooltip, $value_tooltip ) {
	// HTML allowed in info bar values
	$allowed_html = array(
	    'a' => array(
	        'href' => array()
	    ),
	    'em' => array(),
	    'strong' => array()
	);

	$label = esc_attr( $label );
	$label_as_attribute = sanitize_title_with_dashes( $label );
	$value = wp_kses( $value, $allowed_html );
	$label_tooltip = esc_attr( $label_tooltip );
	$value_tooltip = esc_attr( $value_tooltip );

	$item = sprintf(
		'<dt class="wpia-info-%1$s" data-wpia-tooltip="%3$s">%2$s</dt><dd class="wpia-info-%1$s" data-wpia-tooltip="%5$s">%4$s</dd>',
		$label_as_attribute,
		$label,
		$label_tooltip,
		$value,
		$value_tooltip
	);

	return $item;
}

/**
 * Sets "Type" in Info Bar
 * 
 * Type is the only information always displayed so this function is used to trigger and filter other information
 * 
 * @return array array with defaults added
 */
function wpia_info_bar_page_type() {

	global $wp_query;
	$queried_object = $wp_query->get_queried_object();

	/* Add the Type */
	$type = false;
	$value_tooltip = false;

	if( $wp_query->is_singular ) {
		$type = $queried_object->post_type;
		if( $wp_query->is_page && is_page_template() ) {
			add_action( 'wpia_info_bar', 'wpia_info_bar_page_template' );
		}
	} elseif ( $wp_query->is_posts_page ) {
		$type = 'Page for Posts';
		$value_tooltip = 'This page shows a chronological listing of all Posts.';
	} elseif ( $wp_query->is_tag ) {
		$type = 'Tag Archive';
	} elseif ( $wp_query->is_category ) {
		$type = 'Category Archive';
	} elseif ( $wp_query->is_tax ) {
		$type = 'Taxonomy Term Archive';
	} elseif ( $wp_query->is_post_type_archive ) {
		$type = 'Post Type Archive';
	} elseif ( $wp_query->is_search ) {
		$type = 'Search Results Page';
	} elseif ( $wp_query->is_404 ) {
		$type = '404 Page';
		$value_tooltip = 'A "404 Error" means the intended page cannot be found.';
	} elseif ( $wp_query->is_post_type_archive ) {
		$type = 'Post Type Archive';
	} elseif ( $wp_query->is_author ) {
		$type = 'Author Archive';
	} elseif ( $wp_query->is_day ) {
		$type = 'Day Archive';
	} elseif ( $wp_query->is_month ) {
		$type = 'Month Archive';
	} elseif ( $wp_query->is_year ) {
		$type = 'Year Archive';
	}

	$type = apply_filters( 'wpia_info_bar_type', $type );

	echo wpia_info_bar_item( 'Type', $type, 'WordPress uses a variety of web page types depending on the page being displayed.', $value_tooltip );

}
add_action( 'wpia_info_bar', 'wpia_info_bar_page_type', -10 );

/**
 * output template value in info par
 * 
 * @return string           info bar item listing page template
 * 
 * @uses   wpia_info_bar_item
 * @uses   wp_get_theme()
 * @uses   get_page_template_slug()
 */
function wpia_info_bar_page_template() {
	global $wp_query;

	// get page template being used
	$page_template = get_page_template_slug();
	$page_templates = wp_get_theme()->get_page_templates();
	$template_name = $page_templates[$page_template];
	
	// Create a value tooltip for optional usage by themes
	$value_tooltip = false;
	$value_tooltip = apply_filters( 'wpia_template_value_tooltip', $value_tooltip );
	
	// Output template tooltip
	echo wpia_info_bar_item( 'Page Template', $template_name, 'A page template changes the layout or adds special content to a Page.', $value_tooltip );
}

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