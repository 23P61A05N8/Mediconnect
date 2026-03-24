<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'mediconnect'; // Change to your database name
$username = 'root'; // Change to your database username
$password = ''; // Change to your database password

// Create database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        // Update profile information
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $marital_status = $_POST['marital_status'];
        $address = trim($_POST['address']);
        $weight = $_POST['weight'];
        $height = $_POST['height'];
        $blood_group = $_POST['blood_group'];
        $allergies = trim($_POST['allergies']);
        $current_medications = trim($_POST['current_medications']);
        
        // Insurance information
        $has_insurance = $_POST['has_insurance'];
        $insurance_provider = trim($_POST['insurance_provider']);
        $policy_number = trim($_POST['policy_number']);
        $group_number = trim($_POST['group_number']);
        $insurance_phone = trim($_POST['insurance_phone']);
        
        // Emergency contacts
        $emergency_name1 = trim($_POST['emergency_name1']);
        $emergency_relation1 = $_POST['emergency_relation1'];
        $emergency_phone1 = trim($_POST['emergency_phone1']);
        $emergency_email1 = trim($_POST['emergency_email1']);
        $emergency_name2 = trim($_POST['emergency_name2']);
        $emergency_relation2 = $_POST['emergency_relation2'];
        $emergency_phone2 = trim($_POST['emergency_phone2']);
        $emergency_email2 = trim($_POST['emergency_email2']);

        try {
            // Check if email already exists (excluding current patient)
            $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ? AND id != ?");
            $stmt->execute([$email, $patient_id]);
            if ($stmt->fetch()) {
                $error = "Email already exists!";
            } else {
                $stmt = $pdo->prepare("
                    UPDATE patients SET 
                    first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?,
                    dob = ?, gender = ?, marital_status = ?, address = ?,
                    weight = ?, height = ?, blood_group = ?, allergies = ?, current_medications = ?,
                    has_insurance = ?, insurance_provider = ?, policy_number = ?, group_number = ?, insurance_phone = ?,
                    emergency_name1 = ?, emergency_relation1 = ?, emergency_phone1 = ?, emergency_email1 = ?,
                    emergency_name2 = ?, emergency_relation2 = ?, emergency_phone2 = ?, emergency_email2 = ?,
                    updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $first_name, $middle_name, $last_name, $email, $phone,
                    $dob, $gender, $marital_status, $address,
                    $weight, $height, $blood_group, $allergies, $current_medications,
                    $has_insurance, $insurance_provider, $policy_number, $group_number, $insurance_phone,
                    $emergency_name1, $emergency_relation1, $emergency_phone1, $emergency_email1,
                    $emergency_name2, $emergency_relation2, $emergency_phone2, $emergency_email2,
                    $patient_id
                ]);

                // Update session data
                $_SESSION['email'] = $email;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;

                $success = "Profile updated successfully!";
            }
            
        } catch (PDOException $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required!";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match!";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long!";
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM patients WHERE id = ?");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch();

            if ($patient && password_verify($current_password, $patient['password'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE patients SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_password_hash, $patient_id]);
                $success = "Password changed successfully!";
            } else {
                $error = "Current password is incorrect!";
            }
        }
    }
    elseif (isset($_POST['delete_account'])) {
        // Delete account confirmation
        $confirm_delete = $_POST['confirm_delete'];
        
        if ($confirm_delete === 'DELETE') {
            try {
                // Start transaction
                $pdo->beginTransaction();

                // Delete patient data
                $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
                $stmt->execute([$patient_id]);

                // Commit transaction
                $pdo->commit();

                // Destroy session and redirect
                session_destroy();
                header("Location: index.php?account_deleted=1");
                exit();

            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Error deleting account: " . $e->getMessage();
            }
        } else {
            $error = "Please type 'DELETE' to confirm account deletion.";
        }
    }
    elseif (isset($_POST['export_data'])) {
        // Export patient data
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="mediconnect_patient_data_' . date('Y-m-d') . '.json"');
        
        $stmt = $pdo->prepare("
            SELECT first_name, middle_name, last_name, aadhaar, dob, gender, marital_status, 
                   nationality, address, phone, email, weight, height, blood_group, allergies, 
                   current_medications, has_insurance, insurance_provider, policy_number, 
                   group_number, insurance_phone, emergency_name1, emergency_relation1, 
                   emergency_phone1, emergency_email1, emergency_name2, emergency_relation2, 
                   emergency_phone2, emergency_email2, created_at, updated_at 
            FROM patients WHERE id = ?
        ");
        $stmt->execute([$patient_id]);
        $patient_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($patient_data, JSON_PRETTY_PRINT);
        exit();
    }
}

// Get current patient data
try {
    $stmt = $pdo->prepare("
        SELECT first_name, middle_name, last_name, aadhaar, dob, gender, marital_status, 
               nationality, address, phone, email, weight, height, blood_group, allergies, 
               current_medications, has_insurance, insurance_provider, policy_number, 
               group_number, insurance_phone, emergency_name1, emergency_relation1, 
               emergency_phone1, emergency_email1, emergency_name2, emergency_relation2, 
               emergency_phone2, emergency_email2, created_at
        FROM patients WHERE id = ?
    ");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();

    if (!$patient) {
        header("Location: logout.php");
        exit();
    }

} catch (PDOException $e) {
    $error = "Error loading patient data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Settings - MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* All the previous CSS styles remain exactly the same */
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
        }

        .nav-link:hover {
            color: #1976d2;
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

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .settings-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            max-width: 900px;
            margin: 2rem auto;
            animation: fadeInUp 0.8s ease-out;
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

        .settings-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(224, 224, 224, 0.8);
        }

        .settings-section:last-child {
            border-bottom: none;
        }

        .settings-section h3 {
            color: #1976d2;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            outline: none;
            border-color: #1976d2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(25, 118, 210, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(25, 118, 210, 0.1);
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }

        .profile-info h4 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .profile-info p {
            color: #666;
            font-size: 0.9rem;
        }

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
        }

        .btn-block:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(25, 118, 210, 0.4);
        }

        .btn-block i {
            margin-right: 0.5rem;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .close {
            color: #aaa;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #333;
        }

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

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

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

            .settings-container {
                padding: 2rem;
                margin: 1rem auto;
            }

            .section-title {
                font-size: 2rem;
            }

            .form-row, .form-row-3 {
                grid-template-columns: 1fr;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="floating-shape" style="width: 80px; height: 80px; top: 10%; left: 10%; animation-delay: 0s;"></div>
        <div class="floating-shape" style="width: 120px; height: 120px; top: 60%; right: 10%; animation-delay: 2s;"></div>
        <div class="floating-shape" style="width: 60px; height: 60px; bottom: 20%; left: 20%; animation-delay: 4s;"></div>
        <div class="floating-shape" style="width: 100px; height: 100px; top: 30%; right: 20%; animation-delay: 1s;"></div>
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
        <div class="settings-container">
            <h2 class="section-title">Patient Profile Settings</h2>
            
            <!-- Display Messages -->
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

            <form method="POST" action="">
                <!-- Personal Information Section -->
                <div class="settings-section">
                    <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                    
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php 
                                $display_name = $patient['first_name'] ?: 'Patient';
                                echo strtoupper(substr($display_name, 0, 1)); 
                            ?>
                        </div>
                        <div class="profile-info">
                            <h4>
                                <?php 
                                    echo htmlspecialchars(trim($patient['first_name'] . ' ' . ($patient['middle_name'] ? $patient['middle_name'] . ' ' : '') . $patient['last_name']));
                                ?>
                            </h4>
                            <p>Patient since <?php echo date('F Y', strtotime($patient['created_at'])); ?></p>
                            <p><strong>Aadhaar:</strong> <?php echo htmlspecialchars($patient['aadhaar']); ?></p>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middle_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['middle_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['phone']); ?>" 
                                   pattern="[0-9]{10}" title="10-digit phone number" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dob">Date of Birth *</label>
                            <input type="date" id="dob" name="dob" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['dob']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="male" <?php echo $patient['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $patient['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo $patient['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address *</label>
                        <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($patient['address']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="marital_status">Marital Status</label>
                            <select id="marital_status" name="marital_status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="single" <?php echo $patient['marital_status'] === 'single' ? 'selected' : ''; ?>>Single</option>
                                <option value="married" <?php echo $patient['marital_status'] === 'married' ? 'selected' : ''; ?>>Married</option>
                                <option value="divorced" <?php echo $patient['marital_status'] === 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                                <option value="widowed" <?php echo $patient['marital_status'] === 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="blood_group">Blood Group</label>
                            <select id="blood_group" name="blood_group" class="form-control">
                                <option value="">Select Blood Group</option>
                                <option value="A+" <?php echo $patient['blood_group'] === 'A+' ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo $patient['blood_group'] === 'A-' ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo $patient['blood_group'] === 'B+' ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo $patient['blood_group'] === 'B-' ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo $patient['blood_group'] === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo $patient['blood_group'] === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo $patient['blood_group'] === 'O+' ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo $patient['blood_group'] === 'O-' ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Health Information Section -->
                <div class="settings-section">
                    <h3><i class="fas fa-heartbeat"></i> Health Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['weight'] ?? ''); ?>" 
                                   step="0.1" min="0" max="300">
                        </div>
                        <div class="form-group">
                            <label for="height">Height (cm)</label>
                            <input type="number" id="height" name="height" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['height'] ?? ''); ?>" 
                                   step="0.1" min="0" max="300">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="allergies">Allergies</label>
                        <textarea id="allergies" name="allergies" class="form-control" rows="2" 
                                  placeholder="List any allergies (separated by commas)"><?php echo htmlspecialchars($patient['allergies'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="current_medications">Current Medications</label>
                        <textarea id="current_medications" name="current_medications" class="form-control" rows="2" 
                                  placeholder="List current medications (separated by commas)"><?php echo htmlspecialchars($patient['current_medications'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Insurance Information Section -->
                <div class="settings-section">
                    <h3><i class="fas fa-shield-alt"></i> Insurance Information</h3>
                    
                    <div class="form-group">
                        <label for="has_insurance">Do you have insurance?</label>
                        <select id="has_insurance" name="has_insurance" class="form-control">
                            <option value="no" <?php echo $patient['has_insurance'] === 'no' ? 'selected' : ''; ?>>No</option>
                            <option value="yes" <?php echo $patient['has_insurance'] === 'yes' ? 'selected' : ''; ?>>Yes</option>
                        </select>
                    </div>

                    <div id="insurance-details" style="<?php echo $patient['has_insurance'] === 'yes' ? '' : 'display: none;'; ?>">
                        <div class="form-group">
                            <label for="insurance_provider">Insurance Provider</label>
                            <input type="text" id="insurance_provider" name="insurance_provider" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['insurance_provider'] ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="policy_number">Policy Number</label>
                                <input type="text" id="policy_number" name="policy_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($patient['policy_number'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="group_number">Group Number</label>
                                <input type="text" id="group_number" name="group_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($patient['group_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="insurance_phone">Insurance Phone</label>
                            <input type="tel" id="insurance_phone" name="insurance_phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['insurance_phone'] ?? ''); ?>" 
                                   pattern="[0-9]{10}" title="10-digit phone number">
                        </div>
                    </div>
                </div>

                <!-- Emergency Contacts Section -->
                <div class="settings-section">
                    <h3><i class="fas fa-phone-alt"></i> Emergency Contacts</h3>
                    
                    <div class="form-group">
                        <label for="emergency_name1">Primary Emergency Contact Name *</label>
                        <input type="text" id="emergency_name1" name="emergency_name1" class="form-control" 
                               value="<?php echo htmlspecialchars($patient['emergency_name1']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergency_relation1">Relation *</label>
                            <select id="emergency_relation1" name="emergency_relation1" class="form-control" required>
                                <option value="spouse" <?php echo $patient['emergency_relation1'] === 'spouse' ? 'selected' : ''; ?>>Spouse</option>
                                <option value="parent" <?php echo $patient['emergency_relation1'] === 'parent' ? 'selected' : ''; ?>>Parent</option>
                                <option value="child" <?php echo $patient['emergency_relation1'] === 'child' ? 'selected' : ''; ?>>Child</option>
                                <option value="sibling" <?php echo $patient['emergency_relation1'] === 'sibling' ? 'selected' : ''; ?>>Sibling</option>
                                <option value="friend" <?php echo $patient['emergency_relation1'] === 'friend' ? 'selected' : ''; ?>>Friend</option>
                                <option value="other" <?php echo $patient['emergency_relation1'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="emergency_phone1">Phone Number *</label>
                            <input type="tel" id="emergency_phone1" name="emergency_phone1" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['emergency_phone1']); ?>" 
                                   pattern="[0-9]{10}" title="10-digit phone number" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emergency_email1">Email Address</label>
                        <input type="email" id="emergency_email1" name="emergency_email1" class="form-control" 
                               value="<?php echo htmlspecialchars($patient['emergency_email1'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="emergency_name2">Secondary Emergency Contact Name</label>
                        <input type="text" id="emergency_name2" name="emergency_name2" class="form-control" 
                               value="<?php echo htmlspecialchars($patient['emergency_name2'] ?? ''); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergency_relation2">Relation</label>
                            <select id="emergency_relation2" name="emergency_relation2" class="form-control">
                                <option value="">Select Relation</option>
                                <option value="spouse" <?php echo $patient['emergency_relation2'] === 'spouse' ? 'selected' : ''; ?>>Spouse</option>
                                <option value="parent" <?php echo $patient['emergency_relation2'] === 'parent' ? 'selected' : ''; ?>>Parent</option>
                                <option value="child" <?php echo $patient['emergency_relation2'] === 'child' ? 'selected' : ''; ?>>Child</option>
                                <option value="sibling" <?php echo $patient['emergency_relation2'] === 'sibling' ? 'selected' : ''; ?>>Sibling</option>
                                <option value="friend" <?php echo $patient['emergency_relation2'] === 'friend' ? 'selected' : ''; ?>>Friend</option>
                                <option value="other" <?php echo $patient['emergency_relation2'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="emergency_phone2">Phone Number</label>
                            <input type="tel" id="emergency_phone2" name="emergency_phone2" class="form-control" 
                                   value="<?php echo htmlspecialchars($patient['emergency_phone2'] ?? ''); ?>" 
                                   pattern="[0-9]{10}" title="10-digit phone number">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emergency_email2">Email Address</label>
                        <input type="email" id="emergency_email2" name="emergency_email2" class="form-control" 
                               value="<?php echo htmlspecialchars($patient['emergency_email2'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Save Settings -->
                <div class="form-group" style="margin-top: 2rem;">
                    <button type="submit" name="save_settings" class="btn-block">
                        <i class="fas fa-save"></i> Save All Changes
                    </button>
                </div>
            </form>

            <!-- Security & Data Management Section -->
            <div class="settings-section">
                <h3><i class="fas fa-user-shield"></i> Security & Data</h3>
                
                <button type="button" class="btn-block btn-secondary" onclick="openPasswordModal()" style="margin-bottom: 1rem;">
                    <i class="fas fa-key"></i> Change Password
                </button>

                <form method="POST" style="margin-bottom: 1rem;">
                    <button type="submit" name="export_data" class="btn-block btn-secondary">
                        <i class="fas fa-download"></i> Export Medical Data
                    </button>
                </form>

                <button type="button" class="btn-block btn-danger" onclick="openDeleteModal()">
                    <i class="fas fa-trash-alt"></i> Delete Account
                </button>
            </div>
            
            <div class="auth-footer">
                <p><a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></p>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-key"></i> Change Password</h3>
                <span class="close" onclick="closePasswordModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" name="change_password" class="btn-block">
                    <i class="fas fa-save"></i> Change Password
                </button>
            </form>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Delete Account</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <p style="margin-bottom: 1.5rem; color: #721c24;">
                <strong>Warning:</strong> This action cannot be undone. All your medical records and personal data will be permanently deleted.
            </p>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="confirm_delete">Type "DELETE" to confirm:</label>
                    <input type="text" id="confirm_delete" name="confirm_delete" class="form-control" 
                           placeholder="Type DELETE here" required>
                </div>
                <button type="submit" name="delete_account" class="btn-block btn-danger">
                    <i class="fas fa-trash-alt"></i> Permanently Delete Account
                </button>
            </form>
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

        // Insurance details toggle
        const hasInsurance = document.getElementById('has_insurance');
        const insuranceDetails = document.getElementById('insurance-details');

        hasInsurance.addEventListener('change', function() {
            if (this.value === 'yes') {
                insuranceDetails.style.display = 'block';
            } else {
                insuranceDetails.style.display = 'none';
            }
        });

        // Modal functions
        function openPasswordModal() {
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
            document.getElementById('current_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
        }

        function openDeleteModal() {
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.getElementById('confirm_delete').value = '';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const passwordModal = document.getElementById('passwordModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === passwordModal) {
                closePasswordModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>