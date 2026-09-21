<?php
require_once "model/Usuario.class.php";

class PerfilController {
    public function ver() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_data'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $usuario = Usuario::obtenerPorID($_SESSION['usuario_data']['id']);
        $nombreUsuario = $usuario ? $usuario->getNombre() : 'Usuario';
        $inicial = strtoupper(substr($nombreUsuario, 0, 1));

        require_once "view/perfil/ver.php";
    }
}
