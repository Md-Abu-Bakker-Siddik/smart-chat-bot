<?php
/**
 * Plugin Name:       Smart Chat Bot
 * Plugin URI:        https://wordpress.org/plugins/smart-chat-bot/
 * Description:       A lightweight rule-based chat bot widget for your WordPress site.
 * Version:           1.0.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Md Abu Bakker Siddik
 * Author URI:        https://profiles.wordpress.org/mdabubakkersiddik1/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-chat-bot
 * Domain Path:       /languages
 *
 * @package Smart_Chat_Bot
 */

defined( 'ABSPATH' ) || exit;

define( 'SCB_VERSION', '1.0.2' );
define( 'SCB_PLUGIN_FILE', __FILE__ );
define( 'SCB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SCB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once SCB_PLUGIN_DIR . 'includes/class-smart-chat-bot.php';

/**
 * Returns the main plugin instance.
 *
 * @return Smart_Chat_Bot
 */
function scb() {
	return Smart_Chat_Bot::instance();
}

scb();
