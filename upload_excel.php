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
                        // Map fields according to correct order: ID NO., NAME, SEX, ADDRESS2, ADDRESS3, PROVINCE, BIRTHDATE
                        $id_number = strtoupper(trim($row[0]));
                        $name = strtoupper(trim($row[1]));
                        $sex = strtoupper(trim($row[2]));
                        $barangay = strtoupper(trim($row[3]));
                        $city = strtoupper(trim($row[4]));
                        $province = strtoupper(trim($row[5]));
                        $birthdate = trim($row[6]);
                        
                        // Validate required fields
                        if (empty($id_number) || empty($name) || empty($sex) || empty($barangay) ||empty($birthdate)) {
                            $errors[] = "Row " . ($i + 1) . ": Missing required fields";
                            $error_count++;
                            continue;
                        }
                        
                        // Convert and validate sex
                        if (in_array($sex, ['F', 'FEMALE'])) {
                            $sex = 'FEMALE';
                        } elseif (in_array($sex, ['M', 'MALE'])) {
                            $sex = 'MALE';
                        } else {
                            $errors[] = "Row " . ($i + 1) . ": Invalid sex value. Must be M/F or Male/Female";
                            $error_count++;
                            continue;
                        }
                        
                        // Validate date format (allow empty)
                        if (empty($birthdate)) {
                            $birthdate = null;
                        } else {
                            $date = DateTime::createFromFormat('Y-m-d', $birthdate);
                            if (!$date) {
                                $date = DateTime::createFromFormat('m/d/Y', $birthdate);
                            }
                            if (!$date) {
                                $date = DateTime::createFromFormat('d/m/Y', $birthdate);
                            }
                            if (!$date) {
                                $date = DateTime::createFromFormat('d-M-y', $birthdate);
                                // Fix 2-digit years to be in 1900s if they seem unreasonable
                                if ($date) {
                                    $year = (int)$date->format('Y');
                                    if ($year > 2025) {
                                        // If year is in the future, assume it's 1900s
                                        $correct_year = $year - 100;
                                        $date->setDate($correct_year, (int)$date->format('m'), (int)$date->format('d'));
                                    }
                                }
                            }
                            if (!$date) {
                                $date = DateTime::createFromFormat('d-M-Y', $birthdate);
                            }
                            if (!$date) {
                                $date = DateTime::createFromFormat('d M Y', $birthdate);
                            }
                            if (!$date) {
                                $date = DateTime::createFromFormat('d M y', $birthdate);
                                // Fix 2-digit years to be in 1900s if they seem unreasonable
                                if ($date) {
                                    $year = (int)$date->format('Y');
                                    if ($year > 2025) {
                                        // If year is in the future, assume it's 1900s
                                        $correct_year = $year - 100;
                                        $date->setDate($correct_year, (int)$date->format('m'), (int)$date->format('d'));
                                    }
                                }
                            }
                            if (!$date) {
                                $errors[] = "Row " . ($i + 1) . ": Invalid birthdate format";
                                $error_count++;
                                continue;
                            }
                            $birthdate = $date->format('Y-m-d');
                        }
                        
                        // Generate QR code
                        $qr_data = "ID: " . $id_number . "\nName: " . $name . ($birthdate ? "\nBirthdate: " . $birthdate : "");
                        $qr_code = generateQRCode($qr_data);
                        
                        // Check if name already exists
                        $check_stmt = $conn->prepare("SELECT id FROM persons WHERE name = ?");
                        $check_stmt->bind_param("s", $name);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        
                        if ($check_result->num_rows > 0) {
                            $errors[] = "Row " . ($i + 1) . ": Person with name '$name' already exists";
                            $error_count++;
                            continue;
                        }
                        
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
