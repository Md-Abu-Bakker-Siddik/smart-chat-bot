<?php
/**
 * Uninstall Siddik Chat Widget (free).
 *
 * @package Siddik_Chat_Widget
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'mdscw_settings' );
