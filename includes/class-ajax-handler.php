<?php
/**
 * Rule-based bot responses and AJAX handling (free tier).
 *
 * @package Siddik_Chat_Widget
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MDSCW_Ajax_Handler
 */
class MDSCW_Ajax_Handler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_mdscw_send_message', array( $this, 'handle_message' ) );
		add_action( 'wp_ajax_nopriv_mdscw_send_message', array( $this, 'handle_message' ) );
	}

	/**
	 * Handle incoming chat message via AJAX.
	 */
	public function handle_message() {
		check_ajax_referer( 'mdscw_chat_nonce', 'nonce' );

		$message    = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
		$session_id = isset( $_POST['session_id'] ) ? mdscw_sanitize_session_id( wp_unslash( $_POST['session_id'] ) ) : '';
		$channel    = isset( $_POST['current_channel'] ) ? mdscw_sanitize_channel( wp_unslash( $_POST['current_channel'] ) ) : 'live_chat';

		if ( '' === $message ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please enter a message.', 'siddik-chat-widget' ),
				),
				400
			);
		}

		$settings       = MDSCW_Admin_Settings::get_settings();
		$is_external    = mdscw_is_external_channel( $channel );
		$human_takeover = ! $is_external && '' !== $session_id && mdscw_session_is_human_takeover( $session_id );
		$rule_response  = $human_takeover ? null : mdscw_match_rule( $message, $settings['rules'] );

		$payload = array(
			'message'        => $message,
			'session_id'     => $session_id,
			'channel'        => $channel,
			'settings'       => $settings,
			'rule_response'  => $rule_response,
			'response'       => $rule_response,
			'source'         => null !== $rule_response ? 'rule' : null,
			'is_external'    => $is_external,
			'human_takeover' => $human_takeover,
		);

		/**
		 * Filter the message payload before the bot reply is finalized.
		 *
		 * Pro add-ons hook here for live_chat only (DB + OpenAI).
		 *
		 * @param array $payload Message context.
		 */
		$payload = apply_filters( 'mdscw_message_before_reply', $payload );

		if ( ! empty( $payload['human_takeover'] ) ) {
			$payload['response'] = '';
			$payload['source']   = 'human_takeover';
		} elseif ( empty( $payload['response'] ) ) {
			$payload['response'] = $settings['fallback_response'];
			$payload['source']   = 'fallback';
		}

		/**
		 * Filter the message payload after the bot reply is determined.
		 *
		 * @param array $payload Message context.
		 */
		$payload = apply_filters( 'mdscw_message_after_reply', $payload );

		$response_data = array(
			'reply'          => $payload['response'],
			'session_id'     => $payload['session_id'],
			'source'         => $payload['source'],
			'channel'        => $channel,
			'human_takeover' => ! empty( $payload['human_takeover'] ),
		);

		if ( $is_external ) {
			$cta = mdscw_get_channel_cta( $channel, $settings );
			if ( $cta ) {
				$response_data['cta'] = $cta;
			}
		}

		wp_send_json_success( $response_data );
	}
}
