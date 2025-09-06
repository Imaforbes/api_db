<?php
$server = "localhost";
$user = "root";
$pw = "";
$db = "portfolio";

$conex = new mysqli($server, $user, $pw, $db);

// Activar el manejo de errores
if ($conex->connect_error) {
    // Esto detendrá el script si la conexión falla, facilitando la depuración.
    die("Conexión fallida: " . $conex->connect_error);
}

// Establecer el juego de caracteres (buena práctica)
$conex->set_charset("utf8mb4");
