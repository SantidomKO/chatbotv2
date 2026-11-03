<?php
require_once "model/Usuario.class.php";
require_once "model/Authenticator.class.php";

class AuthController
{
    public function login()
    {
        if (isset($_SESSION['pending_2fa']) && (int) $_SESSION['pending_2fa']['expires_at'] < time()) {
            unset($_SESSION['pending_2fa']);
            $_SESSION['error'] = 'La verificación expiró. Inicia sesión nuevamente.';
        }
        require_once "view/auth/login.php";
    }

    public function registrar()
    {
        require_once "view/auth/registrar.php";
    }

    public function guardarRegistro()
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        $twoFactorMethod = Usuario::normalizarMetodo2FA($_POST['two_factor_method'] ?? 'none');

        if ($nombre === '' || $email === '' || $password === '') {
            $_SESSION['error'] = 'Todos los campos son obligatorios';
            require_once "view/auth/registrar.php";
            return;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            require_once "view/auth/registrar.php";
            return;
        }

        $emailExistente = Usuario::obtenerPorEmail($email);
        if ($emailExistente) {
            $_SESSION['error'] = 'El email ya está registrado';
            require_once "view/auth/registrar.php";
            return;
        }

        $rolNormal = Rol::obtenerPorID(2);
        $usuario = new Usuario(null, $nombre, $email, $password, $rolNormal);
        $usuarioId = $usuario->guardar();
        if ($usuarioId) {
            $usuario->setId((int) $usuarioId);
            if ($twoFactorMethod !== 'none') {
                $usuario->guardarMetodo2FA($twoFactorMethod);
            }
            $_SESSION['success'] = 'Usuario registrado exitosamente. Por favor inicia sesión.';
            header('Location: index.php?controller=auth&action=login');
            exit();
        } else {
            $_SESSION['error'] = 'Error al registrar el usuario';
            require_once "view/auth/registrar.php";
        }
    }

    public function validar()
    {
        if (!$this->validarCsrf()) {
            $_SESSION['error'] = 'La solicitud expiró. Intenta nuevamente.';
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $emailRaw = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($emailRaw === '' || $password === '') {
            $_SESSION['error'] = 'Email y contraseña son obligatorios';
            require_once "view/auth/login.php";
            return;
        }

        $email = filter_var($emailRaw, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email inválido';
            require_once "view/auth/login.php";
            return;
        }

        $usuario = Usuario::verificarLogin($email, $password);

        if ($usuario) {
            $method = $this->resolverMetodo2FA($usuario);
            if ($method !== 'none') {
                if ($method === 'authenticator') {
                    $authenticator = new Authenticator();
                    if (!$authenticator->isEnabled($usuario->getId())) {
                        $method = 'email';
                    }
                }

                if ($method === 'authenticator') {
                    session_regenerate_id(true);
                    $_SESSION['pending_2fa'] = [
                        'user_id' => $usuario->getId(),
                        'method' => 'authenticator',
                        'expires_at' => time() + 300,
                    ];
                    header('Location: index.php?controller=auth&action=login');
                    exit();
                }

                if ($method === 'email') {
                    $code = (string) random_int(100000, 999999);
                    session_regenerate_id(true);
                    $mailSent = $this->enviarCodigoPorMail($usuario->getEmail(), $code);
                    $_SESSION['pending_2fa'] = [
                        'user_id' => $usuario->getId(),
                        'method' => 'email',
                        'code' => $code,
                        'mail_sent' => $mailSent,
                        'debug_code' => $code,
                        'expires_at' => time() + 300,
                    ];
                    if (!$mailSent) {
                        $_SESSION['error'] = 'No se pudo enviar el correo desde este entorno. Como es una instalación local sin SMTP configurado, se muestra el código de prueba para continuar.';
                    }
                    header('Location: index.php?controller=auth&action=login');
                    exit();
                }
            }

            $this->completeLogin($usuario);
        } else {
            $_SESSION['error'] = 'Email o contraseña incorrectos';
            require_once "view/auth/login.php";
            return;
        }
    }

    public function verificarSegundoFactor()
    {
        if (!$this->validarCsrf()) {
            $_SESSION['error'] = 'La solicitud expiró. Intenta nuevamente.';
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $pending = $_SESSION['pending_2fa'] ?? null;
        if (!$pending || (int) $pending['expires_at'] < time()) {
            unset($_SESSION['pending_2fa']);
            $_SESSION['error'] = 'La verificación expiró. Inicia sesión nuevamente.';
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        if (($pending['method'] ?? 'authenticator') === 'email') {
            $codigoIngresado = preg_replace('/\D+/', '', (string) ($_POST['code'] ?? ''));
            if (!hash_equals((string) $pending['code'], $codigoIngresado)) {
                $_SESSION['error'] = 'El código enviado por email no es válido o ya expiró.';
                header('Location: index.php?controller=auth&action=login');
                exit();
            }
        } else {
            $authenticator = new Authenticator();
            if (!$authenticator->verifyAndConsume((int) $pending['user_id'], $_POST['code'] ?? '')) {
                $_SESSION['error'] = 'El código no es válido o ya fue utilizado.';
                header('Location: index.php?controller=auth&action=login');
                exit();
            }
        }

        $usuario = Usuario::obtenerPorID((int) $pending['user_id']);
        unset($_SESSION['pending_2fa']);
        if (!$usuario) {
            $_SESSION['error'] = 'No se pudo verificar la cuenta. Inicia sesión nuevamente.';
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $this->completeLogin($usuario);
    }

    public function cancelarSegundoFactor()
    {
        if (!$this->validarCsrf()) {
            $_SESSION['error'] = 'La solicitud expiró. Intenta nuevamente.';
        }
        unset($_SESSION['pending_2fa']);
        header('Location: index.php?controller=auth&action=login');
        exit();
    }

    private function completeLogin($usuario)
    {
        session_regenerate_id(true);
        unset($_SESSION['pending_2fa']);
        $_SESSION['id_usuario'] = $usuario->getId();
        $rolNombre = $usuario->getRol() ? $usuario->getRol()->getNombre() : null;
        $esAdmin = in_array(strtolower(trim((string) ($rolNombre ?? ''))), ['administrador', 'admin'], true)
            || ((int) $usuario->getId() === 1)
            || (strtolower(trim((string) $usuario->getEmail())) === 'admin@epet3.edu.ar');

        $_SESSION['usuario_data'] = [
            'id' => $usuario->getId(),
            'nombre' => $usuario->getNombre(),
            'email' => $usuario->getEmail(),
            'rol' => $rolNombre ?: 'Usuario',
            'es_admin' => $esAdmin,
        ];
        $_SESSION['nombre'] = $usuario->getNombre();
        $_SESSION['email'] = $usuario->getEmail();
        $_SESSION['toast'] = [
            'nombre' => $usuario->getNombre(),
            'rol' => $rolNombre ?: 'Usuario',
        ];

        $authenticator = new Authenticator();
        $metodoElegido = Usuario::obtenerMetodo2FA($usuario->getId());
        if ($metodoElegido === 'authenticator' && !$authenticator->isEnabled($usuario->getId())) {
            $_SESSION['success'] = 'Todavía no configuraste tu autenticador. Puedes hacerlo desde Mi perfil.';
            header('Location: index.php?controller=perfil&action=ver');
            exit();
        }

        header('Location: index.php?controller=chat&action=index');
        exit();
    }

    private function resolverMetodo2FA($usuario): string
    {
        $authenticator = new Authenticator();
        $metodoUsuario = Usuario::obtenerMetodo2FA($usuario->getId());

        if ($metodoUsuario === 'authenticator' && $authenticator->isEnabled($usuario->getId())) {
            return 'authenticator';
        }

        if ($metodoUsuario === 'authenticator' && !$authenticator->isEnabled($usuario->getId())) {
            return 'email';
        }

        if ($metodoUsuario === 'email') {
            return 'email';
        }

        return 'none';
    }

    private function enviarCodigoPorMail(string $email, string $codigo): bool
    {
        $mensaje = "Tu código de verificación es: {$codigo}\n\nEste código vence en 5 minutos.";
        $asunto = 'Código de verificación';
        $headers = [
            'From: no-reply@chatbotv2.local',
            'Reply-To: no-reply@chatbotv2.local',
            'X-Mailer: PHP/' . phpversion(),
            'Content-Type: text/plain; charset=UTF-8',
        ];

        $enviado = @mail($email, $asunto, $mensaje, implode("\r\n", $headers));
        if ($enviado === false) {
            error_log('Fallo al enviar código 2FA por mail a: ' . $email);
        }

        return $enviado !== false;
    }

    private function validarCsrf()
    {
        return isset($_SESSION['csrf_token'], $_POST['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    public function logout(){
        // Session already started in index.php
        session_destroy();
        header('Location: index.php?controller=auth&action=login');
        exit();
    }
}
