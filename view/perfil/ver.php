<?php
    $title = "Mi Perfil";
    require_once __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../layout/sidebar.php';

    $usuarioData = $_SESSION['usuario_data'] ?? null;
    $nombreUsuario = $usuarioData['nombre'] ?? 'Usuario';
    $inicial = strtoupper(substr($nombreUsuario, 0, 1));
    $email = $usuarioData['email'] ?? '';
    $role = $usuarioData['rol'] ?? '';
    $usuarioId = $usuarioData['id'] ?? '';
?>
<div class="main-content">
    <div class="panel-container">
        <div class="header-section">
            <div class="header-left">
                <h1>Mi Perfil</h1>
                <span class="object-count">Información de tu cuenta</span>
            </div>
            <div class="header-right">
                <a class="btn btn-primary" href="index.php?controller=usuario&action=editar&id=<?= urlencode($usuarioId) ?>">Editar Perfil</a>
            </div>
        </div>

        <div class="table-container">
            <div style="padding: 2rem;">
                <div style="display: flex; align-items: center; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(0, 255, 255, 0.1);">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #00ffff, #ff00ff); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: #111318; margin-right: 1.5rem;">
                        <?= htmlspecialchars($inicial) ?>
                    </div>
                    <div>
                        <h2 style="color: #00ffff; margin: 0 0 0.5rem 0;"><?= htmlspecialchars($nombreUsuario) ?></h2>
                        <p style="color: #b0b0b0; margin: 0;"><?= htmlspecialchars($role) ?></p>
                    </div>
                </div>

                <div style="background: rgba(17, 19, 24, 0.5); border: 1px solid rgba(0, 255, 255, 0.15); border-radius: 12px; padding: 1.5rem;">
                    <h3 style="color: #00ffff; margin: 0 0 1.5rem 0;">Información de la Cuenta</h3>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div style="color: #00ffff; font-weight: bold;">Nombre:</div>
                        <div style="color: #e0e0e0;"><?= htmlspecialchars($nombreUsuario) ?></div>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div style="color: #00ffff; font-weight: bold;">Email:</div>
                        <div style="color: #e0e0e0;"><?= htmlspecialchars($email) ?></div>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <div style="color: #00ffff; font-weight: bold;">Rol:</div>
                        <div style="color: #e0e0e0;"><?= htmlspecialchars($role) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>

