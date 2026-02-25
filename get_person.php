<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing ID']);
    exit();
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM persons WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $person = $result->fetch_assoc();
    
    // Format dates for display
    if ($person['birthdate']) {
        $person['birthdate'] = date('Y-m-d', strtotime($person['birthdate']));
    } else {
        $person['birthdate'] = '';
    }
    
    if ($person['deceased_date']) {
        $person['deceased_date'] = date('Y-m-d', strtotime($person['deceased_date']));
    } else {
        $person['deceased_date'] = '';
    }
    
    header('Content-Type: application/json');
    echo json_encode($person);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Person not found']);
}

$stmt->close();
$conn->close();
?>
