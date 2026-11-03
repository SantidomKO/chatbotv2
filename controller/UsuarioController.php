<?php
require_once "model/Usuario.class.php";
require_once "model/Rol.class.php";

class UsuarioController{
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
        $this->requirePermission('Gestionar Usuarios');
        $usuarios = Usuario::listar();
        require_once "view/usuarios/index.php";
    }
    public function crear(){
        $this->requirePermission('Gestionar Usuarios');
        require_once "view/usuarios/crear.php";
    }
    public function editar(){
        $this->requirePermission('Gestionar Usuarios');
        $idUsuario=$_GET["id"] ?? null;
        error_log("DEBUG editar: ID recibido: " . var_export($idUsuario, true));
        if($idUsuario === null){
            header("Location: index.php?controller=usuario&action=listar");
            return;
        }
        $usuario = Usuario::obtenerPorID($idUsuario);
        error_log("DEBUG editar: Usuario obtenido - ID: " . ($usuario ? $usuario->getId() : 'NULL') . ", Nombre: " . ($usuario ? $usuario->getNombre() : 'NULL'));
        error_log("DEBUG editar: Object ID: " . spl_object_hash($usuario));
        require_once "view/usuarios/editar.php";
    }
    public function actualizar(){
        $this->requirePermission('Gestionar Usuarios');
        $idUsuario=$_POST["id"];
        $nombre=$_POST["nombre"];
        $email=$_POST["email"];
        $password=$_POST["password"];
        $id_rol = $_POST["id_rol"] ?? null;
        $twoFactorMethod = Usuario::normalizarMetodo2FA($_POST['two_factor_method'] ?? 'none');

        $rol = $id_rol ? Rol::obtenerPorID($id_rol) : null;
        $usuario= new Usuario($idUsuario, $nombre, $email, $password, $rol);
        $usuario->actualizar();
        $usuario->setId((int) $idUsuario);
        if ($twoFactorMethod !== 'none' || Usuario::obtenerMetodo2FA((int) $idUsuario) !== 'none') {
            $usuario->guardarMetodo2FA($twoFactorMethod === 'none' ? Usuario::obtenerMetodo2FA((int) $idUsuario) : $twoFactorMethod);
        }

        header("Location: index.php?controller=usuario&action=listar");
    }
    public function eliminar(){
        $this->requirePermission('Gestionar Usuarios');
        $idUsuario=$_GET["id"];
        $usuario= new Usuario($idUsuario);
        $usuario->eliminar();

        header("Location: index.php?controller=usuario&action=listar");
    }
    public function guardar(){
        $this->requirePermission('Gestionar Usuarios');
        $nombre=$_POST["nombre"];
        $email=$_POST["email"];
        $password=$_POST["password"];
        $id_rol = $_POST["id_rol"] ?? null;
        $twoFactorMethod = Usuario::normalizarMetodo2FA($_POST['two_factor_method'] ?? 'none');

        $rol = $id_rol ? Rol::obtenerPorID($id_rol) : null;
        $usuario= new Usuario(null, $nombre, $email, $password, $rol);
        $usuarioId = $usuario->guardar();
        if ($usuarioId) {
            $usuario->setId((int) $usuarioId);
            if ($twoFactorMethod !== 'none') {
                $usuario->guardarMetodo2FA($twoFactorMethod);
            }
        }

        header("Location: index.php?controller=usuario&action=listar");
    }

}
