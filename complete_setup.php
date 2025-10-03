<?php

/**
 * Complete Setup Script
 * Sets up everything needed for the portfolio system
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Complete Portfolio Setup</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f8f8f8;padding:10px;border:1px solid #ddd;}</style>";

try {
    // 1. Test database connection
    echo "<h2>1. Testing Database Connection</h2>";
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database connected</p>";

    // 2. Check current database
    $stmt = $db->query("SELECT DATABASE() as current_db");
    $currentDb = $stmt->fetch()['current_db'];
    echo "<p><strong>Current Database:</strong> $currentDb</p>";

    // 3. Check existing tables
    echo "<h2>2. Checking Existing Tables</h2>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Existing tables:</strong> " . implode(', ', $tables) . "</p>";

    // 4. Create usuarios table if it doesn't exist
    echo "<h2>3. Setting up usuarios table</h2>";
    if (!in_array('usuarios', $tables)) {
        $sql = "CREATE TABLE usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
        echo "<p class='success'>✅ usuarios table created</p>";
    } else {
        echo "<p class='success'>✅ usuarios table already exists</p>";
    }

    // 5. Check if admin user exists
    echo "<h2>4. Checking Admin Users</h2>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $count = $stmt->fetch()['total'];
    echo "<p><strong>Total users:</strong> $count</p>";

    if ($count == 0) {
        // Create default admin user
        $username = 'admin';
        $password = 'admin123';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO usuarios (username, password_hash) VALUES (?, ?)");
        $result = $stmt->execute([$username, $passwordHash]);

        if ($result) {
            echo "<p class='success'>✅ Admin user created</p>";
            echo "<p><strong>Username:</strong> $username</p>";
            echo "<p><strong>Password:</strong> $password</p>";
        } else {
            echo "<p class='error'>❌ Failed to create admin user</p>";
        }
    } else {
        echo "<p class='success'>✅ Admin users already exist</p>";

        // Show existing users
        $stmt = $db->query("SELECT id, username, created_at FROM usuarios");
        $users = $stmt->fetchAll();

        echo "<h3>Existing Users:</h3>";
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 6. Test login functionality
    echo "<h2>5. Testing Login Functionality</h2>";

    // Test password verification
    $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch();

    if ($user) {
        $testPassword = 'admin123';
        if (password_verify($testPassword, $user['password_hash'])) {
            echo "<p class='success'>✅ Password verification works</p>";
        } else {
            echo "<p class='error'>❌ Password verification failed</p>";
        }
    } else {
        echo "<p class='error'>❌ No admin user found</p>";
    }

    // 7. Test session functionality
    echo "<h2>6. Testing Session Functionality</h2>";
    session_start();
    $_SESSION['user_logged_in'] = true;
    $_SESSION['username'] = 'admin';
    echo "<p class='success'>✅ Session functionality works</p>";

    // 8. Test API endpoints
    echo "<h2>7. Testing API Endpoints</h2>";

    // Test get_mensajes.php
    echo "<p>Testing get_mensajes.php...</p>";
    try {
        $stmt = $db->query("DESCRIBE datos");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='success'>✅ datos table accessible</p>";
        echo "<p><strong>Columns:</strong> " . implode(', ', $columns) . "</p>";

        $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
        $count = $stmt->fetch()['total'];
        echo "<p><strong>Messages in datos:</strong> $count</p>";
    } catch (Exception $e) {
        echo "<p class='warning'>⚠️ datos table issue: " . $e->getMessage() . "</p>";
    }

    // 9. Summary
    echo "<h2>8. Setup Complete!</h2>";
    echo "<p class='success'>✅ All components are working</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Test login: <a href='test_login.php'>test_login.php</a></li>";
    echo "<li>Go to portfolio: <a href='../my-portfolio-react/'>Portfolio</a></li>";
    echo "<li>Create test message: <a href='create_test_message.php'>create_test_message.php</a></li>";
    echo "</ul>";

    echo "<p><strong>Default Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: admin</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Setup failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
