<?php
require_once "model/Intent.class.php";
require_once "model/Categoria.class.php";
require_once "model/Usuario.class.php";

class IntentController {
    private function requirePermission($permiso) {
        if (!isset($_SESSION['usuario_data'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $usuario = Usuario::obtenerPorID($_SESSION['usuario_data']['id']);
        if (!$usuario || !$usuario->tienePermiso($permiso)) {
            header('Location: index.php?controller=chat&action=index');
            exit();
        }
    }

    public function listar() {
        $this->requirePermission('Gestionar Intents');
        $intents = Intent::listar();
        $categorias = Categoria::listar();
        require_once "view/intents/index.php";
    }

    public function crear() {
        $this->requirePermission('Gestionar Intents');
        $categorias = Categoria::listar();
        require_once "view/intents/crear.php";
    }

    public function editar() {
        $this->requirePermission('Gestionar Intents');
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
        $this->requirePermission('Gestionar Intents');
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
        $this->requirePermission('Gestionar Intents');
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
        $this->requirePermission('Gestionar Intents');
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