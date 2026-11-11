<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/chatbotv2/');

require_once "controller/RolController.php";

$globalStyles = [
    'assets/css/style.css',
    'assets/css/sidebar.css',
    'assets/css/form_style.css',
];
$pageStyles = [];

function addPageStyle(string $cssPath): void
{
    global $pageStyles;

    if (!in_array($cssPath, $pageStyles, true)) {
        $pageStyles[] = $cssPath;
    }
}

$controller = $_GET["controller"] ?? 'auth';
$action = $_GET["action"] ?? 'login';

if (!isset($_SESSION['usuario_data']) && $controller !== 'auth') {
    header('Location: index.php?controller=auth&action=login');
    exit();
}

$usuario = $_SESSION['usuario'] ?? null;

switch ($controller) {
    case 'auth':
        addPageStyle('assets/css/pages/auth.css');
        require_once "controller/AuthController.php";
        $authController = new AuthController();
        switch ($action) {
            case 'login':
                $authController->login();
                break;
            case 'validar':
                $authController->validar();
                break;
            case 'verificarSegundoFactor':
                $authController->verificarSegundoFactor();
                break;
            case 'cancelarSegundoFactor':
                $authController->cancelarSegundoFactor();
                break;
            case 'registrar':
                $authController->registrar();
                break;
            case 'guardarRegistro':
                $authController->guardarRegistro();
                break;
            case 'logout':
                $authController->logout();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'chat':
        addPageStyle('assets/css/pages/chat.css');
        require_once "controller/ChatController.php";
        $chatController = new ChatController();
        switch ($action) {
            case 'index':
                $chatController->index();
                break;
            case 'enviar':
                $chatController->enviar();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'conocimiento':
        addPageStyle('assets/css/pages/conocimiento.css');
        require_once "controller/ConocimientoController.php";
        $conocimientoController = new ConocimientoController();
        switch ($action) {
            case 'index':
                $conocimientoController->index();
                break;
            case 'categorias':
                $conocimientoController->categorias();
                break;
            case 'intents':
                $conocimientoController->intents();
                break;
            case 'detalle':
                $conocimientoController->detalle();
                break;
            case 'guardarCategoria':
                $conocimientoController->guardarCategoria();
                break;
            case 'actualizarCategoria':
                $conocimientoController->actualizarCategoria();
                break;
            case 'eliminarCategoria':
                $conocimientoController->eliminarCategoria();
                break;
            case 'guardarFrase':
                $conocimientoController->guardarFrase();
                break;
            case 'guardarRespuesta':
                $conocimientoController->guardarRespuesta();
                break;
            case 'eliminarFrase':
                $conocimientoController->eliminarFrase();
                break;
            case 'eliminarRespuesta':
                $conocimientoController->eliminarRespuesta();
                break;
            case 'retrain':
                $conocimientoController->retrain();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'rol':
        addPageStyle('assets/css/pages/roles.css');
        $rolController = new RolController();
        switch ($action) {
            case 'listar':
                $rolController->listar();
                break;
            case 'crear':
                $rolController->crear();
                break;
            case 'guardar':
                $rolController->guardar();
                break;
            case 'editar':
                $rolController->editar();
                break;
            case 'actualizar':
                $rolController->actualizar();
                break;
            case 'eliminar':
                $rolController->eliminar();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'permiso':
        addPageStyle('assets/css/pages/permisos.css');
        require_once "controller/PermisoController.php";
        $permisoController = new PermisoController();
        switch ($action) {
            case 'listar':
                $permisoController->listar();
                break;
            case 'crear':
                $permisoController->crear();
                break;
            case 'guardar':
                $permisoController->guardar();
                break;
            case 'editar':
                $permisoController->editar();
                break;
            case 'actualizar':
                $permisoController->actualizar();
                break;
            case 'eliminar':
                $permisoController->eliminar();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'pregunta':
        require_once "controller/PreguntaController.php";
        $preguntaController = new PreguntaController();
        switch ($action) {
            case 'guardar':
                $preguntaController->guardar();
                break;
            case 'actualizar':
                $preguntaController->actualizar();
                break;
            case 'eliminar':
                $preguntaController->eliminar();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'respuesta':
        require_once "controller/RespuestaController.php";
        $respuestaController = new RespuestaController();
        switch ($action) {
            case 'guardar':
                $respuestaController->guardar();
                break;
            case 'actualizar':
                $respuestaController->actualizar();
                break;
            case 'eliminar':
                $respuestaController->eliminar();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'intent':
        addPageStyle('assets/css/pages/intents.css');
        require_once "controller/IntentController.php";
        $intentController = new IntentController();
        switch ($action) {
            case 'listar':
                $intentController->listar();
                break;
            case 'crear':
                $intentController->crear();
                break;
            case 'guardar':
                $intentController->guardar();
                break;
            case 'editar':
                $intentController->editar();
                break;
            case 'actualizar':
                $intentController->actualizar();
                break;
            case 'eliminar':
                $intentController->eliminar();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'usuario':
        addPageStyle('assets/css/pages/usuarios.css');
        require_once "controller/UsuarioController.php";
        $usuarioController = new UsuarioController();
        switch ($action) {
            case 'listar':
                $usuarioController->listar();
                break;
            case 'crear':
                $usuarioController->crear();
                break;
            case 'guardar':
                $usuarioController->guardar();
                break;
            case 'editar':
                $usuarioController->editar();
                break;
            case 'actualizar':
                $usuarioController->actualizar();
                break;
            case 'eliminar':
                $usuarioController->eliminar();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break; 
    case 'perfil':
        addPageStyle('assets/css/pages/perfil.css');
        require_once "controller/PerfilController.php";
        $perfilController = new PerfilController();
        switch ($action) {
            case 'ver':
                $perfilController->ver();
                break;
            case 'iniciarConfiguracion2FA':
                $perfilController->iniciarConfiguracion2FA();
                break;
            case 'activar2FA':
                $perfilController->activar2FA();
                break;
            case 'desactivar2FA':
                $perfilController->desactivar2FA();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    case 'consultas':
        addPageStyle('assets/css/pages/consultas.css');
        require_once "controller/ConsultasController.php";
        $consultasController = new ConsultasController();
        switch ($action) {
            case 'index':
                $consultasController->index();
                break;
            default:
                echo "Accion no encontrada";
                break;
        }
        break;
    default:
        require_once "controller/ChatController.php";
        $chatController = new ChatController();
        $chatController->index();
        break;
}
?>