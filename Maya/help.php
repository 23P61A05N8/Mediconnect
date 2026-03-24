<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - MediConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8fafc;
            overflow-x: hidden;
            position: relative;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(25, 118, 210, 0.1);
            animation: float 20s infinite linear;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            top: 50%;
            right: -100px;
            background: rgba(56, 142, 60, 0.08);
            animation-delay: -5s;
            animation-duration: 25s;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            bottom: -75px;
            left: 20%;
            background: rgba(103, 58, 183, 0.06);
            animation-delay: -10s;
            animation-duration: 30s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 80%;
            background: rgba(245, 124, 0, 0.05);
            animation-delay: -15s;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(20px, 20px) rotate(90deg);
            }
            50% {
                transform: translate(0, 40px) rotate(180deg);
            }
            75% {
                transform: translate(-20px, 20px) rotate(270deg);
            }
            100% {
                transform: translate(0, 0) rotate(360deg);
            }
        }

        /* Particle Background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(25, 118, 210, 0.2);
            border-radius: 50%;
            animation: particle-float 20s infinite linear;
        }

        @keyframes particle-float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Header Styles */
        header {
            background-color: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            min-height: 70px;
        }

        .nav-brand h2 {
            color: #1976d2;
            font-weight: 800;
            font-size: 1.8rem;
            margin: 0;
            letter-spacing: -0.5px;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            align-items: center;
            margin: 0;
            padding: 0;
            gap: 1.5rem;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.95rem;
            padding: 0.5rem 0;
            position: relative;
        }

        .nav-link:hover {
            color: #1976d2;
        }

        .nav-link.active {
            color: #1976d2;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
            border-radius: 3px;
        }

        .btn-primary-nav {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.2);
        }

        .btn-primary-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(25, 118, 210, 0.3);
            color: white;
        }

        .hamburger {
            display: none;
            cursor: pointer;
            padding: 0.5rem;
            background: #f5f5f5;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .hamburger:hover {
            background: #e0e0e0;
        }

        .bar {
            display: block;
            width: 24px;
            height: 2.5px;
            margin: 5px auto;
            transition: all 0.3s ease;
            background-color: #333;
            border-radius: 2px;
        }

        /* Help Hero Section */
        .help-hero {
            padding: 5rem 0 3rem;
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .help-hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.2;
        }

        .help-hero p {
            font-size: 1.3rem;
            color: #555;
            max-width: 800px;
            margin: 0 auto 2rem;
            line-height: 1.7;
        }

        /* Help Content Section */
        .help-content {
            padding: 3rem 0 5rem;
            position: relative;
            z-index: 2;
        }

        .help-section {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out;
        }

        .help-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #1976d2, #0d47a1, #1976d2);
            background-size: 200% 100%;
            animation: gradient-shift 3s ease infinite;
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .action-card {
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 1px solid rgba(25, 118, 210, 0.1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #1976d2, #0d47a1);
        }

        .action-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(25, 118, 210, 0.15);
            color: #1976d2;
        }

        .action-card i {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            color: #1976d2;
            background: rgba(25, 118, 210, 0.1);
            padding: 1.2rem;
            border-radius: 50%;
        }

        .action-card h3 {
            margin-bottom: 0.8rem;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .action-card p {
            color: #666;
            font-size: 1rem;
        }

        /* FAQ Section */
        .faq-section {
            margin: 3rem 0;
        }

        .section-header {
            margin-bottom: 2.5rem;
        }

        .section-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1976d2;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 0.5rem;
        }

        .section-header h2 i {
            font-size: 1.5rem;
        }

        .section-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .faq-item {
            margin-bottom: 1rem;
            border: 1px solid rgba(25, 118, 210, 0.2);
            border-radius: 15px;
            overflow: hidden;
            background: white;
            transition: all 0.3s ease;
            position: relative;
        }

        .faq-item:hover {
            border-color: #1976d2;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(25, 118, 210, 0.1);
        }

        .faq-question {
            padding: 1.5rem;
            background: rgba(25, 118, 210, 0.05);
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
            color: #1976d2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .faq-question:hover {
            background: rgba(25, 118, 210, 0.1);
        }

        .faq-question i {
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }

        .faq-item.active .faq-answer {
            padding: 1.5rem;
            max-height: 500px;
        }

        .faq-answer p {
            color: #555;
            line-height: 1.7;
            margin: 0;
            font-size: 1.05rem;
        }

        /* Contact Section */
        .contact-section {
            margin: 3rem 0;
        }

        .contact-info {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            padding: 3rem;
            border-radius: 20px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(25, 118, 210, 0.3);
        }

        .contact-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff"><path d="M0 70 Q250 30 500 70 T1000 70 V100 H0 Z"/></svg>');
            background-size: cover;
            background-position: center bottom;
            opacity: 0.1;
        }

        .contact-info > * {
            position: relative;
            z-index: 1;
        }

        .contact-info h2 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
        }

        .contact-info h2 i {
            font-size: 1.5rem;
        }

        .contact-info > p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .contact-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2.5rem;
        }

        .contact-method {
            background: rgba(255, 255, 255, 0.15);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .contact-method:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
        }

        .contact-method i {
            font-size: 2.5rem;
            margin-bottom: 1.2rem;
            color: #bbdefb;
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 50%;
        }

        .contact-method h3 {
            margin-bottom: 0.8rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .contact-method p {
            margin: 0.3rem 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        /* Footer */
        footer {
            background: #1e293b;
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
            z-index: 2;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #60a5fa;
            background: linear-gradient(90deg, #60a5fa, #3b82f6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .footer-section h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            color: #94a3b8;
        }

        .footer-section p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.8rem;
        }

        .footer-section a {
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-section a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-bottom {
            border-top: 1px solid #334155;
            padding-top: 2rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background-color: rgba(255, 255, 255, 0.98);
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
                padding: 2rem 0;
                backdrop-filter: blur(10px);
                border-top: 1px solid rgba(255, 255, 255, 0.2);
                z-index: 99;
            }

            .nav-menu.active {
                left: 0;
            }

            .help-hero {
                padding: 3rem 0 2rem;
            }

            .help-hero h1 {
                font-size: 2.2rem;
            }

            .help-hero p {
                font-size: 1.1rem;
            }

            .help-content {
                padding: 2rem 0 3rem;
            }

            .help-section {
                padding: 2rem 1.5rem;
                margin-bottom: 2rem;
            }

            .quick-actions {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .contact-methods {
                grid-template-columns: 1fr;
            }

            .faq-question {
                padding: 1.2rem;
                font-size: 1rem;
            }

            .faq-item.active .faq-answer {
                padding: 1.2rem;
            }

            .navbar {
                min-height: 65px;
            }

            .nav-brand h2 {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .help-hero h1 {
                font-size: 1.8rem;
            }

            .section-header h2 {
                font-size: 1.4rem;
            }

            .contact-info {
                padding: 2rem 1.5rem;
            }

            .contact-method {
                padding: 1.5rem;
            }

            .action-card {
                padding: 2rem 1.5rem;
            }
        }

        /* Hamburger animation */
        .hamburger.active .bar:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active .bar:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }

        .hamburger.active .bar:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Focus styles for accessibility */
        :focus-visible {
            outline: 3px solid #1976d2;
            outline-offset: 2px;
        }

        /* Loading animation for elements */
        .help-section, .action-card, .faq-item, .contact-method {
            animation: fadeInUp 0.8s ease-out;
            animation-fill-mode: both;
        }

        .action-card:nth-child(1) { animation-delay: 0.1s; }
        .action-card:nth-child(2) { animation-delay: 0.2s; }
        .action-card:nth-child(3) { animation-delay: 0.3s; }
        .faq-item:nth-child(1) { animation-delay: 0.1s; }
        .faq-item:nth-child(2) { animation-delay: 0.2s; }
        .faq-item:nth-child(3) { animation-delay: 0.3s; }
        .faq-item:nth-child(4) { animation-delay: 0.4s; }
        .faq-item:nth-child(5) { animation-delay: 0.5s; }
        .faq-item:nth-child(6) { animation-delay: 0.6s; }
        .contact-method:nth-child(1) { animation-delay: 0.1s; }
        .contact-method:nth-child(2) { animation-delay: 0.2s; }
        .contact-method:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <!-- Particle Background -->
    <div class="particles" id="particles"></div>

    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="nav-brand">
                    <h2><i class="fas fa-heartbeat" style="margin-right: 8px; color: #1976d2;"></i>MediConnect</h2>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link"><i class="fas fa-home" style="margin-right: 6px;"></i>Home</a></li>
                    <li><a href="about.php" class="nav-link"><i class="fas fa-info-circle" style="margin-right: 6px;"></i>About</a></li>
                    <li><a href="help.php" class="nav-link active"><i class="fas fa-question-circle" style="margin-right: 6px;"></i>Help</a></li>
                    <li><a href="login.php" class="nav-link"><i class="fas fa-sign-in-alt" style="margin-right: 6px;"></i>Login</a></li>
                    <li><a href="register.php" class="nav-link btn-primary-nav"><i class="fas fa-user-plus" style="margin-right: 6px;"></i>Register</a></li>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Help Hero Section -->
    <section class="help-hero">
        <div class="container">
            <h1>Help & Support</h1>
            <p>Get comprehensive assistance with using MediConnect. We're here to help you every step of the way.</p>
        </div>
    </section>

    <!-- Help Content Section -->
    <div class="container">
        <section class="help-content">
            <!-- Quick Actions Section -->
            <div class="help-section">
                <div class="section-header">
                    <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                    <p>Quick access to common support resources</p>
                </div>
                
                <div class="quick-actions">
                    <a href="#faq" class="action-card">
                        <i class="fas fa-question-circle"></i>
                        <h3>Frequently Asked Questions</h3>
                        <p>Find quick answers to common questions</p>
                    </a>
                    <a href="#contact" class="action-card">
                        <i class="fas fa-headset"></i>
                        <h3>Contact Support</h3>
                        <p>Get direct help from our support team</p>
                    </a>
                    <a href="register.php" class="action-card">
                        <i class="fas fa-user-plus"></i>
                        <h3>Get Started</h3>
                        <p>Create your account and begin your journey</p>
                    </a>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="help-section" id="faq">
                <div class="section-header">
                    <h2><i class="fas fa-question-circle"></i> Frequently Asked Questions</h2>
                    <p>Quick answers to common questions about MediConnect</p>
                </div>
                
                <div class="faq-section">
                    <div class="faq-item">
                        <div class="faq-question">
                            How do I create an account?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Click on the "Register" button in the navigation menu and fill in your personal, contact, and health information. You'll need your Aadhaar number and a valid email address to complete the registration process.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            What if I forget my password?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Click on "Forgot Password" on the login page. You'll receive an OTP on your registered email to reset your password securely.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How do I upload medical documents?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>After logging in, go to the "Documents" section in your dashboard and use the upload feature. We support PDF, JPG, and PNG formats with a maximum file size of 10MB per document.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            Is my medical data secure?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Yes, absolutely. We use enterprise-grade encryption and follow strict data protection protocols. Your medical information is stored securely and is only accessible to you and authorized healthcare providers.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            Can I access MediConnect on mobile?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Yes! MediConnect is fully responsive and works perfectly on all devices including smartphones and tablets. You can access your medical records anytime, anywhere.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How do I update my personal information?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Log in to your account, go to "Settings" in your dashboard, and you can update all your personal, contact, and medical information at any time.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="help-section" id="contact">
                <div class="contact-section">
                    <div class="contact-info">
                        <h2><i class="fas fa-headset"></i> Need More Help?</h2>
                        <p>Our support team is here to assist you with any questions or issues you may have.</p>
                        
                        <div class="contact-methods">
                            <div class="contact-method">
                                <i class="fas fa-envelope"></i>
                                <h3>Email Support</h3>
                                <p>support@mediconnect.com</p>
                                <p>Response within 24 hours</p>
                            </div>
                            
                            <div class="contact-method">
                                <i class="fas fa-phone"></i>
                                <h3>Phone Support</h3>
                                <p>+91-1800-123-4567</p>
                                <p>Mon-Fri, 9 AM to 6 PM</p>
                            </div>
                            
                            <div class="contact-method">
                                <i class="fas fa-comments"></i>
                                <h3>Live Chat</h3>
                                <p>Available on website</p>
                                <p>Real-time assistance</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>MediConnect</h3>
                    <p>Your complete health record solution for secure and accessible medical information management. Empowering patients and doctors through technology.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i>Home</a></li>
                        <li><a href="about.php"><i class="fas fa-chevron-right"></i>About</a></li>
                        <li><a href="help.php"><i class="fas fa-chevron-right"></i>Help</a></li>
                        <li><a href="privacy.php"><i class="fas fa-chevron-right"></i>Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> info@mediconnect.com</li>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Health St, Medical City</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> MediConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const hamburger = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".nav-menu");

        if (hamburger && navMenu) {
            hamburger.addEventListener("click", () => {
                hamburger.classList.toggle("active");
                navMenu.classList.toggle("active");
                
                // Toggle hamburger animation
                const bars = document.querySelectorAll(".bar");
                if (hamburger.classList.contains("active")) {
                    bars[0].style.transform = 'rotate(-45deg) translate(-5px, 6px)';
                    bars[1].style.opacity = '0';
                    bars[2].style.transform = 'rotate(45deg) translate(-5px, -6px)';
                } else {
                    bars[0].style.transform = 'none';
                    bars[1].style.opacity = '1';
                    bars[2].style.transform = 'none';
                }
            });

            // Close mobile menu when clicking on a link
            document.querySelectorAll(".nav-link").forEach(n => n.addEventListener("click", () => {
                hamburger.classList.remove("active");
                navMenu.classList.remove("active");
                const bars = document.querySelectorAll(".bar");
                bars[0].style.transform = 'none';
                bars[1].style.opacity = '1';
                bars[2].style.transform = 'none';
            }));
        }

        // Particle background effect
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;
            
            // Clear existing particles
            particlesContainer.innerHTML = '';
            
            const particleCount = window.innerWidth < 768 ? 15 : 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random properties
                const size = Math.random() * 6 + 2;
                const posX = Math.random() * 100;
                const duration = Math.random() * 25 + 15;
                const delay = Math.random() * 10;
                const color = Math.random() > 0.7 ? 'rgba(56, 142, 60, 0.2)' : 
                             Math.random() > 0.5 ? 'rgba(103, 58, 183, 0.15)' : 
                             'rgba(25, 118, 210, 0.2)';
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;
                particle.style.background = color;
                particle.style.opacity = Math.random() * 0.6 + 0.1;
                
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize particles when page loads
        window.addEventListener('load', createParticles);
        window.addEventListener('resize', createParticles);

        // Enhanced FAQ functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const faqItem = this.parentElement;
                const isActive = faqItem.classList.contains('active');
                
                // Close all FAQ items
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Open clicked item if it wasn't active
                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
        });

        // Add scroll animation to elements
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            // Observe all elements
            document.querySelectorAll('.action-card, .faq-item, .contact-method').forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(element);
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Highlight FAQ section when clicked from quick actions
        const urlHash = window.location.hash;
        if (urlHash) {
            const targetElement = document.querySelector(urlHash);
            if (targetElement) {
                setTimeout(() => {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 300);
            }
        }
    </script>
</body>
</html>