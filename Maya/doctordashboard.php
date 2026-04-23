<?php
session_start();

// Check if doctor is logged in
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

// Get today's appointments
$today = date('Y-m-d');
$appointments_query = "SELECT a.*, p.first_name, p.last_name, p.phone, p.email, p.dob, p.blood_group, p.address, p.allergies, p.current_medications
                      FROM appointments a 
                      LEFT JOIN patients p ON a.patient_id = p.id 
                      WHERE a.doctor_id = '$doctor_id' AND a.appointment_date = '$today' 
                      ORDER BY a.appointment_time";
$appointments_result = mysqli_query($con, $appointments_query);

// Get all patients (distinct) for the view patient feature
$all_patients_query = "SELECT DISTINCT p.id, p.first_name, p.last_name, p.email, p.phone, p.dob 
                       FROM patients p 
                       INNER JOIN appointments a ON p.id = a.patient_id 
                       WHERE a.doctor_id = '$doctor_id' 
                       ORDER BY p.first_name";
$all_patients_result = mysqli_query($con, $all_patients_query);

// Get total patients
$patients_query = "SELECT COUNT(DISTINCT patient_id) as total_patients FROM appointments WHERE doctor_id = '$doctor_id'";
$patients_result = mysqli_query($con, $patients_query);
$total_patients = mysqli_fetch_assoc($patients_result)['total_patients'];

// Get pending appointments
$pending_query = "SELECT COUNT(*) as pending FROM appointments WHERE doctor_id = '$doctor_id' AND status = 'scheduled'";
$pending_result = mysqli_query($con, $pending_query);
$pending_appointments = mysqli_fetch_assoc($pending_result)['pending'];

// Get completed appointments
$completed_query = "SELECT COUNT(*) as completed FROM appointments WHERE doctor_id = '$doctor_id' AND status = 'completed'";
$completed_result = mysqli_query($con, $completed_query);
$completed_appointments = mysqli_fetch_assoc($completed_result)['completed'];

// Handle patient view request
$view_patient_id = isset($_GET['view_patient']) ? $_GET['view_patient'] : null;
$patient_data = null;
$patient_documents = null;
$patient_appointments = null;

