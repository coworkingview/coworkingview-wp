<?php
/**
 * Plugin Name:       CoworkingView – Coworking Spaces & Office Prices
 * Plugin URI:        https://github.com/coworkingview/coworkingview-wp
 * Description:       Show coworking spaces, private offices and market rates for a city, with operator-published prices, using shortcodes. No API key.
 * Version:           0.1.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            CoworkingView
 * Author URI:        https://coworkingview.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       coworkingview
 *
 * @package CoworkingView
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COWORKINGVIEW_VERSION', '0.1.1' );
define( 'COWORKINGVIEW_DIR', plugin_dir_path( __FILE__ ) );
define( 'COWORKINGVIEW_URL', plugin_dir_url( __FILE__ ) );

require_once COWORKINGVIEW_DIR . 'includes/class-coworkingview-api.php';
require_once COWORKINGVIEW_DIR . 'includes/class-coworkingview-shortcodes.php';
require_once COWORKINGVIEW_DIR . 'includes/class-coworkingview-settings.php';

add_action(
	'init',
	static function () {
		CoworkingView_Shortcodes::register();
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_register_style( 'coworkingview', COWORKINGVIEW_URL . 'assets/coworkingview.css', array(), COWORKINGVIEW_VERSION );
	}
);

if ( is_admin() ) {
	CoworkingView_Settings::register();
}
