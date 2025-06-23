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
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Driver Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/driver-theme.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .content {
            margin-left: 250px;
            padding: 2rem;
        }
        .support-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .support-header {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            padding: 2rem;
            border-radius: 12px 12px 0 0;
            text-align: center;
        }
        .support-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .contact-method {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .contact-method:last-child {
            border-bottom: none;
        }
        .contact-method:hover {
            background-color: #f8f9fa;
        }
        .contact-icon {
            width: 48px;
            height: 48px;
            background-color: #e9f2ff;
            color: #0d6efd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        .faq-item {
            border: none;
            background: none;
        }
        .faq-button {
            padding: 1rem;
            font-weight: 600;
            text-align: left;
            background: none;
            border: none;
            width: 100%;
            color: #212529;
        }
        .faq-button:hover {
            color: #0d6efd;
        }
        .faq-button:not(.collapsed) {
            color: #0d6efd;
        }
        .faq-content {
            padding: 0 1rem 1rem;
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
            <span class="navbar-brand mb-0">Support</span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <div class="support-card card mb-4">
            <div class="support-header">
                <i class="fas fa-headset support-icon"></i>
                <h4>How can we help you?</h4>
                <p class="mb-0">We're here to help and answer any question you might have</p>
            </div>
            <div class="card-body p-0">
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">24/7 Support Hotline</h6>
                        <p class="mb-0">
                            <a href="tel:+1234567890" class="text-decoration-none">+1 (234) 567-890</a>
                        </p>
                        <small class="text-muted">Available 24 hours</small>
                    </div>
                </div>
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Email Support</h6>
                        <p class="mb-0">
                            <a href="mailto:support@example.com" class="text-decoration-none">support@example.com</a>
                        </p>
                        <small class="text-muted">We'll respond within 24 hours</small>
                    </div>
                </div>
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Live Chat</h6>
                        <p class="mb-0">Chat with our support team</p>
                        <small class="text-muted">Average response time: 5 minutes</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="card support-card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fas fa-question-circle me-2 text-primary"></i>
                    Frequently Asked Questions
                </h5>

                <div class="accordion" id="faqAccordion">
                    <div class="faq-item">
                        <h2 class="accordion-header">
                            <button class="faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="fas fa-chevron-right me-2"></i>
                                How do I update my profile information?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="faq-content">
                                Go to the "My Profile" section from the sidebar menu. There you can update your personal information, contact details, and profile picture.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h2 class="accordion-header">
                            <button class="faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="fas fa-chevron-right me-2"></i>
                                What documents do I need to upload?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="faq-content">
                                You need to upload your valid driver's license and vehicle registration papers. Make sure all documents are clear and up to date.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h2 class="accordion-header">
                            <button class="faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="fas fa-chevron-right me-2"></i>
                                How do I change my online/offline status?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="faq-content">
                                You can toggle your status using the switch in the top bar of your dashboard. This lets customers know when you're available for rides.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h2 class="accordion-header">
                            <button class="faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="fas fa-chevron-right me-2"></i>
                                What should I do if I need immediate assistance?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="faq-content">
                                For immediate assistance, call our 24/7 support hotline at +1 (234) 567-890. For less urgent matters, you can use the live chat or email support.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/javascript/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }

        // Animate FAQ chevrons
        document.querySelectorAll('.faq-button').forEach(button => {
            button.addEventListener('click', () => {
                const icon = button.querySelector('.fas');
                icon.style.transition = 'transform 0.3s';
                if (button.classList.contains('collapsed')) {
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    icon.style.transform = 'rotate(90deg)';
                }
            });
        });
    </script>
</body>
</html>