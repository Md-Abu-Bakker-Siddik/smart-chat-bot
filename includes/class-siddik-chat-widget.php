<?php
/**
 * Core plugin class – initialization and hooks.
 *
 * @package Siddik_Chat_Widget
 */

defined( 'ABSPATH' ) || exit;

require_once MDSCW_PLUGIN_DIR . 'includes/functions.php';
require_once MDSCW_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once MDSCW_PLUGIN_DIR . 'includes/class-admin-go-pro.php';
require_once MDSCW_PLUGIN_DIR . 'includes/class-frontend.php';
require_once MDSCW_PLUGIN_DIR . 'includes/class-ajax-handler.php';

/**
 * Class Siddik_Chat_Widget
 */
class Siddik_Chat_Widget {

	/**
	 * Singleton instance.
	 *
	 * @var Siddik_Chat_Widget|null
	 */
	private static $instance = null;

	/**
	 * Admin settings handler.
	 *
	 * @var MDSCW_Admin_Settings
	 */
	public $admin;

	/**
	 * Go PRO page handler.
	 *
	 * @var MDSCW_Admin_Go_Pro
	 */
	public $go_pro;

	/**
	 * Frontend handler.
	 *
	 * @var MDSCW_Frontend
	 */
	public $frontend;

	/**
	 * AJAX handler.
	 *
	 * @var MDSCW_Ajax_Handler
	 */
	public $ajax;

	/**
	 * Get singleton instance.
	 *
	 * @return Siddik_Chat_Widget
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
		$this->init_components();

		/**
		 * Fires after the free Siddik Chat Widget plugin is fully loaded.
		 *
		 * Pro add-ons should hook here to register premium features.
		 *
		 * @param Siddik_Chat_Widget $plugin Main plugin instance.
		 */
		do_action( 'mdscw_loaded', $this );
	}

	/**
	 * Register core hooks.
	 */
	private function init_hooks() {
		register_activation_hook( MDSCW_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( MDSCW_PLUGIN_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize free plugin components.
	 */
	private function init_components() {
		$this->admin    = new MDSCW_Admin_Settings();
		$this->go_pro   = new MDSCW_Admin_Go_Pro();
		$this->frontend = new MDSCW_Frontend();
		$this->ajax     = new MDSCW_Ajax_Handler();
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		$legacy_settings = get_option( 'scb_settings', false );
		if ( $legacy_settings && false === get_option( 'mdscw_settings', false ) ) {
			add_option( 'mdscw_settings', $legacy_settings );
		}

		if ( false === get_option( 'mdscw_settings' ) ) {
			add_option( 'mdscw_settings', MDSCW_Admin_Settings::get_default_settings() );
		}

		delete_option( 'scb_settings' );
		delete_option( 'scb_is_pro_active' );
		delete_option( 'mdscw_is_pro_active' );
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Reserved for cleanup if needed.
	}
}
