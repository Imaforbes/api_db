<?php

/**
 * Direct Test of get_mensajes.php
 * Test the actual API endpoint
 */

// Start session and simulate login
session_start();
$_SESSION['user_logged_in'] = true;

echo "<h1>Direct Test of get_mensajes.php</h1>";
echo "<style>body{font-family:Arial;margin:20px;} pre{background:#f8f8f8;padding:10px;border:1px solid #ddd;}</style>";

// Capture output
ob_start();

try {
    // Include the actual get_mensajes.php file
    include 'get_mensajes.php';

    $output = ob_get_clean();

    echo "<h2>Raw API Response:</h2>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";

    // Try to decode JSON
    $jsonData = json_decode($output, true);
    if ($jsonData) {
        echo "<h2>Decoded JSON:</h2>";
        echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT) . "</pre>";

        if (isset($jsonData['data'])) {
            echo "<h2>Messages Data:</h2>";
            echo "<p><strong>Number of messages:</strong> " . count($jsonData['data']) . "</p>";

            if (count($jsonData['data']) > 0) {
                echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
                echo "<tr><th>ID</th><th>Fecha</th><th>Nombre</th><th>Email</th><th>Mensaje</th></tr>";

                foreach ($jsonData['data'] as $message) {
                    echo "<tr>";
                    echo "<td>{$message['id']}</td>";
                    echo "<td>{$message['fecha']}</td>";
                    echo "<td>" . htmlspecialchars($message['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($message['email']) . "</td>";
                    echo "<td>" . htmlspecialchars(substr($message['mensaje'], 0, 50)) . "...</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠️ No messages in API response</p>";
            }
        } else {
            echo "<p class='error'>❌ No 'data' field in API response</p>";
        }
    } else {
        echo "<p class='error'>❌ API response is not valid JSON</p>";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
