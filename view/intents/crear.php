<?php 
    $title = "Crear Intent";
    require_once __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../layout/sidebar.php';
?>
    <div class="main-content">
        <div class="form-container">
            <h1>Nuevo Intent</h1>
            <form action="index.php?controller=intent&action=guardar" method="POST">
                <div class="form-group">
                    <label>Nombre del Intent:</label>
                    <input type="text" name="nombre_intent" required>
                </div>
                <div class="form-group">
                    <label>Categoría:</label>
                    <select name="categoria_id">
                        <option value="">Sin categoría</option>
                        <?php foreach($categorias as $categoria): ?>
                            <option value="<?= $categoria->getId() ?>"><?= $categoria->getNombre() ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción:</label>
                    <textarea name="descripcion"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="index.php?controller=intent&action=listar" class="btn">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>