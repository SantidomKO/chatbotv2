<?php
require_once "model/Usuario.class.php";

class AuthController
{
    public function login()
    {
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
            // Session is started in index.php bootstrap

            $_SESSION['id_usuario'] = $usuario->getId();
            $rolNombre = $usuario->getRol() ? $usuario->getRol()->getNombre() : null;
            $esAdmin = in_array(strtolower(trim((string) ($rolNombre ?? ''))), ['administrador', 'admin'], true)
                || ((int) $usuario->getId() === 1)
                || (strtolower(trim((string) ($email ?? ''))) === 'admin@epet3.edu.ar');

            // Guardar solo datos serializables, no el objeto completo
            $_SESSION['usuario_data'] = [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getNombre(),
                'email' => $usuario->getEmail(),
                'rol' => $rolNombre ?: 'Usuario',
                'es_admin' => $esAdmin,
            ];
            $_SESSION["nombre"] = $usuario->getNombre();
            $_SESSION["email"] = $usuario->getEmail();
            // Mostrar toast de bienvenida una sola vez tras el login
            $_SESSION['toast'] = [
                'nombre' => $usuario->getNombre(),
                'rol' => $rolNombre ?: 'Usuario',
            ];

            if ($esAdmin) {
                header('Location: index.php?controller=dashboard&action=index');
            } else {
                header('Location: index.php?controller=chat&action=index');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Email o contraseña incorrectos';
            require_once "view/auth/login.php";
            return;
        }
    }

    public function logout(){
        // Session already started in index.php
        session_destroy();
        header('Location: index.php?controller=auth&action=login');
        exit();
    }
}
