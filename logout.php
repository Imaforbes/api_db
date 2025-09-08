<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start(); // Reanuda la sesión para poder destruirla

// Configuración de encabezados
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: no-referrer-when-downgrade");

// Destruye todas las variables de sesión
session_unset();
// Destruye la sesión
session_destroy();

http_response_code(200);
echo json_encode(["status" => "success", "message" => "Sesión cerrada correctamente."]);
