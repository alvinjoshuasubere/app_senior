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
    <title>ID Card - <?php echo htmlspecialchars($person['name']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@700&family=Poppins:wght@400;600;700&display=swap');
        
        body { background: #f0f2f5; font-family: 'Poppins', sans-serif; display: flex; flex-direction: column; align-items: center; padding: 20px; }

        .id-card {
            width: 85.6mm;
            height: 54mm;
            background: #fff;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            border: 1px solid #999;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* FRONT HEADER */
        .header { display: flex; align-items: center; padding: 5px 8px; border-bottom: 2px solid #e53e3e; background: linear-gradient(to right, #ffffff, #fff5f5); position: relative; z-index: 2; }
        .header img { width: 32px; height: 32px; margin-right: 8px; }
        .header-text { flex: 1; text-align: center; }
        .header-text h2 { margin: 0; font-size: 8.5pt; color: #e53e3e; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-text p { margin: 0; font-size: 5pt; font-weight: bold; color: #333; }

        /* BODY SECTION */
        .id-body { display: flex; flex: 1; padding: 6px 8px; z-index: 2; position: relative; }
        .photo-area { width: 23mm; position: relative; }
        .photo-box { width: 22mm; height: 22mm; border: 1.5px solid #2d3748; overflow: hidden; background: #eee; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        
        /* Fixed QR positioning to avoid moving UI */
        .qr-code-front { 
            position: absolute;
            top: 23mm; /* Placed exactly below the photo box */
            left: 0;
            width: 10mm; 
            height: 10mm; 
            border: 1px solid #cbd5e0; 
            padding: 1px; 
            background: #fff; 
        }
        .qr-code-front img { width: 100%; height: 100%; }
        
        .info-area { flex: 1; padding-left: 10px; }
        .name-label { font-size: 5pt; color: #718096; text-transform: uppercase; margin: 0; }
        .name-val { font-family: 'Roboto Condensed', sans-serif; font-size: 10.5pt; color: #1a365d; margin-bottom: 4px; line-height: 1.1; }
        
        .data-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 4px; }
        .data-item { font-size: 6pt; font-weight: 700; border-bottom: 1px solid #edf2f7; padding-bottom: 1px; }
        .data-label { color: #a0aec0; display: block; font-size: 4.5pt; text-transform: uppercase; }

        /* SIGNATURES */
        .front-signatures { display: flex; justify-content: flex-end; align-items: flex-end; padding: 0 10px 4px; position: relative; z-index: 2; }
        .sig-container { width: 40%; text-align: center; position: relative; height: 10mm; display: flex; flex-direction: column; justify-content: flex-end; }
        
        .sig-image {
            position: absolute;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: auto;
            max-height: 8mm;
            z-index: 3;
            pointer-events: none;
        }

        .sig-line { border-top: 0.8px solid #000; font-size: 5pt; font-weight: bold; padding-top: 1px; text-transform: uppercase; position: relative; z-index: 4; }
        .sig-title { font-size: 4pt; font-weight: normal; margin-top: 1px; color: #4a5568; }

        .id-num-footer { background: #c53030; color: white; font-size: 7.5pt; font-weight: bold; text-align: center;  letter-spacing: 1px; margin-top: auto; }

        /* BACK SIDE */
        .back-content { padding: 10px; font-size: 6.5pt; line-height: 1.3; color: #2d3748; }
        .back-title { font-weight: 800; border-bottom: 1.5px solid #e53e3e; margin-bottom: 6px; text-align: center; color: #c53030; }
        .back-footer { margin-top: auto; display: flex; flex-direction: column; align-items: center; padding-bottom: 6px; }
        .qr-back { width: 12mm; height: 12mm; padding: 2px; background: #fff; border: 1px solid #cbd5e0; }

        .watermark-logo { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25mm; height: auto; opacity: 0.15; z-index: 1; pointer-events: none; }

        @media print { .actions { display: none; } .id-card { box-shadow: none; border: 1px solid #000; } }
    </style>
</head>
<body>

    <div class="id-card">
        <img src="city_logo.png" class="watermark-logo">
        
        <div class="header">
            <img src="city_logo.png">
            <div class="header-text">
                <p>Republic of the Philippines</p>
                <h2>City of Koronadal</h2>
                <p style="color:#2c5282">Office of Senior Citizens Affairs</p>
            </div>
        </div>

        <div class="id-body">
            <div class="photo-area">
                <div class="photo-box">
                    <img src="<?php echo htmlspecialchars($person['picture']); ?>">
                </div>
                <?php if (!empty($person['qr_code'])): ?>
                    <div class="qr-code-front">
                        <img src="<?php echo htmlspecialchars($person['qr_code']); ?>">
                    </div>
                <?php endif; ?>
            </div>
            <div class="info-area">
                <p class="name-label">Name of Member</p>
                <div class="name-val"><?php echo htmlspecialchars($person['name']); ?></div>
                
                <div class="data-grid">
                    <div class="data-item"><span class="data-label">Birthdate</span><?php echo date('M d, Y', strtotime($person['birthdate'])); ?></div>
                    <div class="data-item"><span class="data-label">Sex</span><?php echo $person['sex']; ?></div>
                    <div class="data-item"><span class="data-label">Barangay</span><?php echo htmlspecialchars($person['barangay']); ?></div>
                    <div class="data-item"><span class="data-label">Blood Type</span><?php echo $person['blood_type'] ?? 'N/A'; ?></div>
                </div>
            </div>
        </div>

        <div class="front-signatures">
            <div class="sig-container">
                <!-- <img src="official_signature.png" class="sig-image"> -->
                <div class="sig-line">HON. ELIORDO U. OGENA</div>
                <div class="sig-title">Authorized Signature</div>
            </div>
        </div>

        <div class="id-num-footer">
            ID NO: <?php echo htmlspecialchars($person['id_number']); ?>
        </div>
    </div>

    <div class="id-card">
        <div class="back-content">
            <div class="back-title">TERMS AND CONDITIONS</div>
            <div style="font-size: 5.5pt;">
                1. This card is non-transferable and valid throughout the Philippines.<br>
                2. Holder is entitled to benefits under RA No. 9994 (Senior Citizens Act).<br>
                3. If lost, report immediately to the OSCA/CSWD Office.<br>
                4. Fraudulent use is subject to legal prosecution.
            </div>
            
            <div style="margin-top: 8px; padding: 5px; background: #f7fafc; border-radius: 4px; border: 1px dashed #cbd5e0;">
                <p style="margin:0; font-weight:bold; color:#2d3748; text-align:center;">IN CASE OF EMERGENCY</p>
                <p style="margin:2px 0; font-size: 6pt;">Contact: <strong><?php echo htmlspecialchars($person['emergency_contact'] ?? '________________'); ?></strong></p>
                <p style="margin:0; font-size: 6pt;">Phone: <strong><?php echo htmlspecialchars($person['emergency_phone'] ?? '________________'); ?></strong></p>
            </div>
        </div>

        <div class="back-footer">
            <?php if (!empty($person['qr_code'])): ?>
                <img src="<?php echo htmlspecialchars($person['qr_code']); ?>" class="qr-back">
            <?php endif; ?>
            <p style="font-size: 4.5pt; margin: 4px 0 0; color: #718096; font-weight: bold;">OFFICIAL VERIFICATION QR</p>
        </div>
    </div>

    <div class="actions">
        <button onclick="window.location.href='index.php'" style="padding: 12px 25px; background: #1a2935ff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2); margin-right: 10px;">Return to List</button>
        <button onclick="window.print()" style="padding: 12px 25px; background: #c53030; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">Print Member ID Card</button>
    </div>

</body>
</html>