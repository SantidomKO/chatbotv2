<?php

include_once "Database.class.php";

class Respuesta {
    private $id;
    private $texto;
    private $activa;
    private $fecha_actualizacion;
    private $intent;
    private $conexion;

    public function __construct($id=null, $texto=null, $activa=true, $fecha_actualizacion=null, $intent=null) {
        $this->id = $id;
        $this->texto = $texto;
        $this->activa = $activa;
        $this->fecha_actualizacion = $fecha_actualizacion;
        $this->intent = $intent;
        $this->conexion = Database::getInstance()->getConnection();
    }

    public function guardar() {
        $sql = "INSERT INTO respuestas (`texto_respuesta`, `activa`, `intent_id`) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $intentId = $this->intent ? $this->intent->getId() : null;
        if ($stmt->execute([$this->texto, $this->activa ? 1 : 0, $intentId])) {
            $this->id = $this->conexion->lastInsertId();
            return true;
        }
        return false;
    }

    public function actualizar() {
        $sql = "UPDATE respuestas SET `texto_respuesta`=?, `activa`=?, `intent_id`=? WHERE `id_respuesta`=?";
        $stmt = $this->conexion->prepare($sql);
        $intentId = $this->intent ? $this->intent->getId() : null;
        return $stmt->execute([$this->texto, $this->activa ? 1 : 0, $intentId, $this->id]);
    }

    public function eliminar() {
        $sql = "DELETE FROM respuestas WHERE `id_respuesta`=?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$this->id]);
    }

    public static function listar() {
        $conexion = Database::getInstance()->getConnection();
        $sql = "SELECT r.id_respuesta, r.texto_respuesta AS texto, r.activa, r.fecha_actualizacion, r.intent_id, i.id AS intent_id_int, i.nombre_intent FROM respuestas r LEFT JOIN intents i ON r.intent_id = i.id";
        try {
            $stmt = $conexion->query($sql);
        } catch (Exception $e) {
            return [];
        }
        $items = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $intent = null;
            if (!empty($fila['intent_id_int'])) {
                $intent = new Intent($fila['intent_id_int'], $fila['nombre_intent']);
            }
            $items[] = new Respuesta($fila['id_respuesta'], $fila['texto'], (bool)$fila['activa'], $fila['fecha_actualizacion'], $intent);
        }
        return $items;
    }

    public static function obtenerPorID($id) {
        $conexion = Database::getInstance()->getConnection();
        $sql = "SELECT r.id_respuesta, r.texto_respuesta AS texto, r.activa, r.fecha_actualizacion, r.intent_id, i.id AS intent_id_int, i.nombre_intent FROM respuestas r LEFT JOIN intents i ON r.intent_id = i.id WHERE r.id_respuesta=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $intent = null;
            if (!empty($r['intent_id_int'])) {
                $intent = new Intent($r['intent_id_int'], $r['nombre_intent']);
            }
            return new Respuesta($r['id_respuesta'], $r['texto'], (bool)$r['activa'], $r['fecha_actualizacion'], $intent);
        }
        return null;
    }

    public function getId() { return $this->id; }
    public function getTexto() { return $this->texto; }
    public function getActiva() { return $this->activa; }
    public function getFechaActualizacion() { return $this->fecha_actualizacion; }
    public function getIntent() { return $this->intent; }

    public function setIntent($intent) {
        $this->intent = $intent;
    }

    public function setTexto($texto) {
        $this->texto = $texto;
    }

    public function setActiva($activa) {
        $this->activa = $activa;
    }
}

?>