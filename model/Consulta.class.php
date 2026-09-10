<?php
include_once "Database.class.php";
include_once "Intent.class.php";
include_once "Usuario.class.php";
date_default_timezone_set("America/Argentina/Buenos_Aires");

class Consulta
{
    private $id;
    private $usuario; // Usuario object or null or int id
    private $intent;  // Intent object or null or intent id or name
    private $texto_pregunta;
    private $confianza;
    private $fue_respondida; // 0 or 1
    private $fecha_hora;
    private $conexion;

    public function __construct($id = null, $usuario = null, $intent = null, $texto_pregunta = null, $confianza = null, $fue_respondida = null, $fecha_hora = null)
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->intent = $intent;
        $this->texto_pregunta = $texto_pregunta;
        $this->confianza = $confianza;
        $this->fue_respondida = $fue_respondida;
        $this->fecha_hora = $fecha_hora ?: date('Y-m-d H:i:s');
        $this->conexion = Database::getInstance()->GetConnection();
    }

    public function guardar()
    {
        $sql = "INSERT INTO consultas (`usuario_id`, `intent_id`, `texto_pregunta`, `confianza`, `fue_respondida`, `fecha_hora`) VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $this->conexion->prepare($sql);
        $usuarioId = null;
        if ($this->usuario instanceof Usuario) {
            $usuarioId = $this->usuario->getId();
        } elseif (is_numeric($this->usuario)) {
            $usuarioId = $this->usuario;
        }
        $intentId = null;
        if ($this->intent instanceof Intent) {
            $intentId = $this->intent->getId();
        } elseif (is_numeric($this->intent)) {
            $intentId = $this->intent;
        }
        // Asegurar que la fecha/hora se establezca al guardar
        $this->fecha_hora = date('Y-m-d H:i:s');
        return $stmt->execute([$usuarioId, $intentId, $this->texto_pregunta, $this->confianza, $this->fue_respondida, $this->fecha_hora]);
    }

    public function actualizar()
    {
        $sql = "UPDATE consultas SET `usuario_id` = ?, `intent_id` = ?, `texto_pregunta` = ?, `confianza` = ?, `fue_respondida` = ?, `fecha_hora` = ? WHERE `id` = ?;";
        $stmt = $this->conexion->prepare($sql);
        $usuarioId = $this->usuario instanceof Usuario ? $this->usuario->getId() : (is_numeric($this->usuario) ? $this->usuario : null);
        $intentId = $this->intent instanceof Intent ? $this->intent->getId() : (is_numeric($this->intent) ? $this->intent : null);
        // Si no se pasó fecha_hora, usar la fecha actual
        $this->fecha_hora = $this->fecha_hora ?: date('Y-m-d H:i:s');
        return $stmt->execute([$usuarioId, $intentId, $this->texto_pregunta, $this->confianza, $this->fue_respondida, $this->fecha_hora, $this->id]);
    }

    public function eliminar()
    {
        $sql = "DELETE FROM consultas WHERE `id` = ?;";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$this->id]);
    }

    public static function listar()
    {
        $conexion = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM consultas ORDER BY fecha_hora DESC";
        $stmt = $conexion->query($sql);
        $consultas = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuario = null;
            if (!empty($fila['usuario_id'])) {
                $usuario = Usuario::obtenerPorID($fila['usuario_id']);
            }
            $intent = null;
            if (!empty($fila['intent_id'])) {
                $intent = Intent::obtenerPorID($fila['intent_id']);
            }
            $consultas[] = new Consulta($fila['id'], $usuario, $intent, $fila['texto_pregunta'], $fila['confianza'], $fila['fue_respondida'], $fila['fecha_hora']);
        }
        return $consultas;
    }

    public static function listarNoRespondidas()
    {
        $conexion = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM consultas WHERE fue_respondida = 0 ORDER BY fecha_hora DESC";
        $stmt = $conexion->query($sql);
        $consultas = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuario = null;
            if (!empty($fila['usuario_id'])) {
                $usuario = Usuario::obtenerPorID($fila['usuario_id']);
            }
            $intent = null;
            if (!empty($fila['intent_id'])) {
                $intent = Intent::obtenerPorID($fila['intent_id']);
            }
            $consultas[] = new Consulta($fila['id'], $usuario, $intent, $fila['texto_pregunta'], $fila['confianza'], $fila['fue_respondida'], $fila['fecha_hora']);
        }
        return $consultas;
    }

    public static function obtenerPorID($id)
    {
        $conexion = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM consultas WHERE `id` = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            $usuario = null;
            if (!empty($fila['usuario_id'])) {
                $usuario = Usuario::obtenerPorID($fila['usuario_id']);
            }
            $intent = null;
            if (!empty($fila['intent_id'])) {
                $intent = Intent::obtenerPorID($fila['intent_id']);
            }
            return new Consulta($fila['id'], $usuario, $intent, $fila['texto_pregunta'], $fila['confianza'], $fila['fue_respondida'], $fila['fecha_hora']);
        }
        return null;
    }

    // Helper: registra una consulta a partir de datos de Rasa.
    // $intentParam puede ser nombre de intent o id.
    public static function registrarDesdeRasa($texto_pregunta, $confianza, $usuarioId = null, $intentParam = null, $umbral = 0.55)
    {
        $conexion = Database::getInstance()->getConnection();
        // Resolver intent id si se pasa nombre
        $intentId = null;
        if ($intentParam !== null) {
            if (is_numeric($intentParam)) {
                $intentId = $intentParam;
            } else {
                $sql = "SELECT id FROM intents WHERE nombre_intent = ? LIMIT 1";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([$intentParam]);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($r) $intentId = $r['id'];
            }
        }

        // Se considera que NO fue respondida cuando confidence <= umbral (<= 0.55)
        $fue_respondida = ($confianza !== null && floatval($confianza) > floatval($umbral)) ? 1 : 0;
        $consulta = new Consulta(null, $usuarioId, $intentId, $texto_pregunta, $confianza, $fue_respondida, date('Y-m-d H:i:s'));
        $ok = $consulta->guardar();
        return $ok ? $consulta : null;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsuario() { return $this->usuario; }
    public function getIntent() { return $this->intent; }
    public function getTextoPregunta() { return $this->texto_pregunta; }
    public function getConfianza() { return $this->confianza; }
    public function getFueRespondida() { return $this->fue_respondida; }
    public function getFechaHora() { return $this->fecha_hora; }
}
?>
