<?php
// $mode: 'crear'|'editar'
// $rol: Rol object when editing
?>
<div class="dashboard-panel dashboard-panel--form">
    <div class="dashboard-panel__head">
        <h2><?= $mode === 'crear' ? 'Crear Rol' : 'Editar Rol' ?></h2>
    </div>
    <div class="dashboard-panel__body">
        <form action="index.php?controller=rol&action=<?= $mode === 'crear' ? 'guardar' : 'actualizar' ?>" method="POST">
            <?php if ($mode === 'editar' && $rol): ?>
                <label>ID:</label>
                <input type="text" name="id" value="<?= htmlspecialchars($rol->getId()) ?>" readonly>
            <?php endif; ?>
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?= $mode === 'editar' && $rol ? htmlspecialchars($rol->getNombre()) : '' ?>" required>

            <label>Descripción:</label>
            <input type="text" name="descripcion" value="<?= $mode === 'editar' && $rol ? htmlspecialchars($rol->getDescripcion()) : '' ?>">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $mode === 'crear' ? 'Guardar' : 'Actualizar' ?></button>
                <a href="index.php?controller=dashboard&action=index&section=roles" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>