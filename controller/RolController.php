<?php
require_once "model/Rol.class.php";
require_once "model/RolPermiso.class.php";
require_once "model/Permiso.class.php";
require_once "model/Usuario.class.php";

class RolController{
    private function requirePermission($permiso) {
        if (!isset($_SESSION['usuario_data'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        $usuario = Usuario::obtenerPorID($_SESSION['usuario_data']['id']);
        if (!$usuario || !$usuario->tienePermiso($permiso)) {
            header('Location: index.php?controller=dashboard&action=index');
            exit();
        }
    }

    public function listar(){
        $this->requirePermission('Gestionar Roles');
        $roles = Rol::listar();
        require_once "view/roles/index.php";
    }
    public function crear(){
        $this->requirePermission('Gestionar Roles');
        require_once "view/roles/crear.php";
    }
    public function editar(){
        $this->requirePermission('Gestionar Roles');
        $id=$_GET["id"] ?? null;
        if($id === null){
            header("Location: index.php?controller=rol&action=listar");
            return;
        }
        $rol = Rol::obtenerPorID($id);
        require_once "view/roles/editar.php";
    }
    public function actualizar(){
        $this->requirePermission('Gestionar Roles');
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
        $this->requirePermission('Gestionar Roles');
        $id=$_GET["id"];
        $rol= new Rol($id);
        $rol->eliminar();

        header("Location: index.php?controller=rol&action=listar");
    }
    public function guardar(){
        $this->requirePermission('Gestionar Roles');
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
