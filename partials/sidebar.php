<?php if (!isset($user)) exit(); ?>

<?php
  $current_page = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
   <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
   <style>
    .sidebar a.active {
  background-color: #cfe2ff;
  color: #0d6efd !important;
  font-weight: 600;
}

   </style>

</head>
<body>
    
<div class="sidebar" id="sidebar">
  <div class="text-center mb-4">
    <?php if (!empty($user['profile_picture'])): ?>
      <img src="<?= htmlspecialchars($user['profile_picture']) ?>" class="profile-pic" alt="Profile Picture">
    <?php else: ?>
      <img src="images/default-profile.png" class="profile-pic" alt="Default Profile Picture">
    <?php endif; ?>
    <h6 class="mt-2 mb-0"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h6>
    <small><?= htmlspecialchars($user['email']) ?></small>
  </div>

  <a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
    <i class="bi bi-house-door-fill me-2 text-primary"></i>Dashboard
  </a>
  <a href="hire-driver.php" class="<?= $current_page === 'hire-driver.php' ? 'active' : '' ?>">
    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Hire a Driver
  </a>

  <a href="hire-vehicle.php" class="<?= $current_page === 'hire-vehicle.php' ? 'active' : '' ?>">
    <i class="bi bi-truck-front-fill me-2 text-primary"></i>Rent a Vehicle
  </a>

  <a href="edit_profile.php" class="<?= $current_page === 'edit_profile.php' ? 'active' : '' ?>">
    <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Profile
  </a>
  <a href="logout.php" class="<?= $current_page === 'logout.php' ? 'active' : '' ?>">
    <i class="bi bi-box-arrow-right me-2 text-primary"></i>Logout
  </a>
</div>




</body>
</html>

