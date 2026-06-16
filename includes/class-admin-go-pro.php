<?php
/**
 * Go PRO / pricing admin page for the free plugin.
 *
 * @package Smart_Chat_Bot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SCB_Admin_Go_Pro
 */
class SCB_Admin_Go_Pro {

	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'smart-chat-bot-go-pro';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register Go PRO submenu (always available for license management).
	 */
	public function register_menu() {
		add_submenu_page(
			SCB_Admin_Settings::MENU_SLUG,
			__( 'Go PRO', 'smart-chat-bot' ),
			__( 'Go PRO', 'smart-chat-bot' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue Go PRO page assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'smart-chat-bot_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'scb-go-pro',
			SCB_PLUGIN_URL . 'admin/css/go-pro.css',
			array(),
			SCB_VERSION
		);

		wp_enqueue_script(
			'scb-go-pro',
			SCB_PLUGIN_URL . 'admin/js/go-pro.js',
			array(),
			SCB_VERSION,
			true
		);

		wp_localize_script(
			'scb-go-pro',
			'scbGoProData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'scb_license_nonce' ),
				'i18n'    => array(
					'errorGeneric'      => __( 'Something went wrong. Please try again.', 'smart-chat-bot' ),
					'deactivateConfirm' => __( 'Deactivate your PRO license? Premium features will be locked again.', 'smart-chat-bot' ),
				),
			)
		);
	}

