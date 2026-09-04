<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';

class ProductResourceV2
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function index()
    {
        AuthMiddleware::authenticate();

        $query = "SELECT * FROM productos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($products);
    }

    public function show($id)
    {
        AuthMiddleware::authenticate();

        $query = "SELECT * FROM productos WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            http_response_code(200);
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Producto no encontrado"]);
        }
    }

    public function store()
    {
        AuthMiddleware::authenticate();

        $data = json_decode(file_get_contents("php://input"), true);

        $query = "INSERT INTO productos (sku, name, description, price, stock) VALUES (:sku, :name, :description, :price, :stock)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":sku", $data['sku']);
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":price", $data['price']);
        $stmt->bindParam(":stock", $data['stock']);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Producto creado con éxito"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al crear producto"]);
        }
    }

    public function update($id)
    {
        AuthMiddleware::authenticate();

        $data = json_decode(file_get_contents("php://input"), true);

        $query = "UPDATE productos SET sku = :sku, name = :name, description = :description, price = :price, stock = :stock WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":sku", $data['sku']);
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":price", $data['price']);
        $stmt->bindParam(":stock", $data['stock']);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Producto actualizado con éxito"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al actualizar producto"]);
        }
    }

    public function destroy($id)
    {
        AuthMiddleware::authenticate();

        $query = "DELETE FROM productos WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Producto eliminado con éxito"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al eliminar producto"]);
        }
    }
}
?>
