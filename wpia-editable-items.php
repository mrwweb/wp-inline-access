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
 * Wrap an element with span for editing
 * 
 * @param  string  		$element 	HTML element to output
 * @param  string  		$path    	admin path for editing, passed to admin_url()
 * @param  string|bool 	$tooltip 	Text for tooltip in hover mode
 * @param  string|array	$capability	capability required to edit this element, pass array if you need both arguments
 * @param  bool 		$span_only	return only the opening-span. occasionally useful
 * 
 * Note: For double-quotes in $tooltip, use "&quot;"
 * 
 * @return string	$element wrapped by span for edit mode
 */
function wpia_editable( $element, $capability = false, $path, $tooltip = false, $span_only = false ) {

	// there's nothing to do if $element is empty AND $span_only is false
	if( empty($element) && !$span_only )
		return;

	// cast $capability as an array to handle multiple args
	$capability = (array) $capability;

	// make sure we're logged in, not in the admin, and have correct permissions
	if( !is_user_logged_in() || is_admin() || !call_user_func_array('current_user_can', $capability ) )
		return $element;

	$span = sprintf(
		'<span class="wpia-is-editable" data-wpia-edit="true" data-wpia-edit-href="%1$s" data-wpia-edit-tooltip="%2$s">',
		admin_url( $path ),
		$tooltip
	);

	if( $span_only ) {
		return $span;
	} else {
		return $span . $element . '</span>';
	}
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

	// handle situations where menu is in a widget
	if( !array_key_exists($args->theme_location, $registered_menus) || !array_key_exists($args->theme_location, $menu_locations ) )
		return $menu;

	$menu_location = $registered_menus[$args->theme_location];
	$menu_id = (int) $menu_locations[$args->theme_location];
	$menu_object = wp_get_nav_menu_object( $menu_id );

	$href = '/nav-menus.php?action=edit&menu=' . $menu_id;

	$tooltip = sprintf(
		'This is the &quot;%1$s&quot; Menu in the theme\'s &quot;%2$s&quot; Menu Location.',
		esc_attr( $menu_object->name ),
		esc_attr( $menu_location )
	);

	return wpia_editable( $menu, 'edit_theme_options', $href, $tooltip );
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

	$href = '/widgets.php#wpia-' . $params[0]['widget_id'];
	$tooltip = sprintf(
		'A &quot;%1$s&quot; Widget located in the &quot;%2$s&quot; Widget Area.',
		$params[0]['widget_name'],
		$params[0]['name']
	);

	$params[0]['before_widget'] =  wpia_editable( '', 'edit_theme_options', $href, $tooltip, true ) . $params[0]['before_widget'];
	$params[0]['after_widget'] =  $params[0]['after_widget'] . '</span>';

	return $params;
}
add_filter( 'dynamic_sidebar_params', 'wpia_editable_widget', 99999 );

function wpia_editable_bloginfo( $output, $show ) {
	switch ($show) {
		case 'description':
			$href = '/options-general.php#wpia-blogdescription';
			$tooltip = 'The &quot;Tagline&quot; (or &quot;Site Description&quot;) is a site-wide setting.';
			$output = wpia_editable( $output, 'manage_options', $href, $tooltip );
			break;

		// site name is getting used in attributes, even in twenty twelve. not sure what to do about that.
		
		default:
			break;
	}
	return $output;
}
add_filter( 'bloginfo', 'wpia_editable_bloginfo', 99999, 2 );

function wpia_editable_the_content( $content ) {
	$link = get_edit_post_link();
	$link = str_replace( admin_url(), '', $link ); // crappy hack
	$link = $link . '#wpia-content_ifr';
	$title = get_the_title();
	return wpia_editable( $content, array( 'edit_post', get_the_ID() ), $link, 'The main body of the post, &quot;' . $title . '.&quot;');
}
add_filter( 'the_content', 'wpia_editable_the_content', 99999 );

function wpia_editable_the_post_thumbnail( $html, $post_id ) {
	$link = get_edit_post_link( $post_id );
	$link = str_replace( admin_url(), '', $link ); // crappy hack
	$link = $link . '#wpia-postimagediv';
	$title = get_the_title( $post_id );
	return wpia_editable( $html, array( 'edit_post', get_the_ID() ), $link, 'The &quot;Featured Image&quot; of the post, &quot;' . $title . '.&quot;');
}
add_filter( 'post_thumbnail_html', 'wpia_editable_the_post_thumbnail', 99999, 2 );

function wpia_editable_the_excerpt( $excerpt ) {
	$post_id = get_the_ID();
	$link = get_edit_post_link( $post_id );
	$link = str_replace( admin_url(), '', $link ); // crappy hack
	$link = $link . '#wpia-postexcerpt';

	$title = get_the_title( $post_id );

	$tooltip = false;
	if( has_excerpt( $post_id ) ) {
		$tooltip = 'The &quot;Excerpt&quot; of the post, &quot;' . $title . '.&quot;';
	} else {
		$tooltip = 'An auto-generated &quot;excerpt&quot; for the post, &quot;' . $title . '.&quot; Use the &quot;Excerpt&quot; field to customize.';
	}
	return wpia_editable( $excerpt, array( 'edit_post', get_the_ID() ), $link, $tooltip );
}
add_filter( 'the_excerpt', 'wpia_editable_the_excerpt', 99999 );

function wpia_editable_the_tags( $tags, $before, $sept, $after, $post_id ) {
	$link = get_edit_post_link( $post_id );
	$link = str_replace( admin_url(), '', $link ); // crappy hack
	$link = $link . '#wpia-tagsdiv-post_tag';

	$title = get_the_title( $post_id );

	$tooltip = 'The &quot;Tags&quot; for the post, &quot;' . $title . '.&quot;';
	
	return wpia_editable( $tags, 'manage_categories', $link, $tooltip );
}
add_filter( 'the_tags', 'wpia_editable_the_tags', 99999, 5 );

function wpia_editable_the_category( $category_list ) {
	$post_id = get_the_ID();
	$link = get_edit_post_link( $post_id );
	$link = str_replace( admin_url(), '', $link ); // crappy hack
	$link = $link . '#wpia-categorydiv';

	$title = get_the_title( $post_id );

	$tooltip = 'The &quot;Category&quot; terms for the post, &quot;' . $title . '.&quot;';
	
	return wpia_editable( $category_list, 'manage_categories', $link, $tooltip );
}
add_filter( 'the_category', 'wpia_editable_the_category', 99999 );