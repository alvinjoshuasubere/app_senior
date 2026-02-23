<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$person_id = $_GET['id'] ?? 0;

if ($person_id == 0) {
    die('Invalid person ID');
}

$stmt = $conn->prepare("SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) as age FROM persons WHERE id = ?");
$stmt->bind_param("i", $person_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Person not found');
}

$person = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card - <?php echo htmlspecialchars($person['name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        
        .id-card {
            width: 400px;
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin: 0 auto;
        }
        
        .id-header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .id-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .id-header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
        }
        
        .id-content {
            display: flex;
            gap: 20px;
        }
        
        .id-photo {
            width: 100px;
            height: 120px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .id-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .id-photo .placeholder {
            color: #667eea;
            font-size: 40px;
        }
        
        .id-details {
            flex: 1;
            font-size: 12px;
        }
        
        .id-details .field {
            margin-bottom: 8px;
        }
        
        .id-details .field strong {
            display: inline-block;
            width: 60px;
            font-weight: bold;
        }
        
        .id-footer {
            position: absolute;
            bottom: 15px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .id-qr {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 5px;
            padding: 3px;
        }
        
        .id-qr img {
            width: 100%;
            height: 100%;
        }
        
        .id-signature {
            font-size: 10px;
            text-align: right;
            opacity: 0.9;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .actions {
                display: none;
            }
            
            .id-card {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="id-card">
        <div class="id-header">
            <h2>SENIOR CITIZEN ID</h2>
            <p>Republic of the Philippines</p>
        </div>
        
        <div class="id-content">
            <div class="id-photo">
                <?php if ($person['picture']): ?>
                    <img src="<?php echo htmlspecialchars($person['picture']); ?>" alt="Photo">
                <?php else: ?>
                    <div class="placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="id-details">
                <div class="field">
                    <strong>ID:</strong> <?php echo htmlspecialchars($person['id_number']); ?>
                </div>
                <div class="field">
                    <strong>Name:</strong> <?php echo htmlspecialchars($person['name']); ?>
                </div>
                <div class="field">
                    <strong>Sex:</strong> <?php echo htmlspecialchars($person['sex']); ?>
                </div>
                <div class="field">
                    <strong>Age:</strong> <?php echo $person['age']; ?>
                </div>
                <div class="field">
                    <strong>Birthday:</strong> <?php echo date('M d, Y', strtotime($person['birthdate'])); ?>
                </div>
                <div class="field">
                    <strong>Address:</strong> <?php echo htmlspecialchars($person['barangay'] . ', ' . $person['city'] . ', ' . $person['province']); ?>
                </div>
            </div>
        </div>
        
        <div class="id-footer">
            <div class="id-signature">
                <div>_____________________</div>
                <div>Authorized Signature</div>
            </div>
            
            <div class="id-qr">
                <?php if ($person['qr_code']): ?>
                    <img src="<?php echo htmlspecialchars($person['qr_code']); ?>" alt="QR Code">
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="actions">
        <button class="btn" onclick="window.print()">
            <i class="bi bi-printer"></i> Print ID
        </button>
        <a href="index.php" class="btn">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css"></script>
</body>
</html>
