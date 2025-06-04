<?php
session_start();
include 'include/db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// Fetch customer details
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Determine profile picture path
$picPath = !empty($user['profile_picture']) ? $user['profile_picture'] : "images/default-profile.png";
$cacheBuster = file_exists($picPath) ? "?v=" . filemtime($picPath) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Dashboard</title>

  
  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8f9fa;
    }

    .sidebar {
      background-color: #e9f2fb;
      color: #343a40;
      padding: 1.5rem 1rem;
      height: 100vh;
      border-right: 1px solid #dee2e6;
      position: fixed;
      top: 0;
      left: 0;
      width: 250px;
      z-index: 1040;
    }

    .sidebar a {
      color: #343a40;
      text-decoration: none;
      display: block;
      padding: 0.75rem 1rem;
      border-radius: 0.375rem;
      margin-bottom: 0.5rem;
      transition: background 0.3s, color 0.3s;
    }

    .sidebar a:hover,
    .sidebar a:focus {
      background-color: #cfe2ff;
      color: #0d6efd;
    }

    .profile-pic {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #0d6efd;
      margin-bottom: 0.75rem;
    }

    .card {
      border: none;
      border-radius: 0.75rem;
    }

    .card h5 {
      font-weight: 600;
    }

    .content {
      margin-left: 250px;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .content {
        margin-left: 0;
      }

      .overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1030;
      }

      .overlay.active {
        display: block;
      }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'partials/sidebar.php'; ?>

<!-- Overlay for mobile -->
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Mobile Navbar -->
<nav class="navbar navbar-light bg-white d-md-none border-bottom">
  <div class="container-fluid">
    <button class="btn btn-outline-primary" onclick="toggleSidebar()">☰ Menu</button>
    <span class="navbar-brand mb-0">Dashboard</span>
  </div>
</nav>

<!-- Main Content -->
<div class="content p-4">
  <h3 class="fw-bold mb-4">Welcome back, <?= htmlspecialchars($user['first_name']) ?>!</h3>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow-sm p-4">
        <h5>📄 Account Summary</h5>
        <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
        <p><strong>State:</strong> <?= htmlspecialchars($user['state'] ?? 'Not Set') ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($user['address'] ?? 'Not Set') ?></p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm p-4 bg-light">
        <h5>💬 Support</h5>
        <p>Need help? <a href="support.php" class="text-decoration-none text-primary">Contact Support</a></p>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
  }
</script>

</body>
</html>
