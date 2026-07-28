<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nudge_Chat_Widget_Render {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_markup' ) );
	}

	private function is_enabled() {
		$options = Nudge_Chat_Widget_Admin_Settings::get_options();
		return ! empty( $options['whatsapp_number'] );
	}

	public function enqueue_assets() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$options = Nudge_Chat_Widget_Admin_Settings::get_options();

		wp_enqueue_style(
			'nudge-chat-widget',
			NUDGE_CHAT_WIDGET_URL . 'assets/css/nudge-chat-widget.css',
			array(),
			NUDGE_CHAT_WIDGET_VERSION
		);

		$inline_css = sprintf(
			'#nudge-chat-widget{--ncw-navy:%s;--ncw-orange:%s;}',
			esc_html( $options['color_primary'] ),
			esc_html( $options['color_accent'] )
		);
		wp_add_inline_style( 'nudge-chat-widget', $inline_css );

		wp_enqueue_script(
			'nudge-chat-widget',
			NUDGE_CHAT_WIDGET_URL . 'assets/js/nudge-chat-widget.js',
			array(),
			NUDGE_CHAT_WIDGET_VERSION,
			true
		);

		wp_localize_script(
			'nudge-chat-widget',
			'NudgeChatWidgetData',
			array(
				'whatsapp'   => $options['whatsapp_number'],
				'botName'    => $options['bot_name'],
				'statusText' => $options['status_text'],
				'siteLabel'  => wp_parse_url( home_url(), PHP_URL_HOST ),
			)
		);
	}

	public function render_markup() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$options = Nudge_Chat_Widget_Admin_Settings::get_options();
		$bot_name = esc_html( $options['bot_name'] );
		?>
		<div id="nudge-chat-widget">
			<button class="ncw-launcher" id="ncw-open" aria-label="Abrir chat de <?php echo $bot_name; ?>">
				<span class="ncw-launcher-label">¿Hablamos? 🚀</span>
				<span class="ncw-launcher-btn">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.5 8.5 8.5 0 0 1-3.8-.9L3 21l1.9-5.7A8.5 8.5 0 1 1 21 11.5z"/></svg>
				</span>
			</button>
			<div class="ncw-panel" id="ncw-panel" role="dialog" aria-label="Chat con <?php echo $bot_name; ?>" hidden>
				<div class="ncw-head">
					<div class="ncw-avatar">
						<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c3 2 5 5.5 5 10 0 1.6-.4 3-1 4.3l-1.5-1c.3-1 .5-2.1.5-3.3 0-3-1.2-5.6-3-7.4C9.2 6.4 8 9 8 12c0 1.2.2 2.3.5 3.3l-1.5 1C6.4 15 6 13.6 6 12c0-4.5 2-8 6-10zm0 9.5a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6zM9 18l1.5-1.2c.5.2 1 .3 1.5.3s1-.1 1.5-.3L15 18l-.8 3.2L12 19.7 9.8 21.2 9 18z"/></svg>
					</div>
					<div class="ncw-head-txt">
						<strong><?php echo $bot_name; ?></strong>
						<span><?php echo esc_html( $options['status_text'] ); ?></span>
					</div>
					<button class="ncw-close" id="ncw-close" aria-label="Cerrar chat">&times;</button>
				</div>
				<div class="ncw-body" id="ncw-body"></div>
				<div class="ncw-input-area" id="ncw-input"></div>
			</div>
		</div>
		<?php
	}
}
