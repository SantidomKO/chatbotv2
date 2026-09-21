<?php 
    $title = "Gestión de Conocimiento";
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
        
        <!-- Mostrar mensajes de sesión -->
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: rgba(32, 199, 122, 0.1); color: #20c77a; border: 1px solid rgba(32, 199, 122, 0.3); border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: rgba(255, 59, 48, 0.1); color: #ff3b30; border: 1px solid rgba(255, 59, 48, 0.3); border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Layout de 3 Columnas -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 1rem; padding: 1rem;" class="knowledge-grid">
            <!-- COLUMNA 1: Categorías -->
            <div style="background: rgba(17, 19, 24, 0.5); border: 1px solid rgba(0, 255, 255, 0.15); border-radius: 12px; padding: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(0, 255, 255, 0.1);">
                    <h3 style="color: #00ffff; margin: 0;">Categorías</h3>
                    <button class="btn btn-sm btn-primary" id="btnCreateCategoria" type="button" title="Crear nueva categoría" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                        + Nueva
                    </button>
                </div>
                <ul style="list-style: none; padding: 0; margin: 0;" id="categoriasContainer" role="list">
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <li class="categoria-item" style="margin-bottom: 0.5rem; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border-radius: 8px; border: 1px solid rgba(0, 255, 255, 0.1); cursor: pointer;" data-category-id="<?php echo htmlspecialchars($cat->getId()); ?>" role="listitem">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span class="categoria-link" style="color: #e0e0e0; text-decoration: none; font-weight: 500; flex: 1;"><?php echo htmlspecialchars($cat->getNombre()); ?></span>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button type="button" class="btn btn-edit-categoria" data-category-id="<?php echo htmlspecialchars($cat->getId()); ?>" data-category-name="<?php echo htmlspecialchars($cat->getNombre()); ?>" data-category-description="<?php echo htmlspecialchars($cat->getDescripcion() ?? ''); ?>" aria-label="Editar categoría <?php echo htmlspecialchars($cat->getNombre()); ?>" style="background: none; border: none; color: #00ffff; cursor: pointer; font-size: 1rem;">✎</button>
                                        <button type="button" class="btn btn-delete-categoria" data-category-id="<?php echo htmlspecialchars($cat->getId()); ?>" aria-label="Eliminar categoría <?php echo htmlspecialchars($cat->getNombre()); ?>" style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 1rem;">✕</button>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="text-align: center; color: #b0b0b0; padding: 1rem;" role="listitem">No hay categorías disponibles</li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- COLUMNA 2: Intents -->
            <div style="background: rgba(17, 19, 24, 0.5); border: 1px solid rgba(0, 255, 255, 0.15); border-radius: 12px; padding: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(0, 255, 255, 0.1);">
                    <h3 style="color: #00ffff; margin: 0;">Intents</h3>
                    <button class="btn btn-sm btn-primary" id="btnCreateIntent" style="display:none;" title="Crear nuevo Intent" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                        + Crear
                    </button>
                </div>
                <div id="intentsContainer" style="color: #b0b0b0;">
                    <p style="text-align: center; padding: 1rem;">Selecciona una categoría</p>
                </div>
            </div>

            <!-- COLUMNA 3: Detalle del Intent -->
            <div style="background: rgba(17, 19, 24, 0.5); border: 1px solid rgba(0, 255, 255, 0.15); border-radius: 12px; padding: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(0, 255, 255, 0.1);">
                    <h3 style="color: #00ffff; margin: 0;">Detalle del Intent</h3>
                </div>
                <div id="detalleContainer" style="color: #b0b0b0;">
                    <p style="text-align: center; padding: 1rem;">Selecciona un intent para ver detalles</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear/editar Categoría -->
