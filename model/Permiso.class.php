<?php
include_once "Database.class.php";
class Permiso
{
    private $id;
    private $nombre;
    private $descripcion;
    private $conexion;


    public function __construct($id=null, $nombre=null, $descripcion=null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->conexion = Database::getInstance()->GetConnection();
    }

    public function guardar(){
        //Armamos la consulta SQL
        $sql="INSERT INTO permisos (`nombre`, `descripcion`) VALUES (?,?)";
        $stmt=$this->conexion->prepare($sql);
        $result = $stmt->execute([$this->nombre, $this->descripcion]);
        return $result ? $this->conexion->lastInsertId() : false;
    }

    public function actualizar(){
        //Armamos la consulta SQL
        $sql="UPDATE permisos SET `nombre`=?, `descripcion`=? WHERE `id_permiso`=?";
        $stmt=$this->conexion->prepare($sql);
        return $stmt->execute([$this->nombre, $this->descripcion, $this->id]);
    }
    public function eliminar(){
        //Armamos la consulta SQL
        $sql="DELETE FROM permisos WHERE `id_permiso`=?";
        $stmt=$this->conexion->prepare($sql);
        return $stmt->execute([$this->id]);
    }

    public static function listar(){
        $conexion=Database::getInstance()->getConnection();
        $sql= "SELECT * FROM permisos";
        $stmt = $conexion->query($sql);
        $permisos=[];
        while ($fila=$stmt->fetch(PDO::FETCH_ASSOC)){
            $permisos[]= new Permiso($fila["id_permiso"], $fila["nombre"], $fila["descripcion"]);

        }
        return $permisos;
    }

    public static function obtenerPorID($id){
        $conexion=Database::getInstance()->getConnection();
        $sql= "SELECT * FROM permisos WHERE `id_permiso`=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $resultado= $stmt->fetch(PDO::FETCH_ASSOC);
        if($resultado){
            return new Permiso ($resultado['id_permiso'],$resultado['nombre'],$resultado['descripcion']);
        }
        return null;
    }

    //Setters
    public function setNombre($nombre)
    {
        if($nombre==null || $nombre == ""){
            echo "El nombre no puede estar vacio";
            return;
        }
        $this->nombre = $nombre;
    }
    public function setDescripcion($descripcion)
    {
        if($descripcion==null || $descripcion == ""){
            echo "La descripcion no puede estar vacia";
            return;
        }
        $this->descripcion = $descripcion;
    }

    //Getters
    public function getId() {return $this->id;}
    public function getNombre() {return $this->nombre;}
    public function getDescripcion() {return $this->descripcion;}
}
?>