<?php 
    $title = 'Editar rol';
    require_once __DIR__ . "/../layout/header.php";
    require_once __DIR__ . "/../layout/sidebar.php";
    require_once __DIR__ . "/../../model/Permiso.class.php";
    require_once __DIR__ . "/../../model/RolPermiso.class.php";
    $permisos = Permiso::listar();
    $rolPermiso = new RolPermiso();
    $permisosAsignados = [];
    foreach($rolPermiso->obtenerPermisosPorRol($rol->getId()) as $permisoAsignado) {
        $permisosAsignados[] = $permisoAsignado->getId();
    }
?>
    <div class="main-content">
        <div class="header-right">
            <a class="btn btn-primary" href="index.php?controller=rol&action=listar">Volver</a>
        </div>
        <div class="form-container">
            <h1>Editar Rol "<?=$rol->getNombre()?>"</h1>
            <form action="index.php?controller=rol&action=actualizar" method="POST">
                <div class="form-group">
                    <label>ID: </label>
                    <input type="text" name="id" value="<?=$rol->getId()?>" readonly>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre: </label>
                    <input type="text" id="nombre" name="nombre" value="<?=$rol->getNombre()?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción: </label>
                    <textarea id="descripcion" name="descripcion"><?=$rol->getDescripcion()?></textarea>
                </div>
                <div class="form-group">
                    <label>Permisos:</label>
                    <div>
                        <?php foreach($permisos as $permiso): ?>
                            <label style="display:block; margin-bottom:6px;">
                                <input type="checkbox" name="permisos[]" value="<?= $permiso->getId(); ?>" <?= in_array($permiso->getId(), $permisosAsignados, true) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($permiso->getNombre(), ENT_QUOTES, 'UTF-8'); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>