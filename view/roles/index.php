<?php 
    $title = "Roles";
    require_once __DIR__ . "/../layout/header.php";
    require_once __DIR__ . "/../layout/sidebar.php";
    require_once __DIR__ . "/../../model/RolPermiso.class.php";
?>
    <div class="main-content">
        <div class="panel-container">
            <div class="header-section">
                <div class="header-left">
                    <h1>Listado de Roles</h1>
                    <?php if(!empty($roles)): ?>
                        <span class="object-count"><?= count($roles) ?> roles</span>
                    <?php endif; ?>    
                </div>
                <div class="header-right">
                    <a class="btn btn-primary" href="index.php?controller=rol&action=crear">Agregar Rol</a>
                </div>
            </div>
            
            <div class="table-container">
                <table class="objects-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Permisos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($roles)): ?>
                            <?php $rolPermiso = new RolPermiso(); ?>
                            <?php foreach($roles as $rol): ?>
                                <tr>
                                    <td class="id-cell"><?= $rol->getId(); ?></td>
                                    <td class="name-cell"><?= $rol->getNombre(); ?></td>
                                    <td class="desc-cell"><?= $rol->getDescripcion(); ?></td>
                                    <td class="desc-cell"><?= count($rolPermiso->obtenerPermisosPorRol($rol->getId())); ?></td>
                                    <td class="actions-cell">
                                        <a class="btn btn-edit" href="index.php?controller=rol&action=editar&id=<?= $rol->getId(); ?>">Editar</a>
                                        <a class="btn btn-delete" href="index.php?controller=rol&action=eliminar&id=<?= $rol->getId(); ?>">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-message">
                                    No hay roles registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>