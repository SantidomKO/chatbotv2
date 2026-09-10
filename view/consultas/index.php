<?php 
    $title = "Preguntas sin Respuesta";
    require_once __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../layout/sidebar.php';
?>
<div class="main-content">
    <div class="panel-container">
        <div class="header-section">
            <div class="header-left">
                <h1>Preguntas sin Respuesta</h1>
                <?php if(!empty($consultasSinResponder)): ?>
                    <span class="object-count"><?= count($consultasSinResponder) ?> consultas</span>
                <?php endif; ?>    
            </div>
        </div>
        
        <div class="table-container">
            <table class="objects-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Intent</th>
                        <th>Pregunta</th>
                        <th>Confianza</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($consultasSinResponder)): ?>
                        <?php foreach($consultasSinResponder as $consulta): ?>
                            <?php
                                $cid = $consulta->getId();
                                $usuarioNombre = $consulta->getUsuario() ? $consulta->getUsuario()->getNombre() : 'Anónimo';
                                $intentNombre = $consulta->getIntent() ? $consulta->getIntent()->getNombreIntent() : 'Sin intent';
                                $textoConsulta = $consulta->getTextoPregunta();
                                $confianza = $consulta->getConfianza();
                                $fechaHora = $consulta->getFechaHora();
                            ?>
                            <tr>
                                <td class="id-cell"><?= $cid ?></td>
                                <td class="name-cell"><?= $usuarioNombre ?></td>
                                <td class="desc-cell"><?= $intentNombre ?></td>
                                <td class="desc-cell"><?= $textoConsulta ?></td>
                                <td class="desc-cell"><?= $confianza ?></td>
                                <td class="desc-cell"><?= $fechaHora ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-message">
                                No hay preguntas sin respuesta
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>