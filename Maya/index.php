<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediConnect - Your Complete Health Record Solution</title>
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

        /* Hero Section */
        .hero {
            padding: 5rem 0;
            min-height: 85vh;
            display: flex;
            align-items: center;
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600" opacity="0.03"><path d="M0 300L50 320C100 340 200 380 300 390C400 400 500 380 600 370C700 360 800 360 900 350C1000 340 1100 320 1150 310L1200 300V600H1150C1100 600 1000 600 900 600C800 600 700 600 600 600C500 600 400 600 300 600C200 600 100 600 50 600H0V300Z" fill="%231976d2"/></svg>');
            background-size: cover;
        }

        .hero-content {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 3;
            animation: fadeInUp 1s ease-out;
        }

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

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            color: #555;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
            text-align: center;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(25, 118, 210, 0.35);
        }

        .btn-secondary {
            background: transparent;
            color: #1976d2;
            border: 2px solid #1976d2;
        }

        .btn-secondary:hover {
            background: #e3f2fd;
            transform: translateY(-2px);
        }

        .hero-image {
            margin-top: 3rem;
            animation: floatAnimation 6s ease-in-out infinite;
        }

        @keyframes floatAnimation {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .placeholder-image svg {
            width: 100%;
            max-width: 600px;
            height: auto;
            filter: drop-shadow(0 20px 40px rgba(25, 118, 210, 0.2));
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            background: #fff;
            position: relative;
            z-index: 2;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 4rem;
            color: #1e293b;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            margin-bottom: 1.5rem;
        }

        .feature-card svg {
            width: 70px;
            height: 70px;
            filter: drop-shadow(0 4px 8px rgba(25, 118, 210, 0.2));
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .feature-card p {
            color: #64748b;
            font-size: 1.1rem;
            line-height: 1.7;
        }

        /* CTA Section */
        .cta {
            padding: 6rem 0;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600" opacity="0.1"><path d="M0 300L50 320C100 340 200 380 300 390C400 400 500 380 600 370C700 360 800 360 900 350C1000 340 1100 320 1150 310L1200 300V600H1150C1100 600 1000 600 900 600C800 600 700 600 600 600C500 600 400 600 300 600C200 600 100 600 50 600H0V300Z" fill="white"/></svg>');
            background-size: cover;
        }

        .cta-content {
            position: relative;
            z-index: 2;
        }

        .cta h2 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .cta p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta .btn-primary {
            background: white;
            color: #1976d2;
            font-weight: 700;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .cta .btn-primary:hover {
            background: #f8fafc;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
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

            .hero {
                padding: 3rem 0;
                min-height: auto;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }

            .features {
                padding: 3rem 0;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 2.5rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .cta {
                padding: 3rem 0;
            }

            .cta h2 {
                font-size: 2rem;
            }

            .cta p {
                font-size: 1.1rem;
            }

            .navbar {
                min-height: 65px;
            }

            .nav-brand h2 {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 1.8rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .feature-card {
                padding: 2rem 1.5rem;
            }

            .cta h2 {
                font-size: 1.8rem;
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

        /* Loading animation for cards */
        .feature-card {
            animation: fadeInUp 0.8s ease-out;
            animation-fill-mode: both;
        }

        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }

        /* Particle background creation function */
        .particle {
            animation: particle-float 20s infinite linear;
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

    <!-- Particle Background -->
    <div class="particles" id="particles"></div>

    <!-- Header Section -->
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="nav-brand">
                    <h2><i class="fas fa-heartbeat" style="margin-right: 8px; color: #1976d2;"></i>MediConnect</h2>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link active"><i class="fas fa-home" style="margin-right: 6px;"></i>Home</a></li>
                    <li><a href="about.php" class="nav-link"><i class="fas fa-info-circle" style="margin-right: 6px;"></i>About</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Your Complete Health Record Solution</h1>
                <p>MediConnect is a digital patient health record management system that helps individuals securely store prescriptions, medical history, treatment progress, reports, and more.</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-primary">Get Started</a>
                    <a href="about.php" class="btn btn-secondary">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="placeholder-image">
                    <svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                        <rect width="400" height="300" fill="#e3f2fd"/>
                        <circle cx="200" cy="150" r="80" fill="#bbdefb"/>
                        <rect x="150" y="120" width="100" height="60" fill="#90caf9"/>
                        <circle cx="170" cy="140" r="10" fill="#42a5f5"/>
                        <circle cx="230" cy="140" r="10" fill="#42a5f5"/>
                        <path d="M160 180 Q200 200 240 180" stroke="#42a5f5" stroke-width="3" fill="none"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Key Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none">
                            <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M19.4 15C19.2669 15.3031 19.1338 15.6062 19.0006 15.9094C18.5441 16.9078 18.0875 17.9062 17.631 18.9047C17.1745 19.9031 16.7179 20.9016 16.2614 21.9C16.0922 22.275 15.8109 22.5875 15.458 22.795C15.1051 23.0025 14.6976 23.095 14.29 23.06L12 23L9.71 23.06C9.30237 23.095 8.89491 23.0025 8.542 22.795C8.18909 22.5875 7.90784 22.275 7.73863 21.9C7.28109 20.9016 6.82454 19.9031 6.368 18.9047C5.91146 17.9062 5.45491 16.9078 4.99837 15.9094C4.86525 15.6062 4.73212 15.3031 4.599 15C4.73212 14.6969 4.86525 14.3938 4.99837 14.0906C5.45491 13.0922 5.91146 12.0938 6.368 11.0953C6.82454 10.0969 7.28109 9.09844 7.73863 8.1C7.90784 7.725 8.18909 7.4125 8.542 7.205C8.89491 6.9975 9.30237 6.905 9.71 6.94L12 7L14.29 6.94C14.6976 6.905 15.1051 6.9975 15.458 7.205C15.8109 7.4125 16.0922 7.725 16.2614 8.1C16.7179 9.09844 17.1745 10.0969 17.631 11.0953C18.0875 12.0938 18.5441 13.0922 19.0006 14.0906C19.1338 14.3938 19.2669 14.6969 19.4 15Z" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3>Secure Health Records</h3>
                    <p>All your medical data is encrypted and protected with enterprise-grade security standards.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 6V12L16 14" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3>24/7 Access</h3>
                    <p>Access your complete medical history anytime, anywhere from any device.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none">
                            <path d="M21 16V8.00002C20.9996 7.6493 20.9071 7.30483 20.7315 7.00119C20.556 6.69754 20.3037 6.44539 20 6.27002L13 2.27002C12.696 2.09449 12.3511 2.00208 12 2.00208C11.6489 2.00208 11.304 2.09449 11 2.27002L4 6.27002C3.69626 6.44539 3.44398 6.69754 3.26846 7.00119C3.09294 7.30483 3.00036 7.6493 3 8.00002V16C3.00036 16.3508 3.09294 16.6952 3.26846 16.9989C3.44398 17.3025 3.69626 17.5547 4 17.73L11 21.73C11.304 21.9056 11.6489 21.998 12 21.998C12.3511 21.998 12.696 21.9056 13 21.73L20 17.73C20.3037 17.5547 20.556 17.3025 20.7315 16.9989C20.9071 16.6952 20.9996 16.3508 21 16Z" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.27002 6.96002L12 12L20.73 6.96002" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 22.08V12" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3>Comprehensive Tracking</h3>
                    <p>Track treatments, medications, appointments, and health metrics in one place.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to take control of your health records?</h2>
                <p>Join thousands of users who trust MediConnect with their medical information.</p>
                <a href="register.php" class="btn btn-primary">Create Your Account</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>MediConnect</h3>
                    <p>Your complete health record solution for secure and accessible medical information management.</p>
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

        // Add scroll animation to feature cards
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            // Observe feature cards
            document.querySelectorAll('.feature-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
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