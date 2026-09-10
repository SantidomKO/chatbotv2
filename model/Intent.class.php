<?php
include_once "Database.class.php";
include_once "Categoria.class.php";

class Intent
{
    private $id;
    private $nombre_intent;
    private $categoria;
    private $descripcion;
    private $conexion;

    public function __construct($id = null, $nombre_intent = null, $categoria = null, $descripcion = null)
    {
        $this->id = $id;
        $this->nombre_intent = $nombre_intent;
        $this->categoria = $categoria;
        $this->descripcion = $descripcion;
        $this->conexion = Database::getInstance()->GetConnection();
    }

    public function guardar()
    {
        $sql = "INSERT INTO intents (`nombre_intent`, `categoria_id`, `descripcion`) VALUES (?, ?, ?);";
        $stmt = $this->conexion->prepare($sql);
        $categoriaId = $this->categoria ? $this->categoria->getId() : null;
        return $stmt->execute([$this->nombre_intent, $categoriaId, $this->descripcion]);
    }

    public function actualizar()
    {
        $sql = "UPDATE intents SET `nombre_intent` = ?, `categoria_id` = ?, `descripcion` = ? WHERE `id` = ?;";
        $stmt = $this->conexion->prepare($sql);
        $categoriaId = $this->categoria ? $this->categoria->getId() : null;
        return $stmt->execute([$this->nombre_intent, $categoriaId, $this->descripcion, $this->id]);
    }

    public function eliminar()
    {
        $sql = "DELETE FROM intents WHERE `id` = ?;";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$this->id]);
    }

    public static function listar()
    {
        $conexion = Database::getInstance()->getConnection();
        $sql = "SELECT i.*, c.id AS categoria_id, c.nombre AS categoria_nombre, c.descripcion AS categoria_descripcion
                FROM intents i
                LEFT JOIN categorias c ON i.categoria_id = c.id";
        $stmt = $conexion->query($sql);
        $intents = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categoria = null;
            if (!empty($fila['categoria_id'])) {
                $categoria = new Categoria($fila['categoria_id'], $fila['categoria_nombre'], $fila['categoria_descripcion']);
            }
            $intents[] = new Intent($fila['id'], $fila['nombre_intent'], $categoria, $fila['descripcion']);
        }
        return $intents;
    }

    public static function obtenerPorID($id)
    {
        $conexion = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM intents WHERE `id` = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($resultado) {
            $categoria = null;
            if (!empty($resultado['categoria_id'])) {
                $categoria = Categoria::obtenerPorID($resultado['categoria_id']);
            }
            return new Intent($resultado['id'], $resultado['nombre_intent'], $categoria, $resultado['descripcion']);
        }
        return null;
    }

    // Setters
    public function setNombreIntent($nombre_intent)
    {
        if ($nombre_intent === null || $nombre_intent === "") {
            echo "El nombre del intent no puede estar vacío";
            return;
        }
        $this->nombre_intent = $nombre_intent;
    }

    public function setCategoria($categoria)
    {
        if ($categoria !== null && !($categoria instanceof Categoria)) {
            echo "La categoría debe ser un objeto Categoria o null";
            return;
        }
        $this->categoria = $categoria;
    }

    public function setDescripcion($descripcion)
    {
        if ($descripcion === null || $descripcion === "") {
            echo "La descripción no puede estar vacía";
            return;
        }
        $this->descripcion = $descripcion;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNombreIntent() { return $this->nombre_intent; }
    public function getCategoria() { return $this->categoria; }
    public function getDescripcion() { return $this->descripcion; }

    public function getCategoriaId() { return $this->categoria ? $this->categoria->getId() : null; }

    /**
     * Devuelve todos los intents con sus frases y respuestas asociadas.
     * Formato: [ ['intent' => Intent, 'frases' => [...], 'respuestas' => [...]], ... ]
     */
    public static function getAllWithExamplesAndResponses()
    {
        require_once "FrasesEntrenamiento.class.php";
        require_once "Respuesta.class.php";

        $intents = self::listar();
        $result = [];

        $allFrases = FraseEntrenamiento::listar();
        $allRespuestas = Respuesta::listar();

        foreach ($intents as $intent) {
            $intentId = $intent->getId();

            $frases = [];
            foreach ($allFrases as $frase) {
                if ($frase->getIntent() && $frase->getIntent()->getId() == $intentId) {
                    $frases[] = $frase->getTextoEjemplo();
                }
            }

            $resps = [];
            foreach ($allRespuestas as $resp) {
                if ($resp->getIntent() && $resp->getIntent()->getId() == $intentId) {
                    $resps[] = [
                        'id' => $resp->getId(),
                        'texto_respuesta' => $resp->getTexto(),
                        'activa' => $resp->getActiva()
                    ];
                }
            }

            $result[] = [
                'intent' => $intent,
                'frases' => $frases,
                'respuestas' => $resps
            ];
        }

        return $result;
    }
}
?>