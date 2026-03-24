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

$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long!";
    } else {
        // Verify current password
        if (password_verify($current_password, $doctor['password'])) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE doctors SET password = '$new_password_hash', updated_at = NOW() WHERE id = '$doctor_id'";
            
            if (mysqli_query($con, $update_query)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Error updating password: " . mysqli_error($con);
            }
        } else {
            $error = "Current password is incorrect!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Settings - MediConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .settings-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(25, 118, 210, 0.1);
        }
        
        .settings-section:last-child {
            border-bottom: none;
        }
        
        .settings-section h3 {
            color: var(--primary);
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
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
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
        }
        
        input:checked + .slider {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
    <!-- Animated Background and Dashboard structure same as dprofile.php -->
    <!-- Include the same sidebar, header, and background as dprofile.php -->
    
    <div class="dashboard">
        <!-- Sidebar (same as dprofile.php) -->
        <div class="sidebar">
            <!-- Same sidebar content as dprofile.php -->
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <div class="header-title">Doctor Settings</div>
                <!-- Same profile dropdown as dprofile.php -->
            </header>
            
            <div class="content">
                <h2 class="section-title"><i class="fas fa-cog"></i> Settings</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="settings-container">
                    <!-- Notification Settings -->
                    <div class="settings-section">
                        <h3><i class="fas fa-bell"></i> Notification Settings</h3>
                        <div class="form-group">
                            <label style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Email Notifications</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </label>
                            <small style="color: #666;">Receive email notifications for new appointments</small>
                        </div>
                        <div class="form-group">
                            <label style="display: flex; justify-content: space-between; align-items: center;">
                                <span>SMS Notifications</span>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </label>
                            <small style="color: #666;">Receive SMS alerts for urgent appointments</small>
                        </div>
                    </div>
                    
                    <!-- Password Change -->
                    <div class="settings-section">
                        <h3><i class="fas fa-key"></i> Change Password</h3>
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
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-save"></i> Change Password
                            </button>
                        </form>
                    </div>
                    
                    <!-- Privacy Settings -->
                    <div class="settings-section">
                        <h3><i class="fas fa-shield-alt"></i> Privacy Settings</h3>
                        <div class="form-group">
                            <label style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Profile Visibility</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </label>
                            <small style="color: #666;">Allow patients to see your profile</small>
                        </div>
                        <div class="form-group">
                            <label style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Online Booking</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </label>
                            <small style="color: #666;">Allow patients to book appointments online</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Same JavaScript as dprofile.php
    </script>
</body>
</html>
<?php mysqli_close($con); ?>