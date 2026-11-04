# Chatbot (chatbotv2) — guía rápida y estructura

Este repositorio contiene la parte web (PHP) que gestiona el conocimiento del chatbot para
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
├── controller/              # controladores PHP (ChatController y administración...)
│   └── helpers/
│       └── RasaGenerator.php # genera YAML desde DB y llama a Rasa
├── data/                    # archivos YAML generados / estáticos (nlu, rules, stories)
├── domain/                  # domain/base.yml + generated.yml generados
├── model/                   # clases PHP de modelo (Intent, Respuesta, Usuario...)
├── models/                  # modelos Rasa (.tar.gz) resultantes tras training
├── scripts/                 # utilidades (run_train.php, fix_sessions.php, etc.)
├── view/                    # vistas PHP (chat, administración y partials)
├── config.yml               # pipeline + policies (Rasa)
├── endpoints.yml            # endpoints de Rasa (action server)
├── index.php                # bootstrap/front controller PHP
├── README.md
└── requirements.txt         # (python) dependencias para scripts de sync/train
```

## Configuración para mover el proyecto

La configuración de conexiones está centralizada en `.env`. Copiá `.env.example`
a `.env` y editá ese archivo para cada instalación:

```bash
cp .env.example .env
```

En Windows también podés copiarlo desde el Explorador de archivos. `.env` está
ignorado por Git para que las contraseñas no se suban al repositorio. La
aplicación PHP lo carga automáticamente; las variables que ya defina el entorno
(por ejemplo, Docker Compose) tienen prioridad.

Configurá `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` con los datos
de la base de datos de esa variante. Si PHP y MySQL están en contenedores de la
misma red de Docker, `DB_HOST` debe ser el nombre del servicio/contenedor MySQL,
no `localhost`. Importá `chatbot_v2.sql` en esa base. Cada variante puede tener
su propio `.env` con base y usuario de base de datos diferentes.

`RASA_URL` es la URL base que usa PHP para conversar y solicitar entrenamiento.
Desde un contenedor, usá el nombre del servicio Rasa accesible en la red Docker.
Si cada variante requiere un modelo diferente, apuntá cada una a su propia
instancia Rasa: una instancia compartida tiene un único modelo activo y el
entrenamiento de una variante puede reemplazar el modelo de las demás.

`OLLAMA_URL` y `OLLAMA_MODEL` configuran la conexión de las acciones
personalizadas. El contenedor del servidor de acciones también debe recibir
estas variables de entorno. Para que Rasa alcance ese servidor dentro de
Docker, usá la dirección de red del servicio de acciones
(`http://action-server:5055/webhook`). `endpoints.docker.yml` ya está preparada
para Compose; en una instalación local, `endpoints.yml` conserva
`http://localhost:5055/webhook`. `localhost` dentro de un contenedor solo se
refiere a ese mismo contenedor.

Docker Compose lee `.env` para interpolar la configuración y entrega a cada
contenedor solo las variables que necesita. No publiques los puertos de MySQL o
Rasa a Internet. Para probar la conexión PHP/MySQL, ejecutá `php test_db.php`
desde el entorno donde corre PHP.

Notas importantes:
- El dominio está dividido en `domain/` para que el generador pueda regenerar
  su parte (`generated.yml`) sin sobrescribir lo definido a mano en `base.yml`.
- El archivo `config.yml` contiene la configuración de `pipeline` y
  `policies` usada para entrenar; el generador puede (y debe
  en el futuro) leerlo dinámicamente.

## Despliegue completo con Docker Compose

El stack de prueba incluye la aplicación PHP/Apache, MariaDB, Rasa, el servidor
de acciones y Ollama. Docker Compose conecta los servicios en una red privada:
PHP usa `mysql` y `rasa` como hosts; Rasa usa `action-server`; y las acciones
usan `ollama`. Solo se publica el puerto HTTP de la aplicación.

### Requisitos

- Docker Engine con el complemento **Docker Compose v2** (`docker compose`).
- Para usar Ollama, suficiente espacio en disco para descargar la imagen y el
  modelo configurado. El modelo predeterminado `gemma3:1b` se descarga durante
  el primer inicio; por defecto Ollama usa CPU.

