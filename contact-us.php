<?php
session_start();
include 'include/db.php';

// Handle contact form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = '<div class="alert alert-danger">Please fill in all fields.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
    } else {
        // Here you would typically send an email or save to database
        // For now, we'll just show a success message
        $message = '<div class="alert alert-success">Thank you for your message! We\'ll get back to you soon.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Pro-Drivers</title>
    <meta name="description" content="Get in touch with Pro-Drivers. We're here to help with any questions about our driver booking service.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #00509e;
            --accent-color: #ff6b35;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --white: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--secondary-color) !important;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 51, 102, 0.2);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 8rem 0 4rem;
            text-align: center;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .contact-section {
            padding: 6rem 0;
            background: var(--bg-light);
        }

        .contact-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
            transition: transform 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 51, 102, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .contact-info {
            background: var(--white);
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            margin-right: 1rem;
        }

        .info-content h5 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .info-content p {
            color: var(--text-light);
            margin: 0;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2.5rem;
            }
            
            .contact-card {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Pro-Drivers
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#how-it-works">How It Works</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="dashboard.php">Dashboard</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="register.php">Get Started</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We're here to help! Get in touch with us for any questions or support.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="row g-5">
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="contact-card">
                        <h3 class="mb-4">Send us a Message</h3>
                        
                        <?php echo $message; ?>
                        
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                
                                <div class="col-12">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="col-lg-4">
                    <div class="contact-info">
                        <h3 class="mb-4">Get in Touch</h3>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <h5>Address</h5>
                                <p>123 Business Street<br>Lagos, Nigeria</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <h5>Phone</h5>
                                <p>+234 123 456 7890<br>+234 987 654 3210</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <h5>Email</h5>
                                <p>info@prodrivers.com<br>support@prodrivers.com</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <h5>Business Hours</h5>
                                <p>Monday - Friday: 8AM - 8PM<br>Saturday: 9AM - 6PM<br>Sunday: 10AM - 4PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Contact Methods -->
            <div class="row g-4 mt-5">
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card text-center">
                        <div class="contact-icon mx-auto">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p>Our customer support team is available round the clock to assist you with any questions or concerns.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card text-center">
                        <div class="contact-icon mx-auto">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4>Live Chat</h4>
                        <p>Chat with our support team in real-time for immediate assistance with your booking or account.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card text-center">
                        <div class="contact-icon mx-auto">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h4>FAQ</h4>
                        <p>Find quick answers to common questions in our comprehensive FAQ section.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5><i class="fas fa-car"></i> Pro-Drivers</h5>
                    <p>Professional driver booking platform providing safe, reliable, and convenient transportation services.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Services</h5>
                    <ul class="list-unstyled">
                        <li><a href="book-driver.php" class="text-white-50">Book Driver</a></li>
                        <li><a href="driver/" class="text-white-50">Become Driver</a></li>
                        <li><a href="#" class="text-white-50">Corporate Services</a></li>
                        <li><a href="#" class="text-white-50">Emergency Transport</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Company</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">About Us</a></li>
                        <li><a href="#" class="text-white-50">Careers</a></li>
                        <li><a href="#" class="text-white-50">Press</a></li>
                        <li><a href="#" class="text-white-50">Blog</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="support.php" class="text-white-50">Help Center</a></li>
                        <li><a href="contact-us.php" class="text-white-50">Contact Us</a></li>
                        <li><a href="#" class="text-white-50">Safety</a></li>
                        <li><a href="#" class="text-white-50">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">Privacy Policy</a></li>
                        <li><a href="#" class="text-white-50">Terms of Use</a></li>
                        <li><a href="#" class="text-white-50">Cookie Policy</a></li>
                        <li><a href="#" class="text-white-50">GDPR</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-top border-secondary pt-4 mt-4 text-center">
                <p class="mb-0">&copy; 2024 Pro-Drivers. All rights reserved. | Designed with <i class="fas fa-heart text-danger"></i> for your safety</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
