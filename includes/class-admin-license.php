<?php
/**
 * Promotional license activation (admin AJAX).
 *
 * @package Smart_Chat_Bot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SCB_Admin_License
 */
class SCB_Admin_License {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_scb_activate_license', array( $this, 'activate_license' ) );
		add_action( 'wp_ajax_scb_deactivate_license', array( $this, 'deactivate_license' ) );
	}

	/**
	 * Verify admin capability and nonce for license AJAX.
	 *
	 * @return void
	 */
	private function verify_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to manage licenses.', 'smart-chat-bot' ),
				),
				403
			);
		}

		check_ajax_referer( 'scb_license_nonce', 'nonce' );
	}

	/**
	 * Activate PRO via promo / license key.
	 */
	public function activate_license() {
		$this->verify_request();

		$key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

		if ( ! scb_is_valid_promo_license_key( $key ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid promo key. Please try again.', 'smart-chat-bot' ),
				),
				400
			);
		}

		scb_activate_promo_license();

		wp_send_json_success(
			array(
				'message' => __( 'PRO Version Activated Successfully!', 'smart-chat-bot' ),
			)
		);
	}

	/**
	 * Deactivate the stored promo / license.
	 */
	public function deactivate_license() {
		$this->verify_request();

		scb_deactivate_promo_license();

		wp_send_json_success(
			array(
				'message' => __( 'License deactivated. PRO features are now locked.', 'smart-chat-bot' ),
			)
		);
	}
}