Si Docker está instalado pero `docker compose` no existe, instalá el
complemento Compose v2 siguiendo la
[guía oficial de Docker](https://docs.docker.com/compose/install/linux/).
Verificá que tu usuario pueda acceder al daemon (`docker info`). Si no tiene
acceso, necesitás permisos de administrador para ejecutar Docker con `sudo`
(por ejemplo, `sudo docker compose up -d --build`) o configurar acceso para tu
cuenta. Agregar un usuario al grupo `docker` equivale prácticamente a darle
privilegios de root; utilizá únicamente cuentas de confianza.

El complemento Compose puede instalarse en la cuenta del usuario, pero eso no
otorga acceso al daemon: un administrador debe configurar ese permiso por
separado. Si `sudo` informa que tu cuenta no está autorizada, pedile a quien
administra el servidor que instale el complemento y habilite el acceso de
Docker.

### Preparar y arrancar

Desde la raíz del repositorio:

1. Creá la configuración local si todavía no existe:

   ```bash
   cp .env.example .env
   ```

2. Ejecutá dos veces, por separado, `openssl rand -hex 32` y reemplazá en
   `.env` los valores de `DB_PASSWORD` y `DB_ROOT_PASSWORD` por los resultados.
   No uses la contraseña de Linux, no compartas esos valores y no subas `.env`
   a Git. En Linux, configurá también `LOCAL_GID` con el resultado de `id -g`;
   así PHP puede guardar los YAML y modelos generados en las carpetas
   compartidas sin cambiar sus permisos.
   `APP_PORT` permite cambiar el puerto publicado (por defecto `8080`).
3. Construí y arrancá todos los servicios:

   ```bash
   docker compose up -d --build
   ```

   El primer inicio puede tardar varios minutos: MariaDB importa
   `chatbot_v2.sql`, Rasa carga el modelo y Ollama descarga
   `OLLAMA_MODEL`.
4. Consultá el estado y los registros:

   ```bash
   docker compose ps
   docker compose logs -f app mysql rasa action-server ollama
   ```

5. Abrí <http://localhost:8080/chatbotv2/> (o el puerto que configuraste en
   `APP_PORT`). Por seguridad, el puerto se limita a la propia máquina por
   defecto. Para permitir acceso desde la red, configurá
   `APP_BIND_ADDRESS=0.0.0.0` en `.env` y protegé el servidor con firewall y un
   proxy inverso HTTPS antes de usarlo con otras personas.

El usuario `root` de Linux no interviene en la configuración de los
contenedores. MariaDB tiene una contraseña de root propia (`DB_ROOT_PASSWORD`)
y PHP se conecta con el usuario de aplicación (`DB_USER`). El archivo SQL
inicializa la base únicamente cuando el volumen de MariaDB está vacío; cambiar
las variables después no modifica las contraseñas o los datos de un volumen ya
creado. El SQL puede incluir cuentas de prueba de la aplicación: verificá y
cambiá sus contraseñas antes de poner el sitio en una red compartida.

### Operaciones habituales

```bash
# Seguir los registros de un servicio
docker compose logs -f app

# Detener los servicios sin borrar la base ni el modelo de Ollama
docker compose down

# Reconstruir y aplicar cambios al código
docker compose up -d --build

# Entrenar Rasa con los YAML del repositorio
docker compose run --rm rasa train --domain domain/ --out /models
docker compose restart rasa
```

Los datos de MariaDB y los modelos descargados de Ollama se guardan en
volúmenes persistentes; el modelo Rasa, junto con los YAML generados por PHP,
queda en `models/`, `data/` y `domain/` en el repositorio.
`docker compose down -v` elimina permanentemente los volúmenes de MariaDB y
Ollama: usalo solo si querés borrar esos datos y empezar de cero.

`endpoints.docker.yml` configura la dirección interna del action server y no
reemplaza `endpoints.yml`, que continúa disponible para el uso local descripto
arriba. El puerto de Rasa, MariaDB y Ollama no se publica al host. Para un
servidor accesible desde Internet, agregá un proxy inverso con HTTPS y
restricciones de acceso antes de publicar la aplicación; este Compose está
pensado para pruebas y no configura TLS ni copias de seguridad.

## Flujo de reentrenado (server-side)

- `controller/helpers/RasaGenerator.php::trainAndLoadModel()` genera los archivos
  y solicita el reentrenamiento a Rasa.
- `RasaGenerator` genera en memoria un YAML combinado y lo POSTea a
  `${RASA_URL}/model/train` con `Content-Type: text/yaml`.
- Si Rasa devuelve JSON con la ruta del modelo, se hace `PUT /model` con
  `{"model_file": "<ruta>"}`. Si devuelve el `.tar.gz` binario, el
  generador lo guarda localmente (en `models/` o en `/tmp`) y hace `PUT` con
  la ruta al archivo guardado.

Comando rápido para probar desde la línea de comandos (en la máquina donde
está corriendo Apache/PHP):

```bash
php scripts/run_train.php
```

El script de entrenamiento devuelve objetos JSON con la fase y el error cuando
algo falla.

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

## Verificación en dos pasos

La aplicación permite proteger cuentas con códigos TOTP de Google Authenticator y aplicaciones compatibles. La guía de [instalación, archivos involucrados y flujo completo](docs/authenticator-2fa.md) explica también cómo actualizar una base existente. El esquema para nuevas instalaciones está en `chatbot_v2.sql`.

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
