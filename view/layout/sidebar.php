<div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Colapsar/Expandir menú">
        <span id="toggleIcon">◀</span>
    </button>
    
    <h1>Chatbot</h1>
    <nav>
        <div class="head">
            
        </div>
        <div class="sidebar-section">
            <div class="section-toggle" onclick="toggleSection(this)">
                <h2>General</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <ul>
                <li><a href="index.php?controller=chat&action=index"><span>□</span> <span>Chat</span></a></li>
            </ul>
        </div>

        <div class="sidebar-section">
            <div class="section-toggle" onclick="toggleSection(this)">
                <h2>Administración</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <ul>
                <li><a href="index.php?controller=usuario&action=listar"><span>○</span> <span>Usuarios</span></a></li>
                <li><a href="index.php?controller=rol&action=listar"><span>◉</span> <span>Roles</span></a></li>
                <li><a href="index.php?controller=permiso&action=listar"><span>▲</span> <span>Permisos</span></a></li>
                <li><a href="index.php?controller=intent&action=listar"><span>●</span> <span>Intents</span></a></li>
            </ul>
        </div>
        
        <div class="sidebar-section">
            <div class="section-toggle" onclick="toggleSection(this)">
                <h2>Chatbot</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <ul>
                <li><a href="index.php?controller=dashboard&action=conocimiento"><span>�</span> <span>Gestión de Conocimiento</span></a></li>
                <li><a href="index.php?controller=consultas&action=index"><span>?</span> <span>Preguntas sin Respuesta</span></a></li>
            </ul>
        </div>
        
        <div class="sidebar-section">
            <div class="section-toggle" onclick="toggleSection(this)">
                <h2>Cuenta</h2>
                <span class="toggle-icon">▼</span>
            </div>
            <ul>
                <li><a href="index.php?controller=perfil&action=ver"><span>⚡</span> <span>Mi perfil</span></a></li>
                <li><a href="index.php?controller=auth&action=logout"><span>✕</span> <span>Cerrar Sesión</span></a></li>
            </ul>
        </div>
    </nav>
    <div class="sidebar-image-container">
        <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Chatbot logo"/>
    </div>
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