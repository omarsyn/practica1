<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ApiUser.php'; // <-- Cambio a ApiUser
require_once __DIR__ . '/../../models/Token.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';

class AuthResource
{
    private $db;
    private $userModel;
    private $tokenModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new ApiUser($this->db); // <-- Usar ApiUser
        $this->tokenModel = new Token($this->db);
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['username']) || empty($data['password'])) {
            http_response_code(401);
            echo json_encode([
                "error" => "invalid_credentials",
                "message" => "Usuario o contraseña incorrectos"
            ]);
            return;
        }

        $user = $this->userModel->findByUsername($data['username']);

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            http_response_code(401);
            echo json_encode([
                "error" => "invalid_credentials",
                "message" => "Usuario o contraseña incorrectos"
            ]);
            return;
        }

        $tokenResult = $this->tokenModel->createToken($user['id'], 60);

        if ($tokenResult) {
            http_response_code(200);
            echo json_encode($tokenResult);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al generar el token"]);
        }
    }

    public function logout()
    {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : null);

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->tokenModel->revokeToken($matches[1]);
        }

        http_response_code(200);
        echo json_encode(["message" => "Sesión cerrada y token revocado con éxito"]);
    }

    public function me()
    {
        $authUser = AuthMiddleware::authenticate();
        http_response_code(200);
        echo json_encode([
            "id" => $authUser['user_id'],
            "username" => $authUser['username'],
            "email" => $authUser['email']
        ]);
    }
}
?>
