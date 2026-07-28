<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nudge_Chat_Widget_Admin_Settings {

	const OPTION_KEY = 'nudge_chat_widget_options';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public static function get_options() {
		$defaults = array(
			'whatsapp_number' => '',
			'bot_name'        => 'Nudge',
			'status_text'     => 'Respondemos al instante',
			'color_primary'   => '#0A1F44',
			'color_accent'    => '#FF5C00',
		);

		return wp_parse_args( get_option( self::OPTION_KEY, array() ), $defaults );
	}

	public function add_settings_page() {
		add_options_page(
			'Nudge Chat Widget',
			'Nudge Chat Widget',
			'manage_options',
			'nudge-chat-widget',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting( 'nudge_chat_widget_group', self::OPTION_KEY, array( $this, 'sanitize' ) );
	}

	public function sanitize( $input ) {
		$output = array();

		$output['whatsapp_number'] = isset( $input['whatsapp_number'] )
			? preg_replace( '/[^0-9]/', '', $input['whatsapp_number'] )
			: '';

		$output['bot_name']    = isset( $input['bot_name'] ) ? sanitize_text_field( $input['bot_name'] ) : 'Nudge';
		$output['status_text'] = isset( $input['status_text'] ) ? sanitize_text_field( $input['status_text'] ) : 'Respondemos al instante';

		$output['color_primary'] = isset( $input['color_primary'] ) ? sanitize_hex_color( $input['color_primary'] ) : '#0A1F44';
		$output['color_accent']  = isset( $input['color_accent'] ) ? sanitize_hex_color( $input['color_accent'] ) : '#FF5C00';

		return $output;
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_nudge-chat-widget' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script( 'wp-color-picker', "jQuery(function($){ $('.ncw-color-field').wpColorPicker(); });" );
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = self::get_options();
		?>
		<div class="wrap">
			<h1>Nudge Chat Widget</h1>

			<?php if ( empty( $options['whatsapp_number'] ) ) : ?>
				<div class="notice notice-warning">
					<p>El widget no se muestra en el sitio hasta que cargues un número de WhatsApp.</p>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'nudge_chat_widget_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ncw_whatsapp_number">Número de WhatsApp</label></th>
						<td>
							<input type="text" id="ncw_whatsapp_number" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[whatsapp_number]" value="<?php echo esc_attr( $options['whatsapp_number'] ); ?>" class="regular-text" placeholder="5491122334455" />
							<p class="description">Formato internacional, solo números (código de país + área + línea, sin + ni espacios).</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ncw_bot_name">Nombre del asistente</label></th>
						<td>
							<input type="text" id="ncw_bot_name" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[bot_name]" value="<?php echo esc_attr( $options['bot_name'] ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ncw_status_text">Texto de estado</label></th>
						<td>
							<input type="text" id="ncw_status_text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[status_text]" value="<?php echo esc_attr( $options['status_text'] ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ncw_color_primary">Color primario</label></th>
						<td>
							<input type="text" id="ncw_color_primary" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[color_primary]" value="<?php echo esc_attr( $options['color_primary'] ); ?>" class="ncw-color-field" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ncw_color_accent">Color de acento</label></th>
						<td>
							<input type="text" id="ncw_color_accent" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[color_accent]" value="<?php echo esc_attr( $options['color_accent'] ); ?>" class="ncw-color-field" />
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
