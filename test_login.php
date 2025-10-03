<?php

/**
 * Test Login
 * Simple login test page
 */

session_start();

echo "<h1>Test Login</h1>";
echo "<style>body{font-family:Arial;margin:20px;} form{margin:20px 0;} input,button{padding:10px;margin:5px;width:200px;} .success{color:green;} .error{color:red;}</style>";

// Check if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    echo "<p class='success'>✅ You are already logged in as: " . ($_SESSION['username'] ?? 'Unknown') . "</p>";
    echo "<p><a href='logout.php'>Logout</a></p>";
    echo "<p><a href='../my-portfolio-react/'>Go to portfolio</a></p>";
    exit;
}

// Handle login form submission
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            require_once 'config/database.php';
            $db = Database::getInstance();

            $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $username;
                echo "<p class='success'>✅ Login successful!</p>";
                echo "<p><a href='../my-portfolio-react/'>Go to portfolio</a></p>";
                exit;
            } else {
                echo "<p class='error'>❌ Invalid credentials</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Login error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Please enter both username and password</p>";
    }
}
?>

<form method="POST">
    <h2>Login</h2>
    <div>
        <input type="text" name="username" placeholder="Username" required>
    </div>
    <div>
        <input type="password" name="password" placeholder="Password" required>
    </div>
    <div>
        <button type="submit">Login</button>
    </div>
</form>

<p><strong>Default credentials:</strong></p>
<ul>
    <li>Username: admin</li>
    <li>Password: admin123</li>
</ul>

<p><a href="check_admin_users.php">Check admin users</a></p>
<p><a href="create_admin_user.php">Create admin user</a></p>