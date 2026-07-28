# Nudge Chat Widget

Plugin de WordPress: widget de chat flotante que califica leads con un flujo corto de preguntas y arma un mensaje de WhatsApp con todo el contexto, listo para enviar.

## Instalación

1. Copiá la carpeta `nudge-chat-widget` a `wp-content/plugins/` de tu instalación de WordPress.
2. Activá el plugin desde **Plugins** en el admin de WordPress.
3. Andá a **Ajustes → Nudge Chat Widget** y cargá tu número de WhatsApp (formato internacional, solo números, sin `+` ni espacios). El widget no se muestra en el sitio hasta que este campo esté completo.
4. Opcionalmente, personalizá el nombre del asistente, el texto de estado y los colores primario/acento.

## Cómo funciona

- El widget se inyecta en el `wp_footer` de cualquier página del sitio.
- Hace una serie de preguntas cortas (nombre, necesidad, situación actual, plazo, contacto) y arma un resumen.
- Al finalizar, el visitante toca un botón que abre WhatsApp (`wa.me`) con el mensaje pre-cargado, incluyendo el dominio del sitio como referencia de origen.
- No guarda datos ni hace requests a servidores externos — todo corre en el navegador del visitante hasta que decide enviar el mensaje por WhatsApp.

## Personalizar el flujo de preguntas

Desde **Ajustes → Nudge Chat Widget** se puede editar, por cada uno de los 6 pasos del chat: el mensaje del bot, las opciones (para los pasos de tipo chips, separadas por `|`) y el placeholder (para los pasos de tipo input). No hace falta tocar código.

Variables disponibles dentro de los mensajes: `{bot_name}` (nombre del asistente) y `{nombre}` (lo que escribió el visitante en el paso 1).

La clave y el tipo de cada paso (`nombre`, `necesidad`, `situacion`, `plazo`, `contacto`, `extra`) son fijos — de eso depende el armado del mensaje final de WhatsApp — y están definidos en `Nudge_Chat_Widget_Admin_Settings::get_step_defs()` dentro de `includes/class-admin-settings.php`.

## Estructura

```
nudge-chat-widget/
├── nudge-chat-widget.php          # Bootstrap del plugin
├── includes/
│   ├── class-admin-settings.php   # Página de ajustes (Settings API)
│   └── class-widget-render.php    # Enqueue de assets + markup del widget
├── assets/
│   ├── css/nudge-chat-widget.css
│   └── js/nudge-chat-widget.js
└── readme.txt                     # Readme estándar de WordPress.org
```
