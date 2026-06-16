<?php
/**
 * Frontend widget rendering and script enqueue.
 *
 * @package Smart_Chat_Bot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SCB_Frontend
 */
class SCB_Frontend {

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
		$settings = SCB_Admin_Settings::get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_display() ) {
			return;
		}

		$settings = SCB_Admin_Settings::get_settings();
		$channels = scb_get_frontend_channels( $settings );

		wp_enqueue_style(
			'scb-frontend',
			SCB_PLUGIN_URL . 'frontend/css/chat.css',
			array(),
			SCB_VERSION
		);

		wp_enqueue_script(
			'scb-frontend',
			SCB_PLUGIN_URL . 'frontend/js/frontend.js',
			array(),
			SCB_VERSION,
			true
		);

		wp_localize_script(
			'scb-frontend',
			'scbData',
			apply_filters(
				'scb_frontend_localize_data',
				array(
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( 'scb_chat_nonce' ),
					'botName'       => $settings['bot_name'],
					'welcome'       => $settings['welcome_message'],
					'placeholder'   => $settings['placeholder'],
					'color'         => $settings['primary_color'],
					'position'      => $settings['position'],
					'liveChat'      => scb_is_pro_active(),
					'pollMs'        => 4000,
					'channelPrompt' => $settings['channel_prompt'],
					'channels'      => $channels,
					'faqs'          => scb_get_faq_items( $settings['rules'] ),
					'i18n'          => array(
						'openChat'       => __( 'Open chat', 'smart-chat-bot' ),
						'closeChat'      => __( 'Close chat', 'smart-chat-bot' ),
						'errorGeneric'   => __( 'Something went wrong. Please try again.', 'smart-chat-bot' ),
						'errorNetwork'   => __( 'Unable to connect. Please try again.', 'smart-chat-bot' ),
						'channelLive'    => __( 'Live Chat', 'smart-chat-bot' ),
						'channelWhatsapp' => __( 'WhatsApp', 'smart-chat-bot' ),
						'channelMessenger' => __( 'Messenger', 'smart-chat-bot' ),
						'channelTelegram' => __( 'Telegram', 'smart-chat-bot' ),
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

		$settings = SCB_Admin_Settings::get_settings();
		$channels = scb_get_frontend_channels( $settings );
		$position = esc_attr( $settings['position'] );
		$color    = esc_attr( $settings['primary_color'] );
		?>
		<div id="scb-widget" class="scb-widget scb-<?php echo esc_attr( $position ); ?>" style="--scb-primary: <?php echo esc_attr( $color ); ?>;" aria-live="polite">
			<div id="scb-window" class="scb-window scb-closed" hidden>
				<div class="scb-header">
					<div class="scb-header-top">
						<div class="scb-header-info">
							<span class="scb-avatar" aria-hidden="true">✨</span>
							<div>
								<span class="scb-bot-name"><?php echo esc_html( $settings['bot_name'] ); ?></span>
								<span class="scb-bot-status"><?php esc_html_e( 'Online · Ready to help', 'smart-chat-bot' ); ?></span>
							</div>
						</div>
						<button type="button" id="scb-close" class="scb-close" aria-label="<?php esc_attr_e( 'Close chat', 'smart-chat-bot' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
								<line x1="18" y1="6" x2="6" y2="18"></line>
								<line x1="6" y1="6" x2="18" y2="18"></line>
							</svg>
						</button>
					</div>
					<div class="scb-channel-bar" id="scb-channel-bar" role="toolbar" aria-label="<?php esc_attr_e( 'Communication channels', 'smart-chat-bot' ); ?>">
						<?php foreach ( $channels as $slug => $channel ) : ?>
							<?php if ( empty( $channel['enabled'] ) ) : ?>
								<?php continue; ?>
							<?php endif; ?>
							<button
								type="button"
								class="scb-channel-btn<?php echo 'live_chat' === $slug ? ' is-active' : ''; ?>"
								data-channel="<?php echo esc_attr( $slug ); ?>"
								title="<?php echo esc_attr( $channel['label'] ); ?>"
								aria-label="<?php echo esc_attr( $channel['label'] ); ?>"
								aria-pressed="<?php echo 'live_chat' === $slug ? 'true' : 'false'; ?>"
							>
								<span class="scb-channel-icon" aria-hidden="true"><?php echo esc_html( $channel['icon'] ); ?></span>
								<span class="scb-channel-label"><?php echo esc_html( $channel['label'] ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
				<div id="scb-messages" class="scb-messages" role="log" aria-label="<?php esc_attr_e( 'Chat messages', 'smart-chat-bot' ); ?>"></div>
				<form id="scb-form" class="scb-form">
					<input
						type="text"
						id="scb-input"
						class="scb-input"
						placeholder="<?php echo esc_attr( $settings['placeholder'] ); ?>"
						autocomplete="off"
						aria-label="<?php esc_attr_e( 'Message input', 'smart-chat-bot' ); ?>"
					/>
					<button type="submit" class="scb-send" aria-label="<?php esc_attr_e( 'Send message', 'smart-chat-bot' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<line x1="22" y1="2" x2="11" y2="13"></line>
							<polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
						</svg>
					</button>
				</form>
			</div>
			<button type="button" id="scb-toggle" class="scb-toggle" aria-label="<?php esc_attr_e( 'Open chat', 'smart-chat-bot' ); ?>" aria-expanded="false" aria-controls="scb-window">
				<svg class="scb-icon-chat" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h3l4 4 4-4h5c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="currentColor"/>
				</svg>
				<svg class="scb-icon-close" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12l-4.89 4.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.89a1 1 0 0 0 0-1.4z" fill="currentColor"/>
				</svg>
			</button>
		</div>
		<?php
	}
}
