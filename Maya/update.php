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
$doctor = mysqli_fetch_assoc($doctor_query);

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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .logo-text span {
            color: #667eea;
        }

        .nav-links {
            display: flex;
            gap: 25px;
        }

        .nav-links a {
            text-decoration: none;
            color: #555;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover, .nav-links a.active {
            background: #667eea;
            color: white;
        }

        .profile-container {
            background: white;
            border-radius: 0 0 15px 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .page-title {
            color: #333;
            margin-bottom: 30px;
            font-size: 32px;
            text-align: center;
        }

        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 10px;
            font-weight: 600;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5ee;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            height: 140px;
            resize: vertical;
        }

        .btn-group {
            grid-column: 1 / -1;
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 35px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: bold;
            margin: 0 auto 15px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-links {
                flex-direction: column;
                gap: 10px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">MC</div>
                <div class="logo-text">Medi<span>Connect</span></div>
            </div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="patients.php">Patients</a>
                <a href="appointments.php">Appointments</a>
                <a href="update.php" class="active">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($doctor['first_name'], 0, 1) . substr($doctor['last_name'], 0, 1)); ?>
                </div>
                <h1 class="page-title">Update Your Profile</h1>
                <p>Keep your information up to date for better patient care</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="update_profile.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($doctor['first_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($doctor['last_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($doctor['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($doctor['phone'] ?? ''); ?>" required maxlength="10">
                    </div>
                    
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" id="specialization" name="specialization" 
                               value="<?php echo htmlspecialchars($doctor['specialization'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="qualification">Qualification</label>
                        <input type="text" id="qualification" name="qualification" 
                               value="<?php echo htmlspecialchars($doctor['qualification'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="license_number">License Number</label>
                        <input type="text" id="license_number" name="license_number" 
                               value="<?php echo htmlspecialchars($doctor['license_number'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Experience (years)</label>
                        <input type="number" id="experience" name="experience" 
                               value="<?php echo htmlspecialchars($doctor['experience'] ?? ''); ?>" min="0" max="50">
                    </div>
                    
                    <div class="form-group">
                        <label for="hospital">Hospital/Clinic</label>
                        <input type="text" id="hospital" name="hospital" 
                               value="<?php echo htmlspecialchars($doctor['hospital'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="bio">Professional Bio</label>
                        <textarea id="bio" name="bio" placeholder="Tell patients about your experience, expertise, and approach to healthcare..."><?php echo htmlspecialchars($doctor['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>