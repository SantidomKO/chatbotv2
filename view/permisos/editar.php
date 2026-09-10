<?php $title = 'Editar permiso'?>
<?php require_once __DIR__ . "/../layout/header.php"; ?>
    <?php require_once __DIR__ . "/../layout/sidebar.php"; ?>
    <div class="main-content">
        <div class="header-right">
            <a class="btn btn-primary" href="index.php?controller=permiso&action=listar">Volver</a>
        </div>
        <div class="form-container">
            <h1>Editar Permiso "<?=$permiso->getNombre()?>"</h1>
            <form action="index.php?controller=permiso&action=actualizar" method="POST">
                <div class="form-group">
                    <label>ID: </label>
                    <input type="text" name="id" value="<?=$permiso->getId()?>" readonly>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre: </label>
                    <input type="text" id="nombre" name="nombre" value="<?=$permiso->getNombre()?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción: </label>
                    <textarea id="descripcion" name="descripcion" ><?=$permiso->getDescripcion()?></textarea>
                </div>
                    <button type="submit" onclick="" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>