<?php
$pdo = new PDO('mysql:host=localhost;dbname=chatbot_v2', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Verificación del usuario ID 5 ===\n\n";

// Verificar usuario ID 5
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([5]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "Usuario ID 5 encontrado:\n";
    echo "ID: " . $usuario['id_usuario'] . "\n";
    echo "Nombre: " . $usuario['nombre'] . "\n";
    echo "Email: " . $usuario['email'] . "\n";
    echo "Rol ID: " . $usuario['id_rol'] . "\n";
} else {
    echo "Usuario ID 5 NO encontrado\n";
}

echo "\n=== Verificación de todos los usuarios ===\n";
$stmt = $pdo->query("SELECT id_usuario, nombre, email FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($usuarios as $u) {
    echo "ID: " . $u['id_usuario'] . ", Nombre: " . $u['nombre'] . ", Email: " . $u['email'] . "\n";
}
