<?php
require_once "model/Rol.class.php";
require_once "model/RolPermiso.class.php";
require_once "model/Permiso.class.php";

class RolController{
    public function listar(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $roles = Rol::listar();
        require_once "view/roles/index.php";
    }
    public function crear(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        require_once "view/roles/crear.php";
    }
    public function editar(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $id=$_GET["id"] ?? null;
        if($id === null){
            header("Location: index.php?controller=rol&action=listar");
            return;
        }
        $rol = Rol::obtenerPorID($id);
        require_once "view/roles/editar.php";
    }
    public function actualizar(){
        $id=$_POST["id"];
        $nombre=$_POST["nombre"];
        $descripcion=$_POST["descripcion"];
        $permisos = $_POST["permisos"] ?? [];

        $rol= new Rol($id, $nombre, $descripcion);
        $rol->actualizar();

        // Actualizar permisos
        $rolPermiso = new RolPermiso();
        $rolPermiso->eliminarPorRol($id);
        foreach($permisos as $permisoId) {
            $rolPermiso->guardar($id, $permisoId);
        }

        header("Location: index.php?controller=rol&action=listar");
    }
    public function eliminar(){
        $id=$_GET["id"];
        $rol= new Rol($id);
        $rol->eliminar();

        header("Location: index.php?controller=rol&action=listar");
    }
    public function guardar(){
        $nombre=$_POST["nombre"];
        $descripcion=$_POST["descripcion"];
        $permisos = $_POST["permisos"] ?? [];

        $rol= new Rol(null, $nombre, $descripcion);
        $rolId = $rol->guardar();

        // Guardar permisos
        $rolPermiso = new RolPermiso();
        foreach($permisos as $permisoId) {
            $rolPermiso->guardar($rolId, $permisoId);
        }

        header("Location: index.php?controller=rol&action=listar");
    }

}
