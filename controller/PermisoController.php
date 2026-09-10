<?php
require_once "model/Permiso.class.php";

class PermisoController{
    public function listar(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $permisos = Permiso::listar();
        require_once "view/permisos/index.php";
    }
    public function crear(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        require_once "view/permisos/crear.php";
    }
    public function editar(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $idPermiso=$_GET["id"] ?? null;
        if($idPermiso === null){
            header("Location: index.php?controller=permiso&action=listar");
            return;
        }
        $permiso = Permiso::obtenerPorID($idPermiso);
        require_once "view/permisos/editar.php";
    }
    public function actualizar(){
        $idPermiso=$_POST["id"];
        $nombre=$_POST["nombre"];
        $descripcion=$_POST["descripcion"];

        $permiso= new Permiso($idPermiso, $nombre, $descripcion);
        $permiso->actualizar();

        header("Location: index.php?controller=permiso&action=listar");
    }
    public function eliminar(){
        $idPermiso=$_GET["id"];
        $permiso= new Permiso($idPermiso);
        $permiso->eliminar();

        header("Location: index.php?controller=permiso&action=listar");
    }
    public function guardar(){
        $idPermiso=$_POST["id"];
        $nombre=$_POST["nombre"];
        $descripcion=$_POST["descripcion"];

        $permiso= new Permiso($idPermiso, $nombre, $descripcion);
        $permiso->guardar();

        header("Location: index.php?controller=permiso&action=listar");
    }

}
