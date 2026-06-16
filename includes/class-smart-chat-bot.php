<?php
/**
 * Core plugin class – initialization and hooks.
 *
 * @package Smart_Chat_Bot
 */

defined( 'ABSPATH' ) || exit;

require_once SCB_PLUGIN_DIR . 'includes/functions.php';
require_once SCB_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once SCB_PLUGIN_DIR . 'includes/class-admin-go-pro.php';
require_once SCB_PLUGIN_DIR . 'includes/class-frontend.php';
require_once SCB_PLUGIN_DIR . 'includes/class-ajax-handler.php';

/**
 * Class Smart_Chat_Bot
 */
class Smart_Chat_Bot {

	/**
	 * Singleton instance.
	 *
	 * @var Smart_Chat_Bot|null
	 */
	private static $instance = null;

	/**
	 * Admin settings handler.
	 *
	 * @var SCB_Admin_Settings
	 */
	public $admin;

	/**
	 * Go PRO page handler.
	 *
	 * @var SCB_Admin_Go_Pro
	 */
	public $go_pro;

	/**
	 * Frontend handler.
	 *
	 * @var SCB_Frontend
	 */
	public $frontend;

	/**
	 * AJAX handler.
	 *
	 * @var SCB_Ajax_Handler
	 */
	public $ajax;

	/**
	 * Get singleton instance.
	 *
	 * @return Smart_Chat_Bot
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
		$this->load_textdomain();
		$this->init_hooks();
		$this->init_components();

		/**
		 * Fires after the free Smart Chat Bot plugin is fully loaded.
		 *
		 * Pro add-ons should hook here to register premium features.
		 *
		 * @param Smart_Chat_Bot $plugin Main plugin instance.
		 */
		do_action( 'scb_loaded', $this );
	}

	/**
	 * Load plugin text domain.
	 */
	private function load_textdomain() {
		add_action(
			'init',
			function () {
				load_plugin_textdomain(
					'smart-chat-bot',
					false,
					dirname( SCB_PLUGIN_BASENAME ) . '/languages'
				);
			}
		);
	}

	/**
	 * Register core hooks.
	 */
	private function init_hooks() {
		register_activation_hook( SCB_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( SCB_PLUGIN_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize free plugin components.
	 */
	private function init_components() {
		$this->admin    = new SCB_Admin_Settings();
		$this->go_pro   = new SCB_Admin_Go_Pro();
		$this->frontend = new SCB_Frontend();
		$this->ajax     = new SCB_Ajax_Handler();
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		if ( false === get_option( 'scb_settings' ) ) {
			add_option( 'scb_settings', SCB_Admin_Settings::get_default_settings() );
		}

		// Remove legacy dev-only option from earlier builds.
		delete_option( 'scb_is_pro_active' );
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Reserved for cleanup if needed.
	}
}
