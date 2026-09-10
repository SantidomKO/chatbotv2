<?php
require_once "model/Consulta.class.php";

class ConsultasController
{
    public function index()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $consultasSinResponder = Consulta::listarNoRespondidas();
        require_once "view/consultas/index.php";
    }
}