if ($view_patient_id) {
    // Get patient details
    $patient_query = "SELECT * FROM patients WHERE id = '$view_patient_id'";
    $patient_result = mysqli_query($con, $patient_query);
    $patient_data = mysqli_fetch_assoc($patient_result);
    
    // Get patient documents
    $documents_query = "SELECT * FROM patient_documents WHERE patient_id = '$view_patient_id' ORDER BY uploaded_at DESC";
    $patient_documents = mysqli_query($con, $documents_query);
    
    // Get patient appointments with this doctor
    $appointments_history_query = "SELECT * FROM appointments WHERE patient_id = '$view_patient_id' AND doctor_id = '$doctor_id' ORDER BY appointment_date DESC, appointment_time DESC";
    $patient_appointments = mysqli_query($con, $appointments_history_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - MediConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 70px;
            --primary: #1976d2;
            --secondary: #0d47a1;
            --accent: #42a5f5;
            --text: #1f2937;
            --light: #f9fafb;
            --gray: #e5e7eb;
            --success: #16a34a;
            --warning: #ea580c;
            --error: #dc2626;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            color: var(--text);
            background: linear-gradient(135deg, #f5f7fa 0%, #e3f2fd 100%);
            min-height: 100vh;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(25, 118, 210, 0.1);
            height: 100vh;
            position: sticky;
            top: 0;
            padding: 1.5rem 0;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.08);
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(25, 118, 210, 0.1);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .sidebar-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sidebar-nav {
            padding: 0 1rem;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            cursor: pointer;
        }
        
        .nav-link:hover, .nav-link.active {
            background: linear-gradient(135deg, rgba(25, 118, 210, 0.1), rgba(66, 165, 245, 0.05));
            color: var(--primary);
            transform: translateX(5px);
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .main-content {
            padding-bottom: 2rem;
        }
        
        /* Header */
        .header {
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(25, 118, 210, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        }
        
        .header-title {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.25rem;
        }
        
        .profile-dropdown {
            position: relative;
        }
        
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .profile-btn:hover {
            background: rgba(25, 118, 210, 0.1);
        }
        
        .profile-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(25, 118, 210, 0.3);
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 12px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            width: 220px;
            padding: 0.5rem 0;
            display: none;
            z-index: 1000;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .dropdown-item:hover {
            background: rgba(25, 118, 210, 0.1);
            color: var(--primary);
        }
        
        .content {
            padding: 2rem;
        }
        
        .section-title {
            margin-bottom: 1.5rem;
            color: var(--primary);
            font-size: 1.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(25, 118, 210, 0.3);
        }
        
        .welcome-section h1 {
            font-size: 2.25rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card p {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        /* Tables */
        .appointments-table, .patients-table, .documents-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .appointments-table th, .appointments-table td,
        .patients-table th, .patients-table td,
        .documents-table th, .documents-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(25, 118, 210, 0.1);
        }
        
        .appointments-table th, .patients-table th, .documents-table th {
            background: linear-gradient(135deg, rgba(25, 118, 210, 0.1), rgba(66, 165, 245, 0.05));
            font-weight: 600;
            color: var(--primary);
        }
        
        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success { background: #dcfce7; color: var(--success); }
        .badge-warning { background: #ffedd5; color: var(--warning); }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideDown 0.3s;
        }
        
        .modal-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 16px 16px 0 0;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray);
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: white;
        }
        
        .close:hover {
            opacity: 0.8;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray);
            border-radius: 8px;
        }
        
        .patient-dashboard {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .info-group label {
            font-size: 0.75rem;
            color: #6b7280;
            display: block;
        }
        
        .info-group p {
            font-weight: 600;
            margin-top: 0.25rem;
        }
        
        .document-icon {
            width: 40px;
            height: 40px;
            background: rgba(25, 118, 210, 0.1);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>MediConnect</h2>
                <p style="color: var(--primary); font-weight: 600; margin-top: 0.5rem;">Doctor Portal</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="doctordashboard.php" class="nav-link <?php echo !$view_patient_id ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </div>
                <div class="nav-item">
    <a href="#" class="nav-link" onclick="openPatientModal(); return false;">
        <i class="fas fa-user-injured"></i> View Patients
    </a>
</div>
                <div class="nav-item">
                    <a href="appointments.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i> Appointments
                    </a>
                </div>
                <div class="nav-item">
                    <a href="dprescriptions.php" class="nav-link">
                        <i class="fas fa-prescription"></i> Prescriptions
                    </a>
                </div>
                <div class="nav-item">
                    <a href="dprofile.php" class="nav-link">
                        <i class="fas fa-user-md"></i> Profile
                    </a>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
    <div class="header-title">
        <i class="fas fa-stethoscope"></i> Doctor Dashboard
    </div>
    <div class="profile-dropdown">
        <button class="profile-btn" id="profileBtn">
            <div class="profile-img"><?php echo $initials; ?></div>
            <span class="profile-name">Dr. <?php echo htmlspecialchars($full_name); ?></span>
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </button>
        <div class="dropdown-menu" id="dropdownMenu">
            <a href="dprofile.php" class="dropdown-item">
                <i class="fas fa-user-circle"></i> My Profile
            </a>
            <a href="dsettings.php" class="dropdown-item">
                <i class="fas fa-sliders-h"></i> Settings
            </a>
            <a href="davailability.php" class="dropdown-item">
                <i class="fas fa-clock"></i> Availability
            </a>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item logout-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</header>
            
            <div class="content">
                <?php if ($view_patient_id && $patient_data): ?>
                    <!-- Patient Complete Dashboard -->
                    <div class="welcome-section" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                        <h1><i class="fas fa-user-injured"></i> <?php echo htmlspecialchars($patient_data['first_name'] . ' ' . $patient_data['last_name']); ?></h1>
                        <p>Patient ID: <?php echo $patient_data['id']; ?> • Registered: <?php echo date('d M Y', strtotime($patient_data['created_at'])); ?></p>
                    </div>
                    
                    <!-- Personal Information -->
                    <div class="patient-dashboard">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">
                            <i class="fas fa-user-circle"></i> Personal Information
                        </h3>
                        <div class="info-grid">
                            <div class="info-group">
                                <label>Full Name</label>
                                <p><?php echo htmlspecialchars($patient_data['first_name'] . ' ' . $patient_data['last_name']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Email</label>
                                <p><?php echo htmlspecialchars($patient_data['email']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Phone</label>
                                <p><?php echo htmlspecialchars($patient_data['phone']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Date of Birth</label>
                                <p><?php echo htmlspecialchars($patient_data['dob']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Gender</label>
                                <p><?php echo ucfirst(htmlspecialchars($patient_data['gender'])); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Blood Group</label>
                                <p><?php echo htmlspecialchars($patient_data['blood_group'] ?: 'Not specified'); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Address</label>
                                <p><?php echo htmlspecialchars($patient_data['address']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Medical History -->
                    <div class="patient-dashboard">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">
                            <i class="fas fa-notes-medical"></i> Medical History
                        </h3>
                        <div class="info-grid">
                            <div class="info-group">
                                <label>Allergies</label>
                                <p><?php echo htmlspecialchars($patient_data['allergies'] ?: 'None reported'); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Current Medications</label>
                                <p><?php echo htmlspecialchars($patient_data['current_medications'] ?: 'None'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documents/Reports -->
                    <div class="patient-dashboard">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">
                            <i class="fas fa-file-medical"></i> Medical Reports & Documents
                        </h3>
                        <?php if ($patient_documents && mysqli_num_rows($patient_documents) > 0): ?>
                            <table class="documents-table">
                                <thead>
                                    <tr>
                                        <th>Document Name</th>
                                        <th>Category</th>
                                        <th>Uploaded Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($doc = mysqli_fetch_assoc($patient_documents)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?php echo ucfirst(htmlspecialchars($doc['document_category'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($doc['uploaded_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo $doc['file_path']; ?>" target="_blank" class="btn btn-outline" style="padding: 0.25rem 0.75rem;">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-folder-open"></i> No documents uploaded yet
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Appointment History -->
                    <div class="patient-dashboard">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">
                            <i class="fas fa-calendar-alt"></i> Appointment History
                        </h3>
                        <?php if ($patient_appointments && mysqli_num_rows($patient_appointments) > 0): ?>
                            <table class="appointments-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($apt = mysqli_fetch_assoc($patient_appointments)): ?>
                                        <tr>
                                            <td><?php echo date('d M Y', strtotime($apt['appointment_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($apt['reason']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $apt['status'] == 'completed' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($apt['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No appointment history found.</p>
                        <?php endif; ?>
                        
                        <div style="margin-top: 1rem;">
                            <a href="doctordashboard.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Regular Dashboard View -->
                    <div class="welcome-section">
                        <h1>Welcome, Dr. <?php echo htmlspecialchars($full_name); ?>!</h1>
                        <p><i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars($doctor['specialization']); ?> • <i class="fas fa-hospital"></i> MediConnect Hospital</p>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Today's Appointments</h3>
                            <p><?php echo mysqli_num_rows($appointments_result); ?></p>
                        </div>
                        <div class="stat-card">
                            <h3>Total Patients</h3>
                            <p><?php echo $total_patients; ?></p>
                        </div>
                        <div class="stat-card">
                            <h3>Pending Appointments</h3>
                            <p><?php echo $pending_appointments; ?></p>
                        </div>
                        <div class="stat-card">
                            <h3>Completed</h3>
                            <p><?php echo $completed_appointments; ?></p>
                        </div>
                    </div>
                    
                    <h3 class="section-title"><i class="fas fa-calendar-day"></i> Today's Appointments</h3>
                    <?php if (mysqli_num_rows($appointments_result) > 0): ?>
                        <table class="appointments-table">
                            <thead>
                                <tr><th>Time</th><th>Patient</th><th>Contact</th><th>Status</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php while($apt = mysqli_fetch_assoc($appointments_result)): ?>
                                    <tr>
                                        <td><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['phone']); ?></td>
                                        <td><span class="badge badge-warning">Scheduled</span></td>
                                        <td>
                                            <a href="?view_patient=<?php echo $apt['patient_id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.75rem;">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="patient-dashboard" style="text-align: center;">
                            <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--primary); opacity: 0.5;"></i>
                            <p>No appointments scheduled for today.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Patient Login Modal -->
   <!-- Patient Login Modal -->
<div id="patientModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close">&times;</span>
            <h2><i class="fas fa-user-injured"></i> View Patient Records</h2>
            <p>Enter patient credentials to access medical records</p>
        </div>
        <div class="modal-body">
            <form id="patientLoginForm">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Patient Name</label>
                    <input type="text" id="patient_name" class="form-control" placeholder="Enter patient's full name" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Date of Birth</label>
                    <input type="password" id="patient_password" class="form-control" placeholder="YYYY-MM-DD" required>
                    <small>Format: YYYY-MM-DD</small>
                </div>
                <div id="loginError" style="color: #dc2626; display: none; margin-top: 0.5rem; font-size: 0.875rem;"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="validatePatient()">View Records</button>
        </div>
    </div>
</div>
    
    <script>
        // Profile dropdown
        // Modal functions for patient name
// Modal functions for patient name
const modal = document.getElementById('patientModal');

function openPatientModal() {
    modal.style.display = 'block';
    document.getElementById('patient_name').value = '';
    document.getElementById('patient_password').value = '';
    document.getElementById('loginError').style.display = 'none';
}

function closeModal() {
    modal.style.display = 'none';
}

if (document.querySelector('.close')) {
    document.querySelector('.close').onclick = closeModal;
}

window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

function validatePatient() {
    const patientName = document.getElementById('patient_name').value.trim();
    const password = document.getElementById('patient_password').value.trim();
    const errorDiv = document.getElementById('loginError');
    
    if (!patientName || !password) {
        errorDiv.innerHTML = 'Please enter both Patient Name and Date of Birth';
        errorDiv.style.display = 'block';
        return;
    }
    
    // Validate date format (YYYY-MM-DD)
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(password)) {
        errorDiv.innerHTML = 'Please enter Date of Birth in YYYY-MM-DD format';
        errorDiv.style.display = 'block';
        return;
    }
    
    errorDiv.style.display = 'none';
    errorDiv.innerHTML = '';
    
    // Show loading state
    const viewBtn = document.querySelector('#patientModal .btn-primary');
    const originalText = viewBtn.innerHTML;
    viewBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    viewBtn.disabled = true;
    
    // Send AJAX request to validate patient
    fetch('validate_patient.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'patient_name=' + encodeURIComponent(patientName) + '&password=' + encodeURIComponent(password)
    })
    .then(response => response.json())
    .then(data => {
        viewBtn.innerHTML = originalText;
        viewBtn.disabled = false;
        
        if (data.success) {
            // Redirect to view patient dashboard
            window.location.href = 'doctordashboard.php?view_patient=' + data.patient_id;
        } else {
            errorDiv.innerHTML = data.message;
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        viewBtn.innerHTML = originalText;
        viewBtn.disabled = false;
        errorDiv.innerHTML = 'Unable to verify. Please try again.';
        errorDiv.style.display = 'block';
    });
}

// Enter key press in modal
document.getElementById('patient_password').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        validatePatient();
    }
});
   // Profile dropdown functionality
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');

profileBtn.addEventListener('click', function (e) {
    e.stopPropagation(); // prevent bubbling
    dropdownMenu.classList.toggle('show');
});

// Close dropdown when clicking outside
window.addEventListener('click', function (e) {
    if (!profileBtn.contains(e.target)) {
        dropdownMenu.classList.remove('show');
    }
});
    </script>
</body>
</html>
<?php mysqli_close($con); ?>