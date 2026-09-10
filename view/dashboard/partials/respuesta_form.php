<?php
// $mode: 'crear'|'editar'
// $respuestaLocal: array|object when editing
?>
<div class="dashboard-panel dashboard-panel--form">
    <div class="dashboard-panel__head">
        <h2><?= $modeLocal === 'crear' ? 'Crear Respuesta' : 'Editar Respuesta' ?></h2>
    </div>
    <div class="dashboard-panel__body">
        <form action="index.php?controller=respuesta&action=<?= $modeLocal === 'crear' ? 'guardar' : 'actualizar' ?>" method="POST">
            <?php if ($modeLocal === 'editar' && $respuestaLocal): ?>
                <label>ID:</label>
                <input type="text" name="id" value="<?= htmlspecialchars(is_object($respuestaLocal) ? $respuestaLocal->getId() : ($respuestaLocal['id'] ?? '')) ?>" readonly>
            <?php endif; ?>

            <label>Texto de la respuesta:</label>
            <textarea name="texto" required><?= $modeLocal === 'editar' && $respuestaLocal ? htmlspecialchars(is_object($respuestaLocal) ? $respuestaLocal->getTexto() : ($respuestaLocal['texto'] ?? '')) : '' ?></textarea>

            <label>Intent asociado:</label>
            <select name="intent_id">
                <option value="">-- Selecciona un intent --</option>
                <?php foreach ($intents as $intentOption) : ?>
                    <?php
                        $intentIdValue = is_object($intentOption) ? $intentOption->getId() : ($intentOption['id'] ?? '');
                        $intentName = is_object($intentOption) ? $intentOption->getNombreIntent() : ($intentOption['nombre_intent'] ?? '');
                        $selectedIntentId = '';
                        if ($modeLocal === 'editar' && $respuestaLocal) {
                            $selectedIntentId = is_object($respuestaLocal) && $respuestaLocal->getIntent() ? $respuestaLocal->getIntent()->getId() : ($respuestaLocal['intent_id'] ?? '');
                        }
                    ?>
                    <option value="<?= htmlspecialchars($intentIdValue) ?>" <?= $selectedIntentId == $intentIdValue ? 'selected' : '' ?>><?= htmlspecialchars($intentName) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Tipo / Canal (opcional):</label>
            <input type="text" name="tipo" value="<?= $modeLocal === 'editar' && $respuestaLocal ? htmlspecialchars(is_object($respuestaLocal) ? $respuestaLocal->getTipo() : ($respuestaLocal['tipo'] ?? '')) : '' ?>">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $modeLocal === 'crear' ? 'Guardar' : 'Actualizar' ?></button>
                <a href="index.php?controller=dashboard&action=index&section=respuestas" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>