<?php
/**
 * Admin settings and dashboard page.
 *
 * @package Siddik_Chat_Widget
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MDSCW_Admin_Settings
 */
class MDSCW_Admin_Settings {

	/**
	 * Option key.
	 */
	const OPTION_KEY = 'mdscw_settings';

	/**
	 * Main menu slug.
	 */
	const MENU_SLUG = 'siddik-chat-widget';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . MDSCW_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_row_meta' ), 10, 2 );
	}

	/**
	 * Default plugin settings.
	 *
	 * @return array
	 */
	public static function get_default_settings() {
		return array(
			'enabled'           => true,
			'bot_name'          => __( 'Smart Bot', 'siddik-chat-widget' ),
			'welcome_message'   => __( 'Hi! How can I help you today?', 'siddik-chat-widget' ),
			'placeholder'       => __( 'Type your message…', 'siddik-chat-widget' ),
			'primary_color'     => '#4f46e5',
			'position'          => 'bottom-right',
			'rules'             => array(
				array(
					'keywords' => 'hello, hi, hey',
					'response' => __( 'Hello! Welcome to our site. How can I assist you?', 'siddik-chat-widget' ),
				),
				array(
					'keywords' => 'price, cost, pricing',
					'response' => __( 'Please visit our pricing page or contact us for a custom quote.', 'siddik-chat-widget' ),
				),
				array(
					'keywords' => 'contact, email, phone',
					'response' => __( 'You can reach us through our contact page. We typically respond within 24 hours.', 'siddik-chat-widget' ),
				),
			),
			'fallback_response' => __( "I'm sorry, I didn't understand that. Could you rephrase your question?", 'siddik-chat-widget' ),
			'channel_prompt'    => __( 'How would you like to connect with us today?', 'siddik-chat-widget' ),
			'whatsapp_enabled'  => false,
			'whatsapp_url'      => '',
			'messenger_enabled' => false,
			'messenger_url'     => '',
			'telegram_enabled'  => false,
			'telegram_url'      => '',
		);
	}

	/**
	 * Get merged settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = self::get_default_settings();
		$stored   = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings = wp_parse_args( $stored, $defaults );

		if ( ! isset( $settings['rules'] ) || ! is_array( $settings['rules'] ) ) {
			$settings['rules'] = $defaults['rules'];
		}

		return $settings;
	}

	/**
	 * Register admin menu.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Siddik Chat Widget', 'siddik-chat-widget' ),
			__( 'Siddik Chat Widget', 'siddik-chat-widget' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' ),
			'dashicons-format-chat',
			30
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'siddik-chat-widget' ),
			__( 'Settings', 'siddik-chat-widget' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			'mdscw_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::get_default_settings(),
			)
		);
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$defaults = self::get_default_settings();
		$output   = array();

		$output['enabled']           = ! empty( $input['enabled'] );
		$output['bot_name']          = sanitize_text_field( $input['bot_name'] ?? $defaults['bot_name'] );
		$output['welcome_message']   = sanitize_textarea_field( $input['welcome_message'] ?? $defaults['welcome_message'] );
		$output['placeholder']       = sanitize_text_field( $input['placeholder'] ?? $defaults['placeholder'] );
		$output['primary_color']     = sanitize_hex_color( $input['primary_color'] ?? $defaults['primary_color'] ) ?: $defaults['primary_color'];
		$output['position']          = in_array( $input['position'] ?? '', array( 'bottom-right', 'bottom-left' ), true )
			? $input['position']
			: $defaults['position'];
		$output['fallback_response'] = sanitize_textarea_field( $input['fallback_response'] ?? $defaults['fallback_response'] );
		$output['channel_prompt']    = sanitize_text_field( $input['channel_prompt'] ?? $defaults['channel_prompt'] );

		$output['whatsapp_enabled']  = ! empty( $input['whatsapp_enabled'] );
		$output['whatsapp_url']      = esc_url_raw( $input['whatsapp_url'] ?? '' );
		$output['messenger_enabled'] = ! empty( $input['messenger_enabled'] );
		$output['messenger_url']     = esc_url_raw( $input['messenger_url'] ?? '' );
		$output['telegram_enabled']  = ! empty( $input['telegram_enabled'] );
		$output['telegram_url']      = esc_url_raw( $input['telegram_url'] ?? '' );

		$output['rules'] = array();
		if ( ! empty( $input['rules'] ) && is_array( $input['rules'] ) ) {
			foreach ( $input['rules'] as $rule ) {
				$keywords = sanitize_text_field( $rule['keywords'] ?? '' );
				$response = sanitize_textarea_field( $rule['response'] ?? '' );
				$faq_label = sanitize_text_field( $rule['faq_label'] ?? '' );

				if ( '' !== $keywords && '' !== $response ) {
					$output['rules'][] = array(
						'keywords'  => $keywords,
						'response'  => $response,
						'faq_label' => $faq_label,
					);
				}
			}
		}

		if ( empty( $output['rules'] ) ) {
			$output['rules'] = $defaults['rules'];
		}

		return $output;
	}

	/**
	 * Add Settings link on the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ),
			esc_html__( 'Settings', 'siddik-chat-widget' )
		);

		array_unshift( $links, $settings_link );

		if ( ! mdscw_is_pro_active() ) {
			$pro_link = sprintf(
				'<a href="%s" style="font-weight:600;color:#4f46e5;">%s</a>',
				esc_url( admin_url( 'admin.php?page=' . MDSCW_Admin_Go_Pro::PAGE_SLUG ) ),
				esc_html__( 'Go PRO', 'siddik-chat-widget' )
			);
			array_push( $links, $pro_link );
		}

		return $links;
	}

	/**
	 * Add plugin row meta links.
	 *
	 * @param array  $links Plugin row links.
	 * @param string $file  Plugin basename.
	 * @return array
	 */
	public function add_row_meta( $links, $file ) {
		if ( MDSCW_PLUGIN_BASENAME !== $file ) {
			return $links;
		}

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( 'https://wordpress.org/support/plugin/siddik-chat-widget/' ),
			esc_html__( 'Support', 'siddik-chat-widget' )
		);

		return $links;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'mdscw-admin',
			MDSCW_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			MDSCW_VERSION
		);

		wp_enqueue_script(
			'mdscw-admin',
			MDSCW_PLUGIN_URL . 'admin/js/admin.js',
			array(),
			MDSCW_VERSION,
			true
		);
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = self::get_settings();
		?>
		<div class="wrap mdscw-admin-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php if ( ! mdscw_is_pro_active() ) : ?>
				<div class="notice notice-info mdscw-pro-notice">
					<p>
						<?php
						if ( mdscw_is_pro_early_access() ) {
							printf(
								/* translators: 1: opening anchor tag, 2: closing anchor tag */
								esc_html__( 'Live inbox, message storage, and AI replies are available in PRO — %1$sdownload free during early access%2$s.', 'siddik-chat-widget' ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=' . MDSCW_Admin_Go_Pro::PAGE_SLUG ) ) . '">',
								'</a>'
							);
						} else {
							printf(
								/* translators: 1: opening anchor tag, 2: closing anchor tag */
								esc_html__( 'Want live inbox, message storage, and AI replies? %1$sUpgrade to PRO%2$s.', 'siddik-chat-widget' ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=' . MDSCW_Admin_Go_Pro::PAGE_SLUG ) ) . '">',
								'</a>'
							);
						}
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php" id="mdscw-settings-form">
				<?php settings_fields( 'mdscw_settings_group' ); ?>

				<h2 class="title"><?php esc_html_e( 'General', 'siddik-chat-widget' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Chat Bot', 'siddik-chat-widget' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enabled]" value="1" <?php checked( $settings['enabled'] ); ?> />
								<?php esc_html_e( 'Show chat widget on the frontend', 'siddik-chat-widget' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mdscw-bot-name"><?php esc_html_e( 'Bot Name', 'siddik-chat-widget' ); ?></label></th>
						<td>
							<input type="text" id="mdscw-bot-name" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[bot_name]" value="<?php echo esc_attr( $settings['bot_name'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mdscw-welcome"><?php esc_html_e( 'Welcome Message', 'siddik-chat-widget' ); ?></label></th>
						<td>
							<textarea id="mdscw-welcome" class="large-text" rows="3" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[welcome_message]"><?php echo esc_textarea( $settings['welcome_message'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mdscw-placeholder"><?php esc_html_e( 'Input Placeholder', 'siddik-chat-widget' ); ?></label></th>
						<td>
							<input type="text" id="mdscw-placeholder" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[placeholder]" value="<?php echo esc_attr( $settings['placeholder'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mdscw-color"><?php esc_html_e( 'Primary Color', 'siddik-chat-widget' ); ?></label></th>
						<td>
							<input type="color" id="mdscw-color" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[primary_color]" value="<?php echo esc_attr( $settings['primary_color'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mdscw-position"><?php esc_html_e( 'Widget Position', 'siddik-chat-widget' ); ?></label></th>
						<td>
							<select id="mdscw-position" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[position]">
								<option value="bottom-right" <?php selected( $settings['position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'siddik-chat-widget' ); ?></option>
								<option value="bottom-left" <?php selected( $settings['position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'siddik-chat-widget' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mdscw-fallback"><?php esc_html_e( 'Fallback Response', 'siddik-chat-widget' ); ?></label></th>
						<td>
							<textarea id="mdscw-fallback" class="large-text" rows="3" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[fallback_response]"><?php echo esc_textarea( $settings['fallback_response'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Shown when no keyword rule matches.', 'siddik-chat-widget' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Omnichannel Settings', 'siddik-chat-widget' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Configure external messenger links. Visitors can route FAQ answers to these channels.', 'siddik-chat-widget' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mdscw-channel-prompt"><?php esc_html_e( 'Channel Selector Prompt', 'siddik-chat-widget' ); ?></label></th>
						<td>
							<input type="text" id="mdscw-channel-prompt" class="large-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[channel_prompt]" value="<?php echo esc_attr( $settings['channel_prompt'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'WhatsApp', 'siddik-chat-widget' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[whatsapp_enabled]" value="1" <?php checked( $settings['whatsapp_enabled'] ); ?> />
								<?php esc_html_e( 'Enable WhatsApp channel', 'siddik-chat-widget' ); ?>
							</label>
							<p>
								<input type="url" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[whatsapp_url]" value="<?php echo esc_attr( $settings['whatsapp_url'] ); ?>" placeholder="https://wa.me/1234567890" />
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Messenger', 'siddik-chat-widget' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[messenger_enabled]" value="1" <?php checked( $settings['messenger_enabled'] ); ?> />
								<?php esc_html_e( 'Enable Messenger channel', 'siddik-chat-widget' ); ?>
							</label>
							<p>
								<input type="url" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[messenger_url]" value="<?php echo esc_attr( $settings['messenger_url'] ); ?>" placeholder="https://m.me/yourpage" />
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Telegram', 'siddik-chat-widget' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[telegram_enabled]" value="1" <?php checked( $settings['telegram_enabled'] ); ?> />
								<?php esc_html_e( 'Enable Telegram channel', 'siddik-chat-widget' ); ?>
							</label>
							<p>
								<input type="url" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[telegram_url]" value="<?php echo esc_attr( $settings['telegram_url'] ); ?>" placeholder="https://t.me/username" />
							</p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Response Rules', 'siddik-chat-widget' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Define keyword triggers and bot responses. Separate multiple keywords with commas.', 'siddik-chat-widget' ); ?></p>

				<div id="mdscw-rules-container">
					<?php foreach ( $settings['rules'] as $index => $rule ) : ?>
						<div class="mdscw-rule-row" data-index="<?php echo esc_attr( $index ); ?>">
							<div class="mdscw-rule-fields">
								<label>
									<span class="mdscw-label"><?php esc_html_e( 'FAQ Button Label', 'siddik-chat-widget' ); ?></span>
									<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[rules][<?php echo esc_attr( $index ); ?>][faq_label]" value="<?php echo esc_attr( $rule['faq_label'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Optional display label', 'siddik-chat-widget' ); ?>" />
								</label>
								<label>
									<span class="mdscw-label"><?php esc_html_e( 'Keywords', 'siddik-chat-widget' ); ?></span>
									<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[rules][<?php echo esc_attr( $index ); ?>][keywords]" value="<?php echo esc_attr( $rule['keywords'] ); ?>" placeholder="<?php esc_attr_e( 'hello, hi, hey', 'siddik-chat-widget' ); ?>" />
								</label>
								<label>
									<span class="mdscw-label"><?php esc_html_e( 'Response', 'siddik-chat-widget' ); ?></span>
									<textarea name="<?php echo esc_attr( self::OPTION_KEY ); ?>[rules][<?php echo esc_attr( $index ); ?>][response]" rows="2"><?php echo esc_textarea( $rule['response'] ); ?></textarea>
								</label>
							</div>
							<button type="button" class="button mdscw-remove-rule" aria-label="<?php esc_attr_e( 'Remove rule', 'siddik-chat-widget' ); ?>">&times;</button>
						</div>
					<?php endforeach; ?>
				</div>

				<p>
					<button type="button" id="mdscw-add-rule" class="button button-secondary"><?php esc_html_e( 'Add Rule', 'siddik-chat-widget' ); ?></button>
				</p>

				<?php submit_button(); ?>
			</form>

			<template id="mdscw-rule-template">
				<div class="mdscw-rule-row" data-index="__INDEX__">
					<div class="mdscw-rule-fields">
						<label>
							<span class="mdscw-label"><?php esc_html_e( 'FAQ Button Label', 'siddik-chat-widget' ); ?></span>
							<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[rules][__INDEX__][faq_label]" value="" placeholder="<?php esc_attr_e( 'Optional display label', 'siddik-chat-widget' ); ?>" />
						</label>
						<label>
							<span class="mdscw-label"><?php esc_html_e( 'Keywords', 'siddik-chat-widget' ); ?></span>
							<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[rules][__INDEX__][keywords]" value="" placeholder="<?php esc_attr_e( 'hello, hi, hey', 'siddik-chat-widget' ); ?>" />
						</label>
						<label>
							<span class="mdscw-label"><?php esc_html_e( 'Response', 'siddik-chat-widget' ); ?></span>
							<textarea name="<?php echo esc_attr( self::OPTION_KEY ); ?>[rules][__INDEX__][response]" rows="2"></textarea>
						</label>
					</div>
					<button type="button" class="button mdscw-remove-rule" aria-label="<?php esc_attr_e( 'Remove rule', 'siddik-chat-widget' ); ?>">&times;</button>
				</div>
			</template>
		</div>
		<?php
	}
}
