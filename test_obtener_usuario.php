<?php
require_once "model/Usuario.class.php";

echo "=== Test de Usuario::obtenerPorID() ===\n\n";

// Test con ID 5
$usuario5 = Usuario::obtenerPorID(5);
if ($usuario5) {
    echo "Usuario ID 5 obtenido correctamente:\n";
    echo "ID: " . $usuario5->getId() . "\n";
    echo "Nombre: " . $usuario5->getNombre() . "\n";
    echo "Email: " . $usuario5->getEmail() . "\n";
} else {
    echo "Usuario ID 5 NO se pudo obtener\n";
}

echo "\n=== Test con ID 2 ===\n";
$usuario2 = Usuario::obtenerPorID(2);
if ($usuario2) {
    echo "Usuario ID 2 obtenido:\n";
    echo "ID: " . $usuario2->getId() . "\n";
    echo "Nombre: " . $usuario2->getNombre() . "\n";
    echo "Email: " . $usuario2->getEmail() . "\n";
} else {
    echo "Usuario ID 2 NO se pudo obtener\n";
}
