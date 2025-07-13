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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background-color: #f0f2f5;
      font-family: 'Inter', sans-serif;
    }

    .main-content {
      padding: 2rem;
    }

    .profile-form {
      max-width: 800px;
      margin: auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .profile-pic-preview {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid #e9ecef;
      margin-bottom: 1rem;
    }

    .form-label {
      font-weight: 500;
    }

    .toast-notification {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1050;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      color: white;
      font-size: 0.9rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      display: none;
    }

    .toast-notification.show {
      display: block;
    }

    .toast-notification.success {
      background-color: #28a745;
    }

    .toast-notification.error {
      background-color: #dc3545;
    }
  </style>
</head>

<body>

  <?php include 'partials/sidebar.php'; ?>

  <div id="toast-notification" class="toast-notification"></div>

  <div class="main-content">
    <div class="profile-form">
      <h4 class="mb-4 text-center">Edit Your Profile</h4>

      <form id="profile-form" method="POST" enctype="multipart/form-data">
        <div class="text-center">
          <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'images/default-avatar.png') ?>"
            alt="Profile Picture" id="profile-pic-preview" class="profile-pic-preview">
        </div>
        <div class="row">
          <div class="col-md-6 mb-3"><label class="form-label">First Name</label><input type="text" class="form-control"
              value="<?= $user['first_name'] ?>" disabled></div>
          <div class="col-md-6 mb-3"><label class="form-label">Last Name</label><input type="text" class="form-control"
              value="<?= $user['last_name'] ?>" disabled></div>
          <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" class="form-control"
              value="<?= $user['email'] ?>" disabled></div>
          <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" class="form-control"
              value="<?= $user['phone'] ?>" disabled></div>
        </div>

        <div class="mb-3"><label for="address" class="form-label">Address</label>
          <input type="text" id="address" name="address" class="form-control"
            value="<?= htmlspecialchars($user['address'] ?? '') ?>">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3"><label for="state" class="form-label">State</label>
            <select id="state" name="state" class="form-select">
              <option value="">Select State</option>
              <?php
                $states = ["Abia", "Adamawa", "Akwa Ibom", "Anambra", "Bauchi", "Bayelsa", "Benue", "Cross River", "Delta", "Ebonyi", "Edo", "Ekiti", "Enugu", "Gombe", "Imo", "Jigawa", "Kaduna", "Kano", "Katsina", "Kebbi", "Kogi", "Kwara", "Lagos", "Nasarawa", "Niger", "Ogun", "Ondo", "Osun", "Oyo", "Plateau", "Rivers", "Sokoto", "Taraba", "Yobe", "Zamfara"];
                foreach ($states as $s) {
                  $selected = ($user['state'] == $s) ? "selected" : "";
                  echo "<option value='$s' $selected>$s</option>";
                }
              ?>
            </select>
          </div>
          <div class="col-md-6 mb-3"><label for="occupation" class="form-label">Occupation</label>
            <input type="text" id="occupation" name="occupation" class="form-control"
              value="<?= htmlspecialchars($user['occupation'] ?? '') ?>">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3"><label for="profile_picture" class="form-label">Profile Picture (optional)</label>
            <input type="file" id="profile_picture" name="profile_picture" class="form-control"
              accept="image/png, image/jpeg">
          </div>
          <div class="col-md-6 mb-3"><label for="id_type" class="form-label">ID Type</label>
            <select id="id_type" name="id_type" class="form-select">
              <option value="">Select ID Type</option>
              <?php
                $id_types = ["Driver's License", "National ID", "Voter's Card", "International Passport"];
                foreach ($id_types as $type) {
                  $selected = ($user['id_type'] == $type) ? "selected" : "";
                  echo "<option value='$type' $selected>$type</option>";
                }
              ?>
            </select>
          </div>
        </div>

        <div class="mb-3"><label for="upload_id" class="form-label">Upload ID (optional)</label>
          <input type="file" id="upload_id" name="upload_id" class="form-control"
            accept="image/png, image/jpeg, application/pdf">
          <?php if (!empty($user['upload_id'])): ?>
          <div class="mt-2">Current ID: <a href="<?= htmlspecialchars($user['upload_id']) ?>" target="_blank">View
              Uploaded ID</a></div>
          <?php endif; ?>
        </div>

        <div class="d-grid">
          <button type="submit" id="submit-btn" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i>&nbsp; Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast-notification');
      toast.textContent = message;
      toast.className = 'toast-notification ' + type;
      toast.classList.add('show');

      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    document.getElementById('profile_picture').addEventListener('change', function (event) {
      const preview = document.getElementById('profile-pic-preview');
      const file = event.target.files[0];
      if (file) {
        preview.src = URL.createObjectURL(file);
      }
    });

    document.getElementById('profile-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = e.target;
      const submitBtn = document.getElementById('submit-btn');
      const formData = new FormData(form);

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>&nbsp; Saving...';

      fetch('api/update_profile.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast(data.message, 'success');
            if (data.new_profile_picture) {
              // Update sidebar profile picture if it exists
              const sidebarPic = document.querySelector('.sidebar .profile-pic');
              if (sidebarPic) {
                sidebarPic.src = data.new_profile_picture;
              }
            }
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An unexpected error occurred.', 'error');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fas fa-save"></i>&nbsp; Save Changes';
        });
    });
  </script>
</body>

</html>