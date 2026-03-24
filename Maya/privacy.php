<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Settings - MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base styles from index.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #e3f2fd 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .nav-brand h2 {
            color: #1976d2;
            font-size: 1.8rem;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
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
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #1976d2;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.3);
        }

        /* Enhanced Privacy Container */
        .privacy-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            max-width: 900px;
            margin: 2rem auto;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out;
        }

        .privacy-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(25, 118, 210, 0.05) 0%, rgba(227, 242, 253, 0.1) 100%);
            z-index: 0;
        }

        .privacy-container > * {
            position: relative;
            z-index: 1;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Enhanced Privacy Sections */
        .privacy-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(224, 224, 224, 0.8);
            position: relative;
        }

        .privacy-section:last-child {
            border-bottom: none;
        }

        .privacy-section h3 {
            color: #1976d2;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .privacy-section h3 i {
            font-size: 1.2rem;
        }

        .privacy-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
            border: 1px solid transparent;
        }

        .privacy-option:hover {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(25, 118, 210, 0.2);
            transform: translateX(5px);
        }

        .privacy-option:last-child {
            margin-bottom: 0;
        }

        .option-info h4 {
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 1.1rem;
        }

        .option-info p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Enhanced Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #ccc, #999);
            transition: .4s;
            border-radius: 34px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        input:checked + .slider {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Enhanced Button */
        .btn-block {
            display: block;
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-block::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .btn-block:hover::before {
            left: 100%;
        }

        .btn-block:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(25, 118, 210, 0.4);
        }

        .btn-block i {
            margin-right: 0.5rem;
        }

        /* Enhanced Footer Link */
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(224, 224, 224, 0.8);
        }

        .auth-footer a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .auth-footer a:hover {
            color: #0d47a1;
            transform: translateX(-5px);
        }

        /* Animations from index.php */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Menu Styles */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .bar {
            width: 25px;
            height: 3px;
            background: #333;
            margin: 3px 0;
            transition: 0.3s;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background: white;
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
                padding: 2rem 0;
            }

            .nav-menu.active {
                left: 0;
            }

            .privacy-container {
                padding: 2rem;
                margin: 1rem auto;
            }

            .section-title {
                font-size: 2rem;
            }

            .privacy-option {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .option-info {
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .privacy-container {
                padding: 1.5rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .privacy-section h3 {
                font-size: 1.2rem;
            }
        }

        /* Enhanced Background Animation */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            background: rgba(25, 118, 210, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            top: 30%;
            right: 20%;
            animation-delay: 1s;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
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

    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="nav-brand">
                    <h2>MediConnect</h2>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="about.php" class="nav-link">About</a></li>
                    <li><a href="help.php" class="nav-link">Help</a></li>
                    <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="logout.php" class="nav-link btn-primary">Logout</a></li>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </nav>
        </div>
    </header>

    <div class="container" style="padding: 2rem 15px; position: relative; z-index: 2;">
        <div class="privacy-container">
            <h2 class="section-title">Privacy Settings</h2>
            
            <div class="privacy-section">
                <h3><i class="fas fa-user-shield"></i> Profile Visibility</h3>
                
                <div class="privacy-option">
                    <div class="option-info">
                        <h4>Public Profile</h4>
                        <p>Allow other users to see your basic profile information</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="privacy-option">
                    <div class="option-info">
                        <h4>Medical History Visibility</h4>
                        <p>Control who can see your medical history</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            
            <div class="privacy-section">
                <h3><i class="fas fa-bell"></i> Notifications</h3>
                
                <div class="privacy-option">
                    <div class="option-info">
                        <h4>Email Notifications</h4>
                        <p>Receive updates and reminders via email</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="privacy-option">
                    <div class="option-info">
                        <h4>SMS Notifications</h4>
                        <p>Receive important alerts via SMS</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            
            <div class="privacy-section">
                <h3><i class="fas fa-share-alt"></i> Data Sharing</h3>
                
                <div class="privacy-option">
                    <div class="option-info">
                        <h4>Share with Healthcare Providers</h4>
                        <p>Allow doctors and hospitals to access your medical records</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="privacy-option">
                    <div class="option-info">
                        <h4>Research Participation</h4>
                        <p>Contribute anonymized data for medical research</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 2rem;">
                <button class="btn-block">
                    <i class="fas fa-save"></i> Save Privacy Settings
                </button>
            </div>
            
            <div class="auth-footer">
                <p><a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></p>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const hamburger = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".nav-menu");

        if (hamburger && navMenu) {
            hamburger.addEventListener("click", () => {
                hamburger.classList.toggle("active");
                navMenu.classList.toggle("active");
            });

            document.querySelectorAll(".nav-link").forEach(n => n.addEventListener("click", () => {
                hamburger.classList.remove("active");
                navMenu.classList.remove("active");
            }));
        }

        // Add animation to privacy options when they come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                }
            });
        }, observerOptions);

        // Observe all privacy options
        document.querySelectorAll('.privacy-option').forEach(option => {
            option.style.opacity = '0';
            observer.observe(option);
        });
    </script>
</body>
</html>