<?php
// ¡NADA PUEDE ESTAR ANTES DE ESTA LÍNEA! NI ESPACIOS, NI TEXTO.

// ESTE BLOQUE ES EL PERMISO QUE NECESITA EL NAVEGADOR
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar preflight (OPTIONS) para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
   http_response_code(200);
   exit();
}

// El resto de tu código...
include("conexion.php");

// 1. LEER EL JSON ENVIADO DESDE REACT
$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

// Verificar que se recibieron datos
if (!$data || empty($data->name) || empty($data->email) || empty($data->message)) {
   // Si faltan datos, enviar una respuesta de error 400 (Bad Request)
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "Datos incompletos. Por favor complete todos los campos."]);
   exit();
}

// 2. LIMPIAR Y VALIDAR LOS DATOS
$name = trim($data->name);
$email = trim($data->email);
$message = trim($data->message);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "El formato del email es inválido."]);
   exit();
}

// 3. PREPARAR Y EJECUTAR LA CONSULTA SQL
$date = date("Y-m-d");

// ----- CAMBIO AQUÍ -----
// Se eliminó la columna 'asunto' de la consulta.
$consulta = "INSERT INTO datos(nombre, email, mensaje, fecha) VALUES (?, ?, ?, ?)";
$stmt = $conex->prepare($consulta);

if ($stmt) {
   // ----- Y CAMBIO AQUÍ -----
   // El tipo ahora es "ssss" (4 strings) y se eliminó la variable del asunto.
   $stmt->bind_param("ssss", $name, $email, $message, $date);

   if ($stmt->execute()) {
      // Si la inserción es exitosa, enviar una respuesta 200 (OK)
      http_response_code(200);
      echo json_encode(["status" => "success", "message" => "¡Mensaje enviado con éxito!"]);
   } else {
      // Si hay un error en la base de datos, enviar una respuesta 500 (Internal Server Error)
      http_response_code(500);
      echo json_encode(["status" => "error", "message" => "Error al guardar el mensaje."]);
   }
   $stmt->close();
} else {
   http_response_code(500);
   echo json_encode(["status" => "error", "message" => "Error en el servidor al preparar la consulta."]);
}

$conex->close();
