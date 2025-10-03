<?php

/**
 * Complete Dashboard Debug Script
 * Comprehensive debugging for the message dashboard issue
 */

echo "<h1>Complete Dashboard Debug</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// 1. Check database connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database connection successful</p>";

    // Check current database
    $stmt = $db->query("SELECT DATABASE() as current_db");
    $currentDb = $stmt->fetch()['current_db'];
    echo "<p><strong>Current Database:</strong> $currentDb</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Check session status
echo "<h2>2. Session Status</h2>";
session_start();
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "</p>";

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    echo "<p class='success'>✅ User is logged in</p>";
} else {
    echo "<p class='error'>❌ User is NOT logged in</p>";
    echo "<p class='warning'>⚠️ This is likely why messages are not showing!</p>";
    echo "<p><a href='login.php'>Click here to login</a></p>";
}

// 3. Check database tables
echo "<h2>3. Database Tables Check</h2>";
$stmt = $db->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<p><strong>Available tables:</strong> " . implode(', ', $tables) . "</p>";

// 4. Check datos table structure and content
echo "<h2>4. Datos Table Analysis</h2>";
if (in_array('datos', $tables)) {
    // Check structure
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Columns in datos table:</strong> " . implode(', ', $columns) . "</p>";

    // Check record count
    $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
    $count = $stmt->fetch()['total'];
    echo "<p><strong>Total records in datos:</strong> $count</p>";

    if ($count > 0) {
        // Show sample data
        $stmt = $db->query("SELECT * FROM datos ORDER BY id DESC LIMIT 3");
        $records = $stmt->fetchAll();

        echo "<h3>Sample records from datos table:</h3>";
        echo "<table>";
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";

        foreach ($records as $record) {
            echo "<tr>";
            foreach ($columns as $column) {
                $value = $record[$column] ?? 'NULL';
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No records found in datos table</p>";
    }
} else {
    echo "<p class='error'>❌ datos table does not exist</p>";
}

// 5. Check contact_messages table
echo "<h2>5. Contact Messages Table Check</h2>";
if (in_array('contact_messages', $tables)) {
    $stmt = $db->query("SELECT COUNT(*) as total FROM contact_messages");
    $count = $stmt->fetch()['total'];
    echo "<p><strong>Total records in contact_messages:</strong> $count</p>";

    if ($count > 0) {
        $stmt = $db->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 3");
        $records = $stmt->fetchAll();

        echo "<h3>Sample records from contact_messages table:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Created At</th></tr>";

        foreach ($records as $record) {
            echo "<tr>";
            echo "<td>{$record['id']}</td>";
            echo "<td>" . htmlspecialchars($record['name']) . "</td>";
            echo "<td>" . htmlspecialchars($record['email']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($record['message'], 0, 50)) . "...</td>";
            echo "<td>{$record['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p class='info'>ℹ️ contact_messages table does not exist</p>";
}

// 6. Test the get_mensajes.php logic
echo "<h2>6. Test get_mensajes.php Logic</h2>";
if (in_array('datos', $tables)) {
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $hasName = in_array('name', $columns) || in_array('nombre', $columns);
    $hasEmail = in_array('email', $columns) || in_array('correo', $columns);
    $hasMessage = in_array('message', $columns) || in_array('mensaje', $columns) || in_array('comentario', $columns);
    $hasDate = in_array('created_at', $columns) || in_array('fecha', $columns) || in_array('date', $columns);

    echo "<p><strong>Column compatibility:</strong></p>";
    echo "<ul>";
    echo "<li>Has name column: " . ($hasName ? "✅ Yes" : "❌ No") . "</li>";
    echo "<li>Has email column: " . ($hasEmail ? "✅ Yes" : "❌ No") . "</li>";
    echo "<li>Has message column: " . ($hasMessage ? "✅ Yes" : "❌ No") . "</li>";
    echo "<li>Has date column: " . ($hasDate ? "✅ Yes" : "❌ No") . "</li>";
    echo "</ul>";

    if ($hasName && $hasEmail && $hasMessage) {
        echo "<p class='success'>✅ datos table is compatible</p>";

        // Test the actual query
        $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
        $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
        $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
        $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

        $sql = "SELECT id, $nameColumn as name, $emailColumn as email, $messageColumn as message, $dateColumn as created_at FROM datos ORDER BY $dateColumn DESC";
        echo "<p><strong>SQL Query:</strong> <code>$sql</code></p>";

        try {
            $stmt = $db->query($sql);
            $messages = $stmt->fetchAll();

            echo "<p><strong>Query result:</strong> " . count($messages) . " messages found</p>";

            if (count($messages) > 0) {
                echo "<h3>Messages that would be returned to frontend:</h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";

                foreach ($messages as $message) {
                    echo "<tr>";
                    echo "<td>{$message['id']}</td>";
                    echo "<td>" . htmlspecialchars($message['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($message['email']) . "</td>";
                    echo "<td>" . htmlspecialchars(substr($message['message'], 0, 50)) . "...</td>";
                    echo "<td>{$message['created_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";

                // Test the transformation
                echo "<h3>Transformed data (what frontend receives):</h3>";
                $transformedMessages = array_map(function ($message) {
                    return [
                        'id' => $message['id'],
                        'fecha' => $message['created_at'],
                        'nombre' => $message['name'],
                        'email' => $message['email'],
                        'mensaje' => $message['message']
                    ];
                }, $messages);

                echo "<pre>" . json_encode($transformedMessages, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p class='warning'>⚠️ No messages found with the query</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Query failed: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>❌ datos table is not compatible</p>";
    }
}

// 7. Test the actual API endpoint
echo "<h2>7. Test API Endpoint</h2>";
echo "<p><strong>Testing get_mensajes.php endpoint:</strong></p>";

// Simulate the get_mensajes.php request
try {
    // Set up the same headers as the API
    header('Content-Type: application/json');

    // Check authentication
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        echo "<p class='error'>❌ Authentication failed - user not logged in</p>";
        echo "<p class='info'>This is why the dashboard shows no messages!</p>";
    } else {
        echo "<p class='success'>✅ Authentication passed</p>";

        // Test the actual API logic
        if (in_array('datos', $tables)) {
            $stmt = $db->query("DESCRIBE datos");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $hasName = in_array('name', $columns) || in_array('nombre', $columns);
            $hasEmail = in_array('email', $columns) || in_array('correo', $columns);
            $hasMessage = in_array('message', $columns) || in_array('mensaje', $columns) || in_array('comentario', $columns);
            $hasDate = in_array('created_at', $columns) || in_array('fecha', $columns) || in_array('date', $columns);

            if ($hasName && $hasEmail && $hasMessage) {
                $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
                $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
                $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
                $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

                $sql = "SELECT id, $nameColumn as name, $emailColumn as email, $messageColumn as message, $dateColumn as created_at FROM datos ORDER BY $dateColumn DESC";
                $stmt = $db->query($sql);
                $messages = $stmt->fetchAll();

                $transformedMessages = array_map(function ($message) {
                    return [
                        'id' => $message['id'],
                        'fecha' => $message['created_at'],
                        'nombre' => $message['name'],
                        'email' => $message['email'],
                        'mensaje' => $message['message']
                    ];
                }, $messages);

                echo "<p class='success'>✅ API would return " . count($transformedMessages) . " messages</p>";
                echo "<p><strong>API Response:</strong></p>";
                echo "<pre>" . json_encode(['success' => true, 'data' => $transformedMessages, 'message' => 'Messages retrieved successfully'], JSON_PRETTY_PRINT) . "</pre>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ API test failed: " . $e->getMessage() . "</p>";
}

// 8. Recommendations
echo "<h2>8. Recommendations</h2>";
echo "<ul>";
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo "<li class='error'>🔐 <strong>CRITICAL:</strong> You need to login first! <a href='login.php'>Click here to login</a></li>";
}
if (!in_array('datos', $tables)) {
    echo "<li class='error'>🗄️ <strong>CRITICAL:</strong> datos table does not exist</li>";
} else {
    $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
    $count = $stmt->fetch()['total'];
    if ($count == 0) {
        echo "<li class='warning'>📝 No messages found in database. Try submitting a test message through the contact form.</li>";
    }
}
echo "<li class='info'>🔗 Test the contact form: <a href='../my-portfolio-react/' target='_blank'>Portfolio Contact Form</a></li>";
echo "<li class='info'>🔗 Test login: <a href='login.php'>Admin Login</a></li>";
echo "</ul>";
