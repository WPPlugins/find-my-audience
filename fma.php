<?php
/**
 * @package Find My Audience
 * @version 3.1
 */

/*
Plugin Name: Find My Audience
Plugin URI: http://findmyaudience.com
Description: Find My Audience
Authors: Find My Audience
Author URI: http://findmyaudience.com
Version: 3.1
*/

// Don't allow this page to be called directly
if ( !function_exists( 'add_action' ) ) {
	exit;
}

define( 'FMA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once( FMA_PLUGIN_DIR . '/class.fma.php' );

register_activation_hook( __FILE__, array( 'FindMyAudience\FMA_Backend', 'fma_activate' ) );
register_deactivation_hook( __FILE__, array( 'FindMyAudience\FMA_Backend', 'fma_deactivate' ) );

/**
 * Initialize the plugin (functions only)
 */
add_action( 'init', array( 'FindMyAudience', 'init' ) );

/**
 * Initialize the scripts, styles, user-interaction, etc.
 */
add_action( 'init', array( 'FindMyAudience\FMA_Frontend', 'init' ) );

