<?php
/**
 * Go PRO / pricing admin page for the free plugin.
 *
 * @package Siddik_Chat_Widget
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MDSCW_Admin_Go_Pro
 */
class MDSCW_Admin_Go_Pro {

	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'siddik-chat-widget-go-pro';

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
		if ( mdscw_is_pro_active() ) {
			return;
		}

		add_submenu_page(
			MDSCW_Admin_Settings::MENU_SLUG,
			__( 'Go PRO', 'siddik-chat-widget' ),
			__( 'Go PRO', 'siddik-chat-widget' ),
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
		if ( 'siddik-chat-widget_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'mdscw-go-pro',
			MDSCW_PLUGIN_URL . 'admin/css/go-pro.css',
			array(),
			MDSCW_VERSION
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
				'feature' => __( 'Floating chat widget', 'siddik-chat-widget' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Keyword response rules', 'siddik-chat-widget' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Omnichannel routing (WhatsApp, Messenger, Telegram)', 'siddik-chat-widget' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Custom colors & position', 'siddik-chat-widget' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Fallback message', 'siddik-chat-widget' ),
				'free'    => true,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Live admin inbox', 'siddik-chat-widget' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Message history & storage', 'siddik-chat-widget' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Real-time admin replies', 'siddik-chat-widget' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'OpenAI-powered responses', 'siddik-chat-widget' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Custom AI system prompt', 'siddik-chat-widget' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Unread message badges', 'siddik-chat-widget' ),
				'free'    => false,
				'pro'     => true,
			),
			array(
				'feature' => __( 'Priority support', 'siddik-chat-widget' ),
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
		$early_access = mdscw_is_pro_early_access();
		$pro_url      = $early_access ? mdscw_get_pro_download_url() : mdscw_get_pro_url();
		?>
		<div class="wrap mdscw-go-pro-wrap">
			<div class="mdscw-go-pro-hero">
				<span class="mdscw-go-pro-badge">
					<?php echo $early_access ? esc_html__( 'Early Access', 'siddik-chat-widget' ) : esc_html__( 'Upgrade', 'siddik-chat-widget' ); ?>
				</span>
				<h1>
					<?php
					echo $early_access
						? esc_html__( 'Get Siddik Chat Widget PRO — Free Early Access', 'siddik-chat-widget' )
						: esc_html__( 'Unlock Siddik Chat Widget PRO', 'siddik-chat-widget' );
					?>
				</h1>
				<p class="mdscw-go-pro-subtitle">
					<?php
					echo $early_access
						? esc_html__( 'Download the PRO add-on free while we grow. Install it alongside this plugin for live inbox, message history, and AI replies.', 'siddik-chat-widget' )
						: esc_html__( 'Supercharge your chat widget with live messaging, conversation history, and AI-powered replies.', 'siddik-chat-widget' );
					?>
				</p>
				<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary button-hero mdscw-go-pro-cta" target="_blank" rel="noopener noreferrer">
					<?php
					echo $early_access
						? esc_html__( 'Download PRO Free →', 'siddik-chat-widget' )
						: esc_html__( 'Get Siddik Chat Widget PRO →', 'siddik-chat-widget' );
					?>
				</a>
				<?php if ( $early_access ) : ?>
					<p class="mdscw-go-pro-fine-print">
						<?php esc_html_e( 'Early adopters keep PRO access when paid pricing launches.', 'siddik-chat-widget' ); ?>
					</p>
				<?php endif; ?>
			</div>

			<div class="mdscw-go-pro-grid">
				<div class="mdscw-go-pro-card mdscw-go-pro-card-free">
					<h2><?php esc_html_e( 'Free', 'siddik-chat-widget' ); ?></h2>
					<p class="mdscw-go-pro-price"><?php esc_html_e( '$0', 'siddik-chat-widget' ); ?></p>
					<p class="mdscw-go-pro-desc"><?php esc_html_e( 'Perfect for simple FAQ bots and getting started.', 'siddik-chat-widget' ); ?></p>
				</div>
				<div class="mdscw-go-pro-card mdscw-go-pro-card-pro">
					<h2><?php esc_html_e( 'PRO', 'siddik-chat-widget' ); ?></h2>
					<p class="mdscw-go-pro-price">
						<?php
						echo $early_access
							? esc_html__( 'Free', 'siddik-chat-widget' )
							: esc_html__( 'Premium', 'siddik-chat-widget' );
						?>
					</p>
					<p class="mdscw-go-pro-desc">
						<?php
						echo $early_access
							? esc_html__( 'Full PRO features at no cost during early access. Limited-time offer.', 'siddik-chat-widget' )
							: esc_html__( 'For businesses that need live support and AI automation.', 'siddik-chat-widget' );
						?>
					</p>
					<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary mdscw-go-pro-card-btn" target="_blank" rel="noopener noreferrer">
						<?php
						echo $early_access
							? esc_html__( 'Download PRO Free', 'siddik-chat-widget' )
							: esc_html__( 'View Pricing', 'siddik-chat-widget' );
						?>
					</a>
				</div>
			</div>

			<div class="mdscw-go-pro-table-wrap">
				<h2><?php esc_html_e( 'Feature Comparison', 'siddik-chat-widget' ); ?></h2>
				<table class="mdscw-go-pro-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Feature', 'siddik-chat-widget' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Free', 'siddik-chat-widget' ); ?></th>
							<th scope="col"><?php esc_html_e( 'PRO', 'siddik-chat-widget' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $features as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['feature'] ); ?></td>
								<td><?php echo $row['free'] ? '<span class="mdscw-check" aria-label="' . esc_attr__( 'Included', 'siddik-chat-widget' ) . '">✓</span>' : '<span class="mdscw-dash" aria-hidden="true">—</span>'; ?></td>
								<td><?php echo $row['pro'] ? '<span class="mdscw-check mdscw-check-pro" aria-label="' . esc_attr__( 'Included', 'siddik-chat-widget' ) . '">✓</span>' : '<span class="mdscw-dash" aria-hidden="true">—</span>'; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="mdscw-go-pro-footer">
				<h3>
					<?php
					echo $early_access
						? esc_html__( 'Ready to unlock PRO?', 'siddik-chat-widget' )
						: esc_html__( 'Ready to upgrade?', 'siddik-chat-widget' );
					?>
				</h3>
				<p>
					<?php
					echo $early_access
						? esc_html__( 'Download and install Siddik Chat Widget PRO alongside this plugin to instantly unlock all premium features.', 'siddik-chat-widget' )
						: esc_html__( 'Install Siddik Chat Widget PRO alongside this plugin to instantly unlock all premium features.', 'siddik-chat-widget' );
					?>
				</p>
				<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary button-hero" target="_blank" rel="noopener noreferrer">
					<?php
					echo $early_access
						? esc_html__( 'Download PRO Free', 'siddik-chat-widget' )
						: esc_html__( 'Get PRO Now', 'siddik-chat-widget' );
					?>
				</a>
			</div>
		</div>
		<?php
	}
}
