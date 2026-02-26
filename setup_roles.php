<?php
require_once 'config.php';

// Add role column to users table
$sql = "ALTER TABLE users ADD COLUMN role ENUM('admin', 'staff') DEFAULT 'staff' AFTER username";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Role column added to users table</p>";
} else {
    echo "<p style='color: red;'>✗ Error adding role column: " . $conn->error . "</p>";
}

// Update existing admin user to have admin role
$sql = "UPDATE users SET role = 'admin' WHERE username = 'admin'";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Admin user updated with admin role</p>";
} else {
    echo "<p style='color: red;'>✗ Error updating admin role: " . $conn->error . "</p>";
}

// Insert a default staff user (password: staff123)
$staff_password = password_hash('staff123', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, password, role) VALUES ('staff', '$staff_password', 'staff')";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Default staff user created (username: staff, password: staff123)</p>";
} else {
    echo "<p style='color: orange;'>⚠ Staff user may already exist</p>";
}

echo "<h3>Role System Setup Complete!</h3>";
echo "<p><strong>User Accounts:</strong></p>";
echo "<ul>";
echo "<li>Admin: username 'admin', password 'admin123' (full access)</li>";
echo "<li>Staff: username 'staff', password 'staff123' (limited access)</li>";
echo "</ul>";
?>
