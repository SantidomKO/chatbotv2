<?php 
    $title = "Login";
    require_once __DIR__ . '/../../app_config.php';
    $debug2FACodeEnabled = filter_var(
        app_config('DEBUG_2FA_CODE', 'false'),
        FILTER_VALIDATE_BOOLEAN
    ) || in_array(
        strtolower((string) ($_SERVER['REMOTE_ADDR'] ?? '')),
        ['127.0.0.1', '::1', '::ffff:127.0.0.1'],
        true
    );
    require_once __DIR__ . '/../layout/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Chatbot</h1>
            <p><?= isset($_SESSION['pending_2fa']) ? 'Verifica tu identidad' : 'Inicia sesión para continuar' ?></p>
        </div>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="form-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: rgba(32, 199, 122, 0.1); color: #20c77a; border: 1px solid rgba(32, 199, 122, 0.3); border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['pending_2fa'])): ?>
            <?php if (
                ($_SESSION['pending_2fa']['method'] ?? '') === 'email'
                && $debug2FACodeEnabled
            ): ?>
                <script>
                    console.warn(
                        'Local testing only — 2FA email code (expires in 5 minutes):',
                        <?= json_encode(
                            (string) ($_SESSION['pending_2fa']['debug_code'] ?? ''),
                            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                        ) ?>
                    );
                </script>
            <?php endif; ?>
            <form action="index.php?controller=auth&action=verificarSegundoFactor" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="form-group">
                    <label for="authenticator-code">
                        <?= (($_SESSION['pending_2fa']['method'] ?? 'authenticator') === 'email') ? 'Código recibido por email' : 'Código de la aplicación' ?>
                    </label>
                    <input id="authenticator-code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,8}" maxlength="8" required autofocus>
                </div>
                <?php if ((($_SESSION['pending_2fa']['method'] ?? 'authenticator') === 'email') && !empty($_SESSION['pending_2fa']['mail_sent']) === false): ?>
                    <div style="margin-top: 0.75rem; padding: 0.75rem 0.9rem; border: 1px solid rgba(255, 204, 0, 0.5); background: rgba(255, 204, 0, 0.08); color: #ffd75e; border-radius: 10px; font-size: 0.9rem;">
                        Código de prueba (entorno local): <strong><?= htmlspecialchars((string) ($_SESSION['pending_2fa']['debug_code'] ?? '')) ?></strong>
                    </div>
                <?php endif; ?>
                <div class="form-actions auth-actions">
                    <button type="submit" class="btn btn-primary">Verificar código</button>
                </div>
            </form>
            <form action="index.php?controller=auth&action=cancelarSegundoFactor" method="POST" class="auth-links">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" class="btn btn-secondary">Cancelar e iniciar de nuevo</button>
            </form>
        <?php else: ?>
            <form action="index.php?controller=auth&action=validar" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-actions auth-actions">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>
            <div class="auth-links">
                <a href="index.php?controller=auth&action=registrar">¿No tienes cuenta? Regístrate</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>