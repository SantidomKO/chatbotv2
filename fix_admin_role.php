<?php
$pdo = new PDO('mysql:host=localhost;dbname=chatbot_v2', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Corrigiendo rol del usuario administrador ===\n\n";

// Actualizar el usuario admin para que tenga el rol correcto (ID 1)
$stmt = $pdo->prepare("UPDATE usuarios SET id_rol = 1 WHERE email = 'admin@epet3.edu.ar'");
$stmt->execute();

echo "Usuario admin@epet3.edu.ar actualizado a rol ID 1\n\n";

// Verificar
$stmt = $pdo->query("SELECT * FROM usuarios WHERE email = 'admin@epet3.edu.ar'");
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Usuario actualizado:\n";
echo "ID: " . $usuario['id_usuario'] . "\n";
echo "Nombre: " . $usuario['nombre'] . "\n";
echo "Rol ID: " . $usuario['id_rol'] . "\n\n";

// Asignar todos los permisos al rol 1
echo "Asignando todos los permisos al rol 1...\n";
$stmt = $pdo->query("SELECT id_permiso FROM permisos");
$all_permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($all_permisos as $id_permiso) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO roles_permisos (id_rol, id_permisos) VALUES (:id_rol, :id_permiso)');
    $stmt->execute(['id_rol' => 1, 'id_permiso' => $id_permiso]);
}

echo "Todos los permisos asignados al rol Administrador (ID 1)\n";
