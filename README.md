# Chatbot (chatbotv2) — guía rápida y estructura

Este repositorio contiene la parte web (PHP) que gestiona un dashboard para
editar intents/respuestas y sincronizarlos con Rasa, además de helpers para
generar el YAML de entrenamiento y disparar el reentrenado y la recarga del
modelo en Rasa.

Resumen rápido: la web genera en memoria el YAML combinado (NLU, domain,
rules, stories) a partir de la base de datos y envía ese contenido a Rasa
(`/model/train`). Si Rasa devuelve el archivo `.tar.gz`, la web lo guarda
en un directorio local y hace un `PUT /model` para recargarlo en caliente.

## Estructura del proyecto (resumen)

```
chatbotv2/
├── actions/                 # (python) server de actions (si aplica)
├── assets/                  # css, js públicos
├── controller/              # controladores PHP (DashboardController, ChatController...)
│   └── helpers/
│       └── RasaGenerator.php # genera YAML desde DB y llama a Rasa
├── data/                    # archivos YAML generados / estáticos (nlu, rules, stories)
├── domain/                  # domain/base.yml + dashboard.yml generados
├── model/                   # clases PHP de modelo (Intent, Respuesta, Usuario...)
├── models/                  # modelos Rasa (.tar.gz) resultantes tras training
├── scripts/                 # utilidades (run_train.php, fix_sessions.php, etc.)
├── view/                    # vistas PHP (dashboard, chat, partials)
├── config.yml               # pipeline + policies (Rasa)
├── endpoints.yml            # endpoints de Rasa (action server)
├── index.php                # bootstrap/front controller PHP
├── README.md
└── requirements.txt         # (python) dependencias para scripts de sync/train
```

Notas importantes:
- El dominio está dividido en `domain/` para que el dashboard pueda regenerar
  su parte (`dashboard.yml`) sin sobrescribir lo definido a mano en `base.yml`.
- El archivo `config.yml` contiene la configuración de `pipeline` y
  `policies` usada para entrenar; el código del dashboard puede (y debe
  en el futuro) leerlo dinámicamente.

## Flujo de reentrenado desde el Dashboard (server-side)

- El endpoint PHP `index.php?controller=dashboard&action=retrain` llama a
  `controller/helpers/RasaGenerator.php::trainAndLoadModel()`.
- `RasaGenerator` genera en memoria un YAML combinado y lo POSTea a
  `http://localhost:5005/model/train` con `Content-Type: text/yaml`.
- Si Rasa devuelve JSON con la ruta del modelo, se hace `PUT /model` con
  `{"model_file": "<ruta>"}`. Si devuelve el `.tar.gz` binario, el
  generador lo guarda localmente (en `models/` o en `/tmp`) y hace `PUT` con
  la ruta al archivo guardado.

Comando rápido para probar desde la línea de comandos (en la máquina donde
está corriendo Apache/PHP):

```bash
curl -i -X POST "http://localhost/chatbotv2/index.php?controller=dashboard&action=retrain"
```

El frontend (`view/dashboard/conocimiento.js`) ha sido endurecido para manejar
respuestas vacías/no-JSON y mostrará mensajes de error útiles si algo falla.

## Cómo levantar Rasa / acciones (resumen)

1. Crear y activar entorno virtual Python
Este proyecto utiliza Python 3.10, por lo que hay que conseguir dicha version vieja.
Para ello vamos a utilizar uv:
```bash
# Descargamos uv
curl -LsSf https://astral.sh/uv/install.sh | sh

# Creamos un entorno virtual con python 3.10
uv venv --python 3.10

# Instalamos Rasa
source venv/bin/activate
pip install rasa
```

2. Entrenar Rasa (si trabajás localmente con los YAML en `domain/` y `data/`):

```bash
rasa train --domain domain/
```

3. Levantar action server (si usás acciones personalizadas):

```bash
rasa run actions
```

4. Levantar Rasa API para que se registre correctamente:

```bash
rasa run --enable-api --cors "*" --domain domain/
```

## Problemas y recomendaciones

- Si ves `Encountered empty domain during validation`, asegurate de pasar
  `--domain domain/` al comando `rasa` porque el repositorio tiene el dominio
  dividido en varios ficheros.
- Si la web devuelve un 500 sin cuerpo, revisá los logs de Apache y el
  `RasaGenerator` (ahora devuelve objetos JSON con `phase` y `error` para
  facilitar el debugging).
- Para producción: no uses `--cors "*"`; configura rutas seguras y ajusta
  permisos del directorio `models/` si querés que la app web guarde modelos
  entrenados ahí.

EPET N°3 7°I

## Funcionalidad de voz del chatbot

La interfaz del chat incorpora entrada y salida de voz como alternativas accesibles al uso tradicional del teclado. La respuesta escrita siempre permanece visible y el envío de consultas continúa utilizando el formulario existente.

### Tecnologías utilizadas

- **Reconocimiento de voz a texto:** `SpeechRecognition`, con `webkitSpeechRecognition` como compatibilidad para navegadores que exponen la API con prefijo. Es una API nativa de la **Web Speech API**: [documentación de MDN sobre SpeechRecognition](https://developer.mozilla.org/en-US/docs/Web/API/SpeechRecognition).
- **Conversión de texto a voz:** `SpeechSynthesis` y `SpeechSynthesisUtterance`, también parte de la **Web Speech API**: [documentación de MDN sobre SpeechSynthesis](https://developer.mozilla.org/en-US/docs/Web/API/SpeechSynthesis).

No se utilizó una librería externa. Se eligieron APIs propias del navegador porque la aplicación ya funciona con PHP y JavaScript del lado del cliente, no utiliza un bundler frontend y estas APIs permiten incorporar la mejora sin agregar dependencias ni modificar el contrato del backend. El texto dictado se envía al mismo controlador que una consulta escrita.

### Funcionamiento

- El botón **Micrófono** solicita permiso al navegador al iniciar el reconocimiento. El texto reconocido se coloca en el campo de consulta, donde puede revisarse o corregirse antes de enviarlo con el botón habitual o con Enter.
- Mientras el reconocimiento está activo, el botón cambia a **Detener micrófono**, queda marcado con `aria-pressed="true"` y se muestra el estado “Escuchando”.
- Cada respuesta de A.U.R.A conserva un botón circular con el icono de volumen. La lectura comienza al pulsarlo y el mismo botón la detiene mientras está activa; iniciar otra respuesta cancela la lectura anterior.
- Si el navegador no ofrece reconocimiento de voz o el permiso es rechazado, se informa el motivo y el chat continúa funcionando mediante teclado. Si no existe síntesis de voz, la respuesta escrita sigue disponible.

### Compatibilidad y accesibilidad

El soporte de `SpeechRecognition` no es uniforme entre navegadores y suele estar disponible con mayor frecuencia en Chrome y navegadores basados en Chromium; puede requerir `webkitSpeechRecognition`. Además, el micrófono normalmente necesita permiso explícito y un contexto seguro como HTTPS o `localhost`. `SpeechSynthesis` tiene una disponibilidad más amplia, aunque las voces instaladas dependen del sistema operativo y del navegador.

La funcionalidad degrada de forma progresiva: la ausencia de reconocimiento, un error del servicio o el rechazo del permiso no bloquean el ingreso manual ni el envío tradicional. Los controles tienen texto visible, etiquetas ARIA, estados anunciados mediante `role="status"` y foco de teclado. Esto facilita la interacción a personas con dificultades motoras, cansancio visual o necesidad de usar la voz para introducir texto, sin retirar la alternativa escrita ni ocultar las respuestas a lectores de pantalla.
