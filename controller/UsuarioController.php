<?php
require_once "model/Usuario.class.php";

class UsuarioController{
    public function listar(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $usuarios = Usuario::listar();
        require_once "view/usuarios/index.php";
    }
    public function crear(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        require_once "view/usuarios/crear.php";
    }
    public function editar(){
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $idUsuario=$_GET["id"] ?? null;
        if($idUsuario === null){
            header("Location: index.php?controller=usuario&action=listar");
            return;
        }
        $usuario = Usuario::obtenerPorID($idUsuario);
        require_once "view/usuarios/editar.php";
    }
    public function actualizar(){
        $idUsuario=$_POST["id"];
        $nombre=$_POST["nombre"];
        $email=$_POST["email"];
        $password=$_POST["password"];

        $usuario= new Usuario($idUsuario, $nombre, $email, $password);
        $usuario->actualizar();

        header("Location: index.php?controller=usuario&action=listar");
    }
    public function eliminar(){
        $idUsuario=$_GET["id"];
        $usuario= new Usuario($idUsuario);
        $usuario->eliminar();

        header("Location: index.php?controller=usuario&action=listar");
    }
    public function guardar(){
        $nombre=$_POST["nombre"];
        $email=$_POST["email"];
        $password=$_POST["password"];
        $idRol = $_POST["id_rol"] ?? null;
        
        $rol = $idRol ? Rol::obtenerPorID($idRol) : null;
        $usuario= new Usuario(null, $nombre, $email, $password, $rol);
        $usuario->guardar();

        header("Location: index.php?controller=usuario&action=listar");
    }

}
