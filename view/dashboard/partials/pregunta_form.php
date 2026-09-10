<?php
// $mode: 'crear'|'editar'
// $preguntaLocal: array|object when editing
?>
<div class="dashboard-panel dashboard-panel--form">
    <div class="dashboard-panel__head">
        <h2><?= $modeLocal === 'crear' ? 'Crear Pregunta' : 'Editar Pregunta' ?></h2>
    </div>
    <div class="dashboard-panel__body">
        <form action="index.php?controller=pregunta&action=<?= $modeLocal === 'crear' ? 'guardar' : 'actualizar' ?>" method="POST">
            <?php if ($modeLocal === 'editar' && $preguntaLocal): ?>
                <label>ID:</label>
                <input type="text" name="id" value="<?= htmlspecialchars(is_object($preguntaLocal) ? $preguntaLocal->getId() : ($preguntaLocal['id'] ?? '')) ?>" readonly>
            <?php endif; ?>

            <label>Texto de la pregunta:</label>
            <textarea name="texto" required><?= $modeLocal === 'editar' && $preguntaLocal ? htmlspecialchars(is_object($preguntaLocal) ? $preguntaLocal->getTexto() : ($preguntaLocal['texto'] ?? '')) : '' ?></textarea>

            <label>Etiqueta / Intent (opcional):</label>
            <input type="text" name="intent" value="<?= $modeLocal === 'editar' && $preguntaLocal ? htmlspecialchars(is_object($preguntaLocal) ? $preguntaLocal->getIntent() : ($preguntaLocal['intent'] ?? '')) : '' ?>">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $modeLocal === 'crear' ? 'Guardar' : 'Actualizar' ?></button>
                <a href="index.php?controller=dashboard&action=index&section=preguntas" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>