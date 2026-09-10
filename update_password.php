<?php
// Script para actualizar la contraseña del usuario admin
require_once "model/Database.class.php";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar el hash actual del usuario admin
    $stmt = $conn->prepare("SELECT id_usuario, nombre, email, password FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@epet3.edu.ar']);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "Usuario encontrado:<br>";
        echo "ID: {$usuario['id_usuario']}<br>";
        echo "Nombre: {$usuario['nombre']}<br>";
        echo "Email: {$usuario['email']}<br>";
        echo "Hash actual: {$usuario['password']}<br>";
        
        // Verificar si "admin" coincide con el hash actual
        if (password_verify('admin', $usuario['password'])) {
            echo "La contraseña 'admin' coincide con el hash actual<br>";
        } else {
            echo "La contraseña 'admin' NO coincide con el hash actual<br>";
            echo "Voy a actualizar la contraseña a 'admin'<br>";
            
            // Generar nuevo hash
            $nuevoHash = password_hash('admin', PASSWORD_DEFAULT);
            echo "Nuevo hash: $nuevoHash<br>";
            
            // Actualizar en la base de datos
            $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
            $stmt->execute([$nuevoHash, 'admin@epet3.edu.ar']);
            
            echo "Contraseña actualizada exitosamente<br>";
        }
    } else {
        echo "Usuario admin@epet3.edu.ar no encontrado<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>