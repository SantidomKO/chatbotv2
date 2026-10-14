<?php
require_once "model/Respuesta.class.php";
require_once "model/Intent.class.php";

class RespuestaController {
    public function guardar() {
        $texto = $_POST['texto'] ?? '';
        $tipo = $_POST['tipo'] ?? null;
        $intentId = $_POST['intent_id'] ?? null;
        $intent = null;
        if (!empty($intentId)) {
            $intent = Intent::obtenerPorID($intentId);
        }

        $r = new Respuesta(null, $texto, $tipo, $intent);
        $r->guardar();
        header('Location: index.php?controller=intent&action=listar');
        exit;
    }

    public function actualizar() {
        $id = $_POST['id'] ?? null;
        $texto = $_POST['texto'] ?? '';
        $tipo = $_POST['tipo'] ?? null;
        $intentId = $_POST['intent_id'] ?? null;
        $intent = null;
        if (!empty($intentId)) {
            $intent = Intent::obtenerPorID($intentId);
        }
        if ($id) {
            $r = new Respuesta($id, $texto, $tipo, $intent);
            $r->actualizar();
        }
        header('Location: index.php?controller=intent&action=listar');
        exit;
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $r = new Respuesta($id);
            $r->eliminar();
        }
        header('Location: index.php?controller=intent&action=listar');
        exit;
    }
}

?>