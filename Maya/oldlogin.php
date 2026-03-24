<?php
session_start();
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "mediconnect";

// Create connection
$con = mysqli_connect($host, $username, $password, $database);

// Check connection
if(mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a forgot password request
    if (isset($_POST['forgot_password'])) {
        $email = mysqli_real_escape_string($con, trim($_POST['email']));
        $userType = mysqli_real_escape_string($con, trim($_POST['userType']));
        
        // Check if email exists
        if ($userType === 'patient') {
            $query = "SELECT * FROM patients WHERE email = '$email'";
        } else {
            $query = "SELECT * FROM doctors WHERE email = '$email'";
        }
        
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            // Generate OTP (6-digit code)
            $otp = rand(100000, 999999);
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_user_type'] = $userType;
            $_SESSION['otp_expiry'] = time() + 600; // OTP valid for 10 minutes
            
            // In a real application, you would send this OTP via email
            // For demo purposes, we'll just display it
            $_SESSION['otp_display'] = $otp;
            
            header("Location: login.php?action=verify_otp");
            exit();
        } else {
            $login_error = "Email not found!";
        }
    }
    // Check if it's an OTP verification request
    else if (isset($_POST['verify_otp'])) {
        $entered_otp = mysqli_real_escape_string($con, trim($_POST['otp']));
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($entered_otp == $_SESSION['reset_otp'] && time() < $_SESSION['otp_expiry']) {
            if ($new_password === $confirm_password) {
                // Update password in database
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $email = $_SESSION['reset_email'];
                $userType = $_SESSION['reset_user_type'];
                
                if ($userType === 'patient') {
                    $query = "UPDATE patients SET password = '$hashed_password' WHERE email = '$email'";
                } else {
                    $query = "UPDATE doctors SET password = '$hashed_password' WHERE email = '$email'";
                }
                
                if (mysqli_query($con, $query)) {
                    // Clear reset session variables
                    unset($_SESSION['reset_otp']);
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_user_type']);
                    unset($_SESSION['otp_expiry']);
                    unset($_SESSION['otp_display']);
                    
                    $login_error = "Password reset successfully! You can now login with your new password.";
                    header("Location: login.php?reset_success=1");
                    exit();
                } else {
                    $login_error = "Error updating password. Please try again.";
                }
            } else {
                $login_error = "Passwords do not match!";
            }
        } else {
            $login_error = "Invalid or expired OTP!";
        }
    }
    // Regular login
    else {
        $userType = mysqli_real_escape_string($con, trim($_POST['userType']));
        $loginId = mysqli_real_escape_string($con, trim($_POST['loginId']));
        $password = $_POST['password'];
        
        if ($userType === 'patient') {
            // Patient login
            $query = "SELECT * FROM patients WHERE (email = '$loginId' OR aadhaar = '$loginId')";
            $result = mysqli_query($con, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_type'] = 'patient';
                    
                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $login_error = "Invalid password!";
                }
            } else {
                $login_error = "Patient ID not found!";
            }
        } elseif ($userType === 'doctor') {
            // Doctor login
            $query = "SELECT * FROM doctors WHERE (email = '$loginId' OR doctor_id = '$loginId')";
            $result = mysqli_query($con, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['doctor_id'] = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_type'] = 'doctor';
                    $_SESSION['specialization'] = $user['specialization'];
                    
                    // Redirect to doctor dashboard
                    header("Location: doctordashboard.php");
                    exit();
                } else {
                    $login_error = "Invalid password!";
                }
            } else {
                $login_error = "Doctor ID not found!";
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
    <title>Login - MediConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Internal CSS for login.php only */
        
        /* Enhanced Auth Container */
        .auth-container {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
            padding: 2rem 0;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
        }
        
        .auth-title {
            text-align: center;
            color: #1976d2;
            margin-bottom: 0.5rem;
            font-weight: 700;
            font-size: 1.8rem;
        }
        
        .auth-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        /* Enhanced User Type Selector */
        .user-type-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            background: #f5f5f5;
            padding: 0.5rem;
            border-radius: 10px;
        }
        
        .user-type-btn {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid transparent;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            text-align: center;
        }
        
        .user-type-btn.active {
            border-color: #1976d2;
            background: #e3f2fd;
            color: #1976d2;
            box-shadow: 0 4px 8px rgba(25, 118, 210, 0.2);
        }
        
        /* Enhanced Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1976d2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 42px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: #666;
        }
        
        /* Enhanced Buttons */
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
            text-align: center;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(25, 118, 210, 0.4);
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

        /* Signup Button Styles */
        .btn-patient {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            border: none;
        }

        .btn-doctor {
            background: linear-gradient(135deg, #388e3c, #1b5e20);
            color: white;
            border: none;
        }

        .btn-patient:hover {
            background: linear-gradient(135deg, #1565c0, #0d47a1);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(25, 118, 210, 0.4);
        }

        .btn-doctor:hover {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(56, 142, 60, 0.4);
        }
        
        /* Forgot Password Link */
        .forgot-password {
            text-align: center;
            margin: 1rem 0;
        }
        
        .forgot-password a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .forgot-password a:hover {
            color: #0d47a1;
            text-decoration: underline;
        }
        
        /* Auth Footer */
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            border-top: 1px solid #e0e0e0;
            padding-top: 1.5rem;
        }
        
        .auth-footer a {
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        /* Alert Styles */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        
        /* OTP Input Styles */
        .otp-container {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 1rem 0;
        }
        
        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .otp-input:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
            outline: none;
        }
        
        /* Demo OTP Display */
        .demo-otp {
            background: #e3f2fd;
            padding: 0.75rem;
            border-radius: 8px;
            text-align: center;
            margin: 1rem 0;
            font-weight: 600;
            color: #1976d2;
        }
        
        /* Animations */
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-card {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            .auth-title {
                font-size: 1.5rem;
            }
            
            .user-type-selector {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: 1.5rem 1rem;
            }
            
            .otp-input {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }

        /* Header Styles */
        header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            min-height: 60px;
        }

        .nav-brand h2 {
            color: #1976d2;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            margin: 0 0.75rem;
            transition: color 0.3s;
            font-size: 0.9rem;
            padding: 0.5rem 0;
        }

        .nav-link:hover {
            color: #1976d2;
        }

        .nav-link.active {
            color: #1976d2;
        }

        .btn-primary {
            background-color: #1976d2;
            color: white;
            padding: 0.4rem 1.2rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
            font-size: 0.9rem;
        }

        .btn-primary:hover {
            background-color: #1565c0;
        }

        .hamburger {
            display: none;
            cursor: pointer;
            padding: 0.25rem;
        }

        .bar {
            display: block;
            width: 22px;
            height: 2px;
            margin: 4px auto;
            transition: all 0.3s ease;
            background-color: #333;
        }

        /* Mobile menu adjustments */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-menu {
                position: fixed;
                left: -100%;
                top: 60px;
                flex-direction: column;
                background-color: white;
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
                padding: 1rem 0;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-link {
                margin: 0.5rem 0;
                padding: 0.75rem;
                display: block;
            }
            
            .navbar {
                padding: 0.4rem 0;
                min-height: 55px;
            }
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
                    <li><a href="index.php" class="nav-link active">Home</a></li>
                    <li><a href="about.php" class="nav-link">About</a></li>
                    <li><a href="help.php" class="nav-link">Help</a></li>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link btn-primary">Register</a></li>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Particle Background -->
    <div class="particles" id="particles"></div>

    <div class="auth-container">
        <div class="auth-card">
            <?php if (isset($_GET['reset_success'])): ?>
                <div class="alert alert-success">
                    Password reset successfully! You can now login with your new password.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['action']) && $_GET['action'] == 'verify_otp'): ?>
                <!-- OTP Verification Form -->
                <h1 class="auth-title">Verify OTP</h1>
                <p class="auth-subtitle">Enter the OTP sent to your email</p>
                
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-error">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['otp_display'])): ?>
                    <div class="demo-otp">
                        Demo OTP: <?php echo $_SESSION['otp_display']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="verify_otp" value="1">
                    
                    <div class="form-group">
                        <label for="otp">Enter OTP</label>
                        <input type="text" id="otp" name="otp" class="form-control" placeholder="Enter 6-digit OTP" required maxlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Reset Password
                    </button>
                </form>
                
                <div class="auth-footer">
                    <a href="login.php">Back to Login</a>
                </div>
                
            <?php elseif (isset($_GET['action']) && $_GET['action'] == 'forgot_password'): ?>
                <!-- Forgot Password Form -->
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">Enter your email to receive a reset OTP</p>
                
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-error">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="user-type-selector">
                    <button type="button" class="user-type-btn active" data-type="patient">
                        👤 Patient
                    </button>
                    <button type="button" class="user-type-btn" data-type="doctor">
                        🩺 Doctor
                    </button>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="userType" id="userType" value="patient">
                    <input type="hidden" name="forgot_password" value="1">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your registered email" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Send OTP
                    </button>
                </form>
                
                <div class="auth-footer">
                    <a href="login.php">Back to Login</a>
                </div>
                
            <?php else: ?>
                <!-- Regular Login Form -->
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to access your MediConnect account</p>
                
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-error">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="user-type-selector">
                    <button type="button" class="user-type-btn active" data-type="patient">
                        👤 Patient
                    </button>
                    <button type="button" class="user-type-btn" data-type="doctor">
                        🩺 Doctor
                    </button>
                </div>
                
                <form id="loginForm" method="POST" action="">
                    <input type="hidden" name="userType" id="userType" value="patient">
                    
                    <div class="form-group">
                        <label for="loginId" id="loginLabel">Email or Aadhaar Number</label>
                        <input type="text" id="loginId" name="loginId" class="form-control" placeholder="Enter your email or Aadhaar" required value="<?php echo isset($_POST['loginId']) ? htmlspecialchars($_POST['loginId']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" id="passwordToggle">👁️</button>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Sign In
                    </button>
                </form>
                
                <div class="forgot-password">
                    <a href="login.php?action=forgot_password">Forgot Password?</a>
                </div>
                
                <div class="auth-footer">
                    Don't have an account? 
                    <a href="register.php" id="signup-link" class="btn btn-patient" style="display: inline-block; margin-top: 0.5rem; padding: 0.6rem 1.5rem;">
                        Sign up as Patient
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // User type selection
        const userTypeBtns = document.querySelectorAll('.user-type-btn');
        const userTypeInput = document.getElementById('userType');
        const loginLabel = document.getElementById('loginLabel');
        const loginInput = document.getElementById('loginId');
        const signupLink = document.getElementById('signup-link');

        userTypeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons
                userTypeBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                btn.classList.add('active');
                
                const userType = btn.getAttribute('data-type');
                userTypeInput.value = userType;
                
                // Update labels and placeholders based on user type
                if (userType === 'patient') {
                    loginLabel.textContent = 'Email or Aadhaar Number';
                    loginInput.placeholder = 'Enter your email or Aadhaar';
                    // Update signup button for patient
                    signupLink.href = 'register.php';
                    signupLink.textContent = 'Sign up as Patient';
                    signupLink.classList.remove('btn-doctor');
                    signupLink.classList.add('btn-patient');
                } else {
                    loginLabel.textContent = 'Email or Doctor ID';
                    loginInput.placeholder = 'Enter your email or Doctor ID';
                    // Update signup button for doctor
                    signupLink.href = 'doctorregister.php';
                    signupLink.textContent = 'Sign up as Doctor';
                    signupLink.classList.remove('btn-patient');
                    signupLink.classList.add('btn-doctor');
                }
            });
        });

        // Password toggle
        const passwordToggle = document.getElementById('passwordToggle');
        if (passwordToggle) {
            const passwordInput = document.getElementById('password');
            
            passwordToggle.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                passwordToggle.textContent = type === 'password' ? '👁️' : '🔒';
            });
        }

        // Initialize signup button based on default user type (patient)
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial signup button for patient
            signupLink.href = 'register.php';
            signupLink.textContent = 'Sign up as Patient';
            signupLink.classList.add('btn-patient');
        });

        // Simple form validation
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                let isValid = true;

                // Basic validation - just check if fields are filled
                if (loginInput.value.trim() === '') {
                    isValid = false;
                    alert('Please enter your login ID');
                }

                const passwordInput = document.getElementById('password');
                if (passwordInput.value.trim() === '') {
                    isValid = false;
                    alert('Please enter your password');
                }

                if (!isValid) {
                    e.preventDefault();
                }
                // If valid, let the form submit naturally to PHP
            });
        }

        // Mobile menu toggle
        const hamburger = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".nav-menu");

        if (hamburger && navMenu) {
            hamburger.addEventListener("click", () => {
                hamburger.classList.toggle("active");
                navMenu.classList.toggle("active");
            });

            // Close mobile menu when clicking on a link
            document.querySelectorAll(".nav-link").forEach(n => n.addEventListener("click", () => {
                hamburger.classList.remove("active");
                navMenu.classList.remove("active");
            }));
        }

        // Particle background effect
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;
            
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random properties
                const size = Math.random() * 5 + 2;
                const posX = Math.random() * 100;
                const duration = Math.random() * 20 + 10;
                const delay = Math.random() * 5;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;
                particle.style.opacity = Math.random() * 0.5 + 0.1;
                
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize particles when page loads
        window.addEventListener('load', createParticles);
    </script>
</body>
</html>