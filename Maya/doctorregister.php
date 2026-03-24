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

// Process form submission
$registration_success = false;
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $firstName = mysqli_real_escape_string($con, trim($_POST['firstName']));
    $lastName = mysqli_real_escape_string($con, trim($_POST['lastName']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $specialization = mysqli_real_escape_string($con, trim($_POST['specialization']));
    $qualification = mysqli_real_escape_string($con, trim($_POST['qualification']));
    $licenseNumber = mysqli_real_escape_string($con, trim($_POST['licenseNumber']));
    $hospital = mysqli_real_escape_string($con, trim($_POST['hospital']));
    $experience = mysqli_real_escape_string($con, $_POST['experience']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Generate unique doctor ID
    $doctor_id = 'DOC' . date('Ymd') . rand(1000, 9999);
    
    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || 
        empty($specialization) || empty($qualification) || empty($licenseNumber)) {
        $error_message = "Please fill all required fields!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error_message = "Phone number must be exactly 10 digits!";
    } else {
        // Check if email or license number already exists
        $check_query = "SELECT id FROM doctors WHERE email = '$email' OR license_number = '$licenseNumber'";
        $check_result = mysqli_query($con, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Email or License Number already registered!";
        } else {
            // Insert into doctors table
            $query = "INSERT INTO doctors (
                doctor_id, first_name, last_name, email, phone, specialization, 
                qualification, license_number, hospital, experience, password, created_at
            ) VALUES (
                '$doctor_id', '$firstName', '$lastName', '$email', '$phone', '$specialization',
                '$qualification', '$licenseNumber', '$hospital', '$experience', '$password', NOW()
            )";
            
            if (mysqli_query($con, $query)) {
                // Get the newly created doctor ID
                $doctor_db_id = mysqli_insert_id($con);
                
                // Set session data
                $_SESSION['doctor_id'] = $doctor_db_id;
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['email'] = $email;
                $_SESSION['user_type'] = 'doctor';
                $_SESSION['specialization'] = $specialization;
                
                // Redirect to doctor dashboard
                header("Location: doctordashboard.php");
                exit();
            } else {
                $error_message = "Error: " . mysqli_error($con);
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
    <title>Doctor Registration - MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Internal CSS for doctorregister.php only - Enhanced with index.php styles */
        
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            color: #333;
            line-height: 1.6;
            background-color: #f8f9fa;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, #bbdefb, #e3f2fd);
            opacity: 0.1;
            animation: float 20s infinite linear;
        }
        
        .shape-1 {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 10%;
            animation-delay: -5s;
        }
        
        .shape-3 {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: -10s;
        }
        
        .shape-4 {
            width: 120px;
            height: 120px;
            top: 30%;
            right: 20%;
            animation-delay: -15s;
        }
        
        @keyframes float {
            0%, 100% {
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
        }
        
        /* Header Styles */
        header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
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
        
        /* Container */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 15px;
            position: relative;
            z-index: 2;
        }
        
        /* Registration Card */
        .registration-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card-header {
            background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%230d47a1"><path d="M0 70 Q250 30 500 70 T1000 70 V100 H0 Z"/></svg>');
            background-size: cover;
            background-position: center bottom;
            opacity: 0.2;
        }
        
        .card-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .card-header p {
            opacity: 0.9;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }
        
        .card-body {
            padding: 2.5rem;
        }
        
        /* Form Sections */
        .form-section {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .section-title {
            color: #1976d2;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e3f2fd;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
        }
        
        .section-title i {
            font-size: 1.3rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .required::after {
            content: " *";
            color: #d32f2f;
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
        
        .form-control.error {
            border-color: #d32f2f;
        }
        
        .form-control.success {
            border-color: #1976d2;
        }
        
        .error-message {
            color: #d32f2f;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(25, 118, 210, 0.4);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }

        .btn-secondary {
            background: transparent;
            color: #1976d2;
            border: 2px solid #1976d2;
        }

        .btn-secondary:hover {
            background: #e3f2fd;
        }
        
        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
        }

        .alert-success {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }
        
        .text-muted {
            color: #666;
            font-size: 0.875rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* Doctor Badge */
        .doctor-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 1rem;
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
        
        /* Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 3rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3,
        .footer-section h4 {
            margin-bottom: 1rem;
            color: #1976d2;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: #1976d2;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid #555;
            color: #aaa;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

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

            .form-actions {
                flex-direction: column;
            }
            
            .card-header h1 {
                font-size: 2rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .card-header h1 {
                font-size: 1.8rem;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
        }

        /* Additional Components */
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            border-radius: 2px;
            background: #e0e0e0;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 2px;
        }
        
        .strength-weak {
            background: #f44336;
            width: 33%;
        }
        
        .strength-medium {
            background: #ff9800;
            width: 66%;
        }
        
        .strength-strong {
            background: #1976d2;
            width: 100%;
        }
        
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #666;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        
        .requirement.met {
            color: #1976d2;
        }
        
        .requirement.unmet {
            color: #f44336;
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
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
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
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link">Patient Register</a></li>
                    <li><a href="doctorregister.php" class="nav-link btn-primary">Doctor Register</a></li>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="registration-card">
            <div class="card-header">
                <h1><i class="fas fa-user-md"></i> Doctor Registration</h1>
                <p>Join MediConnect as a healthcare professional</p>
                <div class="doctor-badge">
                    <i class="fas fa-stethoscope"></i>
                    Medical Professional Account
                </div>
            </div>
            
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Error!</strong> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form id="registrationForm" method="POST" action="">
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-user"></i> Personal Information</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="firstName" class="required">First Name</label>
                                <input type="text" id="firstName" name="firstName" class="form-control" value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>" required>
                                <div class="error-message" id="firstNameError">Please enter a valid first name</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="lastName" class="required">Last Name</label>
                                <input type="text" id="lastName" name="lastName" class="form-control" value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" required>
                                <div class="error-message" id="lastNameError">Please enter a valid last name</div>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="email" class="required">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                <div class="error-message" id="emailError">Please enter a valid email address</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="required">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" pattern="[0-9]{10}" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                <div class="error-message" id="phoneError">Please enter a valid 10-digit phone number</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Professional Information Section -->
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-briefcase-medical"></i> Professional Information</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="specialization" class="required">Specialization</label>
                                <select id="specialization" name="specialization" class="form-control" required>
                                    <option value="">Select Specialization</option>
                                    <option value="Cardiology" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Cardiology') ? 'selected' : ''; ?>>Cardiology</option>
                                    <option value="Dermatology" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Dermatology') ? 'selected' : ''; ?>>Dermatology</option>
                                    <option value="Pediatrics" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Pediatrics') ? 'selected' : ''; ?>>Pediatrics</option>
                                    <option value="Orthopedics" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Orthopedics') ? 'selected' : ''; ?>>Orthopedics</option>
                                    <option value="Neurology" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Neurology') ? 'selected' : ''; ?>>Neurology</option>
                                    <option value="Gynecology" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Gynecology') ? 'selected' : ''; ?>>Gynecology</option>
                                    <option value="General Physician" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'General Physician') ? 'selected' : ''; ?>>General Physician</option>
                                    <option value="Dentist" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Dentist') ? 'selected' : ''; ?>>Dentist</option>
                                    <option value="Psychiatry" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Psychiatry') ? 'selected' : ''; ?>>Psychiatry</option>
                                    <option value="Oncology" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Oncology') ? 'selected' : ''; ?>>Oncology</option>
                                    <option value="Radiology" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Radiology') ? 'selected' : ''; ?>>Radiology</option>
                                    <option value="Surgery" <?php echo (isset($_POST['specialization']) && $_POST['specialization'] == 'Surgery') ? 'selected' : ''; ?>>Surgery</option>
                                </select>
                                <div class="error-message" id="specializationError">Please select your specialization</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="qualification" class="required">Qualification</label>
                                <input type="text" id="qualification" name="qualification" class="form-control" placeholder="MBBS, MD, MS, etc." value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>" required>
                                <div class="error-message" id="qualificationError">Please enter your qualification</div>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="licenseNumber" class="required">Medical License Number</label>
                                <input type="text" id="licenseNumber" name="licenseNumber" class="form-control" value="<?php echo isset($_POST['licenseNumber']) ? htmlspecialchars($_POST['licenseNumber']) : ''; ?>" required>
                                <div class="error-message" id="licenseNumberError">Please enter your medical license number</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="hospital">Hospital/Clinic</label>
                                <input type="text" id="hospital" name="hospital" class="form-control" placeholder="Current workplace" value="<?php echo isset($_POST['hospital']) ? htmlspecialchars($_POST['hospital']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="experience">Years of Experience</label>
                            <input type="number" id="experience" name="experience" class="form-control" min="0" max="50" value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : '0'; ?>">
                            <small class="text-muted">Number of years in medical practice</small>
                        </div>
                    </div>
                    
                    <!-- Account Security Section -->
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-shield-alt"></i> Account Security</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password" class="required">Create Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <div class="password-strength">
                                    <div class="strength-bar" id="passwordStrength"></div>
                                </div>
                                <div class="password-requirements">
                                    <div class="requirement unmet" id="reqLength">
                                        <i class="fas fa-times"></i>
                                        At least 8 characters
                                    </div>
                                    <div class="requirement unmet" id="reqUppercase">
                                        <i class="fas fa-times"></i>
                                        One uppercase letter
                                    </div>
                                    <div class="requirement unmet" id="reqLowercase">
                                        <i class="fas fa-times"></i>
                                        One lowercase letter
                                    </div>
                                    <div class="requirement unmet" id="reqNumber">
                                        <i class="fas fa-times"></i>
                                        One number
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmPassword" class="required">Confirm Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required>
                                <div class="error-message" id="confirmPasswordError">Passwords do not match</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-block">
                            <i class="fas fa-user-plus"></i> Create Doctor Account
                        </button>
                    </div>
                    
                    <div class="auth-footer" style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0;">
                        <p>Already have an account? <a href="login.php" style="color: #388e3c; text-decoration: none; font-weight: 500;">Sign in here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        const requirements = {
            length: document.getElementById('reqLength'),
            uppercase: document.getElementById('reqUppercase'),
            lowercase: document.getElementById('reqLowercase'),
            number: document.getElementById('reqNumber')
        };

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check length
            if (password.length >= 8) {
                strength++;
                requirements.length.classList.remove('unmet');
                requirements.length.classList.add('met');
                requirements.length.innerHTML = '<i class="fas fa-check"></i> At least 8 characters';
            } else {
                requirements.length.classList.remove('met');
                requirements.length.classList.add('unmet');
                requirements.length.innerHTML = '<i class="fas fa-times"></i> At least 8 characters';
            }
            
            // Check uppercase
            if (/[A-Z]/.test(password)) {
                strength++;
                requirements.uppercase.classList.remove('unmet');
                requirements.uppercase.classList.add('met');
                requirements.uppercase.innerHTML = '<i class="fas fa-check"></i> One uppercase letter';
            } else {
                requirements.uppercase.classList.remove('met');
                requirements.uppercase.classList.add('unmet');
                requirements.uppercase.innerHTML = '<i class="fas fa-times"></i> One uppercase letter';
            }
            
            // Check lowercase
            if (/[a-z]/.test(password)) {
                strength++;
                requirements.lowercase.classList.remove('unmet');
                requirements.lowercase.classList.add('met');
                requirements.lowercase.innerHTML = '<i class="fas fa-check"></i> One lowercase letter';
            } else {
                requirements.lowercase.classList.remove('met');
                requirements.lowercase.classList.add('unmet');
                requirements.lowercase.innerHTML = '<i class="fas fa-times"></i> One lowercase letter';
            }
            
            // Check number
            if (/\d/.test(password)) {
                strength++;
                requirements.number.classList.remove('unmet');
                requirements.number.classList.add('met');
                requirements.number.innerHTML = '<i class="fas fa-check"></i> One number';
            } else {
                requirements.number.classList.remove('met');
                requirements.number.classList.add('unmet');
                requirements.number.innerHTML = '<i class="fas fa-times"></i> One number';
            }
            
            // Update strength bar
            passwordStrength.className = 'strength-bar';
            if (strength === 0) {
                passwordStrength.style.width = '0%';
            } else if (strength <= 2) {
                passwordStrength.classList.add('strength-weak');
            } else if (strength === 3) {
                passwordStrength.classList.add('strength-medium');
            } else {
                passwordStrength.classList.add('strength-strong');
            }
        });

        // Confirm password validation
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const confirmPasswordError = document.getElementById('confirmPasswordError');

        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                confirmPasswordError.style.display = 'block';
                this.classList.add('error');
                this.classList.remove('success');
            } else {
                confirmPasswordError.style.display = 'none';
                this.classList.remove('error');
                this.classList.add('success');
            }
        });

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            let isValid = true;

            // Basic validation
            const requiredFields = document.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            // Password validation
            const password = passwordInput.value;
            if (password.length < 8) {
                isValid = false;
                alert('Password must be at least 8 characters long');
            }
            
            if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
                isValid = false;
                alert('Password must contain at least one uppercase letter, one lowercase letter, and one number');
            }
            
            if (password !== confirmPasswordInput.value) {
                isValid = false;
                alert('Passwords do not match');
            }

            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = document.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

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

        // Real-time validation for fields
        const fieldsToValidate = ['firstName', 'lastName', 'email', 'phone', 'specialization', 'qualification', 'licenseNumber'];
        
        fieldsToValidate.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            const errorElement = document.getElementById(fieldName + 'Error');
            
            if (field && errorElement) {
                field.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        errorElement.style.display = 'block';
                        this.classList.add('error');
                        this.classList.remove('success');
                    } else {
                        errorElement.style.display = 'none';
                        this.classList.remove('error');
                        this.classList.add('success');
                    }
                });
            }
        });

        // Email validation
        const emailField = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        
        emailField.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email === '') {
                emailError.textContent = 'Email is required';
                emailError.style.display = 'block';
                this.classList.add('error');
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailError.textContent = 'Please enter a valid email address';
                emailError.style.display = 'block';
                this.classList.add('error');
            } else {
                emailError.style.display = 'none';
                this.classList.remove('error');
                this.classList.add('success');
            }
        });

        // Phone validation
        const phoneField = document.getElementById('phone');
        const phoneError = document.getElementById('phoneError');
        
        phoneField.addEventListener('blur', function() {
            const phone = this.value.trim();
            if (phone === '') {
                phoneError.textContent = 'Phone number is required';
                phoneError.style.display = 'block';
                this.classList.add('error');
            } else if (!/^\d{10}$/.test(phone)) {
                phoneError.textContent = 'Phone must be 10 digits';
                phoneError.style.display = 'block';
                this.classList.add('error');
            } else {
                phoneError.style.display = 'none';
                this.classList.remove('error');
                this.classList.add('success');
            }
        });
    </script>
</body>
</html>