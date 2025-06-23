<?php
session_start();
include 'include/db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing user query: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$message = '';
$message_type = '';

// Handle support ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'submit_ticket') {
        $subject = trim($_POST['subject']);
        $message_text = trim($_POST['message']);
        $priority = $_POST['priority'];
        
        if (empty($subject) || empty($message_text)) {
            $message = 'Please fill in all required fields.';
            $message_type = 'error';
        } else {
            // Create support tickets table if it doesn't exist
            $table_check = $conn->query("SHOW TABLES LIKE 'support_tickets'");
            if ($table_check->num_rows === 0) {
                $create_table_sql = "CREATE TABLE support_tickets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
                    status ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES customers(id) ON DELETE CASCADE
                )";
                $conn->query($create_table_sql);
            }
            
            // Insert ticket
            $insert_sql = "INSERT INTO support_tickets (user_id, subject, message, priority) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("isss", $user_id, $subject, $message_text, $priority);
            
            if ($insert_stmt->execute()) {
                $message = 'Support ticket submitted successfully! We will get back to you soon.';
                $message_type = 'success';
            } else {
                $message = 'Error submitting ticket. Please try again.';
                $message_type = 'error';
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
    <title>Support - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .page-header h3 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
        }

        .support-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .support-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .support-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        .contact-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .contact-method {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .contact-method:hover {
            transform: translateY(-2px);
        }

        .contact-method i {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        .contact-method h6 {
            margin: 0 0 0.5rem 0;
            font-weight: 600;
            color: #1e293b;
        }

        .contact-method p {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
        }

        .faq-section {
            margin-bottom: 2rem;
        }

        .faq-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .faq-question {
            background: #f8f9fa;
            padding: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .faq-question:hover {
            background: #e9ecef;
        }

        .faq-question h6 {
            margin: 0;
            font-weight: 600;
            color: #1e293b;
        }

        .faq-answer {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            display: none;
        }

        .faq-answer.show {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.75rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1030;
        }

        .overlay.active {
            display: block;
        }

        .mobile-nav {
            display: none;
            padding: 1rem;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .support-card {
                padding: 1.5rem;
            }
            
            .mobile-nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
        }

        .hamburger-btn {
            border: none;
            background: none;
            padding: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e293b;
            font-size: 1.25rem;
        }

        .hamburger-btn:hover {
            color: #0d6efd;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'partials/sidebar.php'; ?>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <button class="hamburger-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
            <span class="d-none d-sm-inline">Menu</span>
        </button>
        <span class="fw-bold">Support</span>
        <div style="width: 2rem;"><!-- Empty div for flex spacing --></div>
    </nav>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h3>Support Center</h3>
            <p class="mb-0">Get help with your bookings and account</p>
        </div>

        <!-- Support Card -->
        <div class="support-card">
            <div class="support-header">
                <i class="fas fa-headset support-icon"></i>
                <h4>How can we help you?</h4>
                <p class="mb-0">We're here to help and answer any question you might have</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Contact Methods -->
            <div class="contact-methods">
                <div class="contact-method">
                    <i class="bi bi-telephone"></i>
                    <h6>Call Us</h6>
                    <p>+234 123 456 7890</p>
                    <small>Available 24/7</small>
                </div>
                <div class="contact-method">
                    <i class="bi bi-envelope"></i>
                    <h6>Email Support</h6>
                    <p>support@prodrivers.com</p>
                    <small>Response within 24 hours</small>
                </div>
                <div class="contact-method">
                    <i class="bi bi-chat-dots"></i>
                    <h6>Live Chat</h6>
                    <p>Chat with us online</p>
                    <small>Available during business hours</small>
                </div>
            </div>

            <!-- Submit Support Ticket -->
            <div class="section-title">
                <i class="bi bi-ticket-detailed me-2"></i>Submit a Support Ticket
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="submit_ticket">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   placeholder="Brief description of your issue" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-control" id="priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" 
                              placeholder="Please describe your issue in detail..." required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Submit Ticket
                    </button>
                </div>
            </form>
        </div>

        <!-- FAQ Section -->
        <div class="support-card">
            <h5 class="section-title">
                <i class="bi bi-question-circle me-2"></i>Frequently Asked Questions
            </h5>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h6>How do I book a driver?</h6>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>To book a driver, go to the "Book a Driver" page, enter your pickup and destination locations, select your preferred date and time, and complete the payment process. You'll receive a confirmation once your booking is confirmed.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h6>How can I cancel my booking?</h6>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>You can cancel your booking from the "My Bookings" page. Click on the booking you want to cancel and select the cancel option. Note that cancellation policies may apply depending on how close to the booking time you cancel.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h6>What payment methods are accepted?</h6>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>We accept various payment methods including credit/debit cards, bank transfers, and mobile money. All payments are processed securely through our payment gateway.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h6>How do I contact my driver?</h6>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Once your booking is confirmed, you can contact your driver through the phone number provided in your booking details. You can also use the "Contact Driver" button in your booking information.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h6>What if my driver doesn't show up?</h6>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>If your driver doesn't show up within 15 minutes of the scheduled time, please contact our support team immediately. We'll arrange for a replacement driver or provide a refund as appropriate.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <h6>How do I update my profile information?</h6>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>You can update your profile information by going to the "My Profile" page. Click on the "Update Profile" button after making your changes to save them.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
            document.body.style.overflow = document.getElementById('sidebar').classList.contains('active') ? 'hidden' : '';
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(event.target) && 
                !hamburgerBtn.contains(event.target)) {
                toggleSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        function toggleFAQ(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('i');
            
            if (answer.classList.contains('show')) {
                answer.classList.remove('show');
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            } else {
                answer.classList.add('show');
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            }
        }
    </script>
</body>
</html> 