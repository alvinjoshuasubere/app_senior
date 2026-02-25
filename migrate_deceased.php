<?php
require_once 'config.php';

echo "<h2>Database Migration - Add Deceased Status</h2>";

try {
    // Add deceased column
    $sql1 = "ALTER TABLE persons ADD COLUMN deceased BOOLEAN DEFAULT FALSE";
    if ($conn->query($sql1)) {
        echo "<p style='color: green;'>✓ Added 'deceased' column</p>";
    } else {
        echo "<p style='color: orange;'>- 'deceased' column already exists or error: " . $conn->error . "</p>";
    }
    
    // Add deceased_date column
    $sql2 = "ALTER TABLE persons ADD COLUMN deceased_date DATE";
    if ($conn->query($sql2)) {
        echo "<p style='color: green;'>✓ Added 'deceased_date' column</p>";
    } else {
        echo "<p style='color: orange;'>- 'deceased_date' column already exists or error: " . $conn->error . "</p>";
    }
    
    // Remove UNIQUE constraint from id_number (if it exists)
    $sql3 = "ALTER TABLE persons DROP INDEX id_number";
    if ($conn->query($sql3)) {
        echo "<p style='color: green;'>✓ Removed UNIQUE constraint from id_number</p>";
    } else {
        echo "<p style='color: orange;'>- UNIQUE constraint already removed or error: " . $conn->error . "</p>";
    }
    
    // Add regular index back (for performance)
    $sql4 = "ALTER TABLE persons ADD INDEX idx_id_number (id_number)";
    if ($conn->query($sql4)) {
        echo "<p style='color: green;'>✓ Added regular index to id_number</p>";
    } else {
        echo "<p style='color: orange;'>- Index already exists or error: " . $conn->error . "</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>✓ Migration completed successfully!</p>";
    echo "<p><a href='index.php'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; }
h2 { color: #333; }
p { margin: 10px 0; }
</style>
