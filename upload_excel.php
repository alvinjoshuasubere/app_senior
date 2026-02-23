<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    if ($file['error'] == 0) {
        $allowed_types = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        
        if (in_array($file['type'], $allowed_types)) {
            try {
                $spreadsheet = IOFactory::load($file['tmp_name']);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                $success_count = 0;
                $error_count = 0;
                $errors = [];
                
                // Skip header row (assuming first row is header)
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    if (count($row) >= 7) {
                        $id_number = trim($row[0]);
                        $name = trim($row[1]);
                        $sex = trim($row[2]);
                        $barangay = trim($row[3]);
                        $city = trim($row[4]);
                        $province = trim($row[5]);
                        $birthdate = trim($row[6]);
                        
                        // Validate required fields
                        if (empty($id_number) || empty($name) || empty($sex) || empty($barangay) || empty($city) || empty($province) || empty($birthdate)) {
                            $errors[] = "Row " . ($i + 1) . ": Missing required fields";
                            $error_count++;
                            continue;
                        }
                        
                        // Validate sex
                        if (!in_array(strtolower($sex), ['male', 'female'])) {
                            $errors[] = "Row " . ($i + 1) . ": Invalid sex value. Must be Male or Female";
                            $error_count++;
                            continue;
                        }
                        
                        // Validate date format
                        $date = DateTime::createFromFormat('Y-m-d', $birthdate);
                        if (!$date) {
                            $date = DateTime::createFromFormat('m/d/Y', $birthdate);
                            if (!$date) {
                                $date = DateTime::createFromFormat('d/m/Y', $birthdate);
                                if (!$date) {
                                    $errors[] = "Row " . ($i + 1) . ": Invalid birthdate format";
                                    $error_count++;
                                    continue;
                                }
                            }
                        }
                        $birthdate = $date->format('Y-m-d');
                        
                        // Check if ID number already exists
                        $check_stmt = $conn->prepare("SELECT id FROM persons WHERE id_number = ?");
                        $check_stmt->bind_param("s", $id_number);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        
                        if ($check_result->num_rows > 0) {
                            $errors[] = "Row " . ($i + 1) . ": ID Number $id_number already exists";
                            $error_count++;
                            continue;
                        }
                        
                        // Generate QR code
                        $qr_data = "ID: " . $id_number . "\nName: " . $name . "\nBirthdate: " . $birthdate;
                        $qr_code = generateQRCode($qr_data);
                        
                        // Insert into database
                        $stmt = $conn->prepare("INSERT INTO persons (id_number, name, sex, barangay, city, province, birthdate, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssssss", $id_number, $name, $sex, $barangay, $city, $province, $birthdate, $qr_code);
                        
                        if ($stmt->execute()) {
                            $success_count++;
                        } else {
                            $errors[] = "Row " . ($i + 1) . ": Database error - " . $conn->error;
                            $error_count++;
                        }
                    } else {
                        $errors[] = "Row " . ($i + 1) . ": Insufficient columns";
                        $error_count++;
                    }
                }
                
                if ($success_count > 0) {
                    $message = "Successfully imported $success_count records.";
                    $message_type = "success";
                }
                
                if ($error_count > 0) {
                    if (!empty($message)) {
                        $message .= " ";
                    }
                    $message .= "$error_count records had errors.";
                    $message_type = "warning";
                    $_SESSION['upload_errors'] = $errors;
                }
                
            } catch (Exception $e) {
                $message = "Error reading Excel file: " . $e->getMessage();
                $message_type = "danger";
            }
        } else {
            $message = "Invalid file type. Please upload an Excel file (.xlsx or .xls)";
            $message_type = "danger";
        }
    } else {
        $message = "Error uploading file. Please try again.";
        $message_type = "danger";
    }
    
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $message_type;
    header('Location: index.php');
    exit();
}
?>
