<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

$upload_errors = [];
if (isset($_SESSION['upload_errors'])) {
    $upload_errors = $_SESSION['upload_errors'];
    unset($_SESSION['upload_errors']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_person'])) {
        $id_number = $_POST['id_number'];
        $name = $_POST['name'];
        $sex = $_POST['sex'];
        $barangay = $_POST['barangay'];
        $city = $_POST['city'];
        $province = $_POST['province'];
        $birthdate = $_POST['birthdate'];
        
        // Check if ID number already exists
        $check_stmt = $conn->prepare("SELECT id FROM persons WHERE id_number = ?");
        $check_stmt->bind_param("s", $id_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = "Error: ID Number already exists!";
            $message_type = "danger";
        } else {
            // Handle file upload
            $picture = '';
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $picture = $target_dir . time() . '_' . basename($_FILES["picture"]["name"]);
                move_uploaded_file($_FILES["picture"]["tmp_name"], $picture);
            }
            
            // Generate QR code
            $qr_data = "ID: " . $id_number . "\nName: " . $name . "\nBirthdate: " . $birthdate;
            $qr_code = generateQRCode($qr_data);
            
            $stmt = $conn->prepare("INSERT INTO persons (id_number, name, sex, barangay, city, province, birthdate, picture, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $id_number, $name, $sex, $barangay, $city, $province, $birthdate, $picture, $qr_code);
            
            if ($stmt->execute()) {
                $message = "Person added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding person: " . $conn->error;
                $message_type = "danger";
            }
        }
    }
}

// Fetch all persons with calculated age
$persons = [];
$result = $conn->query("SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) as age FROM persons ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $persons[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Data System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .person-card {
            margin-bottom: 1rem;
        }
        .person-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }
        .qr-code {
            width: 50px;
            height: 50px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Senior System</h5>
                        <p class="text-white-50">Welcome, <?php echo $_SESSION['username']; ?></p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="#dashboard">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#addPersonModal">
                                <i class="bi bi-person-plus"></i> Add Person
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="bi bi-upload"></i> Upload Excel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addPersonModal">
                                <i class="bi bi-plus-circle"></i> Add Person
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="bi bi-upload"></i> Upload Excel
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($upload_errors)): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h6>Upload Errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($upload_errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Persons</h6>
                                        <h3><?php echo count($persons); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Male</h6>
                                        <h3><?php echo count(array_filter($persons, fn($p) => $p['sex'] == 'Male')); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-gender-male" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Female</h6>
                                        <h3><?php echo count(array_filter($persons, fn($p) => $p['sex'] == 'Female')); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-gender-female" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Persons List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Registered Persons</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID Number</th>
                                        <th>Name</th>
                                        <th>Sex</th>
                                        <th>Age</th>
                                        <th>Barangay</th>
                                        <th>City</th>
                                        <th>Picture</th>
                                        <th>QR Code</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($persons as $person): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($person['id_number']); ?></td>
                                        <td><?php echo htmlspecialchars($person['name']); ?></td>
                                        <td><?php echo htmlspecialchars($person['sex']); ?></td>
                                        <td><?php echo $person['age']; ?></td>
                                        <td><?php echo htmlspecialchars($person['barangay']); ?></td>
                                        <td><?php echo htmlspecialchars($person['city']); ?></td>
                                        <td>
                                            <?php if ($person['picture']): ?>
                                                <img src="<?php echo htmlspecialchars($person['picture']); ?>" class="person-image" alt="Picture">
                                            <?php else: ?>
                                                <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($person['qr_code']): ?>
                                                <img src="<?php echo htmlspecialchars($person['qr_code']); ?>" class="qr-code" alt="QR Code">
                                            <?php else: ?>
                                                <i class="bi bi-qr-code" style="font-size: 1.5rem;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="generateID(<?php echo $person['id']; ?>)">
                                                <i class="bi bi-card-text"></i> ID
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Person Modal -->
    <div class="modal fade" id="addPersonModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Person</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_number" class="form-label">ID Number *</label>
                                    <input type="text" class="form-control" id="id_number" name="id_number" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sex" class="form-label">Sex *</label>
                                    <select class="form-select" id="sex" name="sex" required>
                                        <option value="">Select Sex</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="birthdate" class="form-label">Birthdate *</label>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="barangay" class="form-label">Barangay *</label>
                                    <input type="text" class="form-control" id="barangay" name="barangay" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="province" class="form-label">Province *</label>
                                    <input type="text" class="form-control" id="province" name="province" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="picture" class="form-label">Picture</label>
                            <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_person" class="btn btn-primary">Add Person</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Excel Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Excel File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" action="upload_excel.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Select Excel File</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                            <div class="form-text">
                                File should contain columns: ID Number, Name, Sex, Barangay, City, Province, Birthdate
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateID(personId) {
            window.open('generate_id.php?id=' + personId, '_blank');
        }
    </script>
</body>
</html>
