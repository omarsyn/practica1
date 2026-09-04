<?php
class Token
{
    private $conn;
    private $table = "api_tokens";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createToken($userId, $expirationMinutes = 60)
    {
        $this->revokeUserTokens($userId);

        $rawToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+$expirationMinutes minutes"));

        $query = "INSERT INTO " . $this->table . " (user_id, token, expires_at, revoked) VALUES (:user_id, :token, :expires_at, 0)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":token", $rawToken);
        $stmt->bindParam(":expires_at", $expiresAt);

        if ($stmt->execute()) {
            return [
                'access_token' => $rawToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt
            ];
        }
        return false;
    }

    public function validateToken($rawToken)
    {
        $query = "SELECT t.*, u.id as user_id, u.username, u.email 
                  FROM " . $this->table . " t 
                  JOIN api_users u ON t.user_id = u.id 
                  WHERE t.token = :token 
                    AND t.revoked = 0 
                    AND t.expires_at > NOW() 
                    AND u.status = 'ACTIVE' 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $rawToken);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function revokeToken($rawToken)
    {
        $query = "UPDATE " . $this->table . " SET revoked = 1 WHERE token = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $rawToken);
        return $stmt->execute();
    }

    private function revokeUserTokens($userId)
    {
        $query = "UPDATE " . $this->table . " SET revoked = 1 WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
    }
}
?>