	/**
	 * Feature comparison rows.
	 *
	 * @return array
	 */
	private function get_features() {
		return array(
			array(
				'feature' => __( 'Floating chat widget', 'smart-chat-bot' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Keyword response rules', 'smart-chat-bot' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Custom colors & position', 'smart-chat-bot' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Fallback message', 'smart-chat-bot' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Live admin inbox', 'smart-chat-bot' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Message history & storage', 'smart-chat-bot' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Real-time admin replies', 'smart-chat-bot' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'OpenAI-powered responses', 'smart-chat-bot' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Custom AI system prompt', 'smart-chat-bot' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Unread message badges', 'smart-chat-bot' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Priority support', 'smart-chat-bot' ),
				'free'    => false,
				'pro'     => true,
			),
		);
	}

	/**
	 * Render Go PRO page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$features     = $this->get_features();
		$pro_url      = scb_get_pro_url();
		$is_licensed  = scb_is_pro_active();
		$pro_installed = scb_is_pro_plugin_installed();
		?>
		<div class="wrap scb-go-pro-wrap">
			<?php if ( $is_licensed ) : ?>
				<div class="scb-license-status scb-license-status-active">
					<div class="scb-license-status-copy">
						<span class="scb-license-status-icon" aria-hidden="true">✓</span>
						<div>
							<strong><?php esc_html_e( 'Status: PRO Active (Promo Lifetime License)', 'smart-chat-bot' ); ?></strong>
							<?php if ( ! $pro_installed ) : ?>
								<p><?php esc_html_e( 'Install and activate Smart Chat Bot PRO to use inbox, storage, and AI features.', 'smart-chat-bot' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<button type="button" class="button scb-license-deactivate" id="scb-deactivate-license">
						<?php esc_html_e( 'Deactivate', 'smart-chat-bot' ); ?>
					</button>
				</div>
			<?php endif; ?>

			<div id="scb-license-notice" class="scb-license-notice" hidden></div>

			<div class="scb-go-pro-hero">
				<span class="scb-go-pro-badge">
					<?php echo $is_licensed ? esc_html__( 'PRO Unlocked', 'smart-chat-bot' ) : esc_html__( 'Upgrade', 'smart-chat-bot' ); ?>
				</span>
				<h1>
					<?php
					echo $is_licensed
						? esc_html__( 'Smart Chat Bot PRO is Active', 'smart-chat-bot' )
						: esc_html__( 'Unlock Smart Chat Bot PRO', 'smart-chat-bot' );
					?>
				</h1>
				<p class="scb-go-pro-subtitle">
					<?php
					echo $is_licensed
						? esc_html__( 'Your promotional license is active. All premium features are unlocked for testing.', 'smart-chat-bot' )
						: esc_html__( 'Supercharge your chat widget with live messaging, conversation history, and AI-powered replies.', 'smart-chat-bot' );
					?>
				</p>
				<?php if ( ! $is_licensed ) : ?>
					<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary button-hero scb-go-pro-cta" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Get Smart Chat Bot PRO →', 'smart-chat-bot' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="scb-go-pro-grid">
				<div class="scb-go-pro-card scb-go-pro-card-free">
					<h2><?php esc_html_e( 'Free', 'smart-chat-bot' ); ?></h2>
					<p class="scb-go-pro-price"><?php esc_html_e( '$0', 'smart-chat-bot' ); ?></p>
					<p class="scb-go-pro-desc"><?php esc_html_e( 'Perfect for simple FAQ bots and getting started.', 'smart-chat-bot' ); ?></p>
				</div>
				<div class="scb-go-pro-card scb-go-pro-card-pro">
					<h2><?php esc_html_e( 'PRO', 'smart-chat-bot' ); ?></h2>
					<p class="scb-go-pro-price"><?php esc_html_e( 'Premium', 'smart-chat-bot' ); ?></p>
					<p class="scb-go-pro-desc"><?php esc_html_e( 'For businesses that need live support and AI automation.', 'smart-chat-bot' ); ?></p>
					<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary scb-go-pro-card-btn" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View Pricing', 'smart-chat-bot' ); ?>
					</a>

					<?php if ( ! $is_licensed ) : ?>
						<div class="scb-license-box">
							<h3><?php esc_html_e( 'Enter License / Promo Key', 'smart-chat-bot' ); ?></h3>
							<p class="scb-license-box-desc">
								<?php esc_html_e( 'Already have a promo key? Activate PRO instantly without leaving WordPress.', 'smart-chat-bot' ); ?>
							</p>
							<form id="scb-license-form" class="scb-license-form" novalidate>
								<label class="screen-reader-text" for="scb-license-key">
									<?php esc_html_e( 'Enter License / Promo Key', 'smart-chat-bot' ); ?>
								</label>
								<input
									type="text"
									id="scb-license-key"
									name="license_key"
									class="scb-license-input"
									placeholder="<?php esc_attr_e( 'FREE-SMART-BOT-2026', 'smart-chat-bot' ); ?>"
									autocomplete="off"
									spellcheck="false"
								/>
								<button type="submit" class="button button-primary scb-license-activate">
									<?php esc_html_e( 'Activate', 'smart-chat-bot' ); ?>
								</button>
							</form>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="scb-go-pro-table-wrap">
				<h2><?php esc_html_e( 'Feature Comparison', 'smart-chat-bot' ); ?></h2>
				<table class="scb-go-pro-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Feature', 'smart-chat-bot' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Free', 'smart-chat-bot' ); ?></th>
							<th scope="col"><?php esc_html_e( 'PRO', 'smart-chat-bot' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $features as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['feature'] ); ?></td>
								<td><?php echo $row['free'] ? '<span class="scb-check" aria-label="' . esc_attr__( 'Included', 'smart-chat-bot' ) . '">✓</span>' : '<span class="scb-dash" aria-hidden="true">—</span>'; ?></td>
								<td><?php echo $row['pro'] ? '<span class="scb-check scb-check-pro" aria-label="' . esc_attr__( 'Included', 'smart-chat-bot' ) . '">✓</span>' : '<span class="scb-dash" aria-hidden="true">—</span>'; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php if ( ! $is_licensed ) : ?>
				<div class="scb-go-pro-footer">
					<h3><?php esc_html_e( 'Ready to upgrade?', 'smart-chat-bot' ); ?></h3>
					<p><?php esc_html_e( 'Install Smart Chat Bot PRO alongside this plugin to instantly unlock all premium features.', 'smart-chat-bot' ); ?></p>
					<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary button-hero" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Get PRO Now', 'smart-chat-bot' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
