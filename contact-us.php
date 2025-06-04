<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Responsive Dashboard Sidebar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      overflow-x: hidden;
    }
    .sidebar {
      width: 250px;
    }
    @media (min-width: 992px) {
      .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 1030;
        padding: 1rem;
        background-color: #343a40;
        color: white;
      }
      .content {
        margin-left: 250px;
      }
    }
  </style>
</head>
<body>

  <!-- Mobile sidebar toggle button -->
  <nav class="navbar navbar-dark bg-primary d-lg-none">
    <div class="container-fluid">
      <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
        ☰ Menu
      </button>
    </div>
  </nav>

  <!-- Desktop Sidebar -->
  <div class="sidebar d-none d-lg-block">
    <h4>Dashboard</h4>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="#" class="nav-link text-white">Home</a></li>
      <li class="nav-item"><a href="#" class="nav-link text-white">Reports</a></li>
      <li class="nav-item"><a href="#" class="nav-link text-white">Settings</a></li>
    </ul>
  </div>

  <!-- Mobile Sidebar Offcanvas -->
  <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Menu</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="nav flex-column">
        <li class="nav-item"><a href="#" class="nav-link text-white">Home</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white">Reports</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white">Settings</a></li>
      </ul>
    </div>
  </div>

  <!-- Main Content -->
  <div class="content p-4">
    <h1>Welcome to Dashboard</h1>
    <p>This is your dashboard content. Resize the screen to see the responsive sidebar in action.</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
