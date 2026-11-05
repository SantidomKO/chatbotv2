# Política de 2FA por rol y método alternativo

## Objetivo

Este proyecto permite diferenciar la política de verificación en dos pasos según el rol del usuario:

- usuarios normales: el 2FA es opcional y puede elegirse al registrarse;
- usuarios con cualquier otro rol (o sin rol): el 2FA es obligatorio; si no tienen autenticador configurado se usa una alternativa por email.

La idea es evitar que el requisito sea fijo para todos, pero sí reforzar la seguridad para perfiles con mayor acceso o responsabilidades administrativas.

## Política implementada

La lógica quedó centralizada en el modelo de usuario y en el controlador de autenticación:

- `Usuario::esRolNormal()` define si un rol es un usuario normal.
- `Usuario::requiere2FA()` obliga a todos los roles que no sean de usuario normal a completar 2FA, independientemente de la preferencia guardada.
- `Usuario::normalizarMetodo2FA()` convierte las opciones de configuración a un valor uniforme.
- `Usuario::obtenerMetodo2FA()` lee el método persistido por usuario.
- `AuthController::resolverMetodo2FA()` usa el autenticador activo cuando está configurado; para roles obligados sin autenticador, requiere un código por email.

### Regla de negocio

1. Los usuarios normales pueden elegir `none`, `email` o `authenticator`.
2. Los roles administrativos y demás roles no normales siempre deben completar un segundo factor, aunque la preferencia guardada sea `none`.
3. Si se eligió `authenticator` y está activado, el login requiere un código TOTP.
4. Si el autenticador no está activado, o no se eligió un método, los roles obligados reciben un código por email.
5. Si un usuario normal elige `email`, cada login requiere el código enviado por correo; si elige `none`, puede iniciar sesión sin segundo factor.

## Registro

El formulario de registro quedó en:

- [view/auth/registrar.php](view/auth/registrar.php)

Ahora incluye un select con:

```php
<select name="two_factor_method">
    <option value="none">No activar</option>
    <option value="authenticator">Autenticador (Google/Microsoft/Authy)</option>
    <option value="email">Código por email</option>
</select>
```

La acción del registro guarda el valor normalizado y, si corresponde, lo persiste con `two_factor_method`.

## Inicio de sesión

La lógica de login quedó en:

- [controller/AuthController.php](controller/AuthController.php)

Cuando el usuario ingresa contraseña y email:

1. se valida el login habitual;
2. se resuelve el método requerido por la política;
3. si el método es autenticador y está activo, se lanza el desafío TOTP;
4. si el método es email, se genera un código de 6 dígitos y se envía por mail;
5. el usuario debe ingresar el código recibido en la pantalla de verificación;
6. si el código es correcto, se completa la sesión.

### Mail como alternativa

La función que simula el envío del código por correo está implementada en:

```php
private function enviarCodigoPorMail(string $email, string $codigo): void
```

Actualmente usa `mail()` de PHP. En un entorno real, conviene sustituirlo por SMTP confiable o por una librería como PHPMailer.

Para probar cuentas con direcciones de correo ficticias desde localhost, el código de los desafíos por email se muestra automáticamente en la consola del navegador mientras se encuentre pendiente. En otros entornos de desarrollo se puede activar `DEBUG_2FA_CODE=true` en el archivo `.env`. Mantén esta opción desactivada fuera del desarrollo local, ya que el código es el segundo factor de autenticación.

## Base de datos

Se agregó la columna `two_factor_method` a la tabla `usuarios`:

```sql
ALTER TABLE `usuarios`
  ADD COLUMN `two_factor_method` varchar(20) DEFAULT NULL;
```

También quedó reflejada en el esquema de:

- [chatbot_v2.sql](chatbot_v2.sql)

## Pruebas

Queda una prueba de la política en:

- [tests/test_2fa_policy.php](tests/test_2fa_policy.php)

Esa prueba valida:

- que el rol normal se distingue de los roles obligados;
- que un rol no normal y una cuenta sin rol requieren 2FA;
- que el método `MAIL` se normaliza a `email`;
- que el método `authenticator` se mantiene.

## Archivos relevantes

- [model/Usuario.class.php](model/Usuario.class.php)
- [controller/AuthController.php](controller/AuthController.php)
- [view/auth/registrar.php](view/auth/registrar.php)
- [view/auth/login.php](view/auth/login.php)
- [chatbot_v2.sql](chatbot_v2.sql)
- [tests/test_2fa_policy.php](tests/test_2fa_policy.php)

## Observación de seguridad

La alternativa por email no reemplaza el uso de un autenticador cuando el usuario ya lo configuró, pero sí sirve como fallback obligatorio para perfiles que no tienen esa configuración activa. En producción conviene que el envío de emails use un servidor SMTP verificado para evitar bloqueo en proveedores de correo y evitar reproducir mensajes como spam.

## Política efectiva en el login

Las preferencias de 2FA no pueden desactivar el requisito para cuentas no normales. Estas cuentas deben completar un desafío en cada inicio de sesión: TOTP si el autenticador está activo, o código por email si no lo está. Solo los usuarios normales pueden iniciar sesión con `none`.
