# Verificación en dos pasos con aplicaciones autenticadoras

La aplicación admite códigos TOTP de seis dígitos generados por Google Authenticator, Microsoft Authenticator, Authy y otras aplicaciones compatibles. La función es opcional por cuenta. El secreto compartido se cifra en la base de datos con AES-256-GCM; la clave de cifrado se configura por separado en Apache.

## Requisitos y puesta en marcha

1. Usa PHP con las extensiones `openssl` y `pdo_mysql` habilitadas. La hora del servidor y del teléfono debe estar sincronizada.
2. Genera una clave de cifrado de 32 bytes codificada en Base64 desde una consola con PHP:

   ```powershell
   C:\xampp\php\php.exe -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
   ```

3. Configura el resultado en la configuración de Apache, por ejemplo en `C:\xampp\apache\conf\extra\httpd-xampp.conf`:

   ```apache
   SetEnv CHATBOT_TOTP_KEY "PEGA_AQUI_LA_CLAVE_BASE64"
   ```

   Reinicia Apache. No guardes esta clave en el repositorio. Haz una copia segura: perderla hace imposible descifrar los secretos ya inscritos.
4. `chatbot_v2.sql` incluye las columnas para una instalación nueva. Modificar un volcado SQL no cambia una base de datos que ya fue importada. Para actualizar una base existente, ejecuta una sola vez estas sentencias en esa base (por ejemplo, desde phpMyAdmin):

    ```sql
    ALTER TABLE `usuarios`
       ADD COLUMN `two_factor_secret` varchar(255) DEFAULT NULL,
       ADD COLUMN `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
       ADD COLUMN `two_factor_last_counter` bigint(20) DEFAULT NULL;
    ```

    No vuelvas a ejecutar estas sentencias si las columnas ya existen.
5. Ejecuta la prueba autónoma: `C:\xampp\php\php.exe tests\test_authenticator.php`.

## Archivos que participan

- `index.php`: crea el token CSRF de sesión y enruta las acciones de desafío, inscripción y desactivación.
- `controller/AuthController.php`: valida la contraseña; para cuentas protegidas crea una sesión pendiente de cinco minutos, verifica el TOTP y solo después crea la sesión autenticada completa. También permite cancelar el desafío y limpia desafíos expirados.
- `model/Usuario.class.php`: busca la cuenta y verifica la contraseña con `password_verify`; sus datos se usan para completar la sesión autenticada después del TOTP.
- `model/Database.class.php`: proporciona la conexión PDO MySQL usada por los modelos.
- `model/Authenticator.class.php`: genera secretos Base32, crea la URI `otpauth://`, calcula y valida TOTP (HMAC-SHA1, 6 dígitos, 30 segundos, tolerancia de un intervalo), cifra/descifra secretos con AES-256-GCM y rechaza códigos ya usados mediante el contador almacenado.
- `controller/PerfilController.php`: requiere la contraseña para iniciar inscripción o desactivar la función, verifica el código de confirmación y asocia las operaciones con la cuenta de la sesión actual.
- `view/auth/login.php`: presenta el formulario de contraseña o, durante el desafío pendiente, el formulario de código de un solo uso. Ambos incluyen el token CSRF.
- `view/perfil/ver.php`: presenta el estado, los formularios de configuración y desactivación, la clave manual y el QR. El QR se genera en el navegador con QRCode.js; no se envía el secreto a un servicio de generación remoto. Si el CDN no está disponible, se puede añadir la cuenta manualmente.
- `view/layout/header.php` y `view/layout/footer.php`: proporcionan el marco HTML y los estilos compartidos que utilizan las vistas de acceso y perfil; no contienen lógica de TOTP.
- `view/layout/sidebar.php`: muestra el enlace existente a **Mi perfil**, desde donde se administra la inscripción.
- `chatbot_v2.sql`: define `two_factor_secret`, `two_factor_enabled` y `two_factor_last_counter` dentro de `usuarios` para bases nuevas. También es la referencia de esquema; las bases existentes requieren el `ALTER TABLE` indicado arriba.
- `tests/test_authenticator.php`: comprueba el vector de referencia de RFC 6238, entradas inválidas y el formato de secretos generados.
- `README.md`: enlaza esta documentación desde la guía principal del proyecto.
- `docs/authenticator-2fa.md`: esta guía ampliada de instalación, archivos participantes, operación y flujo.

## Flujo general

### Inscripción

1. La persona inicia sesión normalmente y abre **Mi perfil** desde la navegación compartida.
2. Para comenzar, vuelve a introducir su contraseña. El servidor genera un secreto aleatorio y lo conserva en la sesión durante diez minutos; aún no activa TOTP en la cuenta.
3. La persona escanea el QR con su autenticador o introduce manualmente la clave Base32. La cuenta se configura como TOTP, SHA-1, seis dígitos y 30 segundos.
4. Introduce un código generado por la aplicación. Solo si es válido, el servidor cifra y guarda el secreto, activa la función y guarda el intervalo usado para impedir reutilizar ese mismo código.

### Inicio de sesión

1. `AuthController` usa `Usuario::verificarLogin()` para validar la contraseña como antes. Si la cuenta no tiene TOTP, se inicia la sesión habitual.
2. Si TOTP está activo, se regenera el identificador de sesión y solo se conserva un estado pendiente con el identificador de usuario y vencimiento de cinco minutos. No se crea `usuario_data`, por lo que las rutas protegidas siguen inaccesibles.
3. La persona introduce el código de la aplicación. El servidor descifra el secreto, comprueba el código actual y los intervalos adyacentes para tolerar una pequeña diferencia de reloj, y registra atómicamente el contador para bloquear su reutilización.
4. Solo tras una comprobación correcta se regenera nuevamente el identificador de sesión y se crea la sesión autenticada habitual. El bootstrap `index.php` exige `usuario_data` para entrar a las demás áreas, así que el estado pendiente no da acceso al chat ni a las rutas protegidas.

### Desactivación y recuperación

Para desactivar TOTP desde el perfil se requieren la contraseña y un código válido del autenticador. Si se pierde el dispositivo, no hay códigos de recuperación en esta versión: una persona administradora debe restablecer `two_factor_secret`, `two_factor_enabled` y `two_factor_last_counter` para ese usuario, tras verificar su identidad por un canal confiable.

## Consideraciones operativas

- `CHATBOT_TOTP_KEY` debe permanecer estable y respaldarse aparte de la base de datos. Rota únicamente con un procedimiento que descifre y vuelva a cifrar todos los secretos inscritos.
- Los códigos duran 30 segundos; la tolerancia de un intervalo permite una diferencia máxima de aproximadamente 30 segundos entre el servidor y el teléfono.
- La clave manual y el QR son secretos de inscripción. No compartas capturas ni la clave con terceros.
- La generación del QR requiere que el navegador pueda descargar QRCode.js desde cdnjs. La clave manual evita depender del QR o del CDN.