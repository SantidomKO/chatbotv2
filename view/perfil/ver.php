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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="form-error" role="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div style="padding: 0.85rem 1rem; margin-bottom: 1rem; color: #20c77a; border: 1px solid rgba(32, 199, 122, 0.3);" role="status">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

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

                <section style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(0, 255, 255, 0.1);" aria-labelledby="two-factor-title">
                    <h3 id="two-factor-title" style="color: #00ffff; margin-top: 0;">Verificación en dos pasos</h3>
                    <?php if ($twoFactorEnabled): ?>
                        <p>Estado: activada. Al iniciar sesión también necesitarás un código de tu aplicación autenticadora.</p>
                        <form action="index.php?controller=perfil&action=desactivar2FA" method="POST" class="auth-form" style="max-width: 440px;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label for="disable-password">Contraseña actual</label>
                                <input id="disable-password" type="password" name="password" autocomplete="current-password" required>
                            </div>
                            <div class="form-group">
                                <label for="disable-code">Código de la aplicación</label>
                                <input id="disable-code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,8}" maxlength="8" required>
                            </div>
                            <button type="submit" class="btn btn-secondary">Desactivar</button>
                        </form>
                    <?php elseif ($setup): ?>
                        <p>Escanea este código con Google Authenticator u otra aplicación TOTP. Si no puedes escanearlo, agrega la cuenta manualmente con la clave que aparece debajo.</p>
                        <div id="authenticator-qr" data-otpauth="<?= htmlspecialchars($provisioningUri, ENT_QUOTES, 'UTF-8') ?>" style="width: 220px; min-height: 220px; padding: 12px; background: #fff;"></div>
                        <p><strong>Clave de configuración:</strong> <code><?= htmlspecialchars($setup['secret']) ?></code></p>
                        <p>Tipo: basada en tiempo, 6 dígitos, intervalo de 30 segundos.</p>
                        <form action="index.php?controller=perfil&action=activar2FA" method="POST" class="auth-form" style="max-width: 440px;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label for="setup-code">Ingresa el código para confirmar</label>
                                <input id="setup-code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,8}" maxlength="8" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Confirmar y activar</button>
                        </form>
                    <?php else: ?>
                        <p>Protege tu cuenta con códigos temporales de Google Authenticator, Microsoft Authenticator, Authy u otra aplicación compatible con TOTP.</p>
                        <form action="index.php?controller=perfil&action=iniciarConfiguracion2FA" method="POST" class="auth-form" style="max-width: 440px;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label for="setup-password">Confirma tu contraseña para comenzar</label>
                                <input id="setup-password" type="password" name="password" autocomplete="current-password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Configurar aplicación autenticadora</button>
                        </form>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</div>
<?php if ($setup): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" referrerpolicy="no-referrer"></script>
    <script>
        const qrContainer = document.getElementById('authenticator-qr');
        if (qrContainer && window.QRCode) {
            new QRCode(qrContainer, {
                text: qrContainer.dataset.otpauth,
                width: 196,
                height: 196,
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    </script>
<?php endif; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>

