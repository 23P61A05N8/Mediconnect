<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - MediConnect</title>
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

        /* About Hero Section */
        .about-hero {
            padding: 5rem 0 3rem;
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .about-hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.2;
        }

        .about-hero p {
            font-size: 1.3rem;
            color: #555;
            max-width: 800px;
            margin: 0 auto 2rem;
            line-height: 1.7;
        }

        /* About Content Section */
        .about-content {
            padding: 3rem 0 5rem;
            position: relative;
            z-index: 2;
        }

        .about-section {
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

        .about-section::before {
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

        .about-section h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #1976d2;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .about-section h2 i {
            font-size: 1.5rem;
        }

        .about-section p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(25, 118, 210, 0.2);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 40px rgba(25, 118, 210, 0.3);
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Mission & Vision Grid */
        .mission-vision-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .mission-card, .vision-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem 2rem;
            border-radius: 15px;
            border: 1px solid rgba(25, 118, 210, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .mission-card::before, .vision-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #1976d2, #0d47a1);
        }

        .mission-card:hover, .vision-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(25, 118, 210, 0.15);
        }

        .mission-card h3, .vision-card h3 {
            color: #1976d2;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .mission-card h3 i {
            color: #4caf50;
        }

        .vision-card h3 i {
            color: #ff9800;
        }

        /* Features List */
        .features-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }

        .features-list li {
            padding: 1.2rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: all 0.3s ease;
        }

        .features-list li:last-child {
            border-bottom: none;
        }

        .features-list li:hover {
            transform: translateX(10px);
            background: rgba(25, 118, 210, 0.05);
            padding-left: 1rem;
            border-radius: 8px;
        }

        .features-list li i {
            color: #1976d2;
            font-size: 1.3rem;
            width: 28px;
            text-align: center;
            background: rgba(25, 118, 210, 0.1);
            padding: 0.8rem;
            border-radius: 10px;
        }

        .features-list li span strong {
            color: #1976d2;
            font-weight: 700;
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

            .about-hero {
                padding: 3rem 0 2rem;
            }

            .about-hero h1 {
                font-size: 2.2rem;
            }

            .about-hero p {
                font-size: 1.1rem;
            }

            .about-content {
                padding: 2rem 0 3rem;
            }

            .about-section {
                padding: 2rem 1.5rem;
                margin-bottom: 2rem;
            }

            .mission-vision-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .features-list li {
                padding: 1rem 0;
            }

            .navbar {
                min-height: 65px;
            }

            .nav-brand h2 {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .about-hero h1 {
                font-size: 1.8rem;
            }

            .about-section h2 {
                font-size: 1.4rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .stat-number {
                font-size: 2.2rem;
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
        .about-section, .stat-card, .mission-card, .vision-card {
            animation: fadeInUp 0.8s ease-out;
            animation-fill-mode: both;
        }

        .about-section:nth-child(1) { animation-delay: 0.1s; }
        .about-section:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(1) { animation-delay: 0.3s; }
        .stat-card:nth-child(2) { animation-delay: 0.4s; }
        .stat-card:nth-child(3) { animation-delay: 0.5s; }
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
                    <li><a href="about.php" class="nav-link active"><i class="fas fa-info-circle" style="margin-right: 6px;"></i>About</a></li>
                    <li><a href="help.php" class="nav-link"><i class="fas fa-question-circle" style="margin-right: 6px;"></i>Help</a></li>
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

    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>About MediConnect</h1>
            <p><strong>MediConnect</strong> is a revolutionary digital healthcare platform designed to bridge the gap between doctors and patients. We provide a secure, convenient, and centralized system for managing health data, prescriptions, and medical reports with cutting-edge technology.</p>
        </div>
    </section>

    <!-- About Content Section -->
    <div class="container">
        <section class="about-content">
            <!-- Stats Section -->
            <div class="about-section">
                <h2><i class="fas fa-chart-bar"></i> Our Impact</h2>
                <div class="stats-section">
                    <div class="stat-card">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Patients Served</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Doctors Registered</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support Available</div>
                    </div>
                </div>
            </div>

            <!-- Mission & Vision Section -->
            <div class="about-section">
                <div class="mission-vision-grid">
                    <div class="vision-card">
                        <h3><i class="fas fa-bullseye"></i> Our Vision</h3>
                        <p>To revolutionize healthcare accessibility by leveraging technology to provide timely medical support, ensuring that every individual can connect with trusted healthcare professionals easily and efficiently.</p>
                    </div>
                    
                    <div class="mission-card">
                        <h3><i class="fas fa-rocket"></i> Our Mission</h3>
                        <p>To make healthcare management seamless and digital — empowering users to take control of their medical journey with trust, transparency, and state-of-the-art technology.</p>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="about-section">
                <h2><i class="fas fa-star"></i> Key Features</h2>
                <ul class="features-list">
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        <span><strong>Secure Access:</strong> Encrypted patient-doctor login and record access with enterprise-grade security</span>
                    </li>
                    <li>
                        <i class="fas fa-database"></i>
                        <span><strong>Centralized Storage:</strong> Unified storage of prescriptions, medical history, and health records</span>
                    </li>
                    <li>
                        <i class="fas fa-user-md"></i>
                        <span><strong>Doctor Directory:</strong> Easy access to qualified doctors based on specialization and availability</span>
                    </li>
                    <li>
                        <i class="fas fa-chart-line"></i>
                        <span><strong>Smart Dashboards:</strong> Intuitive dashboards for both patients and doctors with analytics</span>
                    </li>
                    <li>
                        <i class="fas fa-mobile-alt"></i>
                        <span><strong>Mobile Friendly:</strong> Fully responsive design accessible from any device, anywhere</span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span><strong>24/7 Availability:</strong> Round-the-clock access to your medical records and history</span>
                    </li>
                </ul>
            </div>

            <!-- Why Choose Us Section -->
            <div class="about-section">
                <h2><i class="fas fa-hand-holding-heart"></i> Why Choose MediConnect?</h2>
                <p>We understand the importance of your health data and the need for seamless healthcare management. Our platform is built with privacy, security, and user experience at its core. Whether you're a patient looking to manage your health records or a doctor seeking efficient patient management tools, MediConnect provides the perfect solution.</p>
            </div>

            <!-- Commitment Section -->
            <div class="about-section">
                <h2><i class="fas fa-users"></i> Our Commitment</h2>
                <p>We are committed to continuously improving our platform, adding new features, and ensuring the highest standards of data protection. Your health journey is our priority, and we're here to make it as smooth and worry-free as possible.</p>
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
            document.querySelectorAll('.about-section, .stat-card, .mission-card, .vision-card, .features-list li').forEach(element => {
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
    </script>
</body>
</html>