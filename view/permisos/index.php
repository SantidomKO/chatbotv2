<?php $title = "Permisos"?> 
<?php require_once __DIR__ . "/../layout/header.php"; ?>
    <?php require_once __DIR__ . "/../layout/sidebar.php"; ?>
    <div class="main-content">
        <div class="panel-container">
            <div class="header-section">
                <div class="header-left">
                    <h1>Listado de Permisos</h1>
                    <?php if(!empty($permisos)): ?>
                        <?php $cantidad = count($permisos); ?>
                        <span class="object-count"><?= $cantidad === 1 ? '1 permiso' : $cantidad . ' permisos' ?></span>
                    <?php endif; ?>    
                </div>
                <div class="header-right">
                    <a class="btn btn-primary" href="index.php?controller=permiso&action=crear">Agregar Permiso</a>
                </div>
            </div>
            
            <div class="table-container">
                <table class="objects-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($permisos)): ?>
                            <?php foreach($permisos as $permiso): ?>
                                <tr>
                                    <td class="id-cell"><?= $permiso->getId(); ?></td>
                                    <td class="name-cell"><?= $permiso->getNombre(); ?></td>
                                    <td class="desc-cell"><?= $permiso->getDescripcion(); ?></td>
                                    <td class="actions-cell">
                                        <a class="btn btn-edit" href="index.php?controller=permiso&action=editar&id=<?= $permiso->getId(); ?>">Editar</a>
                                        <a class="btn btn-delete" href="index.php?controller=permiso&action=eliminar&id=<?= $permiso->getId(); ?>">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty-message">
                                    No hay permisos registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>