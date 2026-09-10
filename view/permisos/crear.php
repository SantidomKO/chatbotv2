<?php $title = "Crear permiso"?> 
<?php require_once __DIR__ . "/../layout/header.php"; ?>
    <?php require_once __DIR__ . "/../layout/sidebar.php"; ?>
    <div class="main-content">
        <div class="header-right">
            <a class="btn btn-primary" href="index.php?controller=permiso&action=listar">Volver</a>
        </div>
        <div class="form-container">
            <h1>Crear Nuevo Permiso</h1>
            <form action="index.php?controller=permiso&action=guardar" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Crear Permiso</button>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>