<?php
require_once __DIR__ . '/../../models/Task.php';

class TaskResource {
    private $db;
    private $task;

    public function __construct($db) {
        $this->db = $db;
        $this->task = new Task($db);
    }

    // Maneja GET /api/v2/tareas y GET /api/v2/tareas/{id}
    public function get($id = null) {
        header('Content-Type: application/json; charset=utf-8');

        if ($id) {
            $item = $this->task->getById($id);
            if ($item) {
                // Convertir completada a booleano para cumplir la especificación OpenAPI
                $item['completada'] = (bool)$item['completada'];
                http_response_code(200);
                echo json_encode($item);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Tarea no encontrada."]);
            }
        } else {
            $stmt = $this->task->getAll();
            $tareas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['completada'] = (bool)$row['completada'];
                $tareas[] = $row;
            }
            http_response_code(200);
            echo json_encode($tareas);
        }
    }

    // Maneja POST /api/v2/tareas
    public function post() {
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['titulo']) || empty(trim($data['titulo']))) {
            http_response_code(400);
            echo json_encode(["message" => "El campo 'titulo' es obligatorio."]);
            return;
        }

        $this->task->titulo = $data['titulo'];
        $this->task->completada = isset($data['completada']) ? $data['completada'] : false;

        if ($this->task->create()) {
            http_response_code(201);
            echo json_encode([
                "message" => "Tarea creada exitosamente.",
                "id" => (int)$this->task->id,
                "titulo" => $this->task->titulo,
                "completada" => (bool)$this->task->completada
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al intentar crear la tarea."]);
        }
    }

    // Maneja PUT /api/v2/tareas/{id}
    public function put($id = null) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Se requiere especificar el ID de la tarea."]);
            return;
        }

        $existente = $this->task->getById($id);
        if (!$existente) {
            http_response_code(404);
            echo json_encode(["message" => "Tarea no encontrada."]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $this->task->id = $id;
        $this->task->titulo = isset($data['titulo']) ? $data['titulo'] : $existente['titulo'];
        $this->task->completada = isset($data['completada']) ? $data['completada'] : $existente['completada'];

        if ($this->task->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Tarea actualizada exitosamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al actualizar la tarea."]);
        }
    }
}