<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - Senior Citizen System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .scanner-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .scanner-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            border: 2px solid #e2e8f0;
            margin-bottom: 20px;
        }
        
        .scanner-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .scanner-header h2 {
            color: #1a1a2e;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .scanner-header p {
            color: #64748b;
            margin: 0;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0 auto 20px;
            border-radius: 15px;
            overflow: hidden;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            aspect-ratio: 1;
        }
        
        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
        
        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 3px solid #1a1a2e;
            border-radius: 15px;
            background: rgba(26, 26, 46, 0.1);
        }
        
        .scanner-overlay::before,
        .scanner-overlay::after {
            content: '';
            position: absolute;
            background: #1a1a2e;
        }
        
        .scanner-overlay::before {
            top: -3px;
            left: -3px;
            right: -3px;
            height: 3px;
        }
        
        .scanner-overlay::after {
            bottom: -3px;
            left: -3px;
            right: -3px;
            height: 3px;
        }
        
        .scanner-corners {
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
        }
        
        .scanner-corners::before,
        .scanner-corners::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid #1a1a2e;
        }
        
        .scanner-corners::before {
            top: 0;
            left: 0;
            border-right: none;
            border-bottom: none;
        }
        
        .scanner-corners::after {
            bottom: 0;
            right: 0;
            border-left: none;
            border-top: none;
        }
        
        .manual-input-section {
            background: #f8fafc;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .manual-input-section h5 {
            color: #1a1a2e;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .btn-scan {
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .btn-scan:hover {
            background: linear-gradient(180deg, #0f3460 0%, #16213e 50%, #1a1a2e 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 26, 46, 0.3);
        }
        
        .btn-validate {
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-validate:hover {
            background: linear-gradient(180deg, #059669 0%, #10b981 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }
        
        .status-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }
        
        .status-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .status-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .status-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        
        .back-btn {
            background: linear-gradient(180deg, #6b7280 0%, #4b5563 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            background: linear-gradient(180deg, #4b5563 0%, #6b7280 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(107, 114, 128, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php require_once 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">QR Scanner</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="back-btn">
                            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="scanner-container">
                    <div class="scanner-card">
                        <div class="scanner-header">
                            <h2><i class="bi bi-qr-code-scan me-2"></i>QR Code Scanner</h2>
                            <p>Scan QR code or manually enter ID number to validate senior citizen</p>
                        </div>

                        <div class="status-message" id="statusMessage"></div>

                        <!-- QR Code Scanner -->
                        <div class="video-container">
                            <video id="video" autoplay playsinline></video>
                            <div class="scanner-overlay">
                                <div class="scanner-corners"></div>
                            </div>
                        </div>

                        <button class="btn-scan" id="startScanBtn" onclick="startScanning()">
                            <i class="bi bi-camera-video me-2"></i>Start Camera Scanner
                        </button>
                        <button class="btn-scan" id="stopScanBtn" onclick="stopScanning()" style="display: none;">
                            <i class="bi bi-stop-circle me-2"></i>Stop Scanner
                        </button>

                        <!-- Manual Input Section -->
                        <div class="manual-input-section">
                            <h5><i class="bi bi-keyboard me-2"></i>Manual ID Input</h5>
                            <div class="input-group">
                                <input type="text" class="form-control" id="manualIdInput" placeholder="Enter Senior Citizen ID Number">
                                <button class="btn btn-validate" onclick="validateManualId()">
                                    <i class="bi bi-search me-2"></i>Validate
                                </button>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle me-2"></i>Instructions:</h6>
                            <ul class="mb-0">
                                <li>Click "Start Camera Scanner" to activate QR code scanning</li>
                                <li>Position QR code within the scanner frame</li>
                                <li>Or manually enter the ID number and click "Validate"</li>
                                <li>Results will be displayed in a modal popup</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Validation Result Modal -->
    <div class="modal fade" id="validationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Validation Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="validationResult">
                    <!-- Results will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printValidation()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let stream = null;
        let scanning = false;

        function showStatus(message, type) {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.className = `status-message status-${type}`;
            statusDiv.textContent = message;
            statusDiv.style.display = 'block';
            
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 5000);
        }

        async function startScanning() {
            try {
                const video = document.getElementById('video');
                const constraints = {
                    video: {
                        facingMode: 'environment',
                        width: { ideal: 400 },
                        height: { ideal: 400 }
                    }
                };

                stream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = stream;
                video.style.display = 'block';
                scanning = true;

                document.getElementById('startScanBtn').style.display = 'none';
                document.getElementById('stopScanBtn').style.display = 'block';

                showStatus('Camera started. Position QR code in the scanner frame.', 'info');

                // Simulate QR code detection (in production, use a QR code library)
                setTimeout(() => {
                    if (scanning) {
                        // Simulate finding a QR code
                        const simulatedId = prompt('QR Scanner Simulation:\nEnter ID number to simulate QR scan:');
                        if (simulatedId) {
                            document.getElementById('manualIdInput').value = simulatedId;
                            validateManualId();
                        }
                    }
                }, 3000);

            } catch (error) {
                console.error('Error accessing camera:', error);
                showStatus('Unable to access camera. Please use manual input.', 'error');
            }
        }

        function stopScanning() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }

            const video = document.getElementById('video');
            video.style.display = 'none';
            scanning = false;

            document.getElementById('startScanBtn').style.display = 'block';
            document.getElementById('stopScanBtn').style.display = 'none';

            showStatus('Scanner stopped.', 'info');
        }

        async function validateManualId() {
            const idNumber = document.getElementById('manualIdInput').value.trim();
            
            if (!idNumber) {
                showStatus('Please enter an ID number.', 'error');
                return;
            }

            try {
                const response = await fetch(`validate_senior.php?id=${encodeURIComponent(idNumber)}`);
                const data = await response.json();

                if (data.error) {
                    showValidationError(data.error);
                } else {
                    showValidationResult(data);
                }
            } catch (error) {
                console.error('Validation error:', error);
                showStatus('Error validating ID. Please try again.', 'error');
            }
        }

        function showValidationResult(person) {
            const resultHtml = `
                <div class="text-center mb-4">
                    <div class="badge bg-success fs-6 p-2 mb-3">
                        <i class="bi bi-check-circle me-2"></i>VERIFIED
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 text-center">
                        ${person.picture ? 
                            `<img src="${person.picture}" class="img-fluid rounded" style="max-height: 200px;" alt="Photo">` : 
                            '<i class="bi bi-person-circle" style="font-size: 8rem; color: #64748b;"></i>'
                        }
                    </div>
                    <div class="col-md-8">
                        <h5 class="mb-3">${person.name}</h5>
                        <div class="row">
                            <div class="col-sm-6">
                                <p><strong>ID Number:</strong> ${person.id_number}</p>
                                <p><strong>Sex:</strong> ${person.sex}</p>
                                <p><strong>Age:</strong> ${person.age}</p>
                            </div>
                            <div class="col-sm-6">
                                <p><strong>Birthdate:</strong> ${person.birthdate}</p>
                                <p><strong>Barangay:</strong> ${person.barangay}</p>
                                <p><strong>City:</strong> ${person.city}</p>
                            </div>
                        </div>
                        <p><strong>Status:</strong> 
                            ${person.deceased ? 
                                '<span class="badge bg-danger">Deceased</span>' : 
                                '<span class="badge bg-success">Active</span>'
                            }
                        </p>
                    </div>
                </div>
                ${person.qr_code ? 
                    `<div class="text-center mt-3">
                        <img src="${person.qr_code}" style="width: 100px; height: 100px;" alt="QR Code">
                    </div>` : ''
                }
            `;
            
            document.getElementById('validationResult').innerHTML = resultHtml;
            new bootstrap.Modal(document.getElementById('validationModal')).show();
        }

        function showValidationError(error) {
            const resultHtml = `
                <div class="text-center">
                    <div class="mb-3">
                        <i class="bi bi-x-circle" style="font-size: 4rem; color: #dc3545;"></i>
                    </div>
                    <h5 class="text-danger">Validation Failed</h5>
                    <p class="text-muted">${error}</p>
                </div>
            `;
            
            document.getElementById('validationResult').innerHTML = resultHtml;
            new bootstrap.Modal(document.getElementById('validationModal')).show();
        }

        function printValidation() {
            window.print();
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>
