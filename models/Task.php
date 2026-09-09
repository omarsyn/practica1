<?php

class Task {
    private $conn;
    private $table_name = "tareas";

    // Propiedades según la tabla 'tareas'
    public $id;
    public $titulo;
    public $completada;
    public $fecha_creacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las tareas
    public function getAll() {
        $query = "SELECT id, titulo, completada, fecha_creacion FROM " . $this->table_name . " ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener una tarea específica por ID
    public function getById($id) {
        $query = "SELECT id, titulo, completada, fecha_creacion FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear una nueva tarea
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (titulo, completada) VALUES (:titulo, :completada)";
        $stmt = $this->conn->prepare($query);

        // Limpieza de datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->completada = $this->completada ? 1 : 0;

        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':completada', $this->completada, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Actualizar una tarea existente
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET titulo = :titulo, completada = :completada WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Limpieza de datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->completada = $this->completada ? 1 : 0;
        $this->id = (int)$this->id;

        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':completada', $this->completada, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}