<?php
// $mode: 'crear'|'editar'
// $permiso: Permiso object when editing
?>
<div class="dashboard-panel dashboard-panel--form">
    <div class="dashboard-panel__head">
        <h2><?= $mode === 'crear' ? 'Crear Permiso' : 'Editar Permiso' ?></h2>
    </div>
    <div class="dashboard-panel__body">
        <form action="index.php?controller=permiso&action=<?= $mode === 'crear' ? 'guardar' : 'actualizar' ?>" method="POST">
            <?php if ($mode === 'editar' && $permiso): ?>
                <label>ID:</label>
                <input type="text" name="id" value="<?= htmlspecialchars($permiso->getId()) ?>" readonly>
            <?php endif; ?>
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?= $mode === 'editar' && $permiso ? htmlspecialchars($permiso->getNombre()) : '' ?>" required>

            <label>Descripción:</label>
            <input type="text" name="descripcion" value="<?= $mode === 'editar' && $permiso ? htmlspecialchars($permiso->getDescripcion()) : '' ?>">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $mode === 'crear' ? 'Guardar' : 'Actualizar' ?></button>
                <a href="index.php?controller=dashboard&action=index&section=permisos" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>