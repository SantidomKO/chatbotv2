<?php
$title = 'Gestión de Conocimiento';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>
<div class="main-content">
    <div class="panel-container">
        <div class="header-section">
            <div class="header-left">
                <h1>Gestión de Conocimiento</h1>
                <p>Administra categorías, intents, frases de entrenamiento y respuestas del chatbot</p>
            </div>
            <div class="header-right">
                <button class="btn btn-primary" id="btnRetrainRasa" type="button">Reentrenar modelo Rasa</button>
                <span id="retrainStatus" style="margin-left: 1rem; color: #b0b0b0;"></span>
            </div>
        </div>

        <div class="knowledge-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 1rem; padding: 1rem;">
            <section class="knowledge-panel" style="background: rgba(17, 19, 24, 0.5); border: 1px solid rgba(0, 255, 255, 0.15); border-radius: 12px; padding: 1rem;">
                <div class="knowledge-panel__header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(0, 255, 255, 0.1);">
                    <h3 style="color: #00ffff; margin: 0;">Categorías</h3>
                    <button class="btn btn-sm btn-primary" id="newCategory" type="button" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;">+ Nueva</button>
                </div>
                <ul id="categories" class="knowledge-list" style="list-style: none; padding: 0; margin: 0;">
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <li style="margin-bottom: 0.5rem; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border-radius: 8px; border: 1px solid rgba(0, 255, 255, 0.1);" role="listitem">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <button type="button" data-category-id="<?= (int)$categoria->getId() ?>" style="flex: 1; background: none; border: 0; color: #e0e0e0; text-align: left; cursor: pointer; font-weight: 500;">
                                        <?= htmlspecialchars($categoria->getNombre()) ?>
                                    </button>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button type="button" class="btn-edit-categoria" data-category-id="<?= (int)$categoria->getId() ?>" data-category-name="<?= htmlspecialchars($categoria->getNombre(), ENT_QUOTES) ?>" data-category-description="<?= htmlspecialchars($categoria->getDescripcion() ?? '', ENT_QUOTES) ?>" aria-label="Editar categoría" style="background: none; border: none; color: #00ffff; cursor: pointer; font-size: 1rem;">✎</button>
                                        <button type="button" class="btn-delete-categoria" data-category-id="<?= (int)$categoria->getId() ?>" aria-label="Eliminar categoría" style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 1rem;">✕</button>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="text-align: center; color: #b0b0b0; padding: 1rem;">No hay categorías disponibles</li>
                    <?php endif; ?>
                </ul>
                <form id="categoryForm" class="knowledge-form" hidden>
                    <label>Nombre<input name="nombre" required></label>
                    <label>Descripción<textarea name="descripcion" rows="2"></textarea></label>
                    <button class="btn btn-primary" type="submit">Guardar categoría</button>
                </form>
            </section>

            <section class="knowledge-panel" style="background: rgba(17, 19, 24, 0.5); border: 1px solid rgba(0, 255, 255, 0.15); border-radius: 12px; padding: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(0, 255, 255, 0.1);">
                    <h3 style="color: #00ffff; margin: 0;">Intents</h3>
                    <button class="btn btn-sm btn-primary" id="btnCreateIntent" type="button" hidden style="font-size: 0.8rem; padding: 0.3rem 0.6rem;">+ Crear</button>
                </div>
                <ul id="intents" class="knowledge-list" style="list-style: none; padding: 0; margin: 0; color: #b0b0b0;">
                    <li style="text-align: center; padding: 1rem;">Selecciona una categoría</li>
                </ul>
            </section>

            <section class="knowledge-panel" style="background: rgba(17, 19, 24, 0.5); border: 1px solid rgba(0, 255, 255, 0.15); border-radius: 12px; padding: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(0, 255, 255, 0.1);">
                    <h3 style="color: #00ffff; margin: 0;">Detalle del Intent</h3>
                </div>
                <div id="detail" class="knowledge-detail" style="color: #b0b0b0;">
                    <p style="text-align: center; padding: 1rem;">Selecciona un intent para ver detalles</p>
                </div>
            </section>
        </div>
    </div>
</div>
<div id="modalCategoria" hidden style="position: fixed; inset: 0; background: rgba(0, 0, 0, 0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: #111318; border: 1px solid rgba(0, 255, 255, 0.3); border-radius: 12px; padding: 1.5rem; width: 400px; max-width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #00ffff; margin: 0;" id="modalCategoriaTitle">Crear Categoría</h3>
            <button type="button" id="closeCategoryModal" aria-label="Cerrar" style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 1.5rem;">×</button>
        </div>
        <form id="categoryModalForm" class="knowledge-form">
            <input type="hidden" name="id" id="categoryId">
            <label>Nombre<input name="nombre" id="categoryName" required></label>
            <label>Descripción<textarea name="descripcion" id="categoryDescription" rows="3"></textarea></label>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" id="cancelCategory" class="btn">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
<script src="view/conocimiento/index.js"></script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
