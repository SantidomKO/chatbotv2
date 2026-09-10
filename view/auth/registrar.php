<?php 
    $title = "Registro";
    require_once __DIR__ . '/../layout/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Registro</h1>
            <p>Crea una cuenta nueva</p>
        </div>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="form-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form action="index.php?controller=auth&action=guardarRegistro" method="POST" class="auth-form">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required minlength="4">
            </div>
            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <input type="password" name="confirm_password" required minlength="4">
            </div>
            <div class="form-actions auth-actions">
                <button type="submit" class="btn btn-primary">Registrarse</button>
            </div>
        </form>
        <div class="auth-links">
            <a href="index.php?controller=auth&action=login">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>