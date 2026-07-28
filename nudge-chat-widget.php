<?php
/**
 * Plugin Name: Nudge Chat Widget
 * Plugin URI: https://github.com/nudge-digital-lab/nudge-chat-widget
 * Description: Widget de chat flotante que califica leads con un flujo de preguntas cortas y los deriva a WhatsApp con todo el contexto.
 * Version: 1.0.0
 * Author: Nudge
 * Author URI: https://github.com/nudge-digital-lab
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nudge-chat-widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NUDGE_CHAT_WIDGET_VERSION', '1.0.0' );
define( 'NUDGE_CHAT_WIDGET_PATH', plugin_dir_path( __FILE__ ) );
define( 'NUDGE_CHAT_WIDGET_URL', plugin_dir_url( __FILE__ ) );

require_once NUDGE_CHAT_WIDGET_PATH . 'includes/class-admin-settings.php';
require_once NUDGE_CHAT_WIDGET_PATH . 'includes/class-widget-render.php';

/**
 * Boot the plugin once all plugins are loaded.
 */
function nudge_chat_widget_init() {
	new Nudge_Chat_Widget_Admin_Settings();
	new Nudge_Chat_Widget_Render();
}
add_action( 'plugins_loaded', 'nudge_chat_widget_init' );