<div id="modalCategoria" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: #111318; border: 1px solid rgba(0, 255, 255, 0.3); border-radius: 12px; padding: 1.5rem; width: 400px; max-width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #00ffff; margin: 0;" id="modalCategoriaTitle">Crear Categoría</h3>
            <button onclick="document.getElementById('modalCategoria').style.display='none'" style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 1.5rem;">×</button>
        </div>
        <form id="formCategoria">
            <input type="hidden" id="categoriaId" name="id">
            <div style="margin-bottom: 1rem;">
                <label for="categoriaNombre" style="color: #00ffff; display: block; margin-bottom: 0.5rem;">Nombre</label>
                <input type="text" id="categoriaNombre" name="nombre" required style="width: 100%; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="categoriaDescripcion" style="color: #00ffff; display: block; margin-bottom: 0.5rem;">Descripción</label>
                <textarea id="categoriaDescripcion" name="descripcion" rows="3" style="width: 100%; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="document.getElementById('modalCategoria').style.display='none'" style="padding: 0.5rem 1rem; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0; cursor: pointer;">Cancelar</button>
                <button type="submit" style="padding: 0.5rem 1rem; background: rgba(0, 255, 255, 0.2); border: 1px solid rgba(0, 255, 255, 0.4); border-radius: 8px; color: #00ffff; cursor: pointer;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para crear/editar Intent -->
<div id="modalIntent" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: #111318; border: 1px solid rgba(0, 255, 255, 0.3); border-radius: 12px; padding: 1.5rem; width: 400px; max-width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #00ffff; margin: 0;" id="modalIntentTitle">Crear Intent</h3>
            <button onclick="document.getElementById('modalIntent').style.display='none'" style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 1.5rem;">×</button>
        </div>
        <form id="formIntent">
            <input type="hidden" id="intentId" name="id">
            <div style="margin-bottom: 1rem;">
                <label for="intentNombre" style="color: #00ffff; display: block; margin-bottom: 0.5rem;">Nombre del Intent</label>
                <input type="text" id="intentNombre" name="nombre_intent" required style="width: 100%; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="intentCategoria" style="color: #00ffff; display: block; margin-bottom: 0.5rem;">Categoría</label>
                <select id="intentCategoria" name="categoria_id" style="width: 100%; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0;">
                    <option value="">Sin categoría</option>
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat->getId()); ?>">
                                <?php echo htmlspecialchars($cat->getNombre()); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="intentDesc" style="color: #00ffff; display: block; margin-bottom: 0.5rem;">Descripción</label>
                <textarea id="intentDesc" name="descripcion" rows="3" style="width: 100%; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="document.getElementById('modalIntent').style.display='none'" style="padding: 0.5rem 1rem; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0; cursor: pointer;">Cancelar</button>
                <button type="submit" style="padding: 0.5rem 1rem; background: rgba(0, 255, 255, 0.2); border: 1px solid rgba(0, 255, 255, 0.4); border-radius: 8px; color: #00ffff; cursor: pointer;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para editar Frase de Entrenamiento -->
<div id="modalFrase" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: #111318; border: 1px solid rgba(0, 255, 255, 0.3); border-radius: 12px; padding: 1.5rem; width: 400px; max-width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #00ffff; margin: 0;" id="modalFraseTitle">Editar Frase</h3>
            <button onclick="document.getElementById('modalFrase').style.display='none'" style="background: none; border: none; color: #ff3b30; cursor: pointer; font-size: 1.5rem;">×</button>
        </div>
        <form id="formFrase">
            <input type="hidden" id="fraseId" name="id">
            <div style="margin-bottom: 1rem;">
                <label style="color: #00ffff; display: block; margin-bottom: 0.5rem;">Texto de la frase</label>
                <textarea id="fraseTexto" name="texto_ejemplo" rows="3" required style="width: 100%; padding: 0.5rem; background: rgba(0, 255, 255, 0.05); border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="document.getElementById('modalFrase').style.display='none'" style="padding: 0.5rem 1rem; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; color: #e0e0e0; cursor: pointer;">Cancelar</button>
                <button type="submit" style="padding: 0.5rem 1rem; background: rgba(0, 255, 255, 0.2); border: 1px solid rgba(0, 255, 255, 0.4); border-radius: 8px; color: #00ffff; cursor: pointer;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<script src="view/dashboard/conocimiento.js"></script>
