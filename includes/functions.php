<?php
/**
 * Shared helper functions and extension API.
 *
 * @package Smart_Chat_Bot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the Smart Chat Bot PRO add-on plugin is installed and active.
 *
 * @return bool
 */
function scb_is_pro_plugin_installed() {
	return defined( 'SCB_PRO_VERSION' );
}

/**
 * Check whether Smart Chat Bot Pro features are unlocked.
 *
 * Pro features load when the separate PRO add-on plugin is active.
 * Extensions may override via the scb_is_pro_active filter.
 *
 * @return bool
 */
function scb_is_pro_active() {
	return (bool) apply_filters( 'scb_is_pro_active', scb_is_pro_plugin_installed() );
}

/**
 * Get the external Pro landing / pricing page URL.
 *
 * @return string
 */
function scb_get_pro_url() {
	return apply_filters( 'scb_pro_landing_url', 'https://profiles.wordpress.org/mdabubakkersiddik1/' );
}

/**
 * Sanitize a chat session identifier.
 *
 * @param string $session_id Raw session ID.
 * @return string
 */
function scb_sanitize_session_id( $session_id ) {
	$session_id = sanitize_text_field( $session_id );
	$session_id = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $session_id );
	return substr( $session_id, 0, 64 );
}

/**
 * Generate a unique chat session identifier.
 *
 * @return string
 */
function scb_generate_session_id() {
	if ( function_exists( 'wp_generate_uuid4' ) ) {
		return wp_generate_uuid4();
	}

	return bin2hex( random_bytes( 16 ) );
}

/**
 * Match a user message against keyword rules.
 *
 * @param string $message User message.
 * @param array  $rules   Rule list.
 * @return string|null
 */
function scb_match_rule( $message, $rules ) {
	$message_lower = strtolower( $message );

	if ( ! is_array( $rules ) ) {
		return null;
	}

	foreach ( $rules as $rule ) {
		$keywords = array_map( 'trim', explode( ',', strtolower( $rule['keywords'] ?? '' ) ) );
		$keywords = array_filter( $keywords );

		foreach ( $keywords as $keyword ) {
			if ( '' !== $keyword && false !== strpos( $message_lower, $keyword ) ) {
				return $rule['response'];
			}
		}
	}

	return null;
}

/**
 * Allowed communication channels.
 *
 * @return array
 */
function scb_get_allowed_channels() {
	return array( 'live_chat', 'whatsapp', 'messenger', 'telegram' );
}

/**
 * Sanitize channel identifier.
 *
 * @param string $channel Raw channel.
 * @return string
 */
function scb_sanitize_channel( $channel ) {
	$channel = sanitize_key( $channel );
	return in_array( $channel, scb_get_allowed_channels(), true ) ? $channel : 'live_chat';
}

/**
 * Whether the channel routes to an external messenger.
 *
 * @param string $channel Channel slug.
 * @return bool
 */
function scb_is_external_channel( $channel ) {
	return in_array( $channel, array( 'whatsapp', 'messenger', 'telegram' ), true );
}

/**
 * Build FAQ quick-reply items from rules.
 *
 * @param array $rules Response rules.
 * @return array
 */
function scb_get_faq_items( $rules ) {
	$faqs = array();

	if ( ! is_array( $rules ) ) {
		return $faqs;
	}

	foreach ( $rules as $rule ) {
		$keywords = trim( $rule['keywords'] ?? '' );
		$response = trim( $rule['response'] ?? '' );

		if ( '' === $keywords || '' === $response ) {
			continue;
		}

		$label = ! empty( $rule['faq_label'] )
			? $rule['faq_label']
			: ucfirst( trim( explode( ',', $keywords )[0] ) );

		$faqs[] = array(
			'label'   => $label,
			'message' => $label,
		);
	}

	return $faqs;
}

/**
 * Get CTA data for an external channel.
 *
 * @param string $channel  Channel slug.
 * @param array  $settings Plugin settings.
 * @return array|null
 */
function scb_get_channel_cta( $channel, $settings ) {
	if ( ! scb_is_external_channel( $channel ) ) {
		return null;
	}

	$url_key     = $channel . '_url';
	$enabled_key = $channel . '_enabled';
	$url         = ! empty( $settings[ $url_key ] ) ? esc_url_raw( $settings[ $url_key ] ) : '';

	if ( empty( $settings[ $enabled_key ] ) || '' === $url ) {
		return null;
	}

	$labels = array(
		'whatsapp'  => __( 'Open in WhatsApp', 'smart-chat-bot' ),
		'messenger' => __( 'Launch Messenger Chat', 'smart-chat-bot' ),
		'telegram'  => __( 'Launch Telegram Chat', 'smart-chat-bot' ),
	);

	return array(
		'label' => $labels[ $channel ] ?? __( 'Continue Chat', 'smart-chat-bot' ),
		'url'   => $url,
	);
}

/**
 * Get enabled channels for the frontend.
 *
 * @param array $settings Plugin settings.
 * @return array
 */
function scb_get_frontend_channels( $settings ) {
	$channels = array(
		'live_chat' => array(
			'enabled' => true,
			'label'   => __( 'Live Chat', 'smart-chat-bot' ),
			'icon'    => '💬',
		),
		'whatsapp'  => array(
			'enabled' => ! empty( $settings['whatsapp_enabled'] ) && ! empty( $settings['whatsapp_url'] ),
			'label'   => __( 'WhatsApp', 'smart-chat-bot' ),
			'icon'    => '🟢',
			'url'     => ! empty( $settings['whatsapp_url'] ) ? esc_url( $settings['whatsapp_url'] ) : '',
		),
		'messenger' => array(
			'enabled' => ! empty( $settings['messenger_enabled'] ) && ! empty( $settings['messenger_url'] ),
			'label'   => __( 'Messenger', 'smart-chat-bot' ),
			'icon'    => '🔵',
			'url'     => ! empty( $settings['messenger_url'] ) ? esc_url( $settings['messenger_url'] ) : '',
		),
		'telegram'  => array(
			'enabled' => ! empty( $settings['telegram_enabled'] ) && ! empty( $settings['telegram_url'] ),
			'label'   => __( 'Telegram', 'smart-chat-bot' ),
			'icon'    => '✈️',
			'url'     => ! empty( $settings['telegram_url'] ) ? esc_url( $settings['telegram_url'] ) : '',
		),
	);

	return apply_filters( 'scb_frontend_channels', $channels, $settings );
}
