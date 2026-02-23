<?php
// Password reset script for admin
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'senior_system';

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Generate fresh hash for admin123
    $plain_password = 'admin123';
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    
    echo "<h2>Password Reset</h2>";
    echo "<p>Generated hash for 'admin123': <code>" . $hashed_password . "</code></p>";
    
    // Update admin password
    $sql = "UPDATE users SET password = ? WHERE username = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $hashed_password);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Admin password updated successfully!</p>";
        echo "<p style='color: green;'>✓ You can now login with:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> admin123</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Error updating password: " . $stmt->error . "</p>";
    }
    
    // Test the hash
    echo "<h3>Testing Password Hash:</h3>";
    if (password_verify($plain_password, $hashed_password)) {
        echo "<p style='color: green;'>✓ Password verification test PASSED</p>";
    } else {
        echo "<p style='color: red;'>✗ Password verification test FAILED</p>";
    }
    
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
    $stmt->close();
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
code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
