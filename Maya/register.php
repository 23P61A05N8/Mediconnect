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
    $middleName = mysqli_real_escape_string($con, trim($_POST['middleName']));
    $lastName = mysqli_real_escape_string($con, trim($_POST['lastName']));
    $aadhaar = mysqli_real_escape_string($con, trim($_POST['aadhaar']));
    $dob = mysqli_real_escape_string($con, $_POST['dob']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $maritalStatus = mysqli_real_escape_string($con, $_POST['maritalStatus']);
    $nationality = mysqli_real_escape_string($con, $_POST['nationality']);
    $address = mysqli_real_escape_string($con, trim($_POST['address']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $weight = !empty($_POST['weight']) ? mysqli_real_escape_string($con, $_POST['weight']) : NULL;
    $height = !empty($_POST['height']) ? mysqli_real_escape_string($con, $_POST['height']) : NULL;
    $bloodGroup = mysqli_real_escape_string($con, $_POST['bloodGroup']);
    $allergies = mysqli_real_escape_string($con, trim($_POST['allergies']));
    $currentMeds = mysqli_real_escape_string($con, trim($_POST['currentMeds']));
    $hasInsurance = mysqli_real_escape_string($con, $_POST['hasInsurance']);
    $insuranceProvider = mysqli_real_escape_string($con, trim($_POST['insuranceProvider']));
    $policyNumber = mysqli_real_escape_string($con, trim($_POST['policyNumber']));
    $groupNumber = mysqli_real_escape_string($con, trim($_POST['groupNumber']));
    $insurancePhone = mysqli_real_escape_string($con, trim($_POST['insurancePhone']));
    $emergencyName1 = mysqli_real_escape_string($con, trim($_POST['emergencyName1']));
    $emergencyRelation1 = mysqli_real_escape_string($con, $_POST['emergencyRelation1']);
    $emergencyPhone1 = mysqli_real_escape_string($con, trim($_POST['emergencyPhone1']));
    $emergencyEmail1 = mysqli_real_escape_string($con, trim($_POST['emergencyEmail1']));
    $emergencyName2 = mysqli_real_escape_string($con, trim($_POST['emergencyName2']));
    $emergencyRelation2 = mysqli_real_escape_string($con, $_POST['emergencyRelation2']);
    $emergencyPhone2 = mysqli_real_escape_string($con, trim($_POST['emergencyPhone2']));
    $emergencyEmail2 = mysqli_real_escape_string($con, trim($_POST['emergencyEmail2']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($aadhaar) || empty($dob) || 
        empty($gender) || empty($address) || empty($phone) || empty($email) || 
        empty($emergencyName1) || empty($emergencyRelation1) || empty($emergencyPhone1)) {
        $error_message = "Please fill all required fields!";
    } elseif (!preg_match('/^\d{12}$/', $aadhaar)) {
        $error_message = "Aadhaar must be exactly 12 digits!";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error_message = "Phone number must be exactly 10 digits!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        // Check if email or Aadhaar already exists
        $check_query = "SELECT id FROM patients WHERE email = '$email' OR aadhaar = '$aadhaar'";
        $check_result = mysqli_query($con, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Email or Aadhaar number already registered!";
        } else {
            // Insert into patients table
            $query = "INSERT INTO patients (
                first_name, middle_name, last_name, aadhaar, dob, gender, marital_status, 
                nationality, address, phone, email, weight, height, blood_group, allergies, 
                current_medications, has_insurance, insurance_provider, policy_number, 
                group_number, insurance_phone, emergency_name1, emergency_relation1, 
                emergency_phone1, emergency_email1, emergency_name2, emergency_relation2, 
                emergency_phone2, emergency_email2, password
            ) VALUES (
                '$firstName', '$middleName', '$lastName', '$aadhaar', '$dob', '$gender', 
                '$maritalStatus', '$nationality', '$address', '$phone', '$email', '$weight', 
                '$height', '$bloodGroup', '$allergies', '$currentMeds', '$hasInsurance', 
                '$insuranceProvider', '$policyNumber', '$groupNumber', '$insurancePhone', 
                '$emergencyName1', '$emergencyRelation1', '$emergencyPhone1', '$emergencyEmail1', 
                '$emergencyName2', '$emergencyRelation2', '$emergencyPhone2', '$emergencyEmail2', 
                '$password'
            )";
            
            if (mysqli_query($con, $query)) {
                // Get the newly created user ID
                $user_id = mysqli_insert_id($con);
                
                // Set session data
                $_SESSION['user_id'] = $user_id;
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['email'] = $email;
                
                // Redirect to dashboard
                header("Location: dashboard.php");
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
    <title>Register - MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Internal CSS for register.php only - Enhanced with index.php styles */
        
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
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .nav-brand h2 {
            color: #1976d2;
            font-weight: 700;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            align-items: center;
        }
        
        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            margin: 0 1rem;
            transition: color 0.3s;
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
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #1565c0;
        }
        
        .hamburger {
            display: none;
            cursor: pointer;
        }
        
        .bar {
            display: block;
            width: 25px;
            height: 3px;
            margin: 5px auto;
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
            border-color: #388e3c;
        }
        
        .error-message {
            color: #d32f2f;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        .radio-group {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .radio-option input[type="radio"] {
            accent-color: #1976d2;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
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
        
        /* Emergency Contact Cards */
        .emergency-contact-card {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid #e0e0e0;
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid #1976d2;
        }
        
        .emergency-contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .contact-title {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #1976d2;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            background-color: #e8f5e8;
            color: #388e3c;
            border: 1px solid #c8e6c9;
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

        .required::after {
            content: " *";
            color: #d32f2f;
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
            
            .radio-group {
                flex-direction: column;
                gap: 0.5rem;
            }

            .hamburger {
                display: block;
            }

            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background-color: white;
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
                padding: 2rem 0;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-link {
                margin: 1rem 0;
                display: block;
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

    <!-- Navigation -->
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
   

    <div class="container">
        <div class="registration-card">
            <div class="card-header">
                <h1>Create Your MediConnect Account</h1>
                <p>Secure your health records in one place</p>
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
                                <label for="middleName">Middle Name</label>
                                <input type="text" id="middleName" name="middleName" class="form-control" value="<?php echo isset($_POST['middleName']) ? htmlspecialchars($_POST['middleName']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="lastName" class="required">Last Name</label>
                                <input type="text" id="lastName" name="lastName" class="form-control" value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" required>
                                <div class="error-message" id="lastNameError">Please enter a valid last name</div>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="aadhaar" class="required">Aadhaar Number</label>
                                <input type="text" id="aadhaar" name="aadhaar" class="form-control" pattern="\d{12}" placeholder="Enter 12 digits" value="<?php echo isset($_POST['aadhaar']) ? htmlspecialchars($_POST['aadhaar']) : ''; ?>" required>
                                <div class="error-message" id="aadhaarError">Please enter a valid 12-digit Aadhaar number</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="dob" class="required">Date of Birth</label>
                                <input type="date" id="dob" name="dob" class="form-control" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" required>
                                <div class="error-message" id="dobError">Please select a valid date of birth</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="required">Gender</label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="gender" value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'checked' : ''; ?> required> Male
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="gender" value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'checked' : ''; ?>> Female
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="gender" value="other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'checked' : ''; ?>> Other
                                    </label>
                                </div>
                                <div class="error-message" id="genderError">Please select your gender</div>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="maritalStatus">Marital Status</label>
                                <select id="maritalStatus" name="maritalStatus" class="form-control">
                                    <option value="">Select</option>
                                    <option value="single" <?php echo (isset($_POST['maritalStatus']) && $_POST['maritalStatus'] == 'single') ? 'selected' : ''; ?>>Single</option>
                                    <option value="married" <?php echo (isset($_POST['maritalStatus']) && $_POST['maritalStatus'] == 'married') ? 'selected' : ''; ?>>Married</option>
                                    <option value="divorced" <?php echo (isset($_POST['maritalStatus']) && $_POST['maritalStatus'] == 'divorced') ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="widowed" <?php echo (isset($_POST['maritalStatus']) && $_POST['maritalStatus'] == 'widowed') ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="nationality">Nationality</label>
                                <input type="text" id="nationality" name="nationality" class="form-control" value="Indian" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information Section -->
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-address-book"></i> Contact Information</h2>
                        
                        <div class="form-group">
                            <label for="address" class="required">Complete Address</label>
                            <textarea id="address" name="address" class="form-control" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            <div class="error-message" id="addressError">Please enter your complete address</div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="phone" class="required">Contact Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" pattern="[0-9]{10}" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                <div class="error-message" id="phoneError">Please enter a valid 10-digit phone number</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="required">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                <div class="error-message" id="emailError">Please enter a valid email address</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Health Information Section -->
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-heartbeat"></i> Health Information</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="weight">Weight (kg)</label>
                                <input type="number" id="weight" name="weight" class="form-control" step="0.1" min="0" value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="height">Height (cm)</label>
                                <input type="number" id="height" name="height" class="form-control" min="0" value="<?php echo isset($_POST['height']) ? htmlspecialchars($_POST['height']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="bloodGroup">Blood Group</label>
                                <select id="bloodGroup" name="bloodGroup" class="form-control">
                                    <option value="">Select</option>
                                    <option value="A+" <?php echo (isset($_POST['bloodGroup']) && $_POST['bloodGroup'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                    <option value="A-" <?php echo (isset($_POST['bloodGroup']) && $_POST['bloodGroup'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                    <option value="B+" <?php echo (isset($_POST['bloodGroup']) && $_POST['bloodGroup'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                    <option value="B-" <?php echo (isset($_POST['bloodGroup']) && $_POST['bloodGroup'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                    <option value="AB+" <?php echo (isset($_POST['bloodGroup']) && $_POST['bloodGroup'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                    <option value="AB-" <?php echo (isset($_POST['bloodGroup']) && $_POST['bloodGroup'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                    <option value="O+" <?php echo (isset($_POST['bloodGroup']) && $_POST['bloodGroup'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                    <option value="O-" <?php echo (isset($_POST['bloodGroup']) && $_POST['bloodGroup'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="allergies">Known Allergies</label>
                            <input type="text" id="allergies" name="allergies" class="form-control" placeholder="Separate with commas" value="<?php echo isset($_POST['allergies']) ? htmlspecialchars($_POST['allergies']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="currentMeds">Current Medications</label>
                            <input type="text" id="currentMeds" name="currentMeds" class="form-control" placeholder="Separate with commas" value="<?php echo isset($_POST['currentMeds']) ? htmlspecialchars($_POST['currentMeds']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="required">Do you have health insurance?</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="hasInsurance" value="yes" <?php echo (isset($_POST['hasInsurance']) && $_POST['hasInsurance'] == 'yes') ? 'checked' : ''; ?>> Yes
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="hasInsurance" value="no" <?php echo (!isset($_POST['hasInsurance']) || $_POST['hasInsurance'] == 'no') ? 'checked' : ''; ?>> No
                                </label>
                            </div>
                        </div>
                        
                        <div class="insurance-details" id="insuranceDetails" style="display: none;">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="insuranceProvider">Insurance Provider</label>
                                    <input type="text" id="insuranceProvider" name="insuranceProvider" class="form-control" value="<?php echo isset($_POST['insuranceProvider']) ? htmlspecialchars($_POST['insuranceProvider']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="policyNumber">Policy Number</label>
                                    <input type="text" id="policyNumber" name="policyNumber" class="form-control" value="<?php echo isset($_POST['policyNumber']) ? htmlspecialchars($_POST['policyNumber']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="groupNumber">Group Number</label>
                                    <input type="text" id="groupNumber" name="groupNumber" class="form-control" value="<?php echo isset($_POST['groupNumber']) ? htmlspecialchars($_POST['groupNumber']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="insurancePhone">Insurance Phone</label>
                                    <input type="tel" id="insurancePhone" name="insurancePhone" class="form-control" pattern="[0-9]{10}" value="<?php echo isset($_POST['insurancePhone']) ? htmlspecialchars($_POST['insurancePhone']) : ''; ?>">
                                    <div class="error-message" id="insurancePhoneError">Please enter a valid 10-digit phone number</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Emergency Contacts Section -->
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-phone-alt"></i> Emergency Contacts</h2>
                        
                        <div class="emergency-contact-card">
                            <h3 class="contact-title"><i class="fas fa-user-md"></i> Primary Emergency Contact</h3>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="emergencyName1" class="required">Full Name</label>
                                    <input type="text" id="emergencyName1" name="emergencyName1" class="form-control" value="<?php echo isset($_POST['emergencyName1']) ? htmlspecialchars($_POST['emergencyName1']) : ''; ?>" required>
                                    <div class="error-message" id="emergencyName1Error">Please enter contact name</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="emergencyRelation1" class="required">Relationship</label>
                                    <select id="emergencyRelation1" name="emergencyRelation1" class="form-control" required>
                                        <option value="">Select</option>
                                        <option value="spouse" <?php echo (isset($_POST['emergencyRelation1']) && $_POST['emergencyRelation1'] == 'spouse') ? 'selected' : ''; ?>>Spouse</option>
                                        <option value="parent" <?php echo (isset($_POST['emergencyRelation1']) && $_POST['emergencyRelation1'] == 'parent') ? 'selected' : ''; ?>>Parent</option>
                                        <option value="child" <?php echo (isset($_POST['emergencyRelation1']) && $_POST['emergencyRelation1'] == 'child') ? 'selected' : ''; ?>>Child</option>
                                        <option value="sibling" <?php echo (isset($_POST['emergencyRelation1']) && $_POST['emergencyRelation1'] == 'sibling') ? 'selected' : ''; ?>>Sibling</option>
                                        <option value="friend" <?php echo (isset($_POST['emergencyRelation1']) && $_POST['emergencyRelation1'] == 'friend') ? 'selected' : ''; ?>>Friend</option>
                                        <option value="other" <?php echo (isset($_POST['emergencyRelation1']) && $_POST['emergencyRelation1'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <div class="error-message" id="emergencyRelation1Error">Please select relationship</div>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="emergencyPhone1" class="required">Phone Number</label>
                                    <input type="tel" id="emergencyPhone1" name="emergencyPhone1" class="form-control" pattern="[0-9]{10}" value="<?php echo isset($_POST['emergencyPhone1']) ? htmlspecialchars($_POST['emergencyPhone1']) : ''; ?>" required>
                                    <div class="error-message" id="emergencyPhone1Error">Please enter a valid 10-digit phone number</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="emergencyEmail1">Email</label>
                                    <input type="email" id="emergencyEmail1" name="emergencyEmail1" class="form-control" value="<?php echo isset($_POST['emergencyEmail1']) ? htmlspecialchars($_POST['emergencyEmail1']) : ''; ?>">
                                    <div class="error-message" id="emergencyEmail1Error">Please enter a valid email</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="emergency-contact-card">
                            <h3 class="contact-title"><i class="fas fa-user-plus"></i> Secondary Emergency Contact</h3>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="emergencyName2">Full Name</label>
                                    <input type="text" id="emergencyName2" name="emergencyName2" class="form-control" value="<?php echo isset($_POST['emergencyName2']) ? htmlspecialchars($_POST['emergencyName2']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="emergencyRelation2">Relationship</label>
                                    <select id="emergencyRelation2" name="emergencyRelation2" class="form-control">
                                        <option value="">Select</option>
                                        <option value="spouse" <?php echo (isset($_POST['emergencyRelation2']) && $_POST['emergencyRelation2'] == 'spouse') ? 'selected' : ''; ?>>Spouse</option>
                                        <option value="parent" <?php echo (isset($_POST['emergencyRelation2']) && $_POST['emergencyRelation2'] == 'parent') ? 'selected' : ''; ?>>Parent</option>
                                        <option value="child" <?php echo (isset($_POST['emergencyRelation2']) && $_POST['emergencyRelation2'] == 'child') ? 'selected' : ''; ?>>Child</option>
                                        <option value="sibling" <?php echo (isset($_POST['emergencyRelation2']) && $_POST['emergencyRelation2'] == 'sibling') ? 'selected' : ''; ?>>Sibling</option>
                                        <option value="friend" <?php echo (isset($_POST['emergencyRelation2']) && $_POST['emergencyRelation2'] == 'friend') ? 'selected' : ''; ?>>Friend</option>
                                        <option value="other" <?php echo (isset($_POST['emergencyRelation2']) && $_POST['emergencyRelation2'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="emergencyPhone2">Phone Number</label>
                                    <input type="tel" id="emergencyPhone2" name="emergencyPhone2" class="form-control" pattern="[0-9]{10}" value="<?php echo isset($_POST['emergencyPhone2']) ? htmlspecialchars($_POST['emergencyPhone2']) : ''; ?>">
                                    <div class="error-message" id="emergencyPhone2Error">Please enter a valid 10-digit phone number</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="emergencyEmail2">Email</label>
                                    <input type="email" id="emergencyEmail2" name="emergencyEmail2" class="form-control" value="<?php echo isset($_POST['emergencyEmail2']) ? htmlspecialchars($_POST['emergencyEmail2']) : ''; ?>">
                                    <div class="error-message" id="emergencyEmail2Error">Please enter a valid email</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Security Section -->
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-shield-alt"></i> Account Security</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password" class="required">Create Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <small class="text-muted">Minimum 8 characters with 1 uppercase, 1 lowercase, and 1 number</small>
                                <div class="error-message" id="passwordError">Password must be at least 8 characters with 1 uppercase, 1 lowercase, and 1 number</div>
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
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // DOM Elements
        const form = document.getElementById('registrationForm');
        const insuranceRadios = document.querySelectorAll('input[name="hasInsurance"]');
        const insuranceDetails = document.getElementById('insuranceDetails');
        
        // Show/hide insurance details based on selection
        insuranceRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                insuranceDetails.style.display = this.value === 'yes' ? 'block' : 'none';
            });
        });
        
        // Initialize insurance details visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            const selectedInsurance = document.querySelector('input[name="hasInsurance"]:checked');
            if (selectedInsurance && selectedInsurance.value === 'yes') {
                insuranceDetails.style.display = 'block';
            }
        });
        
        // Validation Functions
        function validateName(name, errorId) {
            const value = name.value.trim();
            const errorElement = document.getElementById(errorId);
            
            if (value === '') {
                showError(name, errorElement, 'This field is required');
                return false;
            }
            
            if (!/^[a-zA-Z ]+$/.test(value)) {
                showError(name, errorElement, 'Only letters and spaces allowed');
                return false;
            }
            
            showSuccess(name, errorElement);
            return true;
        }
        
        function validateAadhaar(aadhaar, errorId) {
            const value = aadhaar.value.trim();
            const errorElement = document.getElementById(errorId);
            
            if (value === '') {
                showError(aadhaar, errorElement, 'Aadhaar number is required');
                return false;
            }
            
            if (!/^\d{12}$/.test(value)) {
                showError(aadhaar, errorElement, 'Aadhaar must be 12 digits');
                return false;
            }
            
            showSuccess(aadhaar, errorElement);
            return true;
        }
        
        function validateDOB(dob, errorId) {
            const value = dob.value;
            const errorElement = document.getElementById(errorId);
            
            if (value === '') {
                showError(dob, errorElement, 'Date of birth is required');
                return false;
            }
            
            const birthDate = new Date(value);
            const today = new Date();
            
            if (birthDate >= today) {
                showError(dob, errorElement, 'Date must be in the past');
                return false;
            }
            
            showSuccess(dob, errorElement);
            return true;
        }
        
        function validateGender(genderRadios, errorId) {
            const errorElement = document.getElementById(errorId);
            let isChecked = false;
            
            genderRadios.forEach(radio => {
                if (radio.checked) isChecked = true;
            });
            
            if (!isChecked) {
                errorElement.textContent = 'Please select your gender';
                errorElement.style.display = 'block';
                return false;
            }
            
            errorElement.style.display = 'none';
            return true;
        }
        
        function validateAddress(address, errorId) {
            const value = address.value.trim();
            const errorElement = document.getElementById(errorId);
            
            if (value === '') {
                showError(address, errorElement, 'Address is required');
                return false;
            }
            
            if (value.length < 10) {
                showError(address, errorElement, 'Address is too short');
                return false;
            }
            
            showSuccess(address, errorElement);
            return true;
        }
        
        function validatePhone(phone, errorId) {
            const value = phone.value.trim();
            const errorElement = document.getElementById(errorId);
            
            if (value === '') {
                showError(phone, errorElement, 'Phone number is required');
                return false;
            }
            
            if (!/^\d{10}$/.test(value)) {
                showError(phone, errorElement, 'Phone must be 10 digits');
                return false;
            }
            
            showSuccess(phone, errorElement);
            return true;
        }
        
        function validateEmail(email, errorId) {
            const value = email.value.trim();
            const errorElement = document.getElementById(errorId);
            
            if (value === '') {
                showError(email, errorElement, 'Email is required');
                return false;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                showError(email, errorElement, 'Please enter a valid email');
                return false;
            }
            
            showSuccess(email, errorElement);
            return true;
        }
        
        function validatePassword(password, errorId) {
            const value = password.value;
            const errorElement = document.getElementById(errorId);
            
            if (value === '') {
                showError(password, errorElement, 'Password is required');
                return false;
            }
            
            if (value.length < 8) {
                showError(password, errorElement, 'Password must be at least 8 characters');
                return false;
            }
            
            if (!/[A-Z]/.test(value)) {
                showError(password, errorElement, 'Password must contain at least one uppercase letter');
                return false;
            }
            
            if (!/[a-z]/.test(value)) {
                showError(password, errorElement, 'Password must contain at least one lowercase letter');
                return false;
            }
            
            if (!/\d/.test(value)) {
                showError(password, errorElement, 'Password must contain at least one number');
                return false;
            }
            
            showSuccess(password, errorElement);
            return true;
        }
        
        function validateConfirmPassword(password, confirmPassword, errorId) {
            const passValue = password.value;
            const confirmValue = confirmPassword.value;
            const errorElement = document.getElementById(errorId);
            
            if (confirmValue === '') {
                showError(confirmPassword, errorElement, 'Please confirm your password');
                return false;
            }
            
            if (passValue !== confirmValue) {
                showError(confirmPassword, errorElement, 'Passwords do not match');
                return false;
            }
            
            showSuccess(confirmPassword, errorElement);
            return true;
        }
        
        function validateEmergencyContact(name, relation, phone, nameErrorId, relationErrorId, phoneErrorId) {
            const nameValue = name.value.trim();
            const relationValue = relation.value;
            const phoneValue = phone.value.trim();
            
            const nameError = document.getElementById(nameErrorId);
            const relationError = document.getElementById(relationErrorId);
            const phoneError = document.getElementById(phoneErrorId);
            
            let isValid = true;
            
            if (nameValue === '') {
                showError(name, nameError, 'Name is required');
                isValid = false;
            } else {
                showSuccess(name, nameError);
            }
            
            if (relationValue === '') {
                showError(relation, relationError, 'Please select relationship');
                isValid = false;
            } else {
                showSuccess(relation, relationError);
            }
            
            if (phoneValue === '') {
                showError(phone, phoneError, 'Phone number is required');
                isValid = false;
            } else if (!/^\d{10}$/.test(phoneValue)) {
                showError(phone, phoneError, 'Phone must be 10 digits');
                isValid = false;
            } else {
                showSuccess(phone, phoneError);
            }
            
            return isValid;
        }
        
        // Helper Functions
        function showError(input, errorElement, message) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            input.classList.add('error');
            input.classList.remove('success');
        }
        
        function showSuccess(input, errorElement) {
            errorElement.style.display = 'none';
            input.classList.remove('error');
            input.classList.add('success');
        }
        
        // Event Listeners for Real-time Validation
        document.getElementById('firstName').addEventListener('blur', function() {
            validateName(this, 'firstNameError');
        });
        
        document.getElementById('lastName').addEventListener('blur', function() {
            validateName(this, 'lastNameError');
        });
        
        document.getElementById('aadhaar').addEventListener('blur', function() {
            validateAadhaar(this, 'aadhaarError');
        });
        
        document.getElementById('dob').addEventListener('blur', function() {
            validateDOB(this, 'dobError');
        });
        
        document.querySelectorAll('input[name="gender"]').forEach(radio => {
            radio.addEventListener('change', function() {
                validateGender(document.querySelectorAll('input[name="gender"]'), 'genderError');
            });
        });
        
        document.getElementById('address').addEventListener('blur', function() {
            validateAddress(this, 'addressError');
        });
        
        document.getElementById('phone').addEventListener('blur', function() {
            validatePhone(this, 'phoneError');
        });
        
        document.getElementById('email').addEventListener('blur', function() {
            validateEmail(this, 'emailError');
        });
        
        document.getElementById('password').addEventListener('blur', function() {
            validatePassword(this, 'passwordError');
        });
        
        document.getElementById('confirmPassword').addEventListener('blur', function() {
            validateConfirmPassword(
                document.getElementById('password'),
                this,
                'confirmPasswordError'
            );
        });
        
        // Emergency Contact 1 Validation
        document.getElementById('emergencyName1').addEventListener('blur', function() {
            validateEmergencyContact(
                document.getElementById('emergencyName1'),
                document.getElementById('emergencyRelation1'),
                document.getElementById('emergencyPhone1'),
                'emergencyName1Error',
                'emergencyRelation1Error',
                'emergencyPhone1Error'
            );
        });
        
        document.getElementById('emergencyRelation1').addEventListener('change', function() {
            validateEmergencyContact(
                document.getElementById('emergencyName1'),
                document.getElementById('emergencyRelation1'),
                document.getElementById('emergencyPhone1'),
                'emergencyName1Error',
                'emergencyRelation1Error',
                'emergencyPhone1Error'
            );
        });
        
        document.getElementById('emergencyPhone1').addEventListener('blur', function() {
            validateEmergencyContact(
                document.getElementById('emergencyName1'),
                document.getElementById('emergencyRelation1'),
                document.getElementById('emergencyPhone1'),
                'emergencyName1Error',
                'emergencyRelation1Error',
                'emergencyPhone1Error'
            );
        });
        
        // Form Submission - Allow PHP to handle the actual submission
        form.addEventListener('submit', function(e) {
            // Validate all fields
            const isFirstNameValid = validateName(document.getElementById('firstName'), 'firstNameError');
            const isLastNameValid = validateName(document.getElementById('lastName'), 'lastNameError');
            const isAadhaarValid = validateAadhaar(document.getElementById('aadhaar'), 'aadhaarError');
            const isDOBValid = validateDOB(document.getElementById('dob'), 'dobError');
            const isGenderValid = validateGender(document.querySelectorAll('input[name="gender"]'), 'genderError');
            const isAddressValid = validateAddress(document.getElementById('address'), 'addressError');
            const isPhoneValid = validatePhone(document.getElementById('phone'), 'phoneError');
            const isEmailValid = validateEmail(document.getElementById('email'), 'emailError');
            const isPasswordValid = validatePassword(document.getElementById('password'), 'passwordError');
            const isConfirmPasswordValid = validateConfirmPassword(
                document.getElementById('password'),
                document.getElementById('confirmPassword'),
                'confirmPasswordError'
            );
            const isEmergencyContact1Valid = validateEmergencyContact(
                document.getElementById('emergencyName1'),
                document.getElementById('emergencyRelation1'),
                document.getElementById('emergencyPhone1'),
                'emergencyName1Error',
                'emergencyRelation1Error',
                'emergencyPhone1Error'
            );
            
            // Validate insurance details if insurance is selected
            let isInsuranceValid = true;
            if (document.querySelector('input[name="hasInsurance"]:checked').value === 'yes') {
                const insurancePhone = document.getElementById('insurancePhone');
                if (insurancePhone.value && !/^\d{10}$/.test(insurancePhone.value.trim())) {
                    showError(insurancePhone, document.getElementById('insurancePhoneError'), 'Phone must be 10 digits');
                    isInsuranceValid = false;
                } else {
                    showSuccess(insurancePhone, document.getElementById('insurancePhoneError'));
                }
            }
            
            // If validation fails, prevent form submission
            if (!(isFirstNameValid && isLastNameValid && isAadhaarValid && isDOBValid && 
                isGenderValid && isAddressValid && isPhoneValid && isEmailValid && 
                isPasswordValid && isConfirmPasswordValid && isEmergencyContact1Valid && 
                isInsuranceValid)) {
                e.preventDefault();
                
                // Scroll to the first error
                const firstError = document.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    </script>
</body>
</html>