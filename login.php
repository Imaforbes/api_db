<?php
session_start(); // Inicia o reanuda una sesión

include("conexion.php");

// Configuración de encabezados para CORS y JSON
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar preflight (OPTIONS) para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Leer el JSON enviado desde React
$data = json_decode(file_get_contents("php://input"));

if (!$data || empty($data->username) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Usuario y contraseña requeridos."]);
    exit();
}

$username = $data->username;
$password = $data->password;

// Buscar al usuario en la base de datos
$stmt = $conex->prepare("SELECT password_hash FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // Verificar si la contraseña coincide con el hash almacenado
    if (password_verify($password, $user['password_hash'])) {
        // La contraseña es correcta, creamos la sesión
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Login exitoso."]);
    } else {
        // Contraseña incorrecta
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Credenciales incorrectas."]);
    }
} else {
    // Usuario no encontrado
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Credenciales incorrectas."]);
}

$stmt->close();
$conex->close();
