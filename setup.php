<?php
// Database setup script
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect without database first
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>Database Setup</h2>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS senior_system";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Database 'senior_system' created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating database: " . $conn->error . "</p>";
    }
    
    // Select database
    $conn->select_db('senior_system');
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Table 'users' created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating users table: " . $conn->error . "</p>";
    }
    
    // Create persons table
    $sql = "CREATE TABLE IF NOT EXISTS persons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_number VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        sex ENUM('Male', 'Female') NOT NULL,
        barangay VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        province VARCHAR(100) NOT NULL,
        birthdate DATE NOT NULL,
        picture VARCHAR(255),
        qr_code VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Table 'persons' created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating persons table: " . $conn->error . "</p>";
    }
    
    // Add indexes
    $sql = "ALTER TABLE persons ADD INDEX IF NOT EXISTS idx_id_number (id_number)";
    $conn->query($sql);
    
    $sql = "ALTER TABLE persons ADD INDEX IF NOT EXISTS idx_name (name)";
    $conn->query($sql);
    
    // Insert/update default admin user
    $hashed_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $sql = "INSERT INTO users (username, password) VALUES ('admin', '$hashed_password') 
            ON DUPLICATE KEY UPDATE password = '$hashed_password'";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Default admin user created/updated (username: admin, password: admin123)</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating admin user: " . $conn->error . "</p>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; }
h2 { color: #333; }
h3 { color: #333; margin-top: 30px; }
p { margin: 10px 0; }
</style>
