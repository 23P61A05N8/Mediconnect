<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

// Get user data
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM patients WHERE id = '$user_id'";
$user_result = mysqli_query($con, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Process form submission
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = mysqli_real_escape_string($con, trim($_POST['firstName']));
    $middleName = mysqli_real_escape_string($con, trim($_POST['middleName']));
    $lastName = mysqli_real_escape_string($con, trim($_POST['lastName']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $address = mysqli_real_escape_string($con, trim($_POST['address']));
    $weight = mysqli_real_escape_string($con, $_POST['weight']);
    $height = mysqli_real_escape_string($con, $_POST['height']);
    $bloodGroup = mysqli_real_escape_string($con, $_POST['bloodGroup']);
    $allergies = mysqli_real_escape_string($con, trim($_POST['allergies']));
    $currentMeds = mysqli_real_escape_string($con, trim($_POST['currentMeds']));
    
    // Update query
    $update_query = "UPDATE patients SET 
        first_name = '$firstName',
        middle_name = '$middleName',
        last_name = '$lastName',
        phone = '$phone',
        address = '$address',
        weight = '$weight',
        height = '$height',
        blood_group = '$bloodGroup',
        allergies = '$allergies',
        current_medications = '$currentMeds',
        updated_at = NOW()
        WHERE id = '$user_id'";
    
    if (mysqli_query($con, $update_query)) {
        $success_message = "Profile updated successfully!";
        // Refresh user data
        $user_result = mysqli_query($con, $user_query);
        $user = mysqli_fetch_assoc($user_result);
        
        // Update session data
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
    } else {
        $error_message = "Error updating profile: " . mysqli_error($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Internal CSS for editprofile.php - Enhanced with index.php styles */
        
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
        
        /* Main Content */
        .main-content {
            position: relative;
            z-index: 2;
            padding: 2rem 0;
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .section-title {
            color: #1976d2;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            font-size: 1.5rem;
        }
        
        /* Form Sections */
        .form-section {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section h3 {
            color: #1976d2;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-section h3 i {
            font-size: 1.1rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
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
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
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
        
        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
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
        
        .alert-success {
            background-color: #e8f5e8;
            color: #388e3c;
            border: 1px solid #c8e6c9;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
        }
        
        .text-muted {
            color: #666;
            font-size: 0.875rem;
        }
        
        /* Patient Badge */
        .patient-badge {
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
            
            .form-container {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .section-title {
                font-size: 1.5rem;
            }
            
            .form-section h3 {
                font-size: 1.1rem;
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

    <div class="container main-content">
        <div class="form-container">
            <h2 class="section-title">
                <i class="fas fa-user-edit"></i> Edit Profile
            </h2>
            
            <div class="patient-badge">
                <i class="fas fa-user-injured"></i>
                Patient Account
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="firstName" class="required">First Name</label>
                            <input type="text" id="firstName" name="firstName" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middleName" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['middle_name'] ?: ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName" class="required">Last Name</label>
                            <input type="text" id="lastName" name="lastName" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="phone" class="required">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['weight'] ?: ''); ?>" step="0.1" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="height">Height (cm)</label>
                            <input type="number" id="height" name="height" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['height'] ?: ''); ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information Section -->
                <div class="form-section">
                    <h3><i class="fas fa-address-book"></i> Contact Information</h3>
                    
                    <div class="form-group">
                        <label for="address" class="required">Complete Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                </div>
                
                <!-- Health Information Section -->
                <div class="form-section">
                    <h3><i class="fas fa-heartbeat"></i> Health Information</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="bloodGroup">Blood Group</label>
                            <select id="bloodGroup" name="bloodGroup" class="form-control">
                                <option value="">Select Blood Group</option>
                                <option value="A+" <?php echo ($user['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo ($user['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo ($user['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo ($user['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo ($user['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo ($user['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo ($user['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo ($user['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="allergies">Known Allergies</label>
                        <input type="text" id="allergies" name="allergies" class="form-control" 
                               value="<?php echo htmlspecialchars($user['allergies'] ?: ''); ?>" 
                               placeholder="Separate with commas (e.g., Penicillin, Peanuts)">
                    </div>
                    
                    <div class="form-group">
                        <label for="currentMeds">Current Medications</label>
                        <input type="text" id="currentMeds" name="currentMeds" class="form-control" 
                               value="<?php echo htmlspecialchars($user['current_medications'] ?: ''); ?>" 
                               placeholder="Separate with commas (e.g., Metformin, Lisinopril)">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="help.php">Help</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li>Email: info@mediconnect.com</li>
                        <li>Phone: +1 (555) 123-4567</li>
                        <li>Address: 123 Health St, Medical City</li>
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
            });

            // Close mobile menu when clicking on a link
            document.querySelectorAll(".nav-link").forEach(n => n.addEventListener("click", () => {
                hamburger.classList.remove("active");
                navMenu.classList.remove("active");
            }));
        }

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#d32f2f';
                } else {
                    field.style.borderColor = '#e0e0e0';
                }
            });
            
            // Phone number validation
            const phoneField = document.getElementById('phone');
            if (phoneField.value && !/^\d{10}$/.test(phoneField.value.trim())) {
                isValid = false;
                phoneField.style.borderColor = '#d32f2f';
                alert('Please enter a valid 10-digit phone number');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields correctly.');
            }
        });

        // Real-time validation for phone number
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                if (this.value && !/^\d{0,10}$/.test(this.value)) {
                    this.value = this.value.slice(0, -1);
                }
            });
        }
    </script>
</body>
</html>