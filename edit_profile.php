<?php
session_start();
include 'include/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];
$query = "SELECT * FROM customers WHERE id = $id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);
    $state = $_POST['state'];
    $id_type = $_POST['id_type'];
    $occupation = trim($_POST['occupation']);

    $profile_picture = $user['profile_picture'];
    $upload_id = $user['upload_id'];

    if ($_FILES['profile_picture']['name'] != "") {
        $file = $_FILES['profile_picture'];
        $filename = basename($file['name']);
        $filesize = $file['size'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array(strtolower($ext), $allowed)) {
            $error = "Only JPG, PNG, JPEG files are allowed for profile picture.";
        } elseif ($filesize > 800000) {
            $error = "Profile picture size must be under 800KB.";
        } else {
            $newname = "uploads/profile_" . time() . "." . $ext;
            move_uploaded_file($file['tmp_name'], $newname);
            $profile_picture = $newname;
        }
    }

    if ($_FILES['upload_id']['name'] != "") {
        $file = $_FILES['upload_id'];
        $filename = basename($file['name']);
        $filesize = $file['size'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array(strtolower($ext), $allowed)) {
            $error = "Only JPG, PNG, JPEG, or PDF files are allowed for ID upload.";
        } elseif ($filesize > 2000000) {
            $error = "ID file must be under 2MB.";
        } else {
            $newname = "uploads/id_" . time() . "." . $ext;
            move_uploaded_file($file['tmp_name'], $newname);
            $upload_id = $newname;
        }
    }

    if (empty($error)) {
        $sql = "UPDATE customers SET address=?, state=?, id_type=?, occupation=?, profile_picture=?, upload_id=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssi", $address, $state, $id_type, $occupation, $profile_picture, $upload_id, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: edit_profile.php?updated=1");
        exit();
    }
}

if (isset($_GET['updated'])) {
    $success = "Profile updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f0f2f5;
      font-family: 'Inter', sans-serif;
    }
    .sidebar {
      background-color: #e9f2fb;
      color: #343a40;
      padding: 1.5rem 1rem;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      width: 250px;
      border-right: 1px solid #dee2e6;
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
    .sidebar a:hover {
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
        top: 0;
        left: 0;
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

<?php include 'partials/sidebar.php'; ?>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<nav class="navbar navbar-light bg-white d-md-none border-bottom">
  <div class="container-fluid">
    <button class="btn btn-outline-primary" onclick="toggleSidebar()">☰ Menu</button>
    <span class="navbar-brand mb-0">Edit Profile</span>
  </div>
</nav>

<div class="content p-4">
  <div class="col-lg-8 mx-auto bg-white p-4 rounded shadow-sm">
    <h4 class="mb-4">Edit Profile</h4>

    <?php if ($success): ?>
      <div class="alert alert-success show" id="success-alert"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3"><label>First Name</label><input type="text" class="form-control" value="<?= $user['first_name'] ?>" disabled></div>
      <div class="mb-3"><label>Last Name</label><input type="text" class="form-control" value="<?= $user['last_name'] ?>" disabled></div>
      <div class="mb-3"><label>Email</label><input type="email" class="form-control" value="<?= $user['email'] ?>" disabled></div>
      <div class="mb-3"><label>Phone</label><input type="text" class="form-control" value="<?= $user['phone'] ?>" disabled></div>

      <div class="mb-3"><label>Address</label>
        <input type="text" name="address" class="form-control" value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : $user['address'] ?? '' ?>">
      </div>

      <div class="mb-3"><label>State</label>
        <select name="state" class="form-control">
          <option value="">Select State</option>
          <?php
            // $states = ["Lagos", "Abuja", "Rivers", "Kano", "Kaduna", "Oyo"];
            $states = [
              "Abia", "Adamawa", "Akwa Ibom", "Anambra", "Bauchi", "Bayelsa", "Benue", "Cross River",
              "Delta", "Ebonyi", "Edo", "Ekiti", "Enugu", "Gombe", "Imo", "Jigawa", "Kaduna",
              "Kano", "Katsina", "Kebbi", "Kogi", "Kwara", "Lagos", "Nasarawa", "Niger", "Ogun",
              "Ondo", "Osun", "Oyo", "Plateau", "Rivers", "Sokoto", "Taraba", "Yobe", "Zamfara"];
            foreach ($states as $s) {
              $selected = ($user['state'] == $s || (isset($_POST['state']) && $_POST['state'] == $s)) ? "selected" : "";
              echo "<option value='$s' $selected>$s</option>";
            }
          ?>
        </select>
      </div>

      <div class="mb-3"><label>ID Type</label>
        <select name="id_type" class="form-control">
          <option value="">Select ID Type</option>
          <?php
            $ids = ["National ID", "Driver's License", "Passport"];
            foreach ($ids as $idOption) {
              $selected = ($user['id_type'] == $idOption || (isset($_POST['id_type']) && $_POST['id_type'] == $idOption)) ? "selected" : "";
              echo "<option value='$idOption' $selected>$idOption</option>";
            }
          ?>
        </select>
      </div>

      <div class="mb-3">
        <label>Upload ID</label>
        <input type="file" name="upload_id" class="form-control">
        <?php if (!empty($user['upload_id'])): ?>
          <button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#idModal">View Uploaded ID</button>
        <?php endif; ?>
      </div>

      <div class="mb-3"><label>Occupation</label>
        <input type="text" name="occupation" class="form-control" value="<?= isset($_POST['occupation']) ? htmlspecialchars($_POST['occupation']) : $user['occupation'] ?? '' ?>">
      </div>

      <div class="mb-3">
        <label>Upload Profile Picture (JPG/PNG, Max 800KB)</label>
        <input type="file" name="profile_picture" class="form-control">
        <?php if (!empty($user['profile_picture'])): ?>
          <img src="<?= $user['profile_picture'] ?>" class="img-thumbnail mt-2" width="150">
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</div>

<!-- ID Modal -->
<div class="modal fade" id="idModal" tabindex="-1" aria-labelledby="idModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Uploaded ID</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <?php if (pathinfo($user['upload_id'], PATHINFO_EXTENSION) === 'pdf'): ?>
          <embed src="<?= $user['upload_id'] ?>" width="100%" height="500px" type="application/pdf">
        <?php else: ?>
          <img src="<?= $user['upload_id'] ?>" class="img-fluid" alt="Uploaded ID">
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
  }

  window.addEventListener('DOMContentLoaded', () => {
    const alert = document.getElementById('success-alert');
    if (alert) {
      setTimeout(() => {
        alert.classList.add('fade');
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 500);
      }, 4000);
    }

    if (window.location.search.includes('updated=1')) {
      window.history.replaceState({}, document.title, "edit_profile.php");
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
