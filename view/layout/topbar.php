<?php
$controllerNow = $_GET['controller'] ?? '';
$actionNow = $_GET['action'] ?? '';
$showTopbarUser = !($controllerNow === 'perfil' && $actionNow === 'ver');
?>
<div class="topbar">
    <div class="topbar__actions">
        <?php if ($showTopbarUser): ?>
        <a href="index.php?controller=perfil&action=ver" class="topbar__user-link">
            <div class="topbar__user">
                <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
                <div>
                    <div class="topbar__user-name"><?= htmlspecialchars($nombreUsuario) ?></div>
                    <div class="topbar__user-role">Administrador</div>
                </div>
            </div>
        </a>
        <?php endif; ?>
    </div>
</div>