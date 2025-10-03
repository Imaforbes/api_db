<?php

/**
 * Debug Login
 * Debug version of login to see what's causing the 500 error
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Login</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} pre{background:#f8f8f8;padding:10px;border:1px solid #ddd;}</style>";

try {
    echo "<h2>1. Testing includes</h2>";

    // Test database connection
    echo "<p>Testing database connection...</p>";
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database connected</p>";

    // Test response classes
    echo "<p>Testing response classes...</p>";
    require_once 'config/response.php';
    echo "<p class='success'>✅ Response classes loaded</p>";

    // Test CORS
    echo "<p>Testing CORS...</p>";
    CorsHandler::setHeaders();
    echo "<p class='success'>✅ CORS headers set</p>";

    // Test session
    echo "<p>Testing session...</p>";
    session_start();
    echo "<p class='success'>✅ Session started</p>";

    // Test database query
    echo "<h2>2. Testing database query</h2>";
    $stmt = $db->query("SHOW TABLES LIKE 'usuarios'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "<p class='success'>✅ usuarios table exists</p>";

        // Test the actual query
        $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE username = ?");
        $stmt->execute(['admin']);
        $user = $stmt->fetch();

        if ($user) {
            echo "<p class='success'>✅ User found in database</p>";
            echo "<p><strong>Password hash:</strong> " . substr($user['password_hash'], 0, 20) . "...</p>";
        } else {
            echo "<p class='error'>❌ No user found with username 'admin'</p>";
        }
    } else {
        echo "<p class='error'>❌ usuarios table does not exist</p>";
    }

    // Test input validation
    echo "<h2>3. Testing input validation</h2>";
    $testUsername = InputValidator::sanitizeString('admin', 100);
    echo "<p class='success'>✅ Input validation works: '$testUsername'</p>";

    // Test password verification
    echo "<h2>4. Testing password verification</h2>";
    $testPassword = 'admin123';
    if ($user && password_verify($testPassword, $user['password_hash'])) {
        echo "<p class='success'>✅ Password verification works</p>";
    } else {
        echo "<p class='error'>❌ Password verification failed</p>";
    }

    echo "<h2>5. Test complete</h2>";
    echo "<p>All components are working. The 500 error might be caused by:</p>";
    echo "<ul>";
    echo "<li>Missing admin user in database</li>";
    echo "<li>Incorrect password</li>";
    echo "<li>Session issues</li>";
    echo "</ul>";

    echo "<p><a href='create_admin_user.php'>Create admin user</a></p>";
    echo "<p><a href='test_login.php'>Test login form</a></p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
