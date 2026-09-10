<?php
include_once "Database.class.php";
class Rol
{
    private $id;
    private $nombre;
    private $descripcion;
    private $conexion;


    public function __construct($id=null, $nombre=null, $descripcion=null)
    {
        #Asignamos los valores a las propiedades
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->conexion = Database::getInstance()->GetConnection();
    }

    public function guardar(){

        //Armamos la consulta SQL
        $sql="INSERT INTO roles (`nombre`, `descripcion`) VALUES (?,?)";
        $stmt=$this->conexion->prepare($sql);
        $result = $stmt->execute([$this->nombre, $this->descripcion]);
        return $result ? $this->conexion->lastInsertId() : false;
    }

    public function actualizar(){
        //Armamos la consulta SQL
        $sql="UPDATE roles SET `nombre`=?, `descripcion`=? WHERE `id_rol`=?";
        $stmt=$this->conexion->prepare($sql);
        return $stmt->execute([$this->nombre, $this->descripcion, $this->id]);
    }
    public function eliminar(){
        //Armamos la consulta SQL
        $sql="DELETE FROM roles WHERE `id_rol`=?";
        $stmt=$this->conexion->prepare($sql);
        return $stmt->execute([$this->id]);
    }

    public static function listar(){
        $conexion=Database::getInstance()->getConnection();
        $sql= "SELECT * FROM roles";
        $stmt = $conexion->query($sql);
        $roles=[];
        while ($fila=$stmt->fetch(PDO::FETCH_ASSOC)){
            $roles[]= new Rol($fila["id_rol"], $fila["nombre"], $fila["descripcion"]);
        }
        return $roles;
    }

    public static function obtenerPorID($id){
        $conexion=Database::getInstance()->getConnection();

        $sql = "SELECT * FROM roles WHERE `id_rol`=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if($resultado){
            return new Rol($resultado['id_rol'], $resultado['nombre'], $resultado['descripcion']);
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