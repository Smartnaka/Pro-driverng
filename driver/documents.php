<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['driver_id'];

// Fetch driver details
$sql = "SELECT * FROM drivers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set JSON header for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
    }
    
    $response = ['success' => false, 'message' => '', 'path' => ''];
    
    // Define upload directories for each document type
    $uploadDirs = [
        'license' => 'uploads/documents/licenses/',
        'vehicle_papers' => 'uploads/documents/vehicle_papers/',
        'passport' => 'uploads/documents/passport/'
    ];

    // Create upload directories if they don't exist
    foreach ($uploadDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    $allowedTypes = [
        'license' => ['image/jpeg', 'image/png', 'application/pdf'],
        'vehicle_papers' => ['image/jpeg', 'image/png', 'application/pdf'],
        'passport' => ['image/jpeg', 'image/png']
    ];

    $maxFileSize = 5 * 1024 * 1024; // 5MB

    foreach (['license', 'vehicle_papers', 'passport'] as $docType) {
        if (isset($_FILES[$docType]) && $_FILES[$docType]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$docType];
            
            if (!in_array($file['type'], $allowedTypes[$docType])) {
                $response['message'] = "Invalid file type for " . ucwords(str_replace('_', ' ', $docType));
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    echo json_encode($response);
                    exit;
                }
                $error = $response['message'];
                continue;
            }

            if ($file['size'] > $maxFileSize) {
                $response['message'] = "File size exceeds 5MB limit for " . ucwords(str_replace('_', ' ', $docType));
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    echo json_encode($response);
                    exit;
                }
                $error = $response['message'];
                continue;
            }

            // Generate filename with user's full name and document type
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fullName = str_replace(' ', '_', trim($driver['first_name'] . '_' . $driver['last_name']));
            $docTypeName = str_replace('_', '-', $docType);
            $newFileName = $fullName . '_' . $docTypeName . '_' . time() . '.' . $ext;
            $targetPath = $uploadDirs[$docType] . $newFileName;

            // Delete old file if exists
            $dbField = $docType === 'passport' ? 'photo_path' : $docType . '_path';
            $oldFilePath = $driver[$dbField] ?? '';
            if (!empty($oldFilePath) && file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Update database with the new path
                $updateSql = "UPDATE drivers SET $dbField = ? WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                if ($stmt === false) {
                    $response['message'] = "Database error: " . $conn->error;
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        echo json_encode($response);
                        exit;
                    }
                    $error = $response['message'];
                    continue;
                }
                $stmt->bind_param("si", $targetPath, $driver_id);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = ucwords(str_replace('_', ' ', $docType)) . " uploaded successfully!";
                    $response['path'] = $targetPath;
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        echo json_encode($response);
                        exit;
                    }
                    $success = $response['message'];
                } else {
                    $response['message'] = "Failed to update database: " . $stmt->error;
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        echo json_encode($response);
                        exit;
                    }
                    $error = $response['message'];
                }
            } else {
                $response['message'] = "Failed to upload " . ucwords(str_replace('_', ' ', $docType));
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    echo json_encode($response);
                    exit;
                }
                $error = $response['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - Pro-Drivers</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .upload-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .upload-zone {
            border: 2px dashed var(--border-color);
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--light-bg);
        }

        .upload-zone:hover {
            border-color: var(--primary-color);
            background: rgba(37, 99, 235, 0.05);
        }

        .upload-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .document-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.75rem;
            margin-top: 1rem;
            border: 1px solid var(--border-color);
        }

        .document-info {
            margin-top: 1rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .modal-body {
            text-align: center;
            padding: 0;
        }

        .modal-body iframe {
            width: 100%;
            height: 80vh;
            border: none;
        }

        .modal-body img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }

        .card-header {
            background: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-upload {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-upload:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-upload:disabled {
            background: var(--secondary-color);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include Shared Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h3><i class="fas fa-file-alt me-2"></i>Document Management</h3>
            <p class="mb-0 opacity-75">Upload and manage your required documents for verification</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Driver's License -->
            <div class="col-lg-4">
                <div class="upload-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-id-card"></i>
                            Driver's License
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="licenseForm" enctype="multipart/form-data">
                            <div class="upload-zone" onclick="document.getElementById('licenseFile').click()">
                                <div class="upload-icon">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <h6>Upload License</h6>
                                <p class="text-muted mb-0">Click to select or drag and drop</p>
                                <p class="text-muted small">JPG, PNG, PDF (Max 5MB)</p>
                            </div>
                            <input type="file" id="licenseFile" name="license" accept=".jpg,.jpeg,.png,.pdf" style="display: none;" onchange="handleFileSelect(this, 'license')">
                            
                            <?php if (!empty($driver['license_path'])): ?>
                                <div class="mt-3">
                                    <img src="<?= htmlspecialchars($driver['license_path']) ?>" alt="License Preview" class="document-preview" onclick="openModal('<?= htmlspecialchars($driver['license_path']) ?>')">
                                    <div class="document-info">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        License uploaded successfully
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-upload w-100 mt-3" id="licenseBtn" style="display: none;">
                                <i class="fas fa-upload me-2"></i>Upload License
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Vehicle Papers -->
            <div class="col-lg-4">
                <div class="upload-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-car"></i>
                            Vehicle Papers
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="vehicleForm" enctype="multipart/form-data">
                            <div class="upload-zone" onclick="document.getElementById('vehicleFile').click()">
                                <div class="upload-icon">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <h6>Upload Vehicle Papers</h6>
                                <p class="text-muted mb-0">Click to select or drag and drop</p>
                                <p class="text-muted small">JPG, PNG, PDF (Max 5MB)</p>
                            </div>
                            <input type="file" id="vehicleFile" name="vehicle_papers" accept=".jpg,.jpeg,.png,.pdf" style="display: none;" onchange="handleFileSelect(this, 'vehicle')">
                            
                            <?php if (!empty($driver['vehicle_papers_path'])): ?>
                                <div class="mt-3">
                                    <img src="<?= htmlspecialchars($driver['vehicle_papers_path']) ?>" alt="Vehicle Papers Preview" class="document-preview" onclick="openModal('<?= htmlspecialchars($driver['vehicle_papers_path']) ?>')">
                                    <div class="document-info">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        Vehicle papers uploaded successfully
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-upload w-100 mt-3" id="vehicleBtn" style="display: none;">
                                <i class="fas fa-upload me-2"></i>Upload Vehicle Papers
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Passport Photo -->
            <div class="col-lg-4">
                <div class="upload-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-user"></i>
                            Passport Photo
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="passportForm" enctype="multipart/form-data">
                            <div class="upload-zone" onclick="document.getElementById('passportFile').click()">
                                <div class="upload-icon">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <h6>Upload Passport Photo</h6>
                                <p class="text-muted mb-0">Click to select or drag and drop</p>
                                <p class="text-muted small">JPG, PNG (Max 5MB)</p>
                            </div>
                            <input type="file" id="passportFile" name="passport" accept=".jpg,.jpeg,.png" style="display: none;" onchange="handleFileSelect(this, 'passport')">
                            
                            <?php if (!empty($driver['photo_path'])): ?>
                                <div class="mt-3">
                                    <img src="<?= htmlspecialchars($driver['photo_path']) ?>" alt="Passport Photo Preview" class="document-preview" onclick="openModal('<?= htmlspecialchars($driver['photo_path']) ?>')">
                                    <div class="document-info">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        Passport photo uploaded successfully
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-upload w-100 mt-3" id="passportBtn" style="display: none;">
                                <i class="fas fa-upload me-2"></i>Upload Passport Photo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function handleFileSelect(input, type) {
            const file = input.files[0];
            if (file) {
                const btn = document.getElementById(type + 'Btn');
                btn.style.display = 'block';
                btn.disabled = false;
                
                // Show preview if it's an image
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = input.parentElement.querySelector('.document-preview') || 
                                      input.parentElement.querySelector('.upload-zone');
                        if (preview.classList.contains('upload-zone')) {
                            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="document-preview">`;
                        }
                    };
                    reader.readAsDataURL(file);
                }
            }
        }

        function openModal(path) {
            const modal = new bootstrap.Modal(document.getElementById('documentModal'));
            const modalContent = document.getElementById('modalContent');
            
            if (path.toLowerCase().endsWith('.pdf')) {
                modalContent.innerHTML = `<iframe src="${path}"></iframe>`;
            } else {
                modalContent.innerHTML = `<img src="${path}" alt="Document">`;
            }
            
            modal.show();
        }

        // Handle form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = this.querySelector('button[type="submit"]');
                
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while uploading the file.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload';
                });
            });
        });
    </script>
</body>
</html>