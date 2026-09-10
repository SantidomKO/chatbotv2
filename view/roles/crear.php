<?php 
    $title = "Crear nuevo rol";
    require_once __DIR__ . "/../layout/header.php";
    require_once __DIR__ . "/../layout/sidebar.php";
    require_once __DIR__ . "/../../model/Permiso.class.php";
    $permisos = Permiso::listar();
?>
    <div class="main-content">
        <div class="header-right">
            <a class="btn btn-primary" href="index.php?controller=rol&action=listar">Volver</a>
        </div>
        <div class="form-container">
            <h1>Crear Nuevo Rol</h1>
            <form action="index.php?controller=rol&action=guardar" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion"></textarea>
                </div>
                <div class="form-group">
                    <label>Permisos:</label>
                    <div>
                        <?php foreach($permisos as $permiso): ?>
                            <label style="display:block; margin-bottom:6px;">
                                <input type="checkbox" name="permisos[]" value="<?= $permiso->getId(); ?>">
                                <?= htmlspecialchars($permiso->getNombre(), ENT_QUOTES, 'UTF-8'); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>