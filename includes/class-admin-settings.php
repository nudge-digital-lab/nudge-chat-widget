<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nudge_Chat_Widget_Admin_Settings {

	const OPTION_KEY = 'nudge_chat_widget_options';

	/**
	 * Estructura fija de los pasos del chat: clave y tipo no son editables
	 * (de eso depende el armado del mensaje final de WhatsApp). Lo que se
	 * puede editar desde el admin es el texto del bot, las opciones (chips)
	 * y el placeholder de los campos de texto.
	 */
	public static function get_step_defs() {
		return array(
			array(
				'key'         => 'nombre',
				'type'        => 'input',
				'label'       => 'Paso 1 — Nombre',
				'bot'         => '¡Hola! 👋 Quiero ayudarte a contactar a un asesor de {bot_name}. ¿Cómo es tu nombre?',
				'placeholder' => 'Tu nombre',
			),
			array(
				'key'     => 'necesidad',
				'type'    => 'chips',
				'label'   => 'Paso 2 — Motivo de la consulta',
				'bot'     => 'Genial, {nombre} ¿en qué te podemos ayudar?',
				'options' => array( 'Cotización', 'Consulta general', 'Soporte', 'Otro' ),
			),
			array(
				'key'     => 'situacion',
				'type'    => 'chips',
				'label'   => 'Paso 3 — Horario de contacto',
				'bot'     => '¿En qué horario te podemos contactar?',
				'options' => array( 'Mañana', 'Tarde', 'Cualquier horario' ),
			),
			array(
				'key'     => 'plazo',
				'type'    => 'chips',
				'label'   => 'Paso 4 — Urgencia',
				'bot'     => '¿Con qué urgencia necesitás una respuesta?',
				'options' => array( 'Lo antes posible', 'Hoy', 'Esta semana', 'Sin apuro' ),
			),
			array(
				'key'         => 'contacto',
				'type'        => 'input',
				'label'       => 'Paso 5 — Contacto',
				'bot'         => 'Perfecto. Dejanos tu WhatsApp o email así un asesor se contacta con vos 👇',
				'placeholder' => 'WhatsApp o email',
			),
			array(
				'key'         => 'extra',
				'type'        => 'input',
				'label'       => 'Paso 6 — Comentario (opcional)',
				'bot'         => '¿Querés contarnos algo más? (opcional)',
				'placeholder' => 'Escribí o tocá enviar',
				'optional'    => true,
			),
		);
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public static function get_options() {
		$step_defs = self::get_step_defs();
		$default_flow = array();
		foreach ( $step_defs as $i => $def ) {
			$default_flow[ $i ] = array(
				'bot'         => $def['bot'],
				'options'     => isset( $def['options'] ) ? implode( '|', $def['options'] ) : '',
				'placeholder' => isset( $def['placeholder'] ) ? $def['placeholder'] : '',
			);
		}

		$defaults = array(
			'whatsapp_number' => '',
			'bot_name'        => 'Nudge',
			'status_text'     => 'Respondemos al instante',
			'color_primary'   => '#0B1220',
			'color_accent'    => '#C9A227',
			'avatar_id'       => 0,
			'final_message'   => '¡Listo, {nombre}! Tocá el botón para enviarnos tu consulta — solemos responder en pocos minutos ⏱️',
			'flow'            => $default_flow,
		);

		$stored = get_option( self::OPTION_KEY, array() );
		$merged = wp_parse_args( $stored, $defaults );

		// Completa pasos faltantes si se agregó algún paso nuevo en una versión futura.
		$merged['flow'] = isset( $merged['flow'] ) && is_array( $merged['flow'] )
			? array_replace( $default_flow, $merged['flow'] )
			: $default_flow;

		return $merged;
	}

	/**
	 * Devuelve el flow final, combinando la estructura fija (key/type) con
	 * el contenido editable guardado, listo para pasarle al JS del widget.
	 */
	public static function get_resolved_flow() {
		$options   = self::get_options();
		$step_defs = self::get_step_defs();
		$resolved  = array();

		foreach ( $step_defs as $i => $def ) {
			$saved = isset( $options['flow'][ $i ] ) ? $options['flow'][ $i ] : array();

			$step = array(
				'key'  => $def['key'],
				'type' => $def['type'],
				'bot'  => isset( $saved['bot'] ) && '' !== $saved['bot'] ? $saved['bot'] : $def['bot'],
			);

			if ( 'chips' === $def['type'] ) {
				$options_str = isset( $saved['options'] ) && '' !== $saved['options'] ? $saved['options'] : implode( '|', $def['options'] );
				$step['options'] = array_values( array_filter( array_map( 'trim', explode( '|', $options_str ) ) ) );
			} else {
				$step['placeholder'] = isset( $saved['placeholder'] ) && '' !== $saved['placeholder'] ? $saved['placeholder'] : $def['placeholder'];
				if ( ! empty( $def['optional'] ) ) {
					$step['optional'] = true;
				}
			}

			$resolved[] = $step;
		}

		return $resolved;
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

		$output['color_primary'] = isset( $input['color_primary'] ) ? sanitize_hex_color( $input['color_primary'] ) : '#0B1220';
		$output['color_accent']  = isset( $input['color_accent'] ) ? sanitize_hex_color( $input['color_accent'] ) : '#C9A227';
		$output['avatar_id']     = isset( $input['avatar_id'] ) ? absint( $input['avatar_id'] ) : 0;

		$output['final_message'] = isset( $input['final_message'] ) ? sanitize_textarea_field( $input['final_message'] ) : '';

		$step_defs = self::get_step_defs();
		$flow      = array();
		foreach ( $step_defs as $i => $def ) {
			$posted = isset( $input['flow'][ $i ] ) ? $input['flow'][ $i ] : array();

			$options_str = '';
			if ( 'chips' === $def['type'] && isset( $posted['options'] ) ) {
				$opts        = array_filter( array_map( 'sanitize_text_field', explode( '|', $posted['options'] ) ) );
				$options_str = implode( '|', $opts );
			}

			$flow[ $i ] = array(
				'bot'         => isset( $posted['bot'] ) ? sanitize_textarea_field( $posted['bot'] ) : '',
				'options'     => $options_str,
				'placeholder' => isset( $posted['placeholder'] ) ? sanitize_text_field( $posted['placeholder'] ) : '',
			);
		}
		$output['flow'] = $flow;

		return $output;
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_nudge-chat-widget' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();

		$inline_js = <<<JS
jQuery(function(\$){
	\$('.ncw-color-field').wpColorPicker();

	var frame;
	\$('#ncw_avatar_choose').on('click', function(e){
		e.preventDefault();
		if (frame) { frame.open(); return; }
		frame = wp.media({
			title: 'Elegir avatar del asistente',
			button: { text: 'Usar esta imagen' },
			multiple: false,
			library: { type: 'image' }
		});
		frame.on('select', function(){
			var att = frame.state().get('selection').first().toJSON();
			var thumb = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
			\$('#ncw_avatar_id').val(att.id);
			\$('#ncw_avatar_preview').attr('src', thumb).show();
			\$('#ncw_avatar_remove').show();
		});
		frame.open();
	});

	\$('#ncw_avatar_remove').on('click', function(e){
		e.preventDefault();
		\$('#ncw_avatar_id').val('');
		\$('#ncw_avatar_preview').hide().attr('src', '');
		\$(this).hide();
	});
});
JS;
		wp_add_inline_script( 'wp-color-picker', $inline_js );
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options   = self::get_options();
		$step_defs = self::get_step_defs();
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
							<p class="description">Podés usar <code>{bot_name}</code> dentro de los mensajes del chat para insertar este nombre.</p>
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
					<tr>
						<th scope="row"><label for="ncw_avatar_choose">Avatar del asistente</label></th>
						<td>
							<?php
							$avatar_id  = ! empty( $options['avatar_id'] ) ? absint( $options['avatar_id'] ) : 0;
							$avatar_src = $avatar_id ? wp_get_attachment_image_url( $avatar_id, 'thumbnail' ) : '';
							?>
							<img id="ncw_avatar_preview" src="<?php echo esc_url( $avatar_src ); ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-right:10px;<?php echo $avatar_src ? '' : 'display:none;'; ?>" alt="" />
							<input type="hidden" id="ncw_avatar_id" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[avatar_id]" value="<?php echo esc_attr( $avatar_id ); ?>" />
							<button type="button" class="button" id="ncw_avatar_choose">Elegir imagen</button>
							<button type="button" class="button" id="ncw_avatar_remove" style="<?php echo $avatar_src ? '' : 'display:none;'; ?>">Quitar</button>
							<p class="description">Opcional. Si no cargás una imagen, se usa el ícono de chat por defecto.</p>
						</td>
					</tr>
				</table>

				<h2>Preguntas del chat</h2>
				<p class="description">Editá el texto que dice el asistente en cada paso. Podés usar <code>{bot_name}</code> y <code>{nombre}</code> (el nombre que escribió el visitante en el paso 1) como variables. En los pasos de opciones, separá cada opción con <code>|</code>.</p>

				<table class="form-table" role="presentation">
					<?php foreach ( $step_defs as $i => $def ) :
						$saved = isset( $options['flow'][ $i ] ) ? $options['flow'][ $i ] : array();
						$bot_value = isset( $saved['bot'] ) && '' !== $saved['bot'] ? $saved['bot'] : $def['bot'];
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $def['label'] ); ?></th>
							<td>
								<p>
									<label for="ncw_flow_<?php echo esc_attr( $i ); ?>_bot">Mensaje del asistente</label><br />
									<textarea id="ncw_flow_<?php echo esc_attr( $i ); ?>_bot" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[flow][<?php echo esc_attr( $i ); ?>][bot]" rows="2" class="large-text"><?php echo esc_textarea( $bot_value ); ?></textarea>
								</p>
								<?php if ( 'chips' === $def['type'] ) :
									$options_value = isset( $saved['options'] ) && '' !== $saved['options'] ? $saved['options'] : implode( '|', $def['options'] );
									?>
									<p>
										<label for="ncw_flow_<?php echo esc_attr( $i ); ?>_options">Opciones (separadas por <code>|</code>)</label><br />
										<input type="text" id="ncw_flow_<?php echo esc_attr( $i ); ?>_options" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[flow][<?php echo esc_attr( $i ); ?>][options]" value="<?php echo esc_attr( $options_value ); ?>" class="large-text" />
									</p>
								<?php else :
									$placeholder_value = isset( $saved['placeholder'] ) && '' !== $saved['placeholder'] ? $saved['placeholder'] : $def['placeholder'];
									?>
									<p>
										<label for="ncw_flow_<?php echo esc_attr( $i ); ?>_placeholder">Placeholder del campo</label><br />
										<input type="text" id="ncw_flow_<?php echo esc_attr( $i ); ?>_placeholder" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[flow][<?php echo esc_attr( $i ); ?>][placeholder]" value="<?php echo esc_attr( $placeholder_value ); ?>" class="regular-text" />
									</p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th scope="row"><label for="ncw_final_message">Mensaje final</label></th>
						<td>
							<textarea id="ncw_final_message" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[final_message]" rows="2" class="large-text"><?php echo esc_textarea( $options['final_message'] ); ?></textarea>
							<p class="description">Se muestra en el chat justo antes del botón de enviar por WhatsApp.</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
