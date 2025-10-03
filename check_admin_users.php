<?php

/**
 * Check Admin Users
 * See what admin users exist in the database
 */

require_once 'config/database.php';

echo "<h1>Check Admin Users</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;}</style>";

try {
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database connected</p>";

    // Check if usuarios table exists
    $stmt = $db->query("SHOW TABLES LIKE 'usuarios'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "<p class='success'>✅ usuarios table exists</p>";

        // Check users
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
        $count = $stmt->fetch()['total'];
        echo "<p><strong>Total users in usuarios table:</strong> $count</p>";

        if ($count > 0) {
            $stmt = $db->query("SELECT id, username, created_at FROM usuarios");
            $users = $stmt->fetchAll();

            echo "<h2>Admin Users:</h2>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Created</th></tr>";

            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>{$user['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>❌ No admin users found</p>";
            echo "<p><a href='create_admin_user.php'>Create admin user</a></p>";
        }
    } else {
        echo "<p class='error'>❌ usuarios table does not exist</p>";
        echo "<p><a href='simple_setup.php'>Run setup</a></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
