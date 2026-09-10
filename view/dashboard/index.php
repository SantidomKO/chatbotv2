<?php
// Dashboard simplificado - redirige a la vista de usuarios o muestra un mensaje simple
include_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="main-content">
    <div class="panel-container">
        <div class="header-section">
            <div class="header-left">
                <h1>Panel de Control</h1>
                <p>Selecciona una opción del menú lateral para comenzar</p>
            </div>
        </div>
        
        <div class="table-container">
            <div style="text-align: center; padding: 2rem;">
                <h2 style="color: #00ffff; margin-bottom: 1rem;">Bienvenido al Sistema</h2>
                <p style="color: #b0b0b0;">Utiliza el menú lateral para navegar entre las diferentes secciones:</p>
                <ul style="color: #b0b0b0; text-align: left; display: inline-block; margin-top: 1rem;">
                    <li style="margin: 0.5rem 0;">👥 <strong>Usuarios:</strong> Gestión de usuarios del sistema</li>
                    <li style="margin: 0.5rem 0;">🔐 <strong>Roles:</strong> Configuración de roles y permisos</li>
                    <li style="margin: 0.5rem 0;">🎯 <strong>Intents:</strong> Gestión de intents del chatbot</li>
                    <li style="margin: 0.5rem 0;">📚 <strong>Conocimiento:</strong> Base de conocimiento del chatbot</li>
                    <li style="margin: 0.5rem 0;">👤 <strong>Mi Perfil:</strong> Información de tu cuenta</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>