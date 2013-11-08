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
 * Note: For double-quotes in $tooltip, use "&quot;"
 * 
 * @return string	$element wrapped by span for edit mode
 * 
 * @uses wpia_editable_span()
 */
function wpia_editable_wrap( $element, $path, $tooltip = false ) {
	// some functions get used in the admin. bail if that's the case.
	if( is_admin() )
		return $element;

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

	$href = '/widgets.php#wpia-' . $params[0]['widget_id'];
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

function wpia_editable_bloginfo( $output, $show ) {
	switch ($show) {
		case 'description':
			$href = '/options-general.php#wpia-blogdescription';
			$tooltip = 'The &quot;Tagline&quot; (or &quot;Site Description&quot;) is a site-wide setting.';
			$output = wpia_editable_wrap( $output, $href, $tooltip );
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
	return wpia_editable_wrap( $content, $link, 'The main body of the post, &quot;' . $title . '.&quot;');
}
add_filter( 'the_content', 'wpia_editable_the_content', 99999 );

function wpia_editable_the_post_thumbnail( $html, $post_id ) {
	// if no post thumbnail
	if( $html == '' )
		return;

	$link = get_edit_post_link( $post_id );
	$link = str_replace( admin_url(), '', $link ); // crappy hack
	$link = $link . '#wpia-postimagediv';
	$title = get_the_title( $post_id );
	return wpia_editable_wrap( $html, $link, 'The &quot;Featured Image&quot; of the post, &quot;' . $title . '.&quot;');
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
	return wpia_editable_wrap( $excerpt, $link, $tooltip );
}
add_filter( 'the_excerpt', 'wpia_editable_the_excerpt', 99999 );

function wpia_editable_the_tags( $tags, $before, $sept, $after, $post_id ) {
	// no tags to edit
	if( !$tags )
		return $tags;

	$link = get_edit_post_link( $post_id );
	$link = str_replace( admin_url(), '', $link ); // crappy hack
	$link = $link . '#wpia-tagsdiv-post_tag';

	$title = get_the_title( $post_id );

	$tooltip = 'The &quot;Tags&quot; for the post, &quot;' . $title . '.&quot;';
	
	return wpia_editable_wrap( $tags, $link, $tooltip );
}
add_filter( 'the_tags', 'wpia_editable_the_tags', 99999, 5 );

function wpia_editable_the_category( $category_list ) {
	// no categories to edit
	if( empty( $category_list ) )
		return $category_list;

	$post_id = get_the_ID();
	$link = get_edit_post_link( $post_id );
	$link = str_replace( admin_url(), '', $link ); // crappy hack
	$link = $link . '#wpia-categorydiv';

	$title = get_the_title( $post_id );

	$tooltip = 'The &quot;Category&quot; terms for the post, &quot;' . $title . '.&quot;';
	
	return wpia_editable_wrap( $category_list, $link, $tooltip );
}
add_filter( 'the_category', 'wpia_editable_the_category', 99999 );