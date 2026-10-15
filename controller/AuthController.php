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

        // Verificar si el email ya existe
        $emailExistente = Usuario::obtenerPorEmail($email);
        if ($emailExistente) {
            $_SESSION['error'] = 'El email ya está registrado';
            require_once "view/auth/registrar.php";
            return;
        }

        // Crear nuevo usuario (sin rol por defecto)
        $usuario = new Usuario(null, $nombre, $email, $password, null);
        if ($usuario->guardar()) {
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
            $authenticator = new Authenticator();
            if ($authenticator->isEnabled($usuario->getId())) {
                session_regenerate_id(true);
                $_SESSION['pending_2fa'] = [
                    'user_id' => $usuario->getId(),
                    'expires_at' => time() + 300,
                ];
                header('Location: index.php?controller=auth&action=login');
                exit();
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

        $authenticator = new Authenticator();
        if (!$authenticator->verifyAndConsume((int) $pending['user_id'], $_POST['code'] ?? '')) {
            $_SESSION['error'] = 'El código no es válido o ya fue utilizado.';
            header('Location: index.php?controller=auth&action=login');
            exit();
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

        header('Location: index.php?controller=chat&action=index');
        exit();
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
