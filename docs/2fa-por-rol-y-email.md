# Política de 2FA por rol y método alternativo

## Objetivo

Este proyecto permite diferenciar la política de verificación en dos pasos según el rol del usuario:

- usuarios normales: el 2FA es opcional y puede elegirse al registrarse;
- usuarios con otros roles: el 2FA es obligatorio y si no tienen autenticador configurado se usa una alternativa por email.

La idea es evitar que el requisito sea fijo para todos, pero sí reforzar la seguridad para perfiles con mayor acceso o responsabilidades administrativas.

## Política implementada

La lógica quedó centralizada en el modelo de usuario y en el controlador de autenticación:

- `Usuario::esRolNormal()` define si un rol es un usuario normal.
- `Usuario::requiere2FA()` revisa el método elegido por la cuenta y no fuerza el 2FA por rol.
- `Usuario::normalizarMetodo2FA()` convierte las opciones de configuración a un valor uniforme.
- `Usuario::obtenerMetodo2FA()` lee el método persistido por usuario.
- `AuthController::resolverMetodo2FA()` determina si se usa autenticador, email o ninguna verificación según la preferencia real del usuario.

### Regla de negocio

1. Cada usuario puede elegir su método de segundo factor: `ninguno`, `email` o `authenticator`.
2. Los roles administrativos y no normales no tienen una restricción fija por rol; pueden elegir usar email para cada inicio o configurar el autenticador y evitar repetir el mail.
3. Los usuarios normales también pueden elegir exactamente lo mismo o dejarlo en `No activar`.
4. Si el usuario elige `authenticator` pero aún no lo configura, la aplicación lo lleva a Mi perfil para completarlo.
5. Si el usuario elige `email`, cada login requiere el código enviado por correo.
6. Si elige `none`, accede sin mail ni autenticador.

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

- que el rol normal no requiere 2FA obligatorio;
- que un rol no normal sí lo requiere;
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

## Ajuste final del flujo

Se corrigió la política para que ya no exista una imposición por rol:

1. cada usuario puede decidir en el registro o desde la edición del perfil si quiere usar `No activar`, `Email` o `Autenticador`;
2. si elige `Autenticador` pero aún no lo configura, se le redirige a Mi perfil para hacerlo;
3. si elige `Email`, cada inicio de sesión pide el código por correo;
4. si elige `No activar`, ingresa sin mail ni autenticador.

Esto permite que los administradores y demás roles no normales también decidan si prefieren no ver el mail en cada ingreso y setear el autenticador, mientras que los usuarios normales pueden mantener el acceso sencillo y sin 2FA si así lo quieren.
