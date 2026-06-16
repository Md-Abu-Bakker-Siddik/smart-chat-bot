<?php
/**
 * Rule-based bot responses and AJAX handling (free tier).
 *
 * @package Smart_Chat_Bot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SCB_Ajax_Handler
 */
class SCB_Ajax_Handler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_scb_send_message', array( $this, 'handle_message' ) );
		add_action( 'wp_ajax_nopriv_scb_send_message', array( $this, 'handle_message' ) );
	}

	/**
	 * Handle incoming chat message via AJAX.
	 */
	public function handle_message() {
		check_ajax_referer( 'scb_chat_nonce', 'nonce' );

		$message    = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
		$session_id = isset( $_POST['session_id'] ) ? scb_sanitize_session_id( wp_unslash( $_POST['session_id'] ) ) : '';
		$channel    = isset( $_POST['current_channel'] ) ? scb_sanitize_channel( wp_unslash( $_POST['current_channel'] ) ) : 'live_chat';

		if ( '' === $message ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please enter a message.', 'smart-chat-bot' ),
				),
				400
			);
		}

		$settings      = SCB_Admin_Settings::get_settings();
		$rule_response = scb_match_rule( $message, $settings['rules'] );
		$is_external   = scb_is_external_channel( $channel );

		$payload = array(
			'message'       => $message,
			'session_id'    => $session_id,
			'channel'       => $channel,
			'settings'      => $settings,
			'rule_response' => $rule_response,
			'response'      => $rule_response,
			'source'        => null !== $rule_response ? 'rule' : null,
			'is_external'   => $is_external,
		);

		/**
		 * Filter the message payload before the bot reply is finalized.
		 *
		 * Pro add-ons hook here for live_chat only (DB + OpenAI).
		 *
		 * @param array $payload Message context.
		 */
		$payload = apply_filters( 'scb_message_before_reply', $payload );

		if ( empty( $payload['response'] ) ) {
			$payload['response'] = $settings['fallback_response'];
			$payload['source']   = 'fallback';
		}

		/**
		 * Filter the message payload after the bot reply is determined.
		 *
		 * @param array $payload Message context.
		 */
		$payload = apply_filters( 'scb_message_after_reply', $payload );

		$response_data = array(
			'reply'      => $payload['response'],
			'session_id' => $payload['session_id'],
			'source'     => $payload['source'],
			'channel'    => $channel,
		);

		if ( $is_external ) {
			$cta = scb_get_channel_cta( $channel, $settings );
			if ( $cta ) {
				$response_data['cta'] = $cta;
			}
		}

		wp_send_json_success( $response_data );
	}
}
