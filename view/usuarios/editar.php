<?php 
    $title = "Editar Usuario";
    require_once __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../layout/sidebar.php';
?>
    <div class="main-content">
        <div class="form-container">
            <h1>Editar Usuario</h1>
            <form action="index.php?controller=usuario&action=actualizar" method="POST">
                <div class="form-group">
                    <label>ID:</label>
                    <input type="text" name="id" value="<?= $usuario->getId() ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" value="<?= $usuario->getNombre() ?>" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= $usuario->getEmail() ?>" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="index.php?controller=usuario&action=listar" class="btn">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>