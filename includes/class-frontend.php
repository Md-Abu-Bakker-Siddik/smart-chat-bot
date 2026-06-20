<?php
/**
 * Frontend widget rendering and script enqueue.
 *
 * @package Siddik_Chat_Widget
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MDSCW_Frontend
 */
class MDSCW_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_widget' ) );
	}

	/**
	 * Check if widget should be displayed.
	 *
	 * @return bool
	 */
	private function should_display() {
		$settings = MDSCW_Admin_Settings::get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_display() ) {
			return;
		}

		$settings = MDSCW_Admin_Settings::get_settings();
		$channels = mdscw_get_frontend_channels( $settings );

		wp_enqueue_style(
			'mdscw-frontend',
			MDSCW_PLUGIN_URL . 'frontend/css/chat.css',
			array(),
			MDSCW_VERSION
		);

		wp_enqueue_script(
			'mdscw-frontend',
			MDSCW_PLUGIN_URL . 'frontend/js/frontend.js',
			array(),
			MDSCW_VERSION,
			true
		);

		wp_localize_script(
			'mdscw-frontend',
			'mdscwData',
			apply_filters(
				'mdscw_frontend_localize_data',
				array(
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( 'mdscw_chat_nonce' ),
					'botName'       => $settings['bot_name'],
					'welcome'       => $settings['welcome_message'],
					'placeholder'   => $settings['placeholder'],
					'color'         => $settings['primary_color'],
					'position'      => $settings['position'],
					'liveChat'      => mdscw_is_pro_active(),
					'pollMs'        => 4000,
					'humanPollMs'   => 2000,
					'channelPrompt' => $settings['channel_prompt'],
					'channels'      => $channels,
					'faqs'          => mdscw_get_faq_items( $settings['rules'] ),
					'i18n'          => array(
						'openChat'       => __( 'Open chat', 'siddik-chat-widget' ),
						'closeChat'      => __( 'Close chat', 'siddik-chat-widget' ),
						'errorGeneric'   => __( 'Something went wrong. Please try again.', 'siddik-chat-widget' ),
						'errorNetwork'   => __( 'Unable to connect. Please try again.', 'siddik-chat-widget' ),
						'channelLive'    => __( 'Live Chat', 'siddik-chat-widget' ),
						'channelWhatsapp' => __( 'WhatsApp', 'siddik-chat-widget' ),
						'channelMessenger' => __( 'Messenger', 'siddik-chat-widget' ),
						'channelTelegram' => __( 'Telegram', 'siddik-chat-widget' ),
						'liveChatPlaceholder' => __( 'Message support…', 'siddik-chat-widget' ),
					),
				)
			)
		);
	}

	/**
	 * Render chat widget markup in footer.
	 */
	public function render_widget() {
		if ( ! $this->should_display() ) {
			return;
		}

		$settings = MDSCW_Admin_Settings::get_settings();
		$channels = mdscw_get_frontend_channels( $settings );
		$position = esc_attr( $settings['position'] );
		$color    = esc_attr( $settings['primary_color'] );
		?>
		<div id="mdscw-widget" class="mdscw-widget mdscw-<?php echo esc_attr( $position ); ?>" style="--mdscw-primary: <?php echo esc_attr( $color ); ?>;" aria-live="polite">
			<div id="mdscw-window" class="mdscw-window mdscw-closed" hidden>
				<div class="mdscw-header">
					<div class="mdscw-header-top">
						<div class="mdscw-header-info">
							<span class="mdscw-avatar" aria-hidden="true">✨</span>
							<div>
								<span class="mdscw-bot-name"><?php echo esc_html( $settings['bot_name'] ); ?></span>
								<span class="mdscw-bot-status"><?php esc_html_e( 'Online · Ready to help', 'siddik-chat-widget' ); ?></span>
							</div>
						</div>
						<button type="button" id="mdscw-close" class="mdscw-close" aria-label="<?php esc_attr_e( 'Close chat', 'siddik-chat-widget' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
								<line x1="18" y1="6" x2="6" y2="18"></line>
								<line x1="6" y1="6" x2="18" y2="18"></line>
							</svg>
						</button>
					</div>
					<div class="mdscw-channel-bar" id="mdscw-channel-bar" role="toolbar" aria-label="<?php esc_attr_e( 'Communication channels', 'siddik-chat-widget' ); ?>">
						<?php foreach ( $channels as $slug => $channel ) : ?>
							<?php if ( empty( $channel['enabled'] ) ) : ?>
								<?php continue; ?>
							<?php endif; ?>
							<button
								type="button"
								class="mdscw-channel-btn<?php echo 'live_chat' === $slug ? ' is-active' : ''; ?>"
								data-channel="<?php echo esc_attr( $slug ); ?>"
								title="<?php echo esc_attr( $channel['label'] ); ?>"
								aria-label="<?php echo esc_attr( $channel['label'] ); ?>"
								aria-pressed="<?php echo 'live_chat' === $slug ? 'true' : 'false'; ?>"
							>
								<span class="mdscw-channel-icon" aria-hidden="true"><?php echo esc_html( $channel['icon'] ); ?></span>
								<span class="mdscw-channel-label"><?php echo esc_html( $channel['label'] ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
				<div id="mdscw-messages" class="mdscw-messages" role="log" aria-label="<?php esc_attr_e( 'Chat messages', 'siddik-chat-widget' ); ?>"></div>
				<form id="mdscw-form" class="mdscw-form">
					<input
						type="text"
						id="mdscw-input"
						class="mdscw-input"
						placeholder="<?php echo esc_attr( $settings['placeholder'] ); ?>"
						autocomplete="off"
						aria-label="<?php esc_attr_e( 'Message input', 'siddik-chat-widget' ); ?>"
					/>
					<button type="submit" class="mdscw-send" aria-label="<?php esc_attr_e( 'Send message', 'siddik-chat-widget' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<line x1="22" y1="2" x2="11" y2="13"></line>
							<polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
						</svg>
					</button>
				</form>
			</div>
			<button type="button" id="mdscw-toggle" class="mdscw-toggle" aria-label="<?php esc_attr_e( 'Open chat', 'siddik-chat-widget' ); ?>" aria-expanded="false" aria-controls="mdscw-window">
				<svg class="mdscw-icon-chat" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h3l4 4 4-4h5c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="currentColor"/>
				</svg>
				<svg class="mdscw-icon-close" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12l-4.89 4.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.89a1 1 0 0 0 0-1.4z" fill="currentColor"/>
				</svg>
			</button>
		</div>
		<?php
	}
}
