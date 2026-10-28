<?php

require_once __DIR__ . '/../app_config.php';

class Database
{
    private static $instancia=null;

    private $conexion;

    public function __construct()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                app_config('DB_HOST', 'localhost'),
                app_config('DB_PORT', '3306'),
                app_config('DB_NAME', 'chatbot_v2')
            );
            $this->conexion = new PDO(
                $dsn,
                app_config('DB_USER', 'root'),
                app_config('DB_PASSWORD', '')
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexion:".$e->getMessage());
        }
    }

    public static function getInstance(){
        if(!self::$instancia){
            self::$instancia=new Database();
        }
        return self::$instancia;
    }

    public function getConnection(){
        return $this->conexion;
    }
}

?>