<?php
require_once "model/Intent.class.php";
require_once "model/Categoria.class.php";

class IntentController {
    public function listar() {
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $intents = Intent::listar();
        $categorias = Categoria::listar();
        require_once "view/intents/index.php";
    }

    public function crear() {
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $categorias = Categoria::listar();
        require_once "view/intents/crear.php";
    }

    public function editar() {
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $id = $_GET['id'] ?? null;
        if ($id === null) {
            header('Location: index.php?controller=intent&action=listar');
            exit;
        }
        $intent = Intent::obtenerPorID($id);
        $categorias = Categoria::listar();
        require_once "view/intents/editar.php";
    }

    public function guardar() {
        $nombreIntent = $_POST['nombre_intent'] ?? '';
        $categoriaId = $_POST['categoria_id'] ?? null;
        $descripcion = $_POST['descripcion'] ?? '';

        $categoria = null;
        if ($categoriaId) {
            $categoria = Categoria::obtenerPorID($categoriaId);
        }

        $intent = new Intent(null, $nombreIntent, $categoria, $descripcion);
        $intent->guardar();

        header('Location: index.php?controller=intent&action=listar');
        exit;
    }

    public function actualizar() {
        $id = $_POST['id'] ?? null;
        $nombreIntent = $_POST['nombre_intent'] ?? '';
        $categoriaId = $_POST['categoria_id'] ?? null;
        $descripcion = $_POST['descripcion'] ?? '';

        $categoria = null;
        if ($categoriaId) {
            $categoria = Categoria::obtenerPorID($categoriaId);
        }

        if ($id) {
            $intent = new Intent($id, $nombreIntent, $categoria, $descripcion);
            $intent->actualizar();
        }

        header('Location: index.php?controller=intent&action=listar');
        exit;
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $intent = new Intent($id);
            $intent->eliminar();
        }

        header('Location: index.php?controller=intent&action=listar');
        exit;
    }
}
?>