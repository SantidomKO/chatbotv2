<?php
require_once "model/Permiso.class.php";
require_once "model/Usuario.class.php";

class PermisoController{
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

    public function listar(){
        $this->requirePermission('Gestionar Permisos');
        $permisos = Permiso::listar();
        require_once "view/permisos/index.php";
    }
    public function crear(){
        $this->requirePermission('Gestionar Permisos');
        require_once "view/permisos/crear.php";
    }
    public function editar(){
        $this->requirePermission('Gestionar Permisos');
        $idPermiso=$_GET["id"] ?? null;
        if($idPermiso === null){
            header("Location: index.php?controller=permiso&action=listar");
            return;
        }
        $permiso = Permiso::obtenerPorID($idPermiso);
        require_once "view/permisos/editar.php";
    }
    public function actualizar(){
        $this->requirePermission('Gestionar Permisos');
        $idPermiso=$_POST["id"];
        $nombre=$_POST["nombre"];
        $descripcion=$_POST["descripcion"];

        $permiso= new Permiso($idPermiso, $nombre, $descripcion);
        $permiso->actualizar();

        header("Location: index.php?controller=permiso&action=listar");
    }
    public function eliminar(){
        $this->requirePermission('Gestionar Permisos');
        $idPermiso=$_GET["id"];
        $permiso= new Permiso($idPermiso);
        $permiso->eliminar();

        header("Location: index.php?controller=permiso&action=listar");
    }
    public function guardar(){
        $this->requirePermission('Gestionar Permisos');
        $idPermiso=$_POST["id"];
        $nombre=$_POST["nombre"];
        $descripcion=$_POST["descripcion"];

        $permiso= new Permiso($idPermiso, $nombre, $descripcion);
        $permiso->guardar();

        header("Location: index.php?controller=permiso&action=listar");
    }

}
