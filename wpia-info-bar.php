<?php
/**
 * Info Bar
 *
 * This code setups up the Info Bar and handles adding items to it
 */


/**
 * Add "Toggle Edit Mode" button to Admin Bar
 */
function wpia_toggle_mode_button( $admin_bar ) {

	// for now, no edit button in admin
	if( is_admin() )
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
