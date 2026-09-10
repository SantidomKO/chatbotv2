<?php
// Script de prueba para verificar la base de datos
require_once "model/Database.class.php";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Conexión exitosa a la base de datos<br>";
    
    // Verificar si existe la tabla usuarios
    $stmt = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "Tabla 'usuarios' existe<br>";
        
        // Mostrar usuarios existentes
        $stmt = $conn->query("SELECT id_usuario, nombre, email, id_rol FROM usuarios");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($usuarios) > 0) {
            echo "Usuarios encontrados:<br>";
            foreach ($usuarios as $usuario) {
                echo "ID: {$usuario['id_usuario']}, Nombre: {$usuario['nombre']}, Email: {$usuario['email']}, Rol ID: {$usuario['id_rol']}<br>";
            }
        } else {
            echo "No hay usuarios en la base de datos<br>";
        }
        
        // Verificar tabla roles
        $stmt = $conn->query("SHOW TABLES LIKE 'roles'");
        if ($stmt->rowCount() > 0) {
            echo "Tabla 'roles' existe<br>";
            $stmt = $conn->query("SELECT id_rol, nombre FROM roles");
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Roles encontrados:<br>";
            foreach ($roles as $rol) {
                echo "ID: {$rol['id_rol']}, Nombre: {$rol['nombre']}<br>";
            }
        } else {
            echo "Tabla 'roles' no existe<br>";
        }
        
    } else {
        echo "Tabla 'usuarios' no existe<br>";
        echo "Tablas en la base de datos:<br>";
        $stmt = $conn->query("SHOW TABLES");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tablas as $tabla) {
            echo "- $tabla<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>