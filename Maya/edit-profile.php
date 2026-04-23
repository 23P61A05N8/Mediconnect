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

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = mysqli_fetch_assoc($user_result);
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $dob = mysqli_real_escape_string($con, $_POST['dob']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $blood_group = mysqli_real_escape_string($con, $_POST['blood_group']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $allergies = mysqli_real_escape_string($con, $_POST['allergies']);
    $current_medications = mysqli_real_escape_string($con, $_POST['current_medications']);
    
    $update_query = "UPDATE patients SET 
                     first_name = '$first_name',
                     last_name = '$last_name',
                     email = '$email',
                     phone = '$phone',
                     dob = '$dob',
                     gender = '$gender',
                     blood_group = '$blood_group',
                     address = '$address',
                     allergies = '$allergies',
                     current_medications = '$current_medications',
                     updated_at = NOW()
                     WHERE id = '$user_id'";
    
    if (mysqli_query($con, $update_query)) {
        $success_message = "Profile updated successfully!";
        // Refresh user data
        $user_result = mysqli_query($con, $user_query);
        $user = mysqli_fetch_assoc($user_result);
    } else {
        $error_message = "Error updating profile: " . mysqli_error($con);
    }
}

$full_name = $user['first_name'] . ' ' . $user['last_name'];
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles - Matching Dashboard */
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
        
        /* Animated Background - Same as Dashboard */
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
        
        .shape-1 { width: 100px; height: 100px; top: 10%; left: 10%; animation-delay: 0s; }
        .shape-2 { width: 150px; height: 150px; top: 60%; right: 10%; animation-delay: -5s; }
        .shape-3 { width: 80px; height: 80px; bottom: 20%; left: 20%; animation-delay: -10s; }
        .shape-4 { width: 120px; height: 120px; top: 30%; right: 20%; animation-delay: -15s; }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(20px, 20px) rotate(90deg); }
            50% { transform: translate(0, 40px) rotate(180deg); }
            75% { transform: translate(-20px, 20px) rotate(270deg); }
        }
        
        /* Header Styles - Matching Dashboard */
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
        
        .btn-primary {
            background-color: #1976d2;
            color: white;
            padding: 0.4rem 1.2rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
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
        .main-wrapper {
            position: relative;
            z-index: 2;
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        /* Card Styles - Matching Dashboard */
        .info-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            background: rgba(255, 255, 255, 0.98);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            color: #1976d2;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .back-link {
            color: #1976d2;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1976d2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }
        
        select.form-control {
            background: white;
            cursor: pointer;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        /* Alert Messages - Matching Dashboard */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Button Styles - Matching Dashboard */
        .btn-save {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-wrapper {
                padding: 0 1rem;
                margin: 1rem auto;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .navbar {
                padding: 0.4rem 0;
                min-height: 55px;
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
            
            .hamburger {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background - Same as Dashboard -->
    <div class="bg-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <!-- Header - Same as Dashboard -->
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
                    <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
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

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="info-card">
            <div class="card-header">
                <h2><i class="fas fa-user-edit"></i> Edit Personal Information</h2>
                <a href="dashboard.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($user['dob']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo $user['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tint"></i> Blood Group</label>
                        <select name="blood_group" class="form-control">
                            <option value="">Select Blood Group</option>
                            <option value="A+" <?php echo $user['blood_group'] == 'A+' ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo $user['blood_group'] == 'A-' ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo $user['blood_group'] == 'B+' ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo $user['blood_group'] == 'B-' ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo $user['blood_group'] == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo $user['blood_group'] == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo $user['blood_group'] == 'O+' ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo $user['blood_group'] == 'O-' ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Address</label>
                        <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-allergies"></i> Allergies (if any)</label>
                        <input type="text" name="allergies" class="form-control" value="<?php echo htmlspecialchars($user['allergies']); ?>" placeholder="e.g., Penicillin, Dust, Pollen">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-pills"></i> Current Medications</label>
                        <input type="text" name="current_medications" class="form-control" value="<?php echo htmlspecialchars($user['current_medications']); ?>" placeholder="List any medications you're currently taking">
                    </div>
                    
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
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
    </script>
</body>
</html>