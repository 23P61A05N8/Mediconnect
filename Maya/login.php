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

// Function to check if column exists in a table
function columnExists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

// Function to get table primary key
function getPrimaryKey($conn, $table) {
    $result = mysqli_query($conn, "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['Column_name'];
    }
    return 'id'; // Default fallback
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a forgot password request
    if (isset($_POST['forgot_password'])) {
        $email = mysqli_real_escape_string($con, trim($_POST['email']));
        $userType = mysqli_real_escape_string($con, trim($_POST['userType']));
        
        // Check if email exists
        if ($userType === 'patient') {
            // Check if email column exists in patients table
            if (!columnExists($con, 'patients', 'email')) {
                $login_error = "Email feature not available for patients. Please contact support.";
            } else {
                $query = "SELECT * FROM patients WHERE email = '$email'";
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
        } else {
            // Doctor forgot password
            if (!columnExists($con, 'doctors', 'email')) {
                $login_error = "Email feature not available for doctors. Please contact support.";
            } else {
                $query = "SELECT * FROM doctors WHERE email = '$email'";
                $result = mysqli_query($con, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    // Generate OTP
                    $otp = rand(100000, 999999);
                    $_SESSION['reset_otp'] = $otp;
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_user_type'] = $userType;
                    $_SESSION['otp_expiry'] = time() + 600;
                    $_SESSION['otp_display'] = $otp;
                    
                    header("Location: login.php?action=verify_otp");
                    exit();
                } else {
                    $login_error = "Email not found!";
                }
            }
        }
    }
    // Check if it's an OTP verification request
    else if (isset($_POST['verify_otp'])) {
        $entered_otp = mysqli_real_escape_string($con, trim($_POST['otp']));
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (isset($_SESSION['reset_otp']) && $entered_otp == $_SESSION['reset_otp'] && 
            isset($_SESSION['otp_expiry']) && time() < $_SESSION['otp_expiry']) {
            if ($new_password === $confirm_password) {
                // Update password in database
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $email = $_SESSION['reset_email'];
                $userType = $_SESSION['reset_user_type'];
                
                // Check if password column exists before updating
                if ($userType === 'patient') {
                    if (!columnExists($con, 'patients', 'password')) {
                        $login_error = "Password column not found in patients table!";
                    } else if (!columnExists($con, 'patients', 'email')) {
                        $login_error = "Email column not found in patients table!";
                    } else {
                        $query = "UPDATE patients SET password = '$hashed_password' WHERE email = '$email'";
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
                    }
                } else {
                    if (!columnExists($con, 'doctors', 'password')) {
                        $login_error = "Password column not found in doctors table!";
                    } else if (!columnExists($con, 'doctors', 'email')) {
                        $login_error = "Email column not found in doctors table!";
                    } else {
                        $query = "UPDATE doctors SET password = '$hashed_password' WHERE email = '$email'";
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
                    }
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
            // Patient login with dynamic column checking
            $emailExists = columnExists($con, 'patients', 'email');
            $aadhaarExists = columnExists($con, 'patients', 'aadhaar');
            $usernameExists = columnExists($con, 'patients', 'username');
            $phoneExists = columnExists($con, 'patients', 'phone');
            
            $whereClause = [];
            if ($emailExists) $whereClause[] = "email = '$loginId'";
            if ($aadhaarExists) $whereClause[] = "aadhaar = '$loginId'";
            if ($usernameExists) $whereClause[] = "username = '$loginId'";
            if ($phoneExists) $whereClause[] = "phone = '$loginId'";
            
            // If no specific columns found, try generic id or name
            if (empty($whereClause)) {
                $idExists = columnExists($con, 'patients', 'id');
                $patientIdExists = columnExists($con, 'patients', 'patient_id');
                $nameExists = columnExists($con, 'patients', 'name');
                
                if ($idExists) $whereClause[] = "id = '$loginId'";
                if ($patientIdExists) $whereClause[] = "patient_id = '$loginId'";
                if ($nameExists) $whereClause[] = "name = '$loginId'";
            }
            
            if (empty($whereClause)) {
                $login_error = "Patient table is not properly configured. Please contact support.";
            } else {
                $query = "SELECT * FROM patients WHERE " . implode(' OR ', $whereClause);
                $result = mysqli_query($con, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    
                    // Check if password column exists
                    if (!columnExists($con, 'patients', 'password')) {
                        $login_error = "Password authentication not available. Please contact support.";
                    } else if (isset($user['password']) && password_verify($password, $user['password'])) {
                        // Login successful
                        $primaryKey = getPrimaryKey($con, 'patients');
                        $_SESSION['user_id'] = $user[$primaryKey] ?? $user['id'] ?? $user['patient_id'] ?? null;
                        $_SESSION['first_name'] = $user['first_name'] ?? $user['fname'] ?? $user['name'] ?? '';
                        $_SESSION['last_name'] = $user['last_name'] ?? $user['lname'] ?? '';
                        $_SESSION['full_name'] = $user['full_name'] ?? trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
                        if ($emailExists && isset($user['email'])) $_SESSION['email'] = $user['email'];
                        $_SESSION['user_type'] = 'patient';
                        
                        // Store additional user data if available
                        if (isset($user['phone'])) $_SESSION['phone'] = $user['phone'];
                        if (isset($user['aadhaar'])) $_SESSION['aadhaar'] = $user['aadhaar'];
                        if (isset($user['address'])) $_SESSION['address'] = $user['address'];
                        if (isset($user['dob'])) $_SESSION['dob'] = $user['dob'];
                        if (isset($user['gender'])) $_SESSION['gender'] = $user['gender'];
                        
                        // Redirect to dashboard
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $login_error = "Invalid password!";
                    }
                } else {
                    $login_error = "Patient credentials not found!";
                }
            }
        } elseif ($userType === 'doctor') {
            // Doctor login with dynamic column checking
            $emailExists = columnExists($con, 'doctors', 'email');
            $doctorIdExists = columnExists($con, 'doctors', 'doctor_id');
            $usernameExists = columnExists($con, 'doctors', 'username');
            $licenseExists = columnExists($con, 'doctors', 'license_number');
            
            $whereClause = [];
            if ($emailExists) $whereClause[] = "email = '$loginId'";
            if ($doctorIdExists) $whereClause[] = "doctor_id = '$loginId'";
            if ($usernameExists) $whereClause[] = "username = '$loginId'";
            if ($licenseExists) $whereClause[] = "license_number = '$loginId'";
            
            // If no specific columns found, try generic id or name
            if (empty($whereClause)) {
                $idExists = columnExists($con, 'doctors', 'id');
                $nameExists = columnExists($con, 'doctors', 'name');
                
                if ($idExists) $whereClause[] = "id = '$loginId'";
                if ($nameExists) $whereClause[] = "name = '$loginId'";
            }
            
            if (empty($whereClause)) {
                $login_error = "Doctor table is not properly configured. Please contact support.";
            } else {
                $query = "SELECT * FROM doctors WHERE " . implode(' OR ', $whereClause);
                $result = mysqli_query($con, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    
                    // Check if password column exists
                    if (!columnExists($con, 'doctors', 'password')) {
                        $login_error = "Password authentication not available. Please contact support.";
                    } else if (isset($user['password']) && password_verify($password, $user['password'])) {
                        // Login successful
                        $primaryKey = getPrimaryKey($con, 'doctors');
                        $_SESSION['doctor_id'] = $user[$primaryKey] ?? $user['id'] ?? $user['doctor_id'] ?? null;
                        $_SESSION['first_name'] = $user['first_name'] ?? $user['fname'] ?? $user['name'] ?? '';
                        $_SESSION['last_name'] = $user['last_name'] ?? $user['lname'] ?? '';
                        $_SESSION['full_name'] = $user['full_name'] ?? trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
                        if ($emailExists && isset($user['email'])) $_SESSION['email'] = $user['email'];
                        $_SESSION['user_type'] = 'doctor';
                        $_SESSION['specialization'] = $user['specialization'] ?? $user['speciality'] ?? '';
                        
                        // Store additional doctor data if available
                        if (isset($user['license_number'])) $_SESSION['license_number'] = $user['license_number'];
                        if (isset($user['qualification'])) $_SESSION['qualification'] = $user['qualification'];
                        if (isset($user['experience'])) $_SESSION['experience'] = $user['experience'];
                        if (isset($user['hospital'])) $_SESSION['hospital'] = $user['hospital'];
                        if (isset($user['department'])) $_SESSION['department'] = $user['department'];
                        if (isset($user['phone'])) $_SESSION['phone'] = $user['phone'];
                        if (isset($user['consultation_fee'])) $_SESSION['consultation_fee'] = $user['consultation_fee'];
                        if (isset($user['availability'])) $_SESSION['availability'] = $user['availability'];
                        
                        // Redirect to doctor dashboard
                        header("Location: doctordashboard.php");
                        exit();
                    } else {
                        $login_error = "Invalid password!";
                    }
                } else {
                    $login_error = "Doctor credentials not found!";
                }
            }
        } elseif ($userType === 'admin') {
            // Admin login (if you have admin table)
            if (columnExists($con, 'admins', 'username') && columnExists($con, 'admins', 'password')) {
                $query = "SELECT * FROM admins WHERE username = '$loginId' OR email = '$loginId'";
                $result = mysqli_query($con, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $admin = mysqli_fetch_assoc($result);
                    if (password_verify($password, $admin['password'])) {
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_email'] = $admin['email'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['admin_role'] = $admin['role'] ?? 'administrator';
                        
                        header("Location: admin/dashboard.php");
                        exit();
                    } else {
                        $login_error = "Invalid admin password!";
                    }
                } else {
                    $login_error = "Admin credentials not found!";
                }
            } else {
                $login_error = "Admin authentication not configured.";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
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
            background-color: rgba(255, 255, 255, 0.95);
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
        
        /* Enhanced Auth Container */
        .auth-container {
            min-height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
            padding: 2rem 0;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 480px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
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
            height: 5px;
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
        
        .auth-title {
            text-align: center;
            color: #1976d2;
            margin-bottom: 0.8rem;
            font-weight: 800;
            font-size: 2.2rem;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .auth-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
            line-height: 1.5;
        }
        
        /* Enhanced User Type Selector */
        .user-type-selector {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 2rem;
            background: #f8fafc;
            padding: 0.8rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .user-type-btn {
            flex: 1;
            padding: 1rem;
            border: 2px solid transparent;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            text-align: center;
            font-size: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            color: #4a5568;
        }
        
        .user-type-btn i {
            font-size: 1.5rem;
        }
        
        .user-type-btn.active {
            border-color: #1976d2;
            background: #e3f2fd;
            color: #1976d2;
            box-shadow: 0 6px 15px rgba(25, 118, 210, 0.15);
            transform: translateY(-2px);
        }
        
        .user-type-btn[data-type="doctor"].active {
            border-color: #388e3c;
            background: #e8f5e9;
            color: #388e3c;
            box-shadow: 0 6px 15px rgba(56, 142, 60, 0.15);
        }
        
        .user-type-btn:hover:not(.active) {
            background: #f1f5f9;
            transform: translateY(-1px);
        }
        
        /* Enhanced Form Elements */
        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
            font-family: 'Inter', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1976d2;
            box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.1);
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #718096;
            padding: 0.5rem;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #1976d2;
        }
        
        /* Enhanced Buttons */
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
        
        .btn-primary:active {
            transform: translateY(-1px);
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
        
        .btn-block {
            width: 100%;
            display: block;
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
            box-shadow: 0 8px 20px rgba(25, 118, 210, 0.35);
        }
        
        .btn-doctor:hover {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(56, 142, 60, 0.35);
        }
        
        /* Forgot Password Link */
        .forgot-password {
            text-align: right;
            margin: 1.2rem 0;
        }
        
        .forgot-password a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .forgot-password a:hover {
            color: #0d47a1;
            text-decoration: underline;
        }
        
        /* Auth Footer */
        .auth-footer {
            text-align: center;
            margin-top: 2.5rem;
            color: #666;
            border-top: 1px solid #e2e8f0;
            padding-top: 2rem;
        }
        
        .auth-footer p {
            margin-bottom: 1.2rem;
            font-size: 1rem;
        }
        
        .auth-footer a {
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            border-radius: 10px;
            padding: 0.8rem 2rem;
            display: inline-block;
            margin-top: 0.5rem;
            min-width: 200px;
        }
        
        /* Alert Styles */
        .alert {
            padding: 1rem 1.2rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            position: relative;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-error {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1565c0;
            border: 1px solid #90caf9;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3e0, #ffcc80);
            color: #ef6c00;
            border: 1px solid #ffb74d;
        }
        
        .alert-close {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .alert-close:hover {
            opacity: 1;
        }
        
        /* OTP Input Styles */
        .otp-container {
            display: flex;
            gap: 0.8rem;
            justify-content: center;
            margin: 1.5rem 0;
        }
        
        .otp-input {
            width: 60px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.3s;
            background: white;
        }
        
        .otp-input:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.1);
            outline: none;
        }
        
        /* Demo OTP Display */
        .demo-otp {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin: 1.5rem 0;
            font-weight: 700;
            color: #1976d2;
            font-size: 1.2rem;
            border: 1px solid #90caf9;
        }
        
        /* Password Strength Indicator */
        .password-strength {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0;
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
            background: #4caf50;
            width: 100%;
        }
        
        /* Additional Options */
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #4a5568;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1976d2;
        }
        
        /* Social Login */
        .social-login {
            margin: 2rem 0;
        }
        
        .social-divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #718096;
        }
        
        .social-divider::before,
        .social-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .social-divider span {
            padding: 0 1rem;
            font-size: 0.9rem;
        }
        
        .social-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .social-btn {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .social-btn:hover {
            border-color: #1976d2;
            transform: translateY(-2px);
        }
        
        .social-btn.google {
            color: #db4437;
        }
        
        .social-btn.facebook {
            color: #4267B2;
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
            .auth-card {
                margin: 1rem;
                padding: 2.5rem 2rem;
            }
            
            .auth-title {
                font-size: 1.8rem;
            }
            
            .auth-subtitle {
                font-size: 1rem;
            }
            
            .user-type-selector {
                flex-direction: column;
            }
            
            .otp-input {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
            }
            
            /* Mobile menu adjustments */
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
            
            .nav-link {
                margin: 0.8rem 0;
                padding: 0.8rem;
                font-size: 1.1rem;
            }
            
            .navbar {
                padding: 0.6rem 0;
                min-height: 65px;
            }
            
            .nav-brand h2 {
                font-size: 1.6rem;
            }
            
            .social-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: 2rem 1.5rem;
            }
            
            .auth-title {
                font-size: 1.6rem;
            }
            
            .otp-input {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }
            
            .btn {
                padding: 0.9rem 1.5rem;
            }
            
            .auth-footer a {
                padding: 0.7rem 1.5rem;
                min-width: 180px;
            }
        }
        
        /* Loading Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Accessibility */
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        /* Focus Styles for Accessibility */
        :focus-visible {
            outline: 3px solid #1976d2;
            outline-offset: 2px;
        }
        
        /* Print Styles */
        @media print {
            .auth-card {
                box-shadow: none;
                border: 1px solid #ccc;
            }
            
            .bg-animation,
            .particles,
            .hamburger,
            .password-toggle,
            .forgot-password,
            .auth-footer {
                display: none;
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

    <!-- Particle Background -->
    <div class="particles" id="particles"></div>

    <div class="auth-container">
        <div class="auth-card">
            <?php if (isset($_GET['reset_success'])): ?>
                <div class="alert alert-success">
                    <span>Password reset successfully! You can now login with your new password.</span>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">
                    <span>Registration successful! Please login with your credentials.</span>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-info">
                    <span>You have been successfully logged out.</span>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['session_expired'])): ?>
                <div class="alert alert-warning">
                    <span>Your session has expired. Please login again.</span>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['action']) && $_GET['action'] == 'verify_otp'): ?>
                <!-- OTP Verification Form -->
                <h1 class="auth-title">Verify OTP</h1>
                <p class="auth-subtitle">Enter the 6-digit OTP sent to your registered email address</p>
                
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-error">
                        <span><?php echo htmlspecialchars($login_error); ?></span>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['otp_display'])): ?>
                    <div class="demo-otp">
                        <i class="fas fa-key" style="margin-right: 8px;"></i>
                        Demo OTP: <strong><?php echo $_SESSION['otp_display']; ?></strong>
                        <br><small>(In production, this would be sent via email)</small>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="otpForm">
                    <input type="hidden" name="verify_otp" value="1">
                    
                    <div class="form-group">
                        <label for="otp">Enter OTP</label>
                        <div class="otp-container">
                            <?php for($i = 1; $i <= 6; $i++): ?>
                                <input type="text" id="otp<?php echo $i; ?>" name="otp_digit_<?php echo $i; ?>" 
                                       class="otp-input" maxlength="1" oninput="moveToNext(this, <?php echo $i; ?>)" 
                                       onkeydown="handleOtpKeyDown(event, <?php echo $i; ?>)">
                            <?php endfor; ?>
                            <input type="hidden" id="otp" name="otp">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-container">
                            <input type="password" id="new_password" name="new_password" class="form-control" 
                                   placeholder="Enter new password (min. 8 characters)" required minlength="8"
                                   oninput="checkPasswordStrength(this.value)">
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar" id="passwordStrength"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="password-container">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                   placeholder="Confirm your new password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="passwordMatch" style="margin-top: 5px; font-size: 0.9rem;"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="resetBtn">
                        <span id="resetText">Reset Password</span>
                        <div class="spinner" id="resetSpinner"></div>
                    </button>
                </form>
                
                <div class="auth-footer">
                    <a href="login.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>Back to Login
                    </a>
                </div>
                
            <?php elseif (isset($_GET['action']) && $_GET['action'] == 'forgot_password'): ?>
                <!-- Forgot Password Form -->
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">Enter your registered email to receive a password reset OTP</p>
                
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-error">
                        <span><?php echo htmlspecialchars($login_error); ?></span>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
                    </div>
                <?php endif; ?>
                
                <div class="user-type-selector">
                    <button type="button" class="user-type-btn active" data-type="patient">
                        <i class="fas fa-user-injured"></i>
                        <span>Patient</span>
                    </button>
                    <button type="button" class="user-type-btn" data-type="doctor">
                        <i class="fas fa-user-md"></i>
                        <span>Doctor</span>
                    </button>
                </div>
                
                <form method="POST" action="" id="forgotForm">
                    <input type="hidden" name="userType" id="userType" value="patient">
                    <input type="hidden" name="forgot_password" value="1">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="Enter your registered email address" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="sendOtpBtn">
                        <span id="sendOtpText">Send OTP</span>
                        <div class="spinner" id="sendOtpSpinner"></div>
                    </button>
                </form>
                
                <div class="auth-footer">
                    <a href="login.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>Back to Login
                    </a>
                </div>
                
            <?php else: ?>
                <!-- Regular Login Form -->
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to access your MediConnect account and manage your healthcare</p>
                
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-error">
                        <span><?php echo htmlspecialchars($login_error); ?></span>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
                    </div>
                <?php endif; ?>
                
                <div class="user-type-selector">
                    <button type="button" class="user-type-btn active" data-type="patient">
                        <i class="fas fa-user-injured"></i>
                        <span>Patient</span>
                    </button>
                    <button type="button" class="user-type-btn" data-type="doctor">
                        <i class="fas fa-user-md"></i>
                        <span>Doctor</span>
                    </button>
                </div>
                
                <form id="loginForm" method="POST" action="">
                    <input type="hidden" name="userType" id="userType" value="patient">
                    
                    <div class="form-group">
                        <label for="loginId" id="loginLabel">Email or Aadhaar Number</label>
                        <input type="text" id="loginId" name="loginId" class="form-control" 
                               placeholder="Enter your email or Aadhaar number" required 
                               value="<?php echo isset($_POST['loginId']) ? htmlspecialchars($_POST['loginId']) : ''; ?>"
                               autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="login-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me</span>
                        </label>
                        
                        <div class="forgot-password">
                            <a href="login.php?action=forgot_password">
                                <i class="fas fa-key" style="margin-right: 6px;"></i>Forgot Password?
                            </a>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                        <span id="loginText">Sign In</span>
                        <div class="spinner" id="loginSpinner"></div>
                    </button>
                    
                    <!-- Social Login (Optional) 
                    <div class="social-login">
                        <div class="social-divider">
                            <span>Or continue with</span>
                        </div>
                        <div class="social-buttons">
                            <button type="button" class="social-btn google" onclick="socialLogin('google')">
                                <i class="fab fa-google"></i>
                                <span>Google</span>
                            </button>
                            <button type="button" class="social-btn facebook" onclick="socialLogin('facebook')">
                                <i class="fab fa-facebook-f"></i>
                                <span>Facebook</span>
                            </button>
                        </div>
                    </div>
                    -->
                </form>
                
                <div class="auth-footer">
                    <p>Don't have an account yet?</p>
                    <a href="register.php" id="signup-link" class="btn btn-patient">
                        <i class="fas fa-user-plus" style="margin-right: 8px;"></i>
                        <span id="signupText">Sign up as Patient</span>
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
        const signupText = document.getElementById('signupText');

        userTypeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons
                userTypeBtns.forEach(b => {
                    b.classList.remove('active');
                    b.style.transform = 'translateY(0)';
                });
                
                // Add active class to clicked button
                btn.classList.add('active');
                btn.style.transform = 'translateY(-2px)';
                
                const userType = btn.getAttribute('data-type');
                userTypeInput.value = userType;
                
                // Update labels and placeholders based on user type
                if (userType === 'patient') {
                    loginLabel.textContent = 'Email or Aadhaar Number';
                    loginInput.placeholder = 'Enter your email or Aadhaar number';
                    // Update signup button for patient
                    if (signupLink) {
                        signupLink.href = 'register.php';
                        signupText.textContent = 'Sign up as Patient';
                        signupLink.classList.remove('btn-doctor');
                        signupLink.classList.add('btn-patient');
                    }
                } else {
                    loginLabel.textContent = 'Email or Doctor ID';
                    loginInput.placeholder = 'Enter your email or Doctor ID';
                    // Update signup button for doctor
                    if (signupLink) {
                        signupLink.href = 'doctorregister.php';
                        signupText.textContent = 'Sign up as Doctor';
                        signupLink.classList.remove('btn-patient');
                        signupLink.classList.add('btn-doctor');
                    }
                }
                
                // Animate the change
                loginLabel.style.animation = 'none';
                setTimeout(() => {
                    loginLabel.style.animation = 'fadeInUp 0.5s ease';
                }, 10);
            });
        });

        // Password toggle function
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Initialize signup button based on default user type (patient)
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial signup button for patient
            if (signupLink) {
                signupLink.href = 'register.php';
                signupText.textContent = 'Sign up as Patient';
                signupLink.classList.add('btn-patient');
            }
            
            // Auto-focus login input
            if (loginInput) {
                setTimeout(() => {
                    loginInput.focus();
                }, 300);
            }
            
            // Check if remember me was checked
            const rememberCheckbox = document.getElementById('remember');
            if (rememberCheckbox && localStorage.getItem('rememberLogin') === 'true') {
                rememberCheckbox.checked = true;
                const savedLoginId = localStorage.getItem('loginId');
                if (savedLoginId && loginInput) {
                    loginInput.value = savedLoginId;
                }
            }
        });

        // Form validation and submission
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const loginId = loginInput.value.trim();
                const password = document.getElementById('password').value.trim();
                const remember = document.getElementById('remember')?.checked;
                const loginBtn = document.getElementById('loginBtn');
                const loginText = document.getElementById('loginText');
                const loginSpinner = document.getElementById('loginSpinner');
                
                let isValid = true;
                let errorMessage = '';

                // Validation
                if (loginId === '') {
                    isValid = false;
                    errorMessage = 'Please enter your login ID';
                } else if (password === '') {
                    isValid = false;
                    errorMessage = 'Please enter your password';
                } else if (password.length < 6) {
                    isValid = false;
                    errorMessage = 'Password must be at least 6 characters';
                }

                if (!isValid) {
                    showAlert(errorMessage, 'error');
                    return;
                }

                // Save to localStorage if remember me is checked
                if (remember) {
                    localStorage.setItem('rememberLogin', 'true');
                    localStorage.setItem('loginId', loginId);
                } else {
                    localStorage.removeItem('rememberLogin');
                    localStorage.removeItem('loginId');
                }

                // Show loading state
                loginBtn.disabled = true;
                loginText.style.display = 'none';
                loginSpinner.style.display = 'block';

                // Submit the form
                this.submit();
            });
        }

        // Forgot password form
        const forgotForm = document.getElementById('forgotForm');
        if (forgotForm) {
            forgotForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = document.getElementById('email').value.trim();
                const sendOtpBtn = document.getElementById('sendOtpBtn');
                const sendOtpText = document.getElementById('sendOtpText');
                const sendOtpSpinner = document.getElementById('sendOtpSpinner');
                
                if (!validateEmail(email)) {
                    showAlert('Please enter a valid email address', 'error');
                    return;
                }

                // Show loading state
                sendOtpBtn.disabled = true;
                sendOtpText.style.display = 'none';
                sendOtpSpinner.style.display = 'block';

                // Submit the form
                this.submit();
            });
        }

        // OTP Form handling
        const otpForm = document.getElementById('otpForm');
        if (otpForm) {
            otpForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Combine OTP digits
                let otp = '';
                for (let i = 1; i <= 6; i++) {
                    const digit = document.getElementById('otp' + i)?.value || '';
                    otp += digit;
                }
                
                document.getElementById('otp').value = otp;
                
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const resetBtn = document.getElementById('resetBtn');
                const resetText = document.getElementById('resetText');
                const resetSpinner = document.getElementById('resetSpinner');
                
                // Validation
                if (otp.length !== 6) {
                    showAlert('Please enter the complete 6-digit OTP', 'error');
                    return;
                }
                
                if (newPassword.length < 8) {
                    showAlert('Password must be at least 8 characters', 'error');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showAlert('Passwords do not match', 'error');
                    return;
                }
                
                // Show loading state
                resetBtn.disabled = true;
                resetText.style.display = 'none';
                resetSpinner.style.display = 'block';

                // Submit the form
                this.submit();
            });
        }

        // OTP input navigation
        function moveToNext(input, currentIndex) {
            if (input.value.length === 1) {
                if (currentIndex < 6) {
                    document.getElementById('otp' + (currentIndex + 1)).focus();
                }
            }
            updateCombinedOtp();
        }

        function handleOtpKeyDown(event, currentIndex) {
            if (event.key === 'Backspace' && event.target.value === '') {
                if (currentIndex > 1) {
                    document.getElementById('otp' + (currentIndex - 1)).focus();
                }
            }
        }

        function updateCombinedOtp() {
            let otp = '';
            for (let i = 1; i <= 6; i++) {
                otp += document.getElementById('otp' + i)?.value || '';
            }
            document.getElementById('otp').value = otp;
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            if (!strengthBar) return;
            
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update strength bar
            strengthBar.className = 'strength-bar';
            if (password === '') {
                strengthBar.style.width = '0';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
            
            // Check password match
            const confirmPassword = document.getElementById('confirm_password')?.value;
            const matchIndicator = document.getElementById('passwordMatch');
            if (matchIndicator && confirmPassword) {
                if (confirmPassword === password && password !== '') {
                    matchIndicator.innerHTML = '<span style="color: #4caf50;"><i class="fas fa-check-circle"></i> Passwords match</span>';
                } else if (confirmPassword !== '' && password !== '') {
                    matchIndicator.innerHTML = '<span style="color: #f44336;"><i class="fas fa-times-circle"></i> Passwords do not match</span>';
                } else {
                    matchIndicator.innerHTML = '';
                }
            }
        }

        // Email validation
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Alert function
        function showAlert(message, type = 'error') {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());
            
            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <span>${message}</span>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
            `;
            
            // Insert at the top of the auth card
            const authCard = document.querySelector('.auth-card');
            if (authCard) {
                authCard.insertBefore(alertDiv, authCard.firstChild);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.style.opacity = '0';
                        alertDiv.style.transition = 'opacity 0.3s';
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            }
        }

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
        
        // Social login function (placeholder)
        function socialLogin(provider) {
            showAlert(`Social login with ${provider} is not implemented yet.`, 'info');
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + / focuses search/login
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                const loginInput = document.getElementById('loginId');
                if (loginInput) {
                    loginInput.focus();
                }
            }
            
            // Escape closes mobile menu
            if (e.key === 'Escape') {
                if (hamburger && hamburger.classList.contains('active')) {
                    hamburger.click();
                }
            }
        });

        // Add animation to form elements on scroll into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe form elements
        document.querySelectorAll('.form-group, .user-type-selector, .btn').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(el);
        });

        // Session timeout warning
        let timeoutWarning;
        function startSessionTimer() {
            // Clear existing timer
            if (timeoutWarning) clearTimeout(timeoutWarning);
            
            // Set new timer (10 minutes)
            timeoutWarning = setTimeout(() => {
                if (document.visibilityState === 'visible') {
                    showAlert('Your session will expire in 5 minutes due to inactivity.', 'warning');
                }
            }, 300000); // 5 minutes
        }

        // Reset timer on user activity
        ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, startSessionTimer);
        });

        // Start timer initially
        startSessionTimer();
    </script>
</body>
</html>