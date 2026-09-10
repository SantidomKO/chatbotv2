<?php

include_once "Database.class.php";
include_once "Rol.class.php";

class Usuario
{
    private $id;
    private $nombre;
    private $email;
    private $password;
    private ?Rol $rol;
    private $conexion;


    public function __construct($id=null, $nombre=null, $email=null, $password=null, $rol=null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
        $this->rol = $rol;
        $this->conexion = Database::getInstance()->GetConnection();
    }

    public function guardar(){

        // Armamos la consulta SQL y guardamos la contraseña hasheada
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $sql="INSERT INTO usuarios (`nombre`, `email`, `password`, `id_rol`) VALUES (?, ?, ?, ?)";
        $stmt=$this->conexion->prepare($sql);
        $rolId = $this->rol ? $this->rol->getId() : null;
        $result = $stmt->execute([$this->nombre, $this->email, $hashedPassword, $rolId]);
        return $result ? $this->conexion->lastInsertId() : false;
    }

    public function actualizar(){
        if($this->password === null || $this->password === ""){
            $sql="UPDATE usuarios SET `nombre`=?, `email`=?, `id_rol`=? WHERE `id_usuario`=?";
            $stmt=$this->conexion->prepare($sql);
            $rolId = $this->rol ? $this->rol->getId() : null;
            return $stmt->execute([$this->nombre, $this->email, $rolId, $this->id]);
        }

        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $sql="UPDATE usuarios SET `nombre`=?, `email`=?, `password`=? WHERE `id_usuario`=?";
        $stmt=$this->conexion->prepare($sql);
        return $stmt->execute([$this->nombre, $this->email, $hashedPassword, $this->id]);
    }
    public function eliminar(){
        //Armamos la consulta SQL
        $sql="DELETE FROM usuarios WHERE `id_usuario`=?";
        $stmt=$this->conexion->prepare($sql);
        return $stmt->execute([$this->id]);
    }

    public static function listar(){
        $conexion=Database::getInstance()->getConnection();
        $sql= "SELECT * FROM usuarios";
        $stmt = $conexion->query($sql);
        $usuarios=[];
        while ($fila=$stmt->fetch(PDO::FETCH_ASSOC)){
            $rol = $fila["id_rol"] ? Rol::obtenerPorID($fila["id_rol"]) : null;
            $usuarios[]= new Usuario($fila["id_usuario"], $fila["nombre"], $fila["email"], null, $rol);
        }
        return $usuarios;
    }

    public static function obtenerPorID($id){
        $conexion=Database::getInstance()->getConnection();
        $sql= "SELECT * FROM usuarios WHERE id_usuario=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $resultado= $stmt->fetch(PDO::FETCH_ASSOC);
        if($resultado){
            $rol = $resultado['id_rol'] ? Rol::obtenerPorID($resultado['id_rol']) : null;
            return new Usuario ($resultado['id_usuario'],$resultado['nombre'],$resultado['email'],$resultado['password'], $rol);
        }
        return null;
    }

    public static function obtenerPorEmail($email){
        $conexion=Database::getInstance()->getConnection();
        $sql= "SELECT * FROM usuarios WHERE email=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$email]);
        $resultado= $stmt->fetch(PDO::FETCH_ASSOC);
        if($resultado){
            $rol = $resultado['id_rol'] ? Rol::obtenerPorID($resultado['id_rol']) : null;
            return new Usuario ($resultado['id_usuario'],$resultado['nombre'],$resultado['email'],$resultado['password'], $rol);
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
    public function setEmail($email)
    {
        if($email==null || $email == ""){
            echo "El email no puede estar vacio";
            return;
        }
        $this->email = $email;
    }
    public function setPassword($password)
    {
        if($password==null || $password == ""){
            echo "La contrasena no puede estar vacia";
            return;
        }
        $this->password = $password;
    }
    public function setRol($rol)
    {
        if($rol==null || $rol == ""){
            echo "El rol no puede estar vacio";
            return;
        }
        $this->rol = $rol;
    }
    public static function verificarLogin($email, $passwordIngresada){
        $email= filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        $usuario = self::obtenerPorEmail($email);
        if($usuario && password_verify($passwordIngresada, $usuario->getPassword())){
            return $usuario;
        }
        return null;
    }

    //Getters
    public function getId() {return $this->id;}
    public function getNombre() {return $this->nombre;}
    public function getEmail() {return $this->email;}
    public function getRol() {return $this->rol;}
    public function getPassword() {return $this->password;}
    public function getRolNombre() {
        return $this->rol ? $this->rol->getNombre() : null;
    }
}
?>