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
 * Load scripts for front end
 */
function wpia_enqueue_scripts() {}

/**
 * Load styles for front end
 */
function wpia_enqueue_styles() {}

/**
 * Load scripts for back end
 */
function wpia_enqueue_admin_scripts() {}

/**
 * Load styles for back end
 */
function wpia_enqueue_admin_styles() {}

function wpia_toggle_mode_button( $admin_bar ) {

  // top level link to WP Help
  $args = array(
    'id'    => 'wpia-toggle-edit-mode',
    'title' => __( 'Toggle Edit Mode', 'wpinlineaccess' ),
    'href'  => '#'
  );
  $admin_bar->add_node( $args );
 
}
add_action('admin_bar_menu', 'wpia_toggle_mode_button', 1000);