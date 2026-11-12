# Chatbot (chatbotv2)

Aplicación web en PHP para administrar el conocimiento del chatbot y conectarlo
con Rasa. La web construye los datos de entrenamiento a partir de la base de
datos, solicita a Rasa que entrene un modelo y, si el entrenamiento termina
bien, le pide que cargue el modelo nuevo.

Esta guía cubre primero la forma recomendada de probar el proyecto con Docker
Compose. Después describe la configuración manual, el modo de desarrollo local
y las funciones principales.

## Inicio rápido con Docker Compose

El repositorio incluye un script para Linux que prepara la configuración y
arranca el stack automáticamente. Incluye la aplicación PHP/Apache, MariaDB,
Rasa, el servidor de acciones y Ollama.

### Requisitos

- Linux (por ejemplo, CachyOS) o un entorno Linux como WSL2.
- Docker Engine en ejecución y Docker Compose v2 (`docker compose`).
- Espacio en disco para las imágenes y para el modelo de Ollama. El modelo
  predeterminado es `gemma3:1b`; la primera descarga puede tardar.

En Windows, abrí una terminal de WSL2 en la carpeta del proyecto y usá Docker
Desktop con la integración de WSL habilitada. El script es Bash y no se ejecuta
directamente desde PowerShell o CMD.

### Arrancar automáticamente

Desde la raíz del repositorio, ejecutá:

```bash
chmod +x scripts/bootstrap_docker.sh
./scripts/bootstrap_docker.sh
```

El script:

1. Crea `.env` si todavía no existe. Genera contraseñas aleatorias para MariaDB
   y completa los nombres de servicios que Docker usa para conectar los
   contenedores.
2. Usa el puerto `8080` y calcula `LOCAL_GID` cuando puede, para facilitar que
   la aplicación escriba archivos compartidos con el host.
3. Construye las imágenes y ejecuta `docker compose up -d --build`.

Si `.env` ya existe, el script lo deja intacto; no cambia sus valores ni sus
contraseñas. Para simular la preparación sin iniciar los servicios:

```bash
./scripts/bootstrap_docker.sh --dry-run
```

Para generar `.env` sin iniciar Docker:

```bash
./scripts/bootstrap_docker.sh --no-up
```

Cuando los servicios estén arriba, revisá su estado y abrí la aplicación:

```bash
docker compose ps
docker compose logs -f app mysql rasa action-server ollama
```

La aplicación queda en <http://localhost:8080/chatbotv2/>. Para dejar de
seguir los registros, usá `Ctrl+C`; esto no detiene los contenedores.

### Acceso y seguridad

El script configura `APP_BIND_ADDRESS=0.0.0.0`, por lo que la aplicación puede
ser accesible desde otras máquinas de la red. Si solo la vas a usar en esa
misma computadora, cambiá ese valor en `.env` a `127.0.0.1` antes de iniciar el
stack. Para permitir acceso desde una red, limitá el acceso con un firewall y
configurá un proxy inverso con HTTPS antes de compartir el servicio.

No compartas ni subas `.env` a Git: contiene las contraseñas de la base de
datos. El usuario `root` del sistema operativo no es el usuario `root` de
MariaDB; son cuentas distintas. Agregar una cuenta al grupo `docker` equivale,
en la práctica, a darle privilegios de administrador: hacelo solo con cuentas
de confianza.

### Detener, actualizar y borrar datos

```bash
# Detener los servicios; conservar la base de datos y los modelos de Ollama
docker compose down

# Reconstruir imágenes y aplicar los cambios del proyecto
docker compose up -d --build

# Ver los registros de la aplicación
docker compose logs -f app
```

MariaDB y Ollama guardan sus datos en volúmenes persistentes. En cambio, los
modelos de Rasa y los archivos YAML generados se guardan en `models/`, `data/`
y `domain/` dentro del repositorio. El comando siguiente también elimina los
volúmenes de MariaDB y Ollama, y borra sus datos permanentemente:

```bash
docker compose down -v
```

Usalo únicamente si querés empezar desde cero. El SQL inicializa MariaDB solo
cuando el volumen está vacío; cambiar las contraseñas en `.env` no actualiza
las credenciales ni los datos de un volumen existente. La base puede contener
cuentas de prueba: cambiá sus contraseñas antes de exponer la aplicación a
otras personas.

## Configuración manual de Docker

Si preferís no usar el script, necesitás Docker Engine y Compose v2. Desde la
raíz del repositorio:

1. Copiá la plantilla de configuración:

   ```bash
   cp .env.example .env
   ```

2. En `.env`, reemplazá `DB_PASSWORD` y `DB_ROOT_PASSWORD` por contraseñas
   distintas y difíciles de adivinar. En Linux, configurá `LOCAL_GID` con el
   resultado de `id -g`. No uses la contraseña de tu cuenta del sistema.
3. Si la aplicación solo se usará desde esta computadora, configurá
   `APP_BIND_ADDRESS=127.0.0.1`. `APP_PORT` cambia el puerto publicado; su
   valor predeterminado es `8080`.
4. Construí e iniciá el stack:

   ```bash
   docker compose up -d --build
   ```

5. Consultá el estado con `docker compose ps` y abrí
   <http://localhost:8080/chatbotv2/> (o el puerto indicado en `APP_PORT`).

En el primer inicio, Docker descarga las imágenes; MariaDB importa
`chatbot_v2.sql` y Ollama descarga el modelo indicado en `OLLAMA_MODEL`. Esto
puede tardar varios minutos. Si `docker compose` no está disponible, instalá el
complemento Compose v2 siguiendo la
[guía oficial de Docker](https://docs.docker.com/compose/install/).

## Configuración de la aplicación

La configuración local se guarda en `.env`, que está excluido de Git. La
aplicación PHP carga esas variables; si Docker Compose define una variable para
un contenedor, esa configuración tiene prioridad dentro de él.

Las variables principales son:

| Variable | Uso |
| --- | --- |
| `APP_BIND_ADDRESS` | Interfaz del host en la que se publica la aplicación. |
| `APP_PORT` | Puerto HTTP publicado por Docker. |
| `DB_HOST`, `DB_PORT` | Host y puerto de MariaDB. En Compose, el host es `mysql`. |
| `DB_NAME`, `DB_USER`, `DB_PASSWORD` | Base, usuario y contraseña de la aplicación. |
| `DB_ROOT_PASSWORD` | Contraseña propia de la cuenta root de MariaDB. |
| `RASA_URL` | URL base de la API de Rasa. En Compose es `http://rasa:5005`. |
| `OLLAMA_URL`, `OLLAMA_MODEL` | API y modelo que utilizan las acciones personalizadas. |

Dentro de una red de Docker, usá nombres de servicios como `mysql`, `rasa`,
`action-server` y `ollama`, no `localhost`. En un contenedor, `localhost`
apunta a ese mismo contenedor, no al host ni a los otros servicios.

El Compose de este repositorio no publica al host los puertos de MariaDB, Rasa,
Ollama ni del servidor de acciones; solo publica el HTTP de la aplicación.
`endpoints.docker.yml` apunta al servidor de acciones dentro de la red de
Compose. Para ejecutar Rasa localmente, `endpoints.yml` usa
`http://localhost:5055/webhook`.

## Ejecución local de Rasa y acciones

Este modo sirve para desarrollo sin usar todo el stack de Docker. El proyecto
usa Rasa 3.6.21 y Python 3.10. Con Python 3.10 disponible, desde la raíz del
repositorio:

```bash
python3.10 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

En Windows, la activación del entorno virtual se hace con
`venv\Scripts\activate` en CMD o `venv\Scripts\Activate.ps1` en PowerShell.
Luego, en terminales separadas y con el entorno activado, iniciá el servidor de
acciones y Rasa:

```bash
rasa run actions
```

```bash
rasa run --enable-api --cors "*" --domain domain/
```

Antes de iniciar Rasa por primera vez, entrená el modelo:

```bash
rasa train --domain domain/
```

La opción `--cors "*"` es solo para desarrollo. No la uses en producción; en
ese caso restringí los orígenes permitidos y protegé el acceso a la API.

Para probar la conexión PHP con la base de datos desde el entorno donde corre
PHP, ejecutá:

```bash
php test_db.php
```

## Entrenamiento y actualización del modelo

En el flujo de la aplicación, `controller/helpers/RasaGenerator.php`
construye el YAML combinado de entrenamiento con los datos de la base y lo
envía a `${RASA_URL}/model/train`. Cuando Rasa devuelve un modelo, la
aplicación lo guarda localmente y solicita a Rasa que lo cargue.

Para iniciar el entrenamiento desde PHP:

```bash
php scripts/run_train.php
```

El script devuelve JSON con la fase y el error si falla. En Docker también
podés entrenar directamente con los YAML del repositorio:

```bash
docker compose run --rm rasa train --domain domain/ --out /models
docker compose restart rasa
```

El directorio `domain/` separa `base.yml`, mantenido manualmente, de
`generated.yml`, que puede regenerar la aplicación. `data/` contiene datos de
entrenamiento y `models/` los modelos resultantes.

## Estructura del proyecto

```text
actions/                 Acciones personalizadas de Rasa
assets/                  CSS y JavaScript del navegador
controller/              Controladores PHP y lógica de la aplicación
controller/helpers/      Integración y generación de datos para Rasa
data/                    Datos YAML de entrenamiento
domain/                  Dominio de Rasa, incluido el generado
model/                   Clases de datos PHP
models/                  Modelos entrenados de Rasa
scripts/                 Utilidades y tareas de entrenamiento
view/                    Vistas PHP
config.yml               Pipeline y políticas de Rasa
endpoints.yml             Endpoints para Rasa local
endpoints.docker.yml      Endpoint del action server en Docker
index.php                 Punto de entrada de la aplicación web
chatbot_v2.sql            Esquema y datos iniciales de la base de datos
```

## Funcionalidades

### Verificación en dos pasos

La aplicación permite proteger cuentas con códigos TOTP de Google
Authenticator y aplicaciones compatibles. La guía de
[configuración de la verificación en dos pasos](docs/authenticator-2fa.md)
describe la instalación, los archivos involucrados y la actualización de una
base existente. El esquema para instalaciones nuevas está en `chatbot_v2.sql`.

### Entrada y salida de voz

El chat ofrece reconocimiento de voz para ingresar consultas y síntesis de voz
para escuchar las respuestas. El texto reconocido aparece en el campo de
consulta para poder corregirlo antes de enviarlo; la respuesta escrita siempre
permanece visible.

Estas funciones usan las API nativas del navegador: `SpeechRecognition` (o
`webkitSpeechRecognition` en navegadores que exponen esa variante) y
`SpeechSynthesis`. El reconocimiento suele tener mejor soporte en Chrome y
navegadores basados en Chromium, requiere permiso para usar el micrófono y
normalmente necesita HTTPS o `localhost`. Las voces disponibles dependen del
navegador y del sistema operativo.

Si el reconocimiento de voz no está disponible, falla o se deniega el permiso,
se puede seguir usando el teclado. Si no está disponible la síntesis, las
respuestas escritas siguen funcionando.

## Solución de problemas

- **`Encountered empty domain during validation`:** indicá `--domain domain/`;
  el dominio del proyecto está repartido en varios archivos.
- **La web muestra un error 500:** revisá los registros de Apache y ejecutá
  `php scripts/run_train.php` para ver la fase y el error del entrenamiento.
- **PHP no conecta con MariaDB en Docker:** comprobá que `DB_HOST` sea `mysql`,
  que los servicios estén saludables (`docker compose ps`) y que las
  credenciales de `.env` correspondan al volumen existente.
- **Rasa no conecta con las acciones:** en Docker, verificá
  `endpoints.docker.yml` y que `action-server` esté saludable. En modo local,
  verificá que el servidor de acciones esté en ejecución y que se use
  `endpoints.yml`.
- **El micrófono no funciona:** comprobá el permiso del navegador y que la
  página use HTTPS o `localhost`; también podés continuar escribiendo.

Para producción, configurá HTTPS, limitá el acceso a las API internas, usá
orígenes CORS específicos y prepará copias de seguridad. El Compose incluido
está pensado para desarrollo y pruebas; no configura TLS ni respaldos.
