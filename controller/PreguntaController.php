<?php
require_once "model/FrasesEntrenamiento.class.php";
require_once "model/Intent.class.php";

class PreguntaController {
    /**
     * Guardar una nueva frase de entrenamiento (pregunta)
     * POST: texto, intent_id
     */
    public function guardar() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        $intent_id = $_POST['intent_id'] ?? null;
        $texto = $_POST['texto'] ?? null;

        if (!$intent_id || !$texto) {
            $_SESSION['error'] = 'Datos incompletos para guardar la frase de entrenamiento';
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        $intent = Intent::obtenerPorID($intent_id);
        if (!$intent) {
            $_SESSION['error'] = 'Intent no encontrado';
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        $frase = new FraseEntrenamiento(null, $intent, $texto);
        if ($frase->guardar()) {
            $_SESSION['success'] = 'Frase de entrenamiento guardada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al guardar la frase de entrenamiento';
        }

        header('Location: index.php?controller=consultas&action=index');
        exit();
    }

    /**
     * Actualizar una frase de entrenamiento existente
     * POST: id, texto, intent_id
     */
    public function actualizar() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        $id = $_POST['id'] ?? null;
        $intent_id = $_POST['intent_id'] ?? null;
        $texto = $_POST['texto'] ?? null;

        if (!$id || !$intent_id || !$texto) {
            $_SESSION['error'] = 'Datos incompletos para actualizar la frase de entrenamiento';
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        $frase = FraseEntrenamiento::obtenerPorID($id);
        if (!$frase) {
            $_SESSION['error'] = 'Frase de entrenamiento no encontrada';
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        $intent = Intent::obtenerPorID($intent_id);
        if (!$intent) {
            $_SESSION['error'] = 'Intent no encontrado';
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        $frase->setIntent($intent);
        $frase->setTextoEjemplo($texto);

        if ($frase->actualizar()) {
            $_SESSION['success'] = 'Frase de entrenamiento actualizada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la frase de entrenamiento';
        }

        header('Location: index.php?controller=consultas&action=index');
        exit();
    }

    /**
     * Eliminar una frase de entrenamiento
     * GET: id
     */
    public function eliminar() {
        // Session is started in index.php bootstrap

        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit();
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'ID de frase no proporcionado';
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        $frase = FraseEntrenamiento::obtenerPorID($id);
        if (!$frase) {
            $_SESSION['error'] = 'Frase de entrenamiento no encontrada';
            header('Location: index.php?controller=consultas&action=index');
            exit();
        }

        if ($frase->eliminar()) {
            $_SESSION['success'] = 'Frase de entrenamiento eliminada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar la frase de entrenamiento';
        }

            header('Location: index.php?controller=consultas&action=index');
        exit();
    }
}
?>
