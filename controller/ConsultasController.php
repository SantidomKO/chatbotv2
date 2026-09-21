<?php
require_once "model/Consulta.class.php";
require_once "model/Usuario.class.php";

class ConsultasController
{
    public function index()
    {
        if (!isset($_SESSION['usuario_data'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $usuario = Usuario::obtenerPorID($_SESSION['usuario_data']['id']);
        if (!$usuario || !$usuario->tienePermiso('Gestionar Preguntas Sin Respuesta')) {
            header('Location: index.php?controller=dashboard&action=index');
            exit();
        }

        $consultasSinResponder = Consulta::listarNoRespondidas();
        require_once "view/consultas/index.php";
    }
}