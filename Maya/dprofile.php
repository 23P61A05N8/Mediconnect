<?php
session_start();
if (!isset($_SESSION['doctor_id']) || $_SESSION['user_type'] !== 'doctor') {
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

// Get doctor data
$doctor_id = $_SESSION['doctor_id'];
$doctor_query = "SELECT * FROM doctors WHERE id = '$doctor_id'";
$doctor_result = mysqli_query($con, $doctor_query);
$doctor = mysqli_fetch_assoc($doctor_result);

$full_name = $doctor['first_name'] . ' ' . $doctor['last_name'];
$initials = strtoupper(substr($doctor['first_name'], 0, 1) . substr($doctor['last_name'], 0, 1));

$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear session messages
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --border: #e2e8f0;
            --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background: #ffffff;
            min-height: 100vh;
            padding: 0;
            color: #374151;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            background: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }

        .logo-text span {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--secondary);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .nav-links a:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .nav-links a.active {
            background: var(--primary);
            color: white;
        }

        .nav-links a i {
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
        }

        .page-title {
            color: var(--dark);
            font-size: 28px;
            font-weight: 700;
        }

        .page-subtitle {
            color: var(--secondary);
            font-size: 16px;
            margin-top: 4px;
        }

        /* Profile Header */
        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: bold;
            margin: 0 auto 1rem;
            box-shadow: var(--shadow-lg);
        }

        /* Form Styles */
        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            color: var(--dark);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            color: var(--dark);
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea {
            height: 120px;
            resize: vertical;
            font-family: inherit;
            line-height: 1.5;
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border-color: #dcfce7;
        }

        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        /* Button Styles */
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border);
        }

        .btn {
            padding: 12px 32px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: 2px solid var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: white;
            color: var(--secondary);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: var(--secondary);
            transform: translateY(-1px);
        }

        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
            color: var(--secondary);
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Character Counter */
        .char-counter {
            text-align: right;
            font-size: 12px;
            color: var(--secondary);
            margin-top: 4px;
        }

        .char-counter.warning {
            color: var(--warning);
        }

        .char-counter.error {
            color: var(--error);
        }

        /* Help Text */
        .help-text {
            font-size: 12px;
            color: var(--secondary);
            margin-top: 4px;
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .profile-header {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .nav-links a span {
                display: none;
            }

            .nav-links a {
                padding: 8px 12px;
            }

            .main-content {
                padding: 1rem;
            }

            .page-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="dashboard.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="logo-text">Medi<span>Connect</span></div>
            </a>
            <div class="nav-links">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="patients.php">
                    <i class="fas fa-users"></i>
                    <span>Patients</span>
                </a>
                <a href="appointments.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
                <a href="update.php" class="active">
                    <i class="fas fa-user-edit"></i>
                    <span>Profile</span>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Update Profile</h1>
                    <p class="page-subtitle">Keep your professional information up to date</p>
                </div>
                <div class="profile-avatar">
                    <?php echo $initials; ?>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Form -->
            <form action="update_profile.php" method="POST" id="profileForm">
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Personal Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <div class="input-group">
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($doctor['first_name'] ?? ''); ?>" required>
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <div class="input-group">
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($doctor['last_name'] ?? ''); ?>" required>
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($doctor['email'] ?? ''); ?>" required>
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-group">
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($doctor['phone'] ?? ''); ?>" required 
                                       maxlength="10" pattern="[0-9]{10}"
                                       title="Please enter a valid 10-digit phone number">
                                <i class="fas fa-phone input-icon"></i>
                            </div>
                            <span class="help-text">10 digits without spaces or dashes</span>
                        </div>
                    </div>
                </div>

                <!-- Professional Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-briefcase-medical"></i> Professional Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <div class="input-group">
                                <input type="text" id="specialization" name="specialization" 
                                       value="<?php echo htmlspecialchars($doctor['specialization'] ?? ''); ?>" required>
                                <i class="fas fa-stethoscope input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="qualification">Qualification</label>
                            <div class="input-group">
                                <input type="text" id="qualification" name="qualification" 
                                       value="<?php echo htmlspecialchars($doctor['qualification'] ?? ''); ?>" required>
                                <i class="fas fa-graduation-cap input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="license_number">License Number</label>
                            <div class="input-group">
                                <input type="text" id="license_number" name="license_number" 
                                       value="<?php echo htmlspecialchars($doctor['license_number'] ?? ''); ?>" required>
                                <i class="fas fa-id-card input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="experience">Experience (years)</label>
                            <div class="input-group">
                                <input type="number" id="experience" name="experience" 
                                       value="<?php echo htmlspecialchars($doctor['experience'] ?? ''); ?>" 
                                       min="0" max="50">
                                <i class="fas fa-calendar-alt input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="hospital">Hospital/Clinic</label>
                            <div class="input-group">
                                <input type="text" id="hospital" name="hospital" 
                                       value="<?php echo htmlspecialchars($doctor['hospital'] ?? ''); ?>"
                                       placeholder="Enter the name of your primary hospital or clinic">
                                <i class="fas fa-hospital input-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Bio Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-file-medical-alt"></i> Professional Bio
                    </h3>
                    <div class="form-group full-width">
                        <label for="bio">Professional Biography</label>
                        <textarea id="bio" name="bio" placeholder="Share your experience, expertise, treatment philosophy, and anything else that would help patients understand your approach to healthcare..."><?php echo htmlspecialchars($doctor['bio'] ?? ''); ?></textarea>
                        <div class="char-counter" id="bioCounter">0/500 characters</div>
                        <span class="help-text">This bio will be visible to patients on your profile page</span>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
                
                <!-- Loading Indicator -->
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Updating your profile...</p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            
            // Show loading animation
            submitBtn.style.display = 'none';
            loading.style.display = 'block';
            
            // Validate phone number
            const phone = document.getElementById('phone').value;
            if (phone && !/^\d{10}$/.test(phone)) {
                alert('Please enter a valid 10-digit phone number');
                submitBtn.style.display = 'flex';
                loading.style.display = 'none';
                e.preventDefault();
                return;
            }
        });

        // Character counter for bio
        const bioTextarea = document.getElementById('bio');
        const bioCounter = document.getElementById('bioCounter');
        
        if (bioTextarea && bioCounter) {
            bioTextarea.addEventListener('input', function() {
                const charCount = this.value.length;
                bioCounter.textContent = `${charCount}/500 characters`;
                
                if (charCount > 450) {
                    bioCounter.className = 'char-counter warning';
                } else if (charCount > 500) {
                    bioCounter.className = 'char-counter error';
                } else {
                    bioCounter.className = 'char-counter';
                }
            });
            
            // Trigger input event to show initial count
            bioTextarea.dispatchEvent(new Event('input'));
        }
    </script>
</body>
</html>