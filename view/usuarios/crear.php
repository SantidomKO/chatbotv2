<?php 
    $title = "Crear nuevo usuario";
    require_once __DIR__ . "/../layout/header.php";
    require_once __DIR__ . "/../layout/sidebar.php";
    require_once __DIR__ . "/../../model/Rol.class.php";
    $roles = Rol::listar();
?>
    <div class="main-content">
        <div class="header-right">
            <a class="btn btn-primary" href="index.php?controller=usuario&action=listar">Volver</a>
        </div>
        <div class="form-container">
            <h1>Crear Nuevo Usuario</h1>
            <form action="index.php?controller=usuario&action=guardar" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="id_rol">Rol:</label>
                    <select id="id_rol" name="id_rol" required>
                        <option value="">Seleccionar rol</option>
                        <?php foreach($roles as $rol): ?>
                            <option value="<?= $rol->getId(); ?>"><?= htmlentities($rol->getNombre()); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="two_factor_method">Verificación en dos pasos</label>
                    <select id="two_factor_method" name="two_factor_method">
                        <option value="none">No activar</option>
                        <option value="authenticator">Autenticador de Google / Auth app</option>
                        <option value="email">Código por email</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Crear Usuario</button>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . "/../layout/footer.php"; ?>