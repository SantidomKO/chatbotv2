<?php
include_once "Database.class.php";
include_once "Permiso.class.php";

class RolPermiso
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = Database::getInstance()->getConnection();
    }

    public function guardar($id_rol, $idPermiso)
    {
        $sql = "INSERT INTO roles_permisos (id_rol, id_permisos) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id_rol, (int)$idPermiso]);
    }

    public function eliminarPorRol($id_rol)
    {
        $sql = "DELETE FROM roles_permisos WHERE id_rol=?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id_rol]);
    }

    public function obtenerPermisosPorRol($id_rol)
    {
        $sql = "SELECT p.id_permiso, p.nombre, p.descripcion
                FROM permisos p
                INNER JOIN roles_permisos rp ON p.id_permiso = rp.id_permisos
                WHERE rp.id_rol = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id_rol]);

        $permisos = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permisos[] = new Permiso($fila['id_permiso'], $fila['nombre'], $fila['descripcion']);
        }

        return $permisos;
    }

    public function obtenerIdsPermisosPorRol($id_rol)
    {
        $sql = "SELECT id_permisos FROM roles_permisos WHERE id_rol = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id_rol]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>