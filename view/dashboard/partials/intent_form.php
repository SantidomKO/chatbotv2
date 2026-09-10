<?php
// $mode: 'crear'|'editar'
// $intentLocal: Intent object when editing
// $categorias: list of Categoria objects
?>
<div class="dashboard-panel dashboard-panel--form">
    <div class="dashboard-panel__head">
        <h2><?= $modeLocal === 'crear' ? 'Crear Intent' : 'Editar Intent' ?></h2>
    </div>
    <div class="dashboard-panel__body">
        <form action="index.php?controller=intent&action=<?= $modeLocal === 'crear' ? 'guardar' : 'actualizar' ?>" method="POST">
            <?php if ($modeLocal === 'editar' && $intentLocal): ?>
                <label>ID:</label>
                <input type="text" name="id" value="<?= htmlspecialchars($intentLocal->getId()) ?>" readonly>
            <?php endif; ?>

            <label>Nombre del Intent:</label>
            <input type="text" name="nombre_intent" value="<?= $modeLocal === 'editar' && $intentLocal ? htmlspecialchars($intentLocal->getNombreIntent()) : '' ?>" required>

            <label>Categoría:</label>
            <select name="categoria_id">
                <option value="">Sin categoría</option>
                <?php foreach ($categorias as $categoria) : ?>
                    <?php $selected = $modeLocal === 'editar' && $intentLocal && $intentLocal->getCategoria() && $intentLocal->getCategoria()->getId() == $categoria->getId() ? 'selected' : ''; ?>
                    <option value="<?= htmlspecialchars($categoria->getId()) ?>" <?= $selected ?>><?= htmlspecialchars($categoria->getNombre()) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Descripción:</label>
            <textarea name="descripcion" required><?= $modeLocal === 'editar' && $intentLocal ? htmlspecialchars($intentLocal->getDescripcion()) : '' ?></textarea>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $modeLocal === 'crear' ? 'Guardar' : 'Actualizar' ?></button>
                <a href="index.php?controller=dashboard&action=index&section=intents" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>