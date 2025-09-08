<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start(); // Reanuda la sesión para verificar si el usuario está logueado

// Configuración de encabezados
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Credentials: true");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: no-referrer-when-downgrade");



// --- REEMPLAZO DEL SISTEMA DE SEGURIDAD ---
// Verificamos si la variable de sesión 'user_logged_in' existe y es verdadera
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403); // Forbidden
    echo json_encode(["error" => "Acceso no autorizado. Por favor, inicie sesión."]);
    exit(); // Detenemos la ejecución
}

// El resto del código es el mismo
include("conexion.php");
$mensajes = [];
$consulta = "SELECT id, nombre, email, mensaje, fecha FROM datos ORDER BY fecha DESC";
$resultado = $conex->query($consulta);

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $mensajes[] = $fila;
    }
    $resultado->free();
}

$conex->close();
echo json_encode($mensajes);
