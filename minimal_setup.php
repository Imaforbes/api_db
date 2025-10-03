<?php

/**
 * Minimal Database Setup
 * Only create missing tables, don't recreate existing ones
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Minimal Database Setup</h1>";

try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connection successful</p>";

    // Get existing tables
    $stmt = $db->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<p><strong>Existing tables:</strong> " . implode(', ', $existingTables) . "</p>";

    // Define required tables with their SQL
    $requiredTables = [
        'contact_messages' => "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            email VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT
        )",

        'projects' => "CREATE TABLE IF NOT EXISTS projects (
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
        )",

        'admin_users' => "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(200) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'super_admin') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        'admin_sessions' => "CREATE TABLE IF NOT EXISTS admin_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
        )",

        'portfolio_settings' => "CREATE TABLE IF NOT EXISTS portfolio_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            description VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )"
    ];

    $createdTables = [];
    $skippedTables = [];

    foreach ($requiredTables as $tableName => $sql) {
        if (in_array($tableName, $existingTables)) {
            echo "<p style='color: blue;'>ℹ️ Table '$tableName' already exists - skipping</p>";
            $skippedTables[] = $tableName;
        } else {
            try {
                $db->query($sql);
                echo "<p style='color: green;'>✅ Created table '$tableName'</p>";
                $createdTables[] = $tableName;
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Failed to create table '$tableName': " . $e->getMessage() . "</p>";
            }
        }
    }

    // Check if we need to create default admin user
    if (in_array('admin_users', $existingTables) || in_array('admin_users', $createdTables)) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
        $adminCount = $stmt->fetch()['count'];

        if ($adminCount == 0) {
            try {
                $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO admin_users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt->execute(['admin', 'admin@portfolio.com', $passwordHash, 'super_admin']);
                echo "<p style='color: green;'>✅ Created default admin user</p>";
                echo "<p><strong>Username:</strong> admin</p>";
                echo "<p><strong>Password:</strong> admin123</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Failed to create admin user: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Admin user already exists</p>";
        }
    }

    // Check if we need to create default settings
    if (in_array('portfolio_settings', $existingTables) || in_array('portfolio_settings', $createdTables)) {
        $settings = [
            ['site_title', 'Imanol Pérez Arteaga - Portfolio', 'Main site title'],
            ['site_description', 'Software Engineer & Digital Strategist Portfolio', 'Site meta description'],
            ['contact_email', 'imanol@imaforbes.com', 'Contact email address'],
            ['github_url', 'https://github.com/Imaforbes', 'GitHub profile URL'],
            ['linkedin_url', 'https://www.linkedin.com/in/imanol-pérez-arteaga-a72a08235', 'LinkedIn profile URL']
        ];

        foreach ($settings as $setting) {
            try {
                $stmt = $db->prepare("INSERT IGNORE INTO portfolio_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
                $stmt->execute($setting);
            } catch (Exception $e) {
                // Ignore errors for settings (they might already exist)
            }
        }
        echo "<p style='color: green;'>✅ Default settings created/verified</p>";
    }

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

    // Summary
    echo "<h2>Setup Summary:</h2>";
    echo "<p><strong>Created tables:</strong> " . (empty($createdTables) ? 'None' : implode(', ', $createdTables)) . "</p>";
    echo "<p><strong>Skipped tables:</strong> " . (empty($skippedTables) ? 'None' : implode(', ', $skippedTables)) . "</p>";

    if (empty($createdTables)) {
        echo "<p style='color: green; font-weight: bold; font-size: 18px;'>🎉 All required tables already exist! Your database is ready.</p>";
    } else {
        echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ Setup complete! Created " . count($createdTables) . " missing tables.</p>";
    }

    echo "<p><a href='test_contact_simple.php'>Test Contact Form</a> | <a href='check_database.php'>Check Database Status</a></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Setup failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration.</p>";
}
