<?php 
    $title = "Login";
    require_once __DIR__ . '/../layout/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Chatbot</h1>
            <p>Inicia sesión para continuar</p>
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
        <form action="index.php?controller=auth&action=validar" method="POST" class="auth-form">
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
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>