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
    <title>Documents - Driver Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .content {
            margin-left: 250px;
            padding: 2rem;
        }
        .upload-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .upload-zone:hover {
            border-color: #0d6efd;
            background-color: #f8f9ff;
        }
        .upload-icon {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .document-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .document-info {
            margin-top: 1rem;
            font-size: 0.875rem;
            color: #6c757d;
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
            height: auto;
        }
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Mobile Navbar -->
    <nav class="navbar navbar-light bg-white d-md-none border-bottom">
        <div class="container-fluid">
            <button class="btn btn-outline-primary" onclick="toggleSidebar()">☰ Menu</button>
            <span class="navbar-brand mb-0">Documents</span>
        </div>
    </nav>

    <!-- Document View Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id="downloadDocument" download>Download</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h4 class="mb-4">Document Management</h4>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Passport Photo Upload -->
            <div class="col-md-4">
                <div class="card upload-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-id-badge me-2 text-primary"></i>
                            Passport Photo
                        </h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="upload-zone" onclick="document.getElementById('passport').click()">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h6>Click to upload Passport Photo</h6>
                                <p class="text-muted mb-0">Supported formats: JPG, PNG</p>
                                <input type="file" id="passport" name="passport" class="d-none" accept=".jpg,.jpeg,.png">
                            </div>
                            <?php if (!empty($driver['photo_path'])): ?>
                                <div class="document-info">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Photo uploaded
                                    <button type="button" class="btn btn-sm btn-outline-primary float-end" 
                                            onclick="viewDocument('<?= htmlspecialchars($driver['photo_path']) ?>', 'Passport Photo')">
                                        View
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Driver's License Upload -->
            <div class="col-md-4">
                <div class="card upload-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-id-card me-2 text-primary"></i>
                            Driver's License
                        </h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="upload-zone" onclick="document.getElementById('license').click()">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h6>Click to upload Driver's License</h6>
                                <p class="text-muted mb-0">Supported formats: JPG, PNG, PDF</p>
                                <input type="file" id="license" name="license" class="d-none" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <?php if (!empty($driver['license_path'])): ?>
                                <div class="document-info">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Document uploaded
                                    <button type="button" class="btn btn-sm btn-outline-primary float-end" 
                                            onclick="viewDocument('<?= htmlspecialchars($driver['license_path']) ?>', 'Driver\'s License')">
                                        View
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Vehicle Papers Upload -->
            <div class="col-md-4">
                <div class="card upload-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-car me-2 text-primary"></i>
                            Vehicle Papers
                        </h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="upload-zone" onclick="document.getElementById('vehicle_papers').click()">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h6>Click to upload Vehicle Papers</h6>
                                <p class="text-muted mb-0">Supported formats: JPG, PNG, PDF</p>
                                <input type="file" id="vehicle_papers" name="vehicle_papers" class="d-none" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <?php if (!empty($driver['vehicle_papers_path'])): ?>
                                <div class="document-info">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Document uploaded
                                    <button type="button" class="btn btn-sm btn-outline-primary float-end"
                                            onclick="viewDocument('<?= htmlspecialchars($driver['vehicle_papers_path']) ?>', 'Vehicle Papers')">
                                        View
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Guidelines -->
        <div class="card upload-card mt-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Document Guidelines
                </h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Maximum file size: 5MB
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Accepted formats: JPG, PNG, PDF
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Documents must be clear and legible
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        All information must be current and valid
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }

        // Document preview function
        function viewDocument(path, title) {
            const modal = document.getElementById('documentModal');
            const modalTitle = modal.querySelector('.modal-title');
            const modalBody = modal.querySelector('.modal-body');
            const downloadBtn = document.getElementById('downloadDocument');
            
            modalTitle.textContent = title;
            downloadBtn.href = path;
            
            const extension = path.split('.').pop().toLowerCase();
            if (extension === 'pdf') {
                modalBody.innerHTML = `<iframe src="${path}"></iframe>`;
            } else {
                modalBody.innerHTML = `<img src="${path}" class="img-fluid">`;
            }
            
            new bootstrap.Modal(modal).show();
        }

        // AJAX Upload handling
        document.querySelectorAll('input[type="file"]').forEach(fileInput => {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Get the upload zone and form
                const form = this.closest('form');
                const uploadZone = form.querySelector('.upload-zone');
                const docType = this.getAttribute('name');

                // Create progress bar
                const progressBar = document.createElement('div');
                progressBar.className = 'progress mt-3';
                progressBar.innerHTML = `
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 0%" 
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100">0%</div>`;
                
                // Add progress bar to upload zone
                uploadZone.appendChild(progressBar);

                // Create FormData
                const formData = new FormData();
                formData.append(docType, file);

                // Create and configure AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', window.location.href, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                // Upload progress handler
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        const progressBarInner = progressBar.querySelector('.progress-bar');
                        progressBarInner.style.width = percentComplete + '%';
                        progressBarInner.textContent = Math.round(percentComplete) + '%';
                        progressBarInner.setAttribute('aria-valuenow', percentComplete);
                    }
                };

                // Response handler
                xhr.onload = function() {
                    console.log('Response received:', xhr.responseText); // Debug log
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log('Parsed response:', response); // Debug log
                            
                            // Remove any existing alerts
                            form.querySelectorAll('.alert').forEach(alert => alert.remove());
                            
                            if (response.success) {
                                // Show success message
                                const alert = document.createElement('div');
                                alert.className = 'alert alert-success alert-dismissible fade show mt-3';
                                alert.innerHTML = `
                                    ${response.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                                form.appendChild(alert);

                                // Update document info
                                const documentInfo = document.createElement('div');
                                documentInfo.className = 'document-info';
                                documentInfo.innerHTML = `
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Document uploaded
                                    <button type="button" class="btn btn-sm btn-outline-primary float-end" 
                                            onclick="viewDocument('${response.path}', '${docType === 'license' ? 'Driver\'s License' : 'Vehicle Papers'}')">
                                        View
                                    </button>`;
                                
                                // Replace existing document info or add new one
                                const existingInfo = form.querySelector('.document-info');
                                if (existingInfo) {
                                    existingInfo.replaceWith(documentInfo);
                                } else {
                                    form.appendChild(documentInfo);
                                }

                                // Auto-hide success message after 5 seconds
                                setTimeout(() => {
                                    const bsAlert = new bootstrap.Alert(alert);
                                    bsAlert.close();
                                }, 5000);
                            } else {
                                // Show error message
                                const alert = document.createElement('div');
                                alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
                                alert.innerHTML = `
                                    ${response.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                                form.appendChild(alert);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            console.error('Raw response:', xhr.responseText);
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
                            alert.innerHTML = `
                                Error processing response. Please try again.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                            form.appendChild(alert);
                        }
                    } else {
                        console.error('Server returned status:', xhr.status);
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
                        alert.innerHTML = `
                            Server error: ${xhr.status}. Please try again.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                        form.appendChild(alert);
                    }

                    // Remove progress bar after completion
                    setTimeout(() => {
                        progressBar.remove();
                    }, 1000);
                };

                // Error handler
                xhr.onerror = function(e) {
                    console.error('Upload error:', e);
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
                    alert.innerHTML = `
                        Upload failed. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                    form.appendChild(alert);
                    progressBar.remove();
                };

                // Send the request
                xhr.send(formData);
            });
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html> 