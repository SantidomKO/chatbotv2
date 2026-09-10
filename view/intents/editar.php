<?php 
    $title = "Editar Intent";
    require_once __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../layout/sidebar.php';
?>
    <div class="main-content">
        <div class="form-container">
            <h1>Editar Intent</h1>
            <form action="index.php?controller=intent&action=actualizar" method="POST">
                <div class="form-group">
                    <label>ID:</label>
                    <input type="text" name="id" value="<?= $intent->getId() ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Nombre del Intent:</label>
                    <input type="text" name="nombre_intent" value="<?= $intent->getNombreIntent() ?>" required>
                </div>
                <div class="form-group">
                    <label>Categoría:</label>
                    <select name="categoria_id">
                        <option value="">Sin categoría</option>
                        <?php foreach($categorias as $categoria): ?>
                            <option value="<?= $categoria->getId() ?>" <?= $intent->getCategoria() && $intent->getCategoria()->getId() == $categoria->getId() ? 'selected' : '' ?>><?= $categoria->getNombre() ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción:</label>
                    <textarea name="descripcion"><?= $intent->getDescripcion() ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="index.php?controller=intent&action=listar" class="btn">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>