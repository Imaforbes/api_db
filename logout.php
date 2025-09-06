<?php
session_start(); // Reanuda la sesión para poder destruirla

// Configuración de encabezados
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Destruye todas las variables de sesión
session_unset();
// Destruye la sesión
session_destroy();

http_response_code(200);
echo json_encode(["status" => "success", "message" => "Sesión cerrada correctamente."]);
