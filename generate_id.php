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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .id-card {
            width: 450px;
            height: 280px;
            background: white;
            border-radius: 20px;
            padding: 25px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            margin: 0 auto;
            border: 2px solid #e2e8f0;
            overflow: hidden;
        }
        
        .id-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1a1a2e, #16213e, #0f3460);
        }
        
        .id-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .id-header .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
            border-radius: 10px;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .id-header .logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .id-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1a1a2e;
            letter-spacing: 0.5px;
        }
        
        .id-header p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }
        
        .id-content {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .id-photo {
            width: 100px;
            height: 120px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
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
            color: #64748b;
            font-size: 40px;
        }
        
        .id-details {
            flex: 1;
            font-size: 11px;
        }
        
        .id-details .field {
            margin-bottom: 6px;
            display: flex;
        }
        
        .id-details .field strong {
            font-weight: 600;
            color: #374151;
            min-width: 70px;
            display: inline-block;
        }
        
        .id-details .field span {
            color: #4b5563;
            font-weight: 400;
        }
        
        .id-footer {
            position: absolute;
            bottom: 15px;
            left: 25px;
            right: 25px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .id-qr {
            width: 60px;
            height: 60px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px;
        }
        
        .id-qr img {
            width: 100%;
            height: 100%;
        }
        
        .id-signature {
            font-size: 9px;
            text-align: right;
            color: #64748b;
            font-weight: 500;
        }
        
        .id-signature .line {
            border-top: 1px solid #d1d5db;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            margin: 0 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: linear-gradient(180deg, #0f3460 0%, #16213e 50%, #1a1a2e 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 26, 46, 0.3);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
                display: block;
            }
            
            .actions {
                display: none;
            }
            
            .id-card {
                box-shadow: none;
                margin: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="id-card">
        <div class="id-header">
            <div class="logo">
                <img src="city_logo.png" alt="City Logo" onerror="this.style.display='none'">
            </div>
            <h2>CITY OF KORONADAL</h2>
            <p>City Social Welfare and Development Office</p>
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
                    <strong>ID No:</strong>
                    <span><?php echo htmlspecialchars($person['id_number']); ?></span>
                </div>
                <div class="field">
                    <strong>Name:</strong>
                    <span><?php echo htmlspecialchars($person['name']); ?></span>
                </div>
                <div class="field">
                    <strong>Sex:</strong>
                    <span><?php echo htmlspecialchars($person['sex']); ?></span>
                </div>
                <div class="field">
                    <strong>Age:</strong>
                    <span><?php echo $person['age']; ?></span>
                </div>
                <div class="field">
                    <strong>Birthday:</strong>
                    <span><?php echo $person['birthdate'] ? date('M d, Y', strtotime($person['birthdate'])) : 'N/A'; ?></span>
                </div>
                <div class="field">
                    <strong>Address:</strong>
                    <span><?php echo htmlspecialchars($person['barangay'] . ', ' . $person['city'] . ', ' . $person['province']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="id-footer">
            <div class="id-signature">
                <div>_____________________</div>
                <div class="line">Authorized Signature</div>
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
            <i class="bi bi-arrow-left"></i> Back to Senior Citizen List
        </a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
