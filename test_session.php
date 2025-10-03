<?php

/**
 * Test Session Status
 * Check if session is working properly
 */

session_start();

echo "<h1>Session Test</h1>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "</p>";
echo "<p><strong>User Logged In:</strong> " . (isset($_SESSION['user_logged_in']) ? ($_SESSION['user_logged_in'] ? "Yes" : "No") : "Not Set") . "</p>";

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    echo "<p style='color: green;'>✅ User is logged in</p>";
} else {
    echo "<p style='color: red;'>❌ User is NOT logged in</p>";
    echo "<p>This is why messages are not showing in the dashboard.</p>";
    echo "<p><a href='login.php'>Click here to login</a></p>";
}

echo "<h2>All Session Data:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
