=== Nudge Chat Widget ===
Contributors: jorgeclerici
Tags: whatsapp, chat, leads, chatbot
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Widget de chat flotante que califica leads con un flujo corto de preguntas y los deriva a WhatsApp con todo el contexto.

== Description ==

Nudge Chat Widget agrega un botón de chat flotante a tu sitio. El visitante responde unas pocas preguntas guiadas (nombre, qué necesita, situación actual, plazo, contacto) y al final se genera un link a WhatsApp con el resumen completo, listo para enviar.

* Sin dependencias externas ni llamadas a servicios de terceros.
* Número de WhatsApp, nombre del asistente, texto de estado y colores configurables desde el admin.
* El flujo de preguntas se puede editar directamente en el JS del plugin.

== Installation ==

1. Subí la carpeta `nudge-chat-widget` a `/wp-content/plugins/`.
2. Activá el plugin desde el menú Plugins de WordPress.
3. Configurá tu número de WhatsApp en Ajustes → Nudge Chat Widget.

== Changelog ==

= 1.2.1 =
* Preguntas y opciones por defecto del chat generalizadas para cualquier tipo de negocio (antes estaban orientadas a e-commerce/Tiendanube).

= 1.2.0 =
* Nuevo: avatar personalizado del asistente (Ajustes → Nudge Chat Widget), usando la librería de medios de WordPress.
* Nuevos hooks de extensión (`nudge_chat_widget_after_enqueue`, `nudge_chat_widget_after_markup`, evento JS `nudgeChatWidgetLead`) para que otros plugins puedan integrarse.

= 1.1.1 =
* Paleta de colores por defecto actualizada a la identidad de marca (navy/negro + dorado).

= 1.1.0 =
* Las preguntas del chat (mensajes, opciones y placeholders) ahora se editan desde Ajustes → Nudge Chat Widget, sin tocar código.

= 1.0.0 =
* Versión inicial.
