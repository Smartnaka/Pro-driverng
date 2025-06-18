/**
 * Admin Manage Customers Page
 *
 * This script allows admin users to view and manage customer accounts.
 * 
 * Features:
 * - Displays a list of all customers with their details (ID, Name, Email, Phone, Status, Registration Date).
 * - Allows admin to block or activate customer accounts via status updates.
 * - Automatically adds a 'status' column to the 'customers' table if it does not exist.
 * - Uses prepared statements to securely update customer status.
 * - Utilizes Bootstrap for responsive UI and styling.
 * 
 * Dependencies:
 * - Requires an active session and admin authentication (via 'auth_check.php').
 * - Database connection is established through 'db.php'.
 * - Sidebar navigation is included from 'sidebar.php'.
 * - Bootstrap CSS/JS and Bootstrap Icons are used for UI components.
 * 
 * Security:
 * - Uses htmlspecialchars to prevent XSS when displaying customer data.
 * - Uses prepared statements to prevent SQL injection on status updates.
 * 
 * Usage:
 * - Accessible only to authenticated admin users.
 * - Admin can block or activate customers using the action buttons in the table.
 */
<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Handle customer status updates
if (isset($_POST['customer_id']) && isset($_POST['action'])) {
    $customer_id = $_POST['customer_id'];
    $action = $_POST['action'];
    
    // First, let's add the status column if it doesn't exist
    $conn->query("ALTER TABLE customers ADD COLUMN IF NOT EXISTS status ENUM('active', 'blocked') DEFAULT 'active'");
    
    $status = ($action === 'block') ? 'blocked' : 'active';
    
    $sql = "UPDATE customers SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $customer_id);
    $stmt->execute();
}

// Fetch all customers
$sql = "SELECT * FROM customers ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
        }
        .status-badge {
            text-transform: capitalize;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table th {
            white-space: nowrap;
            background: #f8f9fa;
        }
        .table td {
            vertical-align: middle;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        main {
            transition: margin-left 0.3s ease;
        }
        @media (min-width: 768px) {
            main {
                margin-left: 280px; /* Match your sidebar width */
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="px-4 py-4">
        <div class="container-fluid">
            <h2 class="mb-4">Manage Customers</h2>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($customer = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($customer['id']) ?></td>
                                        <td><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></td>
                                        <td><?= htmlspecialchars($customer['email']) ?></td>
                                        <td><?= htmlspecialchars($customer['phone']) ?></td>
                                        <td>
                                            <span class="badge status-badge bg-<?= ($customer['status'] ?? 'active') === 'active' ? 'success' : 'danger' ?>">
                                                <?= htmlspecialchars($customer['status'] ?? 'active') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($customer['created_at']))) ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                                                <?php if(($customer['status'] ?? 'active') === 'active'): ?>
                                                    <input type="hidden" name="action" value="block">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-slash-circle"></i> Block
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="action" value="activate">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle"></i> Activate
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 