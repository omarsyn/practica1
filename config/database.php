<?php
class Database
{
    private $host = "localhost";
    private $db_name = "bd_22031401";
    private $username = "u22031401";
    private $password = "22031401"; // Reemplaza con tu contraseña real de MySQL
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // No hacer echo directo para no romper el formato JSON
            http_response_code(500);
            echo json_encode(["message" => "Error de conexion a la base de datos"]);
            exit();
        }

        return $this->conn;
    }
}
?>
