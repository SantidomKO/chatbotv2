<?php
require_once __DIR__ . '/../model/Rol.class.php';
require_once __DIR__ . '/../model/Usuario.class.php';

$rolUsuario = (new ReflectionClass(Rol::class))->newInstanceWithoutConstructor();
$propiedadNombre = new ReflectionProperty(Rol::class, 'nombre');
$propiedadNombre->setAccessible(true);
$propiedadNombre->setValue($rolUsuario, 'Usuario');

if (!Usuario::esRolNormal($rolUsuario)) {
    throw new RuntimeException('El rol Usuario no debería requerir 2FA obligatorio.');
}

$rolAdmin = (new ReflectionClass(Rol::class))->newInstanceWithoutConstructor();
$propiedadNombre->setValue($rolAdmin, 'Administrador');
if (Usuario::esRolNormal($rolAdmin)) {
    throw new RuntimeException('Los roles no normales sí deben requerir 2FA.');
}

if (Usuario::normalizarMetodo2FA('MAIL') !== 'email') {
    throw new RuntimeException('El método de 2FA por email no se normalizó correctamente.');
}

if (Usuario::normalizarMetodo2FA('authenticator') !== 'authenticator') {
    throw new RuntimeException('El método de 2FA por autenticador no se normalizó correctamente.');
}

if (Usuario::normalizarMetodo2FA('none') !== 'none') {
    throw new RuntimeException('El valor none debe quedar permitido para usuarios que no quieran 2FA.');
}

echo "2FA policy checks passed.\n";
