<?php
/**
 * Shared helper functions and extension API.
 *
 * @package Siddik_Chat_Widget
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the Siddik Chat Widget PRO add-on plugin is installed and active.
 *
 * @return bool
 */
function mdscw_is_pro_plugin_installed() {
	return defined( 'MDSCWPRO_VERSION' );
}

/**
 * Check whether Siddik Chat Widget Pro features are unlocked.
 *
 * Pro features load when the separate PRO add-on plugin is active.
 * Extensions may override via the mdscw_is_pro_active filter.
 *
 * @return bool
 */
function mdscw_is_pro_active() {
	return (bool) apply_filters( 'mdscw_is_pro_active', mdscw_is_pro_plugin_installed() );
}

/**
 * Whether a live human agent has taken over the conversation.
 *
 * Pro add-ons hook mdscw_session_human_takeover to provide session state.
 *
 * @param string $session_id Chat session ID.
 * @return bool
 */
function mdscw_session_is_human_takeover( $session_id ) {
	$session_id = mdscw_sanitize_session_id( $session_id );
	if ( '' === $session_id ) {
		return false;
	}

	return (bool) apply_filters( 'mdscw_session_human_takeover', false, $session_id );
}

/**
 * Get the external Pro landing / pricing page URL.
 *
 * @return string
 */
function mdscw_get_pro_url() {
	return apply_filters( 'mdscw_pro_landing_url', 'https://profiles.wordpress.org/mdabubakkersiddik/' );
}

/**
 * Whether the PRO add-on is offered as a free early-access download.
 *
 * Used only for Go PRO page copy in the free plugin. License logic lives in the PRO add-on.
 * Extensions may disable when moving to paid pricing: add_filter( 'mdscw_pro_early_access_enabled', '__return_false' );
 *
 * @return bool
 */
function mdscw_is_pro_early_access() {
	return (bool) apply_filters( 'mdscw_pro_early_access_enabled', true );
}

/**
 * URL to download the separate PRO add-on plugin ZIP.
 *
 * @return string
 */
function mdscw_get_pro_download_url() {
	return apply_filters( 'mdscw_pro_download_url', mdscw_get_pro_url() );
}

/**
 * Sanitize a chat session identifier.
 *
 * @param string $session_id Raw session ID.
 * @return string
 */
function mdscw_sanitize_session_id( $session_id ) {
	$session_id = sanitize_text_field( $session_id );
	$session_id = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $session_id );
	return substr( $session_id, 0, 64 );
}

/**
 * Generate a unique chat session identifier.
 *
 * @return string
 */
function mdscw_generate_session_id() {
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
function mdscw_match_rule( $message, $rules ) {
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
function mdscw_get_allowed_channels() {
	return array( 'live_chat', 'whatsapp', 'messenger', 'telegram' );
}

/**
 * Sanitize channel identifier.
 *
 * @param string $channel Raw channel.
 * @return string
 */
function mdscw_sanitize_channel( $channel ) {
	$channel = sanitize_key( $channel );
	return in_array( $channel, mdscw_get_allowed_channels(), true ) ? $channel : 'live_chat';
}

/**
 * Whether the channel routes to an external messenger.
 *
 * @param string $channel Channel slug.
 * @return bool
 */
function mdscw_is_external_channel( $channel ) {
	return in_array( $channel, array( 'whatsapp', 'messenger', 'telegram' ), true );
}

/**
 * Build FAQ quick-reply items from rules.
 *
 * @param array $rules Response rules.
 * @return array
 */
function mdscw_get_faq_items( $rules ) {
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
function mdscw_get_channel_cta( $channel, $settings ) {
	if ( ! mdscw_is_external_channel( $channel ) ) {
		return null;
	}

	$url_key     = $channel . '_url';
	$enabled_key = $channel . '_enabled';
	$url         = ! empty( $settings[ $url_key ] ) ? esc_url_raw( $settings[ $url_key ] ) : '';

	if ( empty( $settings[ $enabled_key ] ) || '' === $url ) {
		return null;
	}

	$labels = array(
		'whatsapp'  => __( 'Open in WhatsApp', 'siddik-chat-widget' ),
		'messenger' => __( 'Launch Messenger Chat', 'siddik-chat-widget' ),
		'telegram'  => __( 'Launch Telegram Chat', 'siddik-chat-widget' ),
	);

	return array(
		'label' => $labels[ $channel ] ?? __( 'Continue Chat', 'siddik-chat-widget' ),
		'url'   => $url,
	);
}

/**
 * Get enabled channels for the frontend.
 *
 * @param array $settings Plugin settings.
 * @return array
 */
function mdscw_get_frontend_channels( $settings ) {
	$channels = array(
		'live_chat' => array(
			'enabled' => true,
			'label'   => __( 'Live Chat', 'siddik-chat-widget' ),
			'icon'    => '💬',
		),
		'whatsapp'  => array(
			'enabled' => ! empty( $settings['whatsapp_enabled'] ) && ! empty( $settings['whatsapp_url'] ),
			'label'   => __( 'WhatsApp', 'siddik-chat-widget' ),
			'icon'    => '🟢',
			'url'     => ! empty( $settings['whatsapp_url'] ) ? esc_url( $settings['whatsapp_url'] ) : '',
		),
		'messenger' => array(
			'enabled' => ! empty( $settings['messenger_enabled'] ) && ! empty( $settings['messenger_url'] ),
			'label'   => __( 'Messenger', 'siddik-chat-widget' ),
			'icon'    => '🔵',
			'url'     => ! empty( $settings['messenger_url'] ) ? esc_url( $settings['messenger_url'] ) : '',
		),
		'telegram'  => array(
			'enabled' => ! empty( $settings['telegram_enabled'] ) && ! empty( $settings['telegram_url'] ),
			'label'   => __( 'Telegram', 'siddik-chat-widget' ),
			'icon'    => '✈️',
			'url'     => ! empty( $settings['telegram_url'] ) ? esc_url( $settings['telegram_url'] ) : '',
		),
	);

	return apply_filters( 'mdscw_frontend_channels', $channels, $settings );
}
