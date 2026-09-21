<?php
    $title = "Editar Usuario";
    error_log("DEBUG VISTA editar: Usuario recibido - ID: " . ($usuario ? $usuario->getId() : 'NULL') . ", Nombre: " . ($usuario ? $usuario->getNombre() : 'NULL'));
    require_once __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../layout/sidebar.php';
    require_once __DIR__ . '/../../model/Rol.class.php';
    $roles = Rol::listar();
?>
    <div class="main-content">
        <div class="form-container">
            <h1>Editar Usuario</h1>
            <!-- Debug: Usuario ID = <?= $usuario->getId() ?>, Nombre = <?= $usuario->getNombre() ?> -->
            <form action="index.php?controller=usuario&action=actualizar" method="POST">
                <div class="form-group">
                    <label>ID:</label>
                    <input type="text" name="id" value="<?= htmlspecialchars($usuario->getId()) ?>" readonly data-orig="<?= htmlspecialchars($usuario->getId()) ?>">
                </div>
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario->getNombre()) ?>" required data-orig="<?= htmlspecialchars($usuario->getNombre()) ?>">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario->getEmail()) ?>" required data-orig="<?= htmlspecialchars($usuario->getEmail()) ?>">
                </div>
                <div class="form-group">
                    <label>Rol:</label>
                    <select name="id_rol" required>
                        <option value="">Seleccione un rol</option>
                        <?php foreach($roles as $rol): ?>
                            <option value="<?= $rol->getId() ?>" <?= $usuario->getRol() && $usuario->getRol()->getId() == $rol->getId() ? 'selected' : '' ?>>
                                <?= $rol->getNombre() ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password">
                    <small>Dejar en blanco para mantener la contraseña actual</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="index.php?controller=usuario&action=listar" class="btn">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Proteger los valores originales de los inputs
    document.querySelectorAll('input[data-orig]').forEach(function(input) {
        const originalValue = input.getAttribute('data-orig');
        console.log('Input:', input.name, 'Original:', originalValue, 'Actual:', input.value);
        
        // Restaurar valor original si fue modificado
        if (input.value !== originalValue) {
            console.warn('Input modificado! Restaurando valor original.');
            input.value = originalValue;
        }
        
        // Prevenir modificaciones por JavaScript
        input.addEventListener('input', function(e) {
            if (input.value !== originalValue) {
                console.warn('Intento de modificación detectado en:', input.name);
                input.value = originalValue;
            }
        });
    });
});
</script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>