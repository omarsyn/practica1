<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/PasswordGenerator.php';
require_once __DIR__ . '/../src/Services/PasswordValidator.php';

use Services\PasswordGenerator;
use Services\PasswordValidator;

$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Parse URL Path
$path = parse_url($requestUri, PHP_URL_PATH);

// --- ENDPOINT 1: POST /v1/passwords/generate ---
if (strpos($path, '/passwords/generate') !== false && $method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    try {
        $result = PasswordGenerator::generate($input);
        http_response_code(200);
        echo json_encode($result);
    } catch (\InvalidArgumentException $e) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_parameters', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- ENDPOINT 2: POST /v1/passwords/validate ---
if (strpos($path, '/passwords/validate') !== false && $method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['password']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_password', 'message' => "El campo 'password' es obligatorio."]);
        exit();
    }
    $result = PasswordValidator::validate($input['password'], $input['username'] ?? null);
    http_response_code(200);
    echo json_encode($result);
    exit();
}

// --- ENDPOINT 3: GET /v1/passwords/policy ---
if (strpos($path, '/passwords/policy') !== false && $method === 'GET') {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("SELECT * FROM password_policies WHERE id = 1");
    $stmt->execute();
    $policy = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'minLength' => (int)$policy['min_length'],
        'maxLength' => (int)$policy['max_length'],
        'requireUppercase' => (bool)$policy['require_uppercase'],
        'requireLowercase' => (bool)$policy['require_lowercase'],
        'requireNumbers' => (bool)$policy['require_numbers'],
        'requireSymbols' => (bool)$policy['require_symbols'],
        'disallowCommonPasswords' => (bool)$policy['disallow_common_passwords'],
        'disallowUsernameInPassword' => (bool)$policy['disallow_username_in_password'],
        'disallowSequentialCharacters' => (bool)$policy['disallow_sequential_characters'],
        'passwordHistoryLimit' => (int)$policy['password_history_limit'],
        'expirationDays' => (int)$policy['expiration_days']
    ]);
    exit();
}

// --- ENDPOINT 4: POST /v1/auth/register ---
if (strpos($path, '/auth/register') !== false && $method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['username'], $input['email'], $input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_body', 'message' => 'Faltan campos obligatorios.']);
        exit();
    }

    $validation = PasswordValidator::validate($input['password'], $input['username']);
    if (!$validation['isValid']) {
        http_response_code(422);
        echo json_encode($validation);
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Check conflict
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
    $stmt->execute([':u' => $input['username'], ':e' => $input['email']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'user_exists', 'message' => 'El usuario o correo ya existe.']);
        exit();
    }

    // Insert User
    $id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    $hash = password_hash($input['password'], PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (id, username, email, password_hash) VALUES (:id, :u, :e, :p)");
    $stmt->execute([':id' => $id, ':u' => $input['username'], ':e' => $input['email'], ':p' => $hash]);

    http_response_code(201);
    echo json_encode([
        'id' => $id,
        'username' => $input['username'],
        'email' => $input['email'],
        'createdAt' => date('Y-m-d\TH:i:s\Z')
    ]);
    exit();
}

// Ruta no encontrada
http_response_code(404);
echo json_encode(['error' => 'not_found', 'message' => 'Endpoint no encontrado.']);
