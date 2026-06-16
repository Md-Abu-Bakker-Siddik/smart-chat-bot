<?php
/**
 * Uninstall Smart Chat Bot (free).
 *
 * @package Smart_Chat_Bot
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'scb_settings' );
