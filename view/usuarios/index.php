<?php $title = "Usuarios"?> 
<?php require_once __DIR__ . "/../layout/header.php"; ?>
    <?php require_once __DIR__ . "/../layout/sidebar.php"; ?>
    <div class="main-content">
        <div class="panel-container">
            <div class="header-section">
                <div class="header-left">
                    <h1>Listado de Usuarios</h1>
                    <?php if(!empty($usuarios)): ?>
                        <span class="object-count"><?= count($usuarios) ?> usuarios</span>
                    <?php endif; ?>    
                </div>
                <div class="header-right">
                    <a class="btn btn-primary" href="index.php?controller=usuario&action=crear">Agregar Usuario</a>
                </div>
            </div>
            
            <div class="table-container">
                <table class="objects-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($usuarios)): ?>
                            <?php foreach($usuarios as $usuario): ?>
                                <tr>
                                    <td class="id-cell"><?= $usuario->getId(); ?></td>
                                    <td class="name-cell"><?= $usuario->getNombre(); ?></td>
                                    <td class="email-cell"><?= $usuario->getEmail(); ?></td>
                                    <td class="role-cell"><?= $usuario->getRolNombre() ?: 'Sin rol'; ?></td>
                                    <td class="actions-cell">
                                        <a class="btn btn-edit" href="index.php?controller=usuario&action=editar&id=<?= $usuario->getId(); ?>">Editar</a>
                                        <a class="btn btn-delete" href="index.php?controller=usuario&action=eliminar&id=<?= $usuario->getId(); ?>">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-message">
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>