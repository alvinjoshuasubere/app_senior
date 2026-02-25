<?php
// Generate correct hash for admin123
$plain_password = 'admin123';
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "Generated hash for 'admin123': " . $hashed_password . "\n";

// Test the hash
if (password_verify($plain_password, $hashed_password)) {
    echo "✓ Password verification test PASSED\n";
} else {
    echo "✗ Password verification test FAILED\n";
}

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'senior_system';

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Update admin password
    $sql = "UPDATE users SET password = ? WHERE username = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $hashed_password);
    
    if ($stmt->execute()) {
        echo "✓ Admin password updated successfully!\n";
        echo "You can now login with:\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        echo "✗ Error updating password: " . $stmt->error . "\n";
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
