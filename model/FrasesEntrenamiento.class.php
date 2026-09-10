<?php
include_once "Database.class.php";
include_once "Intent.class.php";
class FraseEntrenamiento
{
    private $id;
    private $intent;
    private $texto_ejemplo;
    private $conexion;


    public function __construct($id=null, $intent=null, $texto_ejemplo=null)
    {
        $this->id = $id;
        $this->intent = $intent;
        $this->texto_ejemplo = $texto_ejemplo;
        $this->conexion = Database::getInstance()->GetConnection();
    }

    public function guardar(){
        //Armamos la consulta SQL
        $sql="INSERT INTO frases_entrenamiento (`intent_id`, `texto_ejemplo`) VALUES (?,?)";
        $stmt=$this->conexion->prepare($sql);
        if ($stmt->execute([$this->intent->getId(), $this->texto_ejemplo])) {
            $this->id = $this->conexion->lastInsertId();
            return true;
        }
        return false;
    }

    public function actualizar(){
        //Armamos la consulta SQL
        $sql="UPDATE frases_entrenamiento SET `intent_id`=?, `texto_ejemplo`=? WHERE `id`=?";
        $stmt=$this->conexion->prepare($sql);
        return $stmt->execute([$this->intent->getId(), $this->texto_ejemplo, $this->id]);
    }
    public function eliminar(){
        //Armamos la consulta SQL
        $sql="DELETE FROM frases_entrenamiento WHERE `id`=?";
        $stmt=$this->conexion->prepare($sql);
        return $stmt->execute([$this->id]);
    }

    public static function listar(){
        $conexion=Database::getInstance()->getConnection();
        $sql= "SELECT * FROM frases_entrenamiento";
        $stmt = $conexion->query($sql);
        $frases=[];
        while ($fila=$stmt->fetch(PDO::FETCH_ASSOC)){
            $frases[]= new FraseEntrenamiento($fila["id"], Intent::obtenerPorID($fila["intent_id"]), $fila["texto_ejemplo"]);
        }
        return $frases;
    }

    public static function obtenerPorID($id){
        $conexion=Database::getInstance()->getConnection();
        $sql= "SELECT * FROM frases_entrenamiento WHERE `id`=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $resultado= $stmt->fetch(PDO::FETCH_ASSOC);
        if($resultado){
            return new FraseEntrenamiento($resultado["id"], Intent::obtenerPorID($resultado["intent_id"]), $resultado["texto_ejemplo"]);
        }
        return null;
    }

    //Setters
    public function setIntent($intent)
    {
        $this->intent = $intent;
    }
    public function setTextoEjemplo($texto_ejemplo)
    {
        if($texto_ejemplo==null || $texto_ejemplo == ""){
            echo "El texto de ejemplo no puede estar vacio";
            return;
        }
        $this->texto_ejemplo = $texto_ejemplo;
    }

    //Getters
    public function getId() {return $this->id;}
    public function getIntent() {return $this->intent;}
    public function getTextoEjemplo() {return $this->texto_ejemplo;}
}
?>