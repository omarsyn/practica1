<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Token.php';

class AuthMiddleware
{
    public static function authenticate()
    {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : null);

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode([
                "error" => "unauthorized",
                "message" => "Token inválido, expirado o no proporcionado"
            ]);
            exit();
        }

        $jwtToken = $matches[1];
        $database = new Database();
        $db = $database->getConnection();
        $tokenModel = new Token($db);

        $userData = $tokenModel->validateToken($jwtToken);

        if (!$userData) {
            http_response_code(401);
            echo json_encode([
                "error" => "unauthorized",
                "message" => "Token inválido, expirado o no proporcionado"
            ]);
            exit();
        }

        return $userData;
    }
}
?>
