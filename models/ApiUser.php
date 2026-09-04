<?php
class ApiUser
{
    private $conn;
    private $table = "api_users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function findByUsername($username)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username AND status = 'ACTIVE' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $query = "SELECT id, username, email, status, created_at FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
