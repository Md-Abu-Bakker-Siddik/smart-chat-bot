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
	 * Register Go PRO submenu when Pro is not active.
	 */
	public function register_menu() {
		if ( scb_is_pro_active() ) {
			return;
		}

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
				'feature' => __( 'Omnichannel routing (WhatsApp, Messenger, Telegram)', 'smart-chat-bot' ),
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
		$early_access = scb_is_pro_early_access();
		$pro_url      = $early_access ? scb_get_pro_download_url() : scb_get_pro_url();
		?>
		<div class="wrap scb-go-pro-wrap">
			<div class="scb-go-pro-hero">
				<span class="scb-go-pro-badge">
					<?php echo $early_access ? esc_html__( 'Early Access', 'smart-chat-bot' ) : esc_html__( 'Upgrade', 'smart-chat-bot' ); ?>
				</span>
				<h1>
					<?php
					echo $early_access
						? esc_html__( 'Get Smart Chat Bot PRO — Free Early Access', 'smart-chat-bot' )
						: esc_html__( 'Unlock Smart Chat Bot PRO', 'smart-chat-bot' );
					?>
				</h1>
				<p class="scb-go-pro-subtitle">
					<?php
					echo $early_access
						? esc_html__( 'Download the PRO add-on free while we grow. Install it alongside this plugin for live inbox, message history, and AI replies.', 'smart-chat-bot' )
						: esc_html__( 'Supercharge your chat widget with live messaging, conversation history, and AI-powered replies.', 'smart-chat-bot' );
					?>
				</p>
				<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary button-hero scb-go-pro-cta" target="_blank" rel="noopener noreferrer">
					<?php
					echo $early_access
						? esc_html__( 'Download PRO Free →', 'smart-chat-bot' )
						: esc_html__( 'Get Smart Chat Bot PRO →', 'smart-chat-bot' );
					?>
				</a>
				<?php if ( $early_access ) : ?>
					<p class="scb-go-pro-fine-print">
						<?php esc_html_e( 'Early adopters keep PRO access when paid pricing launches.', 'smart-chat-bot' ); ?>
					</p>
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
					<p class="scb-go-pro-price">
						<?php
						echo $early_access
							? esc_html__( 'Free', 'smart-chat-bot' )
							: esc_html__( 'Premium', 'smart-chat-bot' );
						?>
					</p>
					<p class="scb-go-pro-desc">
						<?php
						echo $early_access
							? esc_html__( 'Full PRO features at no cost during early access. Limited-time offer.', 'smart-chat-bot' )
							: esc_html__( 'For businesses that need live support and AI automation.', 'smart-chat-bot' );
						?>
					</p>
					<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary scb-go-pro-card-btn" target="_blank" rel="noopener noreferrer">
						<?php
						echo $early_access
							? esc_html__( 'Download PRO Free', 'smart-chat-bot' )
							: esc_html__( 'View Pricing', 'smart-chat-bot' );
						?>
					</a>
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

			<div class="scb-go-pro-footer">
				<h3>
					<?php
					echo $early_access
						? esc_html__( 'Ready to unlock PRO?', 'smart-chat-bot' )
						: esc_html__( 'Ready to upgrade?', 'smart-chat-bot' );
					?>
				</h3>
				<p>
					<?php
					echo $early_access
						? esc_html__( 'Download and install Smart Chat Bot PRO alongside this plugin to instantly unlock all premium features.', 'smart-chat-bot' )
						: esc_html__( 'Install Smart Chat Bot PRO alongside this plugin to instantly unlock all premium features.', 'smart-chat-bot' );
					?>
				</p>
				<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary button-hero" target="_blank" rel="noopener noreferrer">
					<?php
					echo $early_access
						? esc_html__( 'Download PRO Free', 'smart-chat-bot' )
						: esc_html__( 'Get PRO Now', 'smart-chat-bot' );
					?>
				</a>
			</div>
		</div>
		<?php
	}
}
