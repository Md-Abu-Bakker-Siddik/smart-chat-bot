<?php
/**
 * Plugin Name:       Siddik Chat Widget
 * Plugin URI:        https://wordpress.org/plugins/siddik-chat-widget/
 * Description:       A lightweight rule-based chat bot widget for your WordPress site.
 * Version:           1.0.4
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Md Abu Bakker Siddik
 * Author URI:        https://profiles.wordpress.org/mdabubakkersiddik/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       siddik-chat-widget
 * Domain Path:       /languages
 *
 * @package Siddik_Chat_Widget
 */

defined( 'ABSPATH' ) || exit;

define( 'MDSCW_VERSION', '1.0.4' );
define( 'MDSCW_PLUGIN_FILE', __FILE__ );
define( 'MDSCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MDSCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MDSCW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once MDSCW_PLUGIN_DIR . 'includes/class-siddik-chat-widget.php';

/**
 * Returns the main plugin instance.
 *
 * @return Siddik_Chat_Widget
 */
function mdscw() {
	return Siddik_Chat_Widget::instance();
}

mdscw();
