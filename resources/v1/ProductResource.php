<?php
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../config/database.php';

class ProductResource {
    private $db;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
    }

    public function index() {
        $stmt = $this->product->readAll();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode($products);
    }

    public function show($id) {
        $this->product->id = $id;
        $data = $this->product->readOne();
        if ($data) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Producto no encontrado"]);
        }
    }

    public function store() {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->sku) && !empty($data->name) && isset($data->price)) {
            $this->product->sku = $data->sku;
            $this->product->name = $data->name;
            $this->product->description = $data->description ?? '';
            $this->product->price = $data->price;
            $this->product->stock = $data->stock ?? 0;

            if ($this->product->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Producto creado con éxito"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error al crear producto"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos"]);
        }
    }

    public function update($id) {
        $data = json_decode(file_get_contents("php://input"));
        $this->product->id = $id;
        $this->product->sku = $data->sku;
        $this->product->name = $data->name;
        $this->product->description = $data->description ?? '';
        $this->product->price = $data->price;
        $this->product->stock = $data->stock ?? 0;

        if ($this->product->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Producto actualizado con éxito"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al actualizar producto"]);
        }
    }

    public function destroy($id) {
        $this->product->id = $id;
        if ($this->product->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Producto eliminado"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al eliminar producto"]);
        }
    }
}
