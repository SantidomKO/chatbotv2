<?php
class PerfilController {
    public function ver() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $usuario = $_SESSION['usuario'];
        $nombreUsuario = is_array($usuario) && !empty($usuario['nombre']) ? $usuario['nombre'] : 'Usuario';
        $inicial = strtoupper(substr($nombreUsuario, 0, 1));

        require_once "view/perfil/ver.php";
    }
}
