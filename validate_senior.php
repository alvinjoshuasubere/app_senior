<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID number is required']);
    exit();
}

$id_number = trim($_GET['id']);

// Query the database for the senior citizen
$stmt = $conn->prepare("SELECT *, 
    CASE 
        WHEN deceased = 1 AND deceased_date IS NOT NULL AND deceased_date >= birthdate THEN 
            TIMESTAMPDIFF(YEAR, birthdate, deceased_date)
        WHEN deceased = 1 AND deceased_date IS NOT NULL THEN 
            TIMESTAMPDIFF(YEAR, birthdate, CURDATE())
        WHEN deceased = 1 AND deceased_date IS NULL THEN 
            TIMESTAMPDIFF(YEAR, birthdate, CURDATE())
        WHEN birthdate IS NULL THEN 0 
        ELSE TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) 
    END as age 
    FROM persons WHERE id_number = ?");
$stmt->bind_param("s", $id_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $person = $result->fetch_assoc();
    
    // Format dates for display
    if ($person['birthdate']) {
        $person['birthdate'] = date('M d, Y', strtotime($person['birthdate']));
    } else {
        $person['birthdate'] = 'N/A';
    }
    
    if ($person['deceased_date']) {
        $person['deceased_date'] = date('M d, Y', strtotime($person['deceased_date']));
    } else {
        $person['deceased_date'] = null;
    }
    
    // Ensure age is never negative
    if ($person['age'] < 0) {
        $person['age'] = 0;
    }
    
    header('Content-Type: application/json');
    echo json_encode($person);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Senior citizen not found with ID: ' . $id_number]);
}

$stmt->close();
$conn->close();
?>
