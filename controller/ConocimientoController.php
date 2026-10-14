<?php
require_once "model/Usuario.class.php";
require_once "model/Categoria.class.php";
require_once "model/Intent.class.php";
require_once "model/Respuesta.class.php";
require_once "model/FrasesEntrenamiento.class.php";

class ConocimientoController
{
    private function requirePermission()
    {
        if (!isset($_SESSION['usuario_data']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $usuario = Usuario::obtenerPorID($_SESSION['usuario_data']['id']);
        if (!$usuario || !$usuario->tienePermiso('Gestionar Conocimiento')) {
            header('Location: index.php?controller=chat&action=index');
            exit();
        }
    }

    private function requireJsonPermission()
    {
        if (!isset($_SESSION['usuario_data']['id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        $usuario = Usuario::obtenerPorID($_SESSION['usuario_data']['id']);
        if (!$usuario || !$usuario->tienePermiso('Gestionar Conocimiento')) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Sin permisos']);
            exit();
        }
    }

    private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    private function input($key)
    {
        return trim(strip_tags((string)($_POST[$key] ?? '')));
    }

    public function index()
    {
        $this->requirePermission();
        $categorias = Categoria::listar();
        require_once "view/conocimiento/index.php";
    }

    public function categorias()
    {
        $this->requireJsonPermission();
        $items = [];
        foreach (Categoria::listar() as $categoria) {
            $items[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre(),
                'descripcion' => $categoria->getDescripcion()
            ];
        }
        $this->json($items);
    }

    public function intents()
    {
        $this->requireJsonPermission();
        $categoriaId = (int)($_GET['id'] ?? 0);
        $items = [];
        foreach (Intent::listar() as $intent) {
            if (!$categoriaId || ($intent->getCategoria() && (int)$intent->getCategoria()->getId() === $categoriaId)) {
                $items[] = [
                    'id' => $intent->getId(),
                    'nombre_intent' => $intent->getNombreIntent(),
                    'descripcion' => $intent->getDescripcion()
                ];
            }
        }
        $this->json($items);
    }

    public function detalle()
    {
        $this->requireJsonPermission();
        $intentId = (int)($_GET['id'] ?? 0);
        $intent = Intent::obtenerPorID($intentId);
        if (!$intent) {
            $this->json(['success' => false, 'error' => 'Intent no encontrado'], 404);
        }

        $frases = [];
        foreach (FraseEntrenamiento::listar() as $frase) {
            if ($frase->getIntent() && (int)$frase->getIntent()->getId() === $intentId) {
                $frases[] = ['id' => $frase->getId(), 'texto' => $frase->getTextoEjemplo()];
            }
        }

        $respuestas = [];
        foreach (Respuesta::listar() as $respuesta) {
            if ($respuesta->getIntent() && (int)$respuesta->getIntent()->getId() === $intentId) {
                $respuestas[] = [
                    'id' => $respuesta->getId(),
                    'texto' => $respuesta->getTexto(),
                    'activa' => $respuesta->getActiva()
                ];
            }
        }

        $this->json([
            'intent' => [
                'id' => $intent->getId(),
                'nombre' => $intent->getNombreIntent(),
                'descripcion' => $intent->getDescripcion()
            ],
            'frases' => $frases,
            'respuestas' => $respuestas
        ]);
    }

    public function guardarCategoria()
    {
        $this->requireJsonPermission();
        $nombre = $this->input('nombre');
        if ($nombre === '') {
            $this->json(['success' => false, 'error' => 'El nombre es obligatorio'], 400);
        }
        $categoria = new Categoria(null, $nombre, $this->input('descripcion'));
        $this->json(['success' => $categoria->guardar()]);
    }

    public function actualizarCategoria()
    {
        $this->requireJsonPermission();
        $categoria = Categoria::obtenerPorID((int)($_POST['id'] ?? 0));
        $nombre = $this->input('nombre');
        if (!$categoria || $nombre === '') {
            $this->json(['success' => false, 'error' => 'Datos incompletos'], 400);
        }
        $categoria->setNombre($nombre);
        $descripcion = $this->input('descripcion');
        if ($descripcion !== '') {
            $categoria->setDescripcion($descripcion);
        }
        $this->json(['success' => $categoria->actualizar()]);
    }

    public function eliminarCategoria()
    {
        $this->requireJsonPermission();
        $categoria = Categoria::obtenerPorID((int)($_POST['id'] ?? 0));
        $this->json(['success' => $categoria ? $categoria->eliminar() : false]);
    }

    public function retrain()
    {
        $this->requireJsonPermission();
        require_once __DIR__ . '/helpers/RasaGenerator.php';
        try {
            $result = RasaGenerator::trainAndLoadModel();
            $this->json($result, !empty($result['success']) ? 200 : 500);
        } catch (Throwable $exception) {
            $this->json(['success' => false, 'error' => $exception->getMessage()], 500);
        }
    }

    public function guardarFrase()
    {
        $this->requireJsonPermission();
        $intent = Intent::obtenerPorID((int)($_POST['intent_id'] ?? 0));
        $texto = $this->input('texto');
        if (!$intent || $texto === '') {
            $this->json(['success' => false, 'error' => 'Datos incompletos'], 400);
        }
        $frase = new FraseEntrenamiento(null, $intent, $texto);
        $this->json(['success' => $frase->guardar()]);
    }

    public function guardarRespuesta()
    {
        $this->requireJsonPermission();
        $intent = Intent::obtenerPorID((int)($_POST['intent_id'] ?? 0));
        $texto = $this->input('texto');
        if (!$intent || $texto === '') {
            $this->json(['success' => false, 'error' => 'Datos incompletos'], 400);
        }
        $respuesta = new Respuesta(null, $texto, true, null, $intent);
        $this->json(['success' => $respuesta->guardar()]);
    }

    public function eliminarFrase()
    {
        $this->requireJsonPermission();
        $frase = FraseEntrenamiento::obtenerPorID((int)($_POST['id'] ?? 0));
        $this->json(['success' => $frase ? $frase->eliminar() : false]);
    }

    public function eliminarRespuesta()
    {
        $this->requireJsonPermission();
        $respuesta = Respuesta::obtenerPorID((int)($_POST['id'] ?? 0));
        $this->json(['success' => $respuesta ? $respuesta->eliminar() : false]);
    }
}
