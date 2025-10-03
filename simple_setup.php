<?php

/**
 * Simple Database Setup
 * Create database and tables if they don't exist
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Database Setup</h1>";

try {
    // Connect to MySQL server (without database)
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'portfolio';

    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p style='color: green;'>✅ MySQL connection successful</p>";

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ Database '$database' created/verified</p>";

    // Use the database
    $pdo->exec("USE `$database`");

    // Create contact_messages table
    $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        email VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ip_address VARCHAR(45),
        user_agent TEXT
    )";

    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ contact_messages table created/verified</p>";

    // Create admin_users table
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(200) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'super_admin') DEFAULT 'admin',
        is_active BOOLEAN DEFAULT TRUE,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ admin_users table created/verified</p>";

    // Create admin_sessions table
    $sql = "CREATE TABLE IF NOT EXISTS admin_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_token VARCHAR(255) UNIQUE NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ admin_sessions table created/verified</p>";

    // Create projects table
    $sql = "CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        short_description VARCHAR(500),
        image_url VARCHAR(500),
        technologies JSON,
        github_url VARCHAR(500),
        live_url VARCHAR(500),
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        featured BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ projects table created/verified</p>";

    // Create portfolio_settings table
    $sql = "CREATE TABLE IF NOT EXISTS portfolio_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        description VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ portfolio_settings table created/verified</p>";

    // Insert default admin user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn();

    if ($adminExists == 0) {
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@portfolio.com', $passwordHash, 'super_admin']);
        echo "<p style='color: green;'>✅ Default admin user created</p>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Admin user already exists</p>";
    }

    // Insert default settings
    $settings = [
        ['site_title', 'Imanol Pérez Arteaga - Portfolio', 'Main site title'],
        ['site_description', 'Software Engineer & Digital Strategist Portfolio', 'Site meta description'],
        ['contact_email', 'imanol@imaforbes.com', 'Contact email address'],
        ['github_url', 'https://github.com/Imaforbes', 'GitHub profile URL'],
        ['linkedin_url', 'https://www.linkedin.com/in/imanol-pérez-arteaga-a72a08235', 'LinkedIn profile URL']
    ];

    foreach ($settings as $setting) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO portfolio_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        $stmt->execute($setting);
    }
    echo "<p style='color: green;'>✅ Default settings created/verified</p>";

    // Create upload directories
    $uploadDirs = ['uploads/images', 'uploads/documents'];
    foreach ($uploadDirs as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "<p style='color: green;'>✅ Created directory: $dir</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to create directory: $dir</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Directory exists: $dir</p>";
        }
    }

    echo "<h2 style='color: green;'>🎉 Setup Complete!</h2>";
    echo "<p>Your portfolio API database is ready to use.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Setup failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL configuration.</p>";
}
