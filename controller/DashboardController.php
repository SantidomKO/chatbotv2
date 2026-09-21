<?php
require_once "model/Rol.class.php";
require_once "model/Usuario.class.php";
require_once "model/Permiso.class.php";
require_once "model/Respuesta.class.php";
require_once "model/Intent.class.php";
require_once "model/Categoria.class.php";
require_once "model/Consulta.class.php";

class DashboardController {
    private function requireAuthJson() {
        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }
    }

    private function sanitizeString($value) {
        return trim(strip_tags((string)($value ?? '')));
    }

    public function index() {
        // Session is started in index.php bootstrap
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }
        require_once "view/dashboard/index.php";
    }

    /**
     * Cargar la vista de Gestión de Conocimiento
     */
    public function conocimiento() {
        // Session is started in index.php bootstrap
        if (!isset($_SESSION['usuario_data'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $usuario = Usuario::obtenerPorID($_SESSION['usuario_data']['id']);
        if (!$usuario || !$usuario->tienePermiso('Gestionar Conocimiento')) {
            header('Location: index.php?controller=dashboard&action=index');
            exit();
        }

        $titulo = "Gestión de Conocimiento - Dashboard";
        $categorias = Categoria::listar();
        require_once "view/dashboard/conocimiento.php";
    }

    public function createCategoria() {
        // Session is started in index.php bootstrap
        $this->requireAuthJson();

        header('Content-Type: application/json; charset=utf-8');

        $nombre = $this->sanitizeString($_POST['nombre'] ?? '');
        $descripcion = $this->sanitizeString($_POST['descripcion'] ?? '');

        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El nombre de la categoría es obligatorio']);
            exit();
        }

        $categoria = new Categoria(null, $nombre, $descripcion);

        if ($categoria->guardar()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al guardar la categoría']);
        }
        exit();
    }

    public function updateCategoria() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        header('Content-Type: application/json; charset=utf-8');

        $id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : null;
        $nombre = $this->sanitizeString($_POST['nombre'] ?? '');
        $descripcion = $this->sanitizeString($_POST['descripcion'] ?? '');

        if (!$id || $nombre === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        $categoria = Categoria::obtenerPorID($id);
        if (!$categoria) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Categoría no encontrada']);
            exit();
        }

        $categoria->setNombre($nombre);
        $categoria->setDescripcion($descripcion);

        if ($categoria->actualizar()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al actualizar la categoría']);
        }
        exit();
    }

    public function deleteCategoria() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        header('Content-Type: application/json; charset=utf-8');

        $id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de categoría requerido']);
            exit();
        }

        $categoria = Categoria::obtenerPorID($id);
        if (!$categoria) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Categoría no encontrada']);
            exit();
        }

        foreach (Intent::listar() as $intent) {
            if ($intent->getCategoria() && $intent->getCategoria()->getId() == $id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar una categoría con intents asociados']);
                exit();
            }
        }

        if ($categoria->eliminar()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al eliminar la categoría']);
        }
        exit();
    }

    /**
     * Obtener intents filtrados por categoría (JSON)
     * GET: id (category_id)
     */
    public function getIntentsByCategory() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit();
        }

        $categoryId = is_numeric($_GET['id'] ?? null) ? (int)$_GET['id'] : null;
        header('Content-Type: application/json; charset=utf-8');

        if (!$categoryId) {
            http_response_code(400);
            echo json_encode(['error' => 'Categoría no especificada']);
            exit();
        }

        $intents = Intent::listar();
        $filtered = [];
        
        foreach ($intents as $intent) {
            if ($intent->getCategoria() && $intent->getCategoria()->getId() == $categoryId) {
                $filtered[] = [
                    'id' => $intent->getId(),
                    'nombre_intent' => $intent->getNombreIntent(),
                    'categoria_id' => $intent->getCategoria()->getId()
                ];
            }
        }

        echo json_encode($filtered);
        exit();
    }

    /**
     * Obtener detalle completo de un intent (intent + frases + respuestas) (JSON)
     * GET: id (intent_id)
     */
    public function getIntentDetail() {
        header('Content-Type: application/json; charset=utf-8');
        
        // Session is started in index.php bootstrap
        $this->requireAuthJson();

        $intentId = is_numeric($_GET['id'] ?? null) ? (int)$_GET['id'] : null;

        if (!$intentId) {
            http_response_code(400);
            echo json_encode(['error' => 'Intent no especificado']);
            exit();
        }

        $intent = Intent::obtenerPorID($intentId);
        if (!$intent) {
            http_response_code(404);
            echo json_encode(['error' => 'Intent no encontrado']);
            exit();
        }

        try {
            // Obtener frases de entrenamiento
            require_once "model/FrasesEntrenamiento.class.php";
            $allFrases = FraseEntrenamiento::listar();
            $frases = [];
            foreach ($allFrases as $frase) {
                if ($frase->getIntent() && $frase->getIntent()->getId() == $intentId) {
                    $frases[] = [
                        'id' => $frase->getId(),
                        'texto_ejemplo' => $frase->getTextoEjemplo()
                    ];
                }
            }

            // Obtener respuestas
            $allRespuestas = Respuesta::listar();
            $respuestas = [];
            foreach ($allRespuestas as $respuesta) {
                if ($respuesta->getIntent() && $respuesta->getIntent()->getId() == $intentId) {
                    $respuestas[] = [
                        'id' => $respuesta->getId(),
                        'texto_respuesta' => $respuesta->getTexto(),
                        'activa' => $respuesta->getActiva(),
                        'fecha_actualizacion' => $respuesta->getFechaActualizacion()
                    ];
                }
            }

            echo json_encode([
                'intent' => [
                    'id' => $intent->getId(),
                    'nombre_intent' => $intent->getNombreIntent(),
                    'categoria_id' => $intent->getCategoria() ? $intent->getCategoria()->getId() : null,
                    'descripcion' => $intent->getDescripcion()
                ],
                'frases_entrenamiento' => $frases,
                'respuestas' => $respuestas
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al cargar detalles: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Exportar todos los intents con frases y respuestas en formato JSON consumible por RasaGenerator/script externo
     * GET: none
     */
    public function exportDashboardRasa() {
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit();
        }

        $items = Intent::getAllWithExamplesAndResponses();
        $out = [];
        foreach ($items as $row) {
            $intent = $row['intent'];
            $frases = $row['frases'];
            $respuestas = $row['respuestas'];

            $categoria = $intent->getCategoria() ? $intent->getCategoria()->getNombre() : null;

            $out[] = [
                'id' => $intent->getId(),
                'categoria' => $categoria,
                'nombre_intent' => $intent->getNombreIntent(),
                'preguntas' => $frases,
                'respuestas' => $respuestas
            ];
        }

        echo json_encode($out);
        exit();
    }

    /**
     * Crear nuevo intent (AJAX)
     * POST: nombre_intent, categoria_id, descripcion
     */
    public function createIntent() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        header('Content-Type: application/json; charset=utf-8');

        $nombre = $this->sanitizeString($_POST['nombre_intent'] ?? null);
        $categoriaId = is_numeric($_POST['categoria_id'] ?? null) ? (int)$_POST['categoria_id'] : null;
        $descripcion = $this->sanitizeString($_POST['descripcion'] ?? null);

        if (!$nombre) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nombre de intent requerido']);
            exit();
        }

        $categoria = $categoriaId ? Categoria::obtenerPorID($categoriaId) : null;
        $intent = new Intent(null, $nombre, $categoria, $descripcion);

        if ($intent->guardar()) {
            echo json_encode(['success' => true, 'id' => $intent->getId()]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al guardar intent']);
        }
        exit();
    }

    /**
     * Actualizar intent (AJAX)
     * POST: id, nombre_intent, descripcion
     */
    public function updateIntent() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        header('Content-Type: application/json; charset=utf-8');

        $id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : null;
        $nombre = $this->sanitizeString($_POST['nombre_intent'] ?? null);
        $descripcion = $this->sanitizeString($_POST['descripcion'] ?? null);

        if (!$id || !$nombre) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        $intent = Intent::obtenerPorID($id);
        if (!$intent) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Intent no encontrado']);
            exit();
        }

        $intent->setNombreIntent($nombre);
        $intent->setDescripcion($descripcion);

        if ($intent->actualizar()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al actualizar intent']);
        }
        exit();
    }

    public function deleteIntent() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        header('Content-Type: application/json; charset=utf-8');

        $id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de intent requerido']);
            exit();
        }

        $intent = Intent::obtenerPorID($id);
        if (!$intent) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Intent no encontrado']);
            exit();
        }

        require_once "model/FrasesEntrenamiento.class.php";
        foreach (FraseEntrenamiento::listar() as $frase) {
            if ($frase->getIntent() && $frase->getIntent()->getId() == $id) {
                $frase->eliminar();
            }
        }

        foreach (Respuesta::listar() as $respuesta) {
            if ($respuesta->getIntent() && $respuesta->getIntent()->getId() == $id) {
                $respuesta->eliminar();
            }
        }

        if ($intent->eliminar()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el intent']);
        }
        exit();
    }

    /**
     * Agregar frase de entrenamiento (AJAX)
     * POST: intent_id, texto_ejemplo
     */
    public function addFrase() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        header('Content-Type: application/json; charset=utf-8');
        require_once "model/FrasesEntrenamiento.class.php";

        $intentId = is_numeric($_POST['intent_id'] ?? null) ? (int)$_POST['intent_id'] : null;
        $texto = $this->sanitizeString($_POST['texto_ejemplo'] ?? null);

        if (!$intentId || !$texto) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        $intent = Intent::obtenerPorID($intentId);
        if (!$intent) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Intent no encontrado']);
            exit();
        }

        $frase = new FraseEntrenamiento(null, $intent, $texto);
        if ($frase->guardar()) {
            echo json_encode(['success' => true, 'id' => $frase->getId()]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al guardar frase']);
        }
        exit();
    }

    /**
     * Eliminar frase de entrenamiento (AJAX)
     * POST: id (frase_id)
     */
    public function deleteFrase() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        header('Content-Type: application/json; charset=utf-8');
        require_once "model/FrasesEntrenamiento.class.php";

        $id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de frase requerido']);
            exit();
        }

        $frase = FraseEntrenamiento::obtenerPorID($id);
        if (!$frase) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Frase no encontrada']);
            exit();
        }

        if ($frase->eliminar()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al eliminar frase']);
        }
        exit();
    }

    /**
     * Actualizar frase de entrenamiento (AJAX)
     * POST: id (frase_id), texto_ejemplo
     */
    public function updateFrase() {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        require_once "model/FrasesEntrenamiento.class.php";

        $id = $_POST['id'] ?? null;
        $texto = $_POST['texto_ejemplo'] ?? null;

        if (!$id || $texto === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        $frase = FraseEntrenamiento::obtenerPorID($id);
        if (!$frase) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Frase no encontrada']);
            exit();
        }

        $frase->setTextoEjemplo($texto);
        if ($frase->actualizar()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al actualizar la frase']);
        }
        exit();
    }

    /**
     * Agregar respuesta (AJAX)
     * POST: intent_id, texto_respuesta
     */
    public function addRespuesta() {
        header('Content-Type: application/json; charset=utf-8');
        
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        $intentId = is_numeric($_POST['intent_id'] ?? null) ? (int)$_POST['intent_id'] : null;
        $texto = $this->sanitizeString($_POST['texto_respuesta'] ?? null);

        if (!$intentId || !$texto) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        $intent = Intent::obtenerPorID($intentId);
        if (!$intent) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Intent no encontrado']);
            exit();
        }

        $respuesta = new Respuesta(null, $texto, true, null, $intent);
        if ($respuesta->guardar()) {
            echo json_encode(['success' => true, 'id' => $respuesta->getId()]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al guardar respuesta']);
        }
        exit();
    }

    /**
     * Actualizar respuesta (AJAX)
     * POST: id (respuesta_id), texto_respuesta
     */
    public function updateRespuesta() {
        header('Content-Type: application/json; charset=utf-8');
        
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        $id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : null;
        $texto = $this->sanitizeString($_POST['texto_respuesta'] ?? null);

        if (!$id || !$texto) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        try {
            $respuesta = Respuesta::obtenerPorID($id);
            if (!$respuesta) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Respuesta no encontrada']);
                exit();
            }

            $respuesta->setTexto($texto);

            if ($respuesta->actualizar()) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error al actualizar respuesta']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Alternar estado activo/inactivo de respuesta (AJAX)
     * POST: id (respuesta_id)
     */
    public function toggleRespuesta() {
        header('Content-Type: application/json; charset=utf-8');
        
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        $id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de respuesta requerido']);
            exit();
        }

        try {
            $respuesta = Respuesta::obtenerPorID($id);
            if (!$respuesta) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Respuesta no encontrada']);
                exit();
            }

            // Alternar estado
            $respuesta->setActiva(!$respuesta->getActiva());

            if ($respuesta->actualizar()) {
                echo json_encode(['success' => true, 'activa' => $respuesta->getActiva()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error al actualizar estado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Eliminar respuesta (AJAX)
     * POST: id (respuesta_id)
     */
    public function deleteRespuesta() {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        $id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de respuesta requerido']);
            exit();
        }

        try {
            $respuesta = Respuesta::obtenerPorID($id);
            if (!$respuesta) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Respuesta no encontrada']);
                exit();
            }

            if ($respuesta->eliminar()) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error al eliminar respuesta']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Dispara generación de YAML, entrenamiento y carga del modelo en Rasa (síncrono).
     * POST: none
     */
    public function retrain() {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

    
        $this->requireAuthJson();

        require_once __DIR__ . '/helpers/RasaGenerator.php';

        try {
            $res = RasaGenerator::trainAndLoadModel();
        } catch (Throwable $e) {
            
            $res = ['success' => false, 'phase' => 'exception', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()];
        }
        

        if (!is_array($res)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unexpected response from trainer']);
            exit();
        }

        if (!empty($res['success'])) {
            $out = ['success' => true, 'model_file' => $res['model_file'] ?? null];
            $json = json_encode($out, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            if ($json === false) {
                $fallback = ['success' => true, 'warning' => 'json_encode failed', 'json_error' => json_last_error_msg()];
                echo json_encode($fallback);
            } else {
                echo $json;
            }
        } else {
            http_response_code(500);
            // Incluimos detalles útiles para debugging
            $out = ['success' => false, 'details' => $res];
            $json = json_encode($out, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            if ($json === false) {
                // json_encode falló (probablemente por datos binarios o caracteres inválidos). Devolver fallback legible y guardar raw en log
                $msg = 'json_encode failed: ' . json_last_error_msg();
                @file_put_contents('/tmp/rasa_retrain.log', date('c') . " retrain:json_encode_failed: " . $msg . "\nraw_res=" . substr(print_r($res, true), 0, 2000) . "\n", FILE_APPEND);
                echo json_encode(['success' => false, 'error' => $msg, 'note' => 'See /tmp/rasa_retrain.log for raw details']);
            } else {
                echo $json;
            }
        }
        exit();
    }
}