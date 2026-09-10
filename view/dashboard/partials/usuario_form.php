<?php
// $mode: 'crear'|'editar'
// $usuario: Usuario object when editing
?>
<div class="dashboard-panel dashboard-panel--form">
    <div class="dashboard-panel__head">
        <h2><?= $mode === 'crear' ? 'Crear Usuario' : 'Editar Usuario' ?></h2>
    </div>
    <div class="dashboard-panel__body">
        <form action="index.php?controller=usuario&action=<?= $mode === 'crear' ? 'guardar' : 'actualizar' ?>" method="POST">
            <?php if ($mode === 'editar' && $usuario): ?>
                <label>ID:</label>
                <input type="text" name="id" value="<?= htmlspecialchars($usuario->getId()) ?>" readonly>
            <?php endif; ?>
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?= $mode === 'editar' && $usuario ? htmlspecialchars($usuario->getNombre()) : '' ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= $mode === 'editar' && $usuario ? htmlspecialchars($usuario->getEmail()) : '' ?>" required>

            <label>Password:</label>
            <input type="password" name="password" <?= $mode === 'crear' ? 'required' : '' ?> >

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $mode === 'crear' ? 'Guardar' : 'Actualizar' ?></button>
                <a href="index.php?controller=dashboard&action=index&section=usuarios" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>