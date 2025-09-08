<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: no-referrer-when-downgrade");

// Handle preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. Verify that the user has an active admin session
// ----- ¡CAMBIO AQUÍ! -----
// Se cambió $_SESSION['loggedin'] por $_SESSION['user_logged_in'] para que sea consistente.
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403); // Forbidden
    echo json_encode(["status" => "error", "message" => "Acceso no autorizado. Por favor, inicie sesión."]);
    exit();
}

include("conexion.php");

// 2. Get the message ID from the JSON request body
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id) || !is_numeric($data->id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "ID de mensaje inválido."]);
    exit();
}

$id = intval($data->id);

// 3. Prepare and execute the delete statement to prevent SQL injection
$consulta = "DELETE FROM datos WHERE id = ?";
$stmt = $conex->prepare($consulta);

if ($stmt) {
    $stmt->bind_param("i", $id); // 'i' for integer

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Mensaje eliminado con éxito."]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["status" => "error", "message" => "No se encontró el mensaje a eliminar."]);
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["status" => "error", "message" => "Error al ejecutar la consulta."]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error al preparar la consulta."]);
}

$conex->close();
