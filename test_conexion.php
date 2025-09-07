<?php
// test_conexion.php

echo "Intentando conectar a la base de datos...<br>";

include("conexion.php");

// Si el script llega hasta aquí, la conexión fue exitosa.
echo "¡Conexión exitosa!";

$conex->close();
