<?php
require_once __DIR__ . '/../../model/Usuario.class.php';
$usuarioData = isset($_SESSION['usuario_data']) ? $_SESSION['usuario_data'] : null;
$usuarioId = $usuarioData['id'] ?? null;

$usuarioSesion = null;
if ($usuarioId) {
    $usuarioSesion = Usuario::obtenerPorID($usuarioId);
}

$puedeVerConocimiento = $usuarioSesion && $usuarioSesion->tienePermiso('Gestionar Conocimiento');
$puedeVerPreguntas = $usuarioSesion && $usuarioSesion->tienePermiso('Gestionar Preguntas Sin Respuesta');
$puedeVerUsuarios = $usuarioSesion && $usuarioSesion->tienePermiso('Gestionar Usuarios');
$puedeVerRoles = $usuarioSesion && $usuarioSesion->tienePermiso('Gestionar Roles');
$puedeVerPermisos = $usuarioSesion && $usuarioSesion->tienePermiso('Gestionar Permisos');
$puedeVerIntents = $usuarioSesion && $usuarioSesion->tienePermiso('Gestionar Intents');
$tienePermisosAdmin = $puedeVerConocimiento || $puedeVerPreguntas || $puedeVerUsuarios || $puedeVerRoles || $puedeVerPermisos || $puedeVerIntents;
?>

<div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Colapsar/Expandir menú">
        <span id="toggleIcon">◀</span>
    </button>

    <h1>A.U.R.A</h1>
    <nav>
        <div class="head">

        </div>
        <div class="sidebar-section">
            <div class="section-toggle" onclick="toggleSection(this)">
                <h2>General</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <ul>
                <li><a href="index.php?controller=chat&action=index"><img src="<?php echo BASE_URL; ?>assets/icons/message-circle.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Chat</span></a></li>
            </ul>
        </div>

        <?php if ($tienePermisosAdmin): ?>
        <div class="sidebar-section">
            <div class="section-toggle" onclick="toggleSection(this)">
                <h2>Administración</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <ul>
                <?php if ($puedeVerConocimiento): ?>
                <li><a href="index.php?controller=dashboard&action=conocimiento"><img src="<?php echo BASE_URL; ?>assets/icons/database.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Gestión de Conocimiento</span></a></li>
                <?php endif; ?>
                <?php if ($puedeVerPreguntas): ?>
                <li><a href="index.php?controller=consultas&action=index"><img src="<?php echo BASE_URL; ?>assets/icons/file-question-alt.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Preguntas sin Respuesta</span></a></li>
                <?php endif; ?>
                <?php if ($puedeVerUsuarios): ?>
                <li><a href="index.php?controller=usuario&action=listar"><img src="<?php echo BASE_URL; ?>assets/icons/users.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Usuarios</span></a></li>
                <?php endif; ?>
                <?php if ($puedeVerRoles): ?>
                <li><a href="index.php?controller=rol&action=listar"><img src="<?php echo BASE_URL; ?>assets/icons/briefcase.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Roles</span></a></li>
                <?php endif; ?>
                <?php if ($puedeVerPermisos): ?>
                <li><a href="index.php?controller=permiso&action=listar"><img src="<?php echo BASE_URL; ?>assets/icons/check.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Permisos</span></a></li>
                <?php endif; ?>
                <?php if ($puedeVerIntents): ?>
                <li><a href="index.php?controller=intent&action=listar"><img src="<?php echo BASE_URL; ?>assets/icons/database.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Intents</span></a></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="sidebar-section">
            <div class="section-toggle" onclick="toggleSection(this)">
                <h2>Cuenta</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <ul>
                <li><a href="index.php?controller=perfil&action=ver"><img src="<?php echo BASE_URL; ?>assets/icons/user-circle.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Mi perfil</span></a></li>
                <li><a href="index.php?controller=auth&action=logout"><img src="<?php echo BASE_URL; ?>assets/icons/sign-out-alt.svg" alt="" aria-hidden="true" class="sidebar-icon"> <span>Cerrar Sesión</span></a></li>
            </ul>
        </div>
    </nav>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('toggleIcon');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('collapsed');
    
    if (sidebar.classList.contains('collapsed')) {
        toggleIcon.textContent = '▶';
        mainContent.style.marginLeft = '50px';
        mainContent.style.width = 'calc(100% - 50px)';
    } else {
        toggleIcon.textContent = '◀';
        mainContent.style.marginLeft = '200px';
        mainContent.style.width = 'calc(100% - 200px)';
    }
}

function toggleSection(element) {
    const section = element.parentElement;
    const ul = section.querySelector('ul');
    const toggleIcon = element.querySelector('.toggle-icon');
    
    element.classList.toggle('collapsed');
    ul.classList.toggle('collapsed');
}

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarScrollKey = 'chatbot-sidebar-scroll-top';

    if (!sidebar) return;

    const savedScrollTop = sessionStorage.getItem(sidebarScrollKey);
    if (savedScrollTop !== null) {
        sidebar.scrollTop = Number(savedScrollTop);
    }

    function saveSidebarScroll() {
        sessionStorage.setItem(sidebarScrollKey, String(sidebar.scrollTop));
    }

    sidebar.addEventListener('scroll', saveSidebarScroll, { passive: true });
    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', saveSidebarScroll);
    });
});
</script>