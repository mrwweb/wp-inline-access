<?php
/**
 * Info Bar Defaults
 *
 * This code handles adding the standard items to the info bar
 */


/**
 * Sets "Type" in Info Bar
 * 
 * Type is the only information always displayed so this function is used to trigger and filter other information
 * 
 * @return array array with defaults added
 */
function wpia_info_bar_page_type() {

	global $wp_query;
	$queried_object = get_queried_object();

	global $_wp_theme_features;

	/* Add the Type */
	$type = false;
	$value_tooltip = false;

	if( $wp_query->is_singular ) {
		$type = $queried_object->post_type;
		if( $wp_query->is_page ) {
			add_action( 'wpia_info_bar', 'wpia_info_bar_page_parent' );
			if ( is_page_template() ) {
				add_action( 'wpia_info_bar', 'wpia_info_bar_page_template' );
			}
		}
		if( get_query_var('page_id') == get_option( 'page_on_front' ) ) {
			add_action( 'wpia_info_bar', 'wpia_info_bar_front' );
		}
		if( $wp_query->is_single ) {
			add_action( 'wpia_info_bar', 'wpia_info_bar_post_format' );
		}
		add_action( 'wpia_info_bar', 'wpia_info_bar_modified_time' );
	} elseif ( $wp_query->is_posts_page ) {
		$type = 'Page for Posts';
		add_action( 'wpia_info_bar', 'wpia_info_bar_posts_page' );
	} elseif ( $wp_query->is_tag ) {
		$type = 'Tag Archive';
		add_action( 'wpia_info_bar', 'wpia_info_bar_term_archive' );
	} elseif ( $wp_query->is_category ) {
		$type = 'Category Archive';
		add_action( 'wpia_info_bar', 'wpia_info_bar_term_archive' );
	} elseif ( $wp_query->is_tax ) {
		$type = 'Taxonomy Term Archive';
		add_action( 'wpia_info_bar', 'wpia_info_bar_term_archive' );
	} elseif ( $wp_query->is_post_type_archive ) {
		$type = 'Post Type Archive';
		add_action( 'wpia_info_bar', 'wpia_info_bar_archive_post_type' );
	} elseif ( $wp_query->is_search ) {
		$type = 'Search Results Page';
	} elseif ( $wp_query->is_404 ) {
		$type = '404 Page';
		$value_tooltip = 'A "404 Error" means the intended page cannot be found.';
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
 * @uses   wpia_info_bar_item
 * @uses   wp_get_theme()
 * @uses   get_page_template_slug()
 * 
 * @return string           info bar item listing page template
 */
function wpia_info_bar_page_template() {
	global $wp_query;

	// get page template being used
	$page_template = get_page_template_slug();
	$page_templates = wp_get_theme()->get_page_templates();
	
	// Make sure there's not some template left-over from old theme
	if( !array_key_exists($page_template, $page_templates) )
		return;

	$value = sprintf( '%1$s [<a href="%2$s#wpia-page_template">Change Template</a>]', $page_templates[$page_template], get_edit_post_link() );

	// Create a value tooltip for optional usage by themes
	$value_tooltip = false;
	$value_tooltip = apply_filters( 'wpia_template_value_tooltip', $value_tooltip );
	
	// Output template tooltip
	echo wpia_info_bar_item( 'Page Template', $value, 'A page template changes the layout or adds special content to a Page.', $value_tooltip );
}

function wpia_info_bar_front() {
	echo wpia_info_bar_item( 'Front Page', 'This page is set as the &quot;Static Front Page&quot; on <strong><a href="' . admin_url( '/options-reading.php#wpia-page_on_front' ) . '">Settings > Reading</a></strong>.' );
}

function wpia_info_bar_posts_page() {
	echo wpia_info_bar_item( 'Page for Posts', 'This page is set as the &quot;Page for Posts&quot; on <strong><a href="' . admin_url( '/options-reading.php#wpia-page_for_posts' ) . '">Settings > Reading</a></strong>.' );
}

/**
 * outputs post format on archive and post pages if theme supports them
 * 
 * @uses get_post_format
 * @uses  get_queried_object
 * 
 * @return strings Info bar item for post format
 */
function wpia_info_bar_post_format() {
	global $wp_query;

	if( $wp_query->is_single ) {
		$post_format = get_post_format() ? get_post_format() : 'Standard';
	} else {
		$queried_object = get_queried_object();
		$post_format = $queried_object->name;
	}

	// make sure theme supports post formats
	global $_wp_theme_features;
	if( ! array_key_exists('post-formats', $_wp_theme_features)
		&& array_search($post_format, $_wp_theme_features['post-formats'][0]) == -1 )
		return;

	echo wpia_info_bar_item( 'Post Format', $post_format, 'Themes can use Post Formats to alter how a post appears.' );

}

function wpia_info_bar_archive_post_type() {
	$post_type = get_query_var( 'post_type' );
	echo wpia_info_bar_item( 'Post Type', $post_type );
}

function wpia_info_bar_page_parent() {
	$queried_object = get_queried_object();
	$post_parent = $queried_object->post_parent;
	$value = sprintf( '%1$s [<a href="%2$s#wpia-parent_id">Edit Parent</a>]',
		get_the_title( $post_parent ),
		get_edit_post_link()
	);
	if( $post_parent !== 0 ) {
		echo wpia_info_bar_item( 'Page Parent', $value, 'The Page Parent determines this page\'s URL structure' );
	}
}

function wpia_info_bar_modified_time() {
	if( get_the_modified_time('r') == get_the_time('r') ) {
		$label = 'Published';
	} else {
		$label = 'Last Modified';
	}
	echo wpia_info_bar_item( $label, get_the_modified_time( 'H:i, j M Y' ) );
}

function wpia_info_bar_term_archive() {
	$queried_object = get_queried_object();
	if( $queried_object->taxonomy == 'post_format' ){
		$label = 'Post Format';
	} elseif( is_tax() ) {
		$label = 'Taxonomy Term';
	} elseif( is_category() ) {
		$label = 'Category';
	} elseif( is_tag() ) {
		$label = 'Tag';
	}
	echo wpia_info_bar_item( $label, single_term_title('',false) );
}