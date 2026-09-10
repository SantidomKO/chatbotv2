<?php 
    $title = "Intents";
    require_once __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../layout/sidebar.php';
?>
    <div class="main-content">
        <div class="panel-container">
            <div class="header-section">
                <div class="header-left">
                    <h1>Listado de Intents</h1>
                    <?php if(!empty($intents)): ?>
                        <span class="object-count"><?= count($intents) ?> intents</span>
                    <?php endif; ?>    
                </div>
                <div class="header-right">
                    <a class="btn btn-primary" href="index.php?controller=intent&action=crear">Agregar Intent</a>
                </div>
            </div>
            
            <div class="table-container">
                <table class="objects-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Intent</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($intents)): ?>
                            <?php foreach($intents as $intent): ?>
                                <tr>
                                    <td class="id-cell"><?= $intent->getId(); ?></td>
                                    <td class="name-cell"><?= $intent->getNombreIntent(); ?></td>
                                    <td class="desc-cell"><?= $intent->getCategoria() ? $intent->getCategoria()->getNombre() : ''; ?></td>
                                    <td class="desc-cell"><?= $intent->getDescripcion(); ?></td>
                                    <td class="actions-cell">
                                        <a class="btn btn-edit" href="index.php?controller=intent&action=editar&id=<?= $intent->getId(); ?>">Editar</a>
                                        <a class="btn btn-delete" href="index.php?controller=intent&action=eliminar&id=<?= $intent->getId(); ?>">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-message">
                                    No hay intents registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>