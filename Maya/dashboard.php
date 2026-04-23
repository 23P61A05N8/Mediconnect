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
    // User not found, logout
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = mysqli_fetch_assoc($user_result);

// Create documents table if not exists (with all columns)
$create_documents_table = "
CREATE TABLE IF NOT EXISTS patient_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_type VARCHAR(100),
    document_category ENUM('report', 'prescription', 'xray', 'scan', 'other') DEFAULT 'other',
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (patient_id)
)";

mysqli_query($con, $create_documents_table);

// Check if document_category column exists, if not add it
$check_column = mysqli_query($con, "SHOW COLUMNS FROM patient_documents LIKE 'document_category'");
if(mysqli_num_rows($check_column) == 0) {
    mysqli_query($con, "ALTER TABLE patient_documents ADD COLUMN document_category ENUM('report', 'prescription', 'xray', 'scan', 'other') DEFAULT 'other' AFTER document_type");
}

// Create uploads directory if not exists
$upload_dir = "uploads/patient_documents/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle file upload
$upload_message = "";
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $target_dir = $upload_dir;
    
    $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES["document"]["name"]));
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $file_size = $_FILES["document"]["size"];
    $document_name = mysqli_real_escape_string($con, $_POST['document_name'] ?: $_FILES["document"]["name"]);
    $document_category = mysqli_real_escape_string($con, $_POST['document_category']);
    
    // Allowed file types
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
    
    if (in_array($file_type, $allowed_types)) {
        if ($file_size <= 10 * 1024 * 1024) { // 10MB limit
            if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
                $insert_query = "INSERT INTO patient_documents (patient_id, document_name, document_type, document_category, file_path, file_size) 
                                 VALUES ('$user_id', '$document_name', '$file_type', '$document_category', '$target_file', '$file_size')";
                
                if (mysqli_query($con, $insert_query)) {
                    $upload_message = "Document uploaded successfully!";
                    $upload_success = true;
                } else {
                    $upload_message = "Database error: " . mysqli_error($con);
                }
            } else {
                $upload_message = "Error uploading file.";
            }
        } else {
            $upload_message = "File size must be less than 10MB.";
        }
    } else {
        $upload_message = "Invalid file type. Allowed: " . implode(', ', $allowed_types);
    }
}

// Handle file deletion
if (isset($_GET['delete_doc'])) {
    $doc_id = mysqli_real_escape_string($con, $_GET['delete_doc']);
    $get_file_query = "SELECT file_path FROM patient_documents WHERE id = '$doc_id' AND patient_id = '$user_id'";
    $file_result = mysqli_query($con, $get_file_query);
    
    if ($file_row = mysqli_fetch_assoc($file_result)) {
        $file_path = $file_row['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $delete_query = "DELETE FROM patient_documents WHERE id = '$doc_id' AND patient_id = '$user_id'";
        mysqli_query($con, $delete_query);
        $upload_message = "Document deleted successfully!";
    }
}

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Build documents query based on filter (with error handling for missing column)
$documents_query = "SELECT * FROM patient_documents WHERE patient_id = '$user_id'";

if ($filter == 'reports') {
    $documents_query .= " AND document_category = 'report'";
} elseif ($filter == 'prescriptions') {
    $documents_query .= " AND document_category = 'prescription'";
} elseif ($filter == 'xray') {
    $documents_query .= " AND document_category IN ('xray', 'scan')";
}

$documents_query .= " ORDER BY uploaded_at DESC";
$documents_result = mysqli_query($con, $documents_query);

// Get document counts by category with error handling
$count_all = 0;
$count_reports = 0;
$count_prescriptions = 0;
$count_xrays = 0;

$all_result = mysqli_query($con, "SELECT COUNT(*) as count FROM patient_documents WHERE patient_id = '$user_id'");
if ($all_result) {
    $count_all = mysqli_fetch_assoc($all_result)['count'];
}

$reports_result = mysqli_query($con, "SELECT COUNT(*) as count FROM patient_documents WHERE patient_id = '$user_id' AND document_category = 'report'");
if ($reports_result) {
    $count_reports = mysqli_fetch_assoc($reports_result)['count'];
}

$prescriptions_result = mysqli_query($con, "SELECT COUNT(*) as count FROM patient_documents WHERE patient_id = '$user_id' AND document_category = 'prescription'");
if ($prescriptions_result) {
    $count_prescriptions = mysqli_fetch_assoc($prescriptions_result)['count'];
}

$xrays_result = mysqli_query($con, "SELECT COUNT(*) as count FROM patient_documents WHERE patient_id = '$user_id' AND document_category IN ('xray', 'scan')");
if ($xrays_result) {
    $count_xrays = mysqli_fetch_assoc($xrays_result)['count'];
}

// Prepare user data for display
$full_name = $user['first_name'] . ' ' . $user['last_name'];
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

// Fetch prescriptions for this patient
$prescriptions_query = "SELECT p.*, 
                        CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                        d.specialization
                        FROM prescriptions p
                        LEFT JOIN doctors d ON p.doctor_id = d.id
                        WHERE p.patient_id = '$user_id'
                        ORDER BY p.prescription_date DESC, p.created_at DESC";
$prescriptions_result = mysqli_query($con, $prescriptions_query);

// Get active prescriptions count
$active_prescriptions_count = 0;
$active_prescriptions_query = "SELECT COUNT(*) as count FROM prescriptions WHERE patient_id = '$user_id' AND status = 'active'";
$active_prescriptions_result = mysqli_query($con, $active_prescriptions_query);
if ($active_prescriptions_result) {
    $active_prescriptions_count = mysqli_fetch_assoc($active_prescriptions_result)['count'];
}

// Get total prescriptions count
$total_prescriptions_count = mysqli_num_rows($prescriptions_result);
?>

<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Internal CSS for dashboard.php - Enhanced with index.php styles */
        
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
            display: inline-block;
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
        
        /* Dashboard Layout */
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: calc(100vh - 80px);
            position: relative;
            z-index: 2;
        }
        
        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            border-right: 1px solid #e0e0e0;
            height: 100%;
            position: sticky;
            top: 0;
            padding: 1rem 0;
            backdrop-filter: blur(10px);
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 1rem;
        }
        
        .sidebar-header h2 {
            color: #1976d2;
            font-weight: 700;
        }
        
        .sidebar-nav {
            padding: 0 1rem;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .sidebar-nav .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding-bottom: 2rem;
        }
        
        .header-bar {
            height: 60px;
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        
        .profile-dropdown {
            position: relative;
        }
        
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        
        .profile-btn:hover {
            background-color: #e3f2fd;
        }
        
        .profile-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 200px;
            padding: 0.5rem 0;
            display: none;
            z-index: 1000;
            border: 1px solid #e0e0e0;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            padding: 0.75rem 1rem;
            display: block;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
            font-weight: 500;
        }
        
        .dropdown-item:hover {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 0.5rem 0;
        }
        
        .content {
            padding: 2rem;
        }
        
        .tab-content {
            display: none;
            animation: fadeInUp 0.5s ease-out;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .section-title {
            margin-bottom: 1.5rem;
            color: #1976d2;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card h3 {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1976d2;
        }
        
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }
        
        .appointments-table th, 
        .appointments-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .appointments-table th {
            background-color: #e3f2fd;
            font-weight: 600;
            color: #1976d2;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #e8f5e8;
            color: #388e3c;
        }
        
        .badge-warning {
            background-color: #fff3e0;
            color: #f57c00;
        }
        
        .badge-error {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-group {
            margin-bottom: 1rem;
        }

        .info-group label {
            font-size: 0.875rem;
            color: #666;
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .info-group p {
            font-weight: 500;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .info-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
            font-size: 0.875rem;
            color: #666;
        }

        .verification-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .edit-btn {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        }
        
        /* Document Upload Styles */
        .upload-area {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 2px dashed #1976d2;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1976d2;
        }
        
        select.form-control {
            background: white;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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
        
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background: #1976d2;
            color: white;
            border-color: #1976d2;
        }
        
        .filter-btn:hover:not(.active) {
            background: #e3f2fd;
        }
        
        .document-list {
            display: grid;
            gap: 1rem;
        }
        
        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .document-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .document-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #1976d2;
        }
        
        .document-details h4 {
            margin-bottom: 0.25rem;
        }
        
        .document-details p {
            font-size: 0.75rem;
            color: #666;
        }
        
        .document-category {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 0.25rem;
        }
        
        .category-report {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .category-prescription {
            background: #e8f5e8;
            color: #388e3c;
        }
        
        .category-xray {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .category-scan {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .document-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn-outline {
            background: white;
            border: 1px solid #ddd;
            color: #333;
        }
        
        .btn-outline:hover {
            background: #e3f2fd;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background: #c82333;
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                height: auto;
                position: static;
                display: none;
            }
            
            .sidebar.active {
                display: block;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .info-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .document-item {
                flex-direction: column;
                gap: 1rem;
            }
            
            .document-actions {
                width: 100%;
                justify-content: center;
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

    <div class="dashboard">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Patient Dashboard</h2>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="#dashboard" class="nav-link <?php echo $current_tab == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#appointments" class="nav-link">
                        <i class="fas fa-calendar-alt"></i> Appointments
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#personal-info" class="nav-link">
                        <i class="fas fa-user"></i> Personal Info
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#medical-history" class="nav-link">
                        <i class="fas fa-hospital"></i> Medical History
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#current-treatment" class="nav-link">
                        <i class="fas fa-pills"></i> Current Treatment
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#documents" class="nav-link">
                        <i class="fas fa-file-medical"></i> Documents
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#medicines" class="nav-link">
                        <i class="fas fa-capsules"></i> Medicines
                    </a>
                
            </nav>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <div class="header-bar">
                <div></div> <!-- Empty div for spacing -->
                <div class="profile-dropdown">
                    <button class="profile-btn">
                        <div class="profile-img"><?php echo $initials; ?></div>
                        <span><?php echo htmlspecialchars($full_name); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="editprofile.php" class="dropdown-item">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <a href="privacy.php" class="dropdown-item">
                            <i class="fas fa-shield-alt"></i> Privacy
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="content">
                <!-- Dashboard Overview -->
                <div id="dashboard" class="tab-content <?php echo $current_tab == 'dashboard' ? 'active' : ''; ?>">
                    <h2 class="section-title">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
                    
                    <div class="stats-grid">
    <div class="stat-card">
        <h3>Total Documents</h3>
        <p><?php echo $count_all; ?></p>
    </div>
    <div class="stat-card">
        <h3>Medical Reports</h3>
        <p><?php echo $count_reports; ?></p>
    </div>
    <div class="stat-card">
        <h3>Active Prescriptions</h3>
        <p><?php echo $active_prescriptions_count; ?></p>
    </div>
    <div class="stat-card">
        <h3>X-Rays & Scans</h3>
        <p><?php echo $count_xrays; ?></p>
    </div>
</div>
                    
                    <h3 class="section-title">Recent Activity</h3>
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_docs = mysqli_query($con, "SELECT * FROM patient_documents WHERE patient_id = '$user_id' ORDER BY uploaded_at DESC LIMIT 3");
                            if(mysqli_num_rows($recent_docs) > 0) {
                                while($doc = mysqli_fetch_assoc($recent_docs)) {
                                    $category_label = isset($doc['document_category']) ? ucfirst($doc['document_category']) : 'Document';
                                    echo "<tr>
                                        <td>" . date('M d, Y', strtotime($doc['uploaded_at'])) . "</td>
                                        <td>" . htmlspecialchars($doc['document_name']) . "</td>
                                        <td>$category_label</td>
                                        <td><span class='badge badge-success'>Uploaded</span></td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align: center'>No documents uploaded yet</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Appointments -->
                <div id="appointments" class="tab-content">
                    <h2 class="section-title">My Appointments</h2>
                    <table class="appointments-table">
                    <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2026-11-15</td>
                                <td>Blood Test Results</td>
                                <td>Lab Report</td>
                                <td><span class="badge badge-success">Upcoming</span></td>
                            </tr>
                            <tr>
                                <td>2026-11-10</td>
                                <td>Dr. Smith Consultation</td>
                                <td>Appointment</td>
                                <td><span class="badge badge-success">Upcoming</span></td>
                            </tr>
                            <tr>
                                <td>2026-11-05</td>
                                <td>X-Ray Scan</td>
                                <td>Diagnostic</td>
                                <td><span class="badge badge-success">Upcoming</span></td>
                            </tr>
                            <tr>
                                <td>2026-11-20</td>
                                <td>Follow-up with Dr. Johnson</td>
                                <td>Appointment</td>
                                <td><span class="badge badge-warning">Upcoming</span></td>
                            </tr>
                        </tbody>
</table>
                </div>
                
                <!-- Personal Information -->
                <div id="personal-info" class="tab-content">
                    <h2 class="section-title">Personal Information</h2>
                    <div class="info-card">
                        <div class="info-header">
                            <h3>Basic Details</h3>
                            <button class="edit-btn" onclick="alert('Edit functionality will be implemented soon.')">Edit</button>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-group">
                                <label>First Name</label>
                                <p><?php echo htmlspecialchars($user['first_name']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Last Name</label>
                                <p><?php echo htmlspecialchars($user['last_name']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Email</label>
                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Phone</label>
                                <p><?php echo htmlspecialchars($user['phone']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Date of Birth</label>
                                <p><?php echo htmlspecialchars($user['dob']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Gender</label>
                                <p><?php echo ucfirst(htmlspecialchars($user['gender'])); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Blood Group</label>
                                <p><?php echo htmlspecialchars($user['blood_group'] ?: 'Not specified'); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Address</label>
                                <p><?php echo htmlspecialchars($user['address']); ?></p>
                            </div>
                        </div>
                        
                        <div class="info-footer">
                            <p>Last updated: <?php echo date('d/m/Y', strtotime($user['updated_at'] ?? 'now')); ?></p>
                            <div class="verification-badge">
                                <i class="fas fa-check-circle" style="color: #388e3c;"></i>
                                <span>Information verified</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Medical History -->
                <div id="medical-history" class="tab-content">
                    <h2 class="section-title">Medical History</h2>
                    <div class="info-card">
                        <div class="info-group">
                            <label>Allergies</label>
                            <p><?php echo htmlspecialchars($user['allergies'] ?: 'None reported'); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Current Medications</label>
                            <p><?php echo htmlspecialchars($user['current_medications'] ?: 'None'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Current Treatment -->
                <div id="current-treatment" class="tab-content">
                    <h2 class="section-title">Current Treatment</h2>
                    <div class="info-card">
                        <p>Your active treatments will appear here.</p>
                    </div>
                </div>
                
                <!-- Documents -->
                <div id="documents" class="tab-content">
                    <h2 class="section-title">My Documents</h2>
                    
                    <?php if ($upload_message): ?>
                        <div class="alert alert-<?php echo $upload_success ? 'success' : 'danger'; ?>">
                            <?php echo $upload_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload Area -->
                    <div class="upload-area">
                        <h3 style="margin-bottom: 1rem; color: #1976d2;">
                            <i class="fas fa-cloud-upload-alt"></i> Upload New Document
                        </h3>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Document Name</label>
                                <input type="text" name="document_name" class="form-control" placeholder="e.g., X-Ray Report - 2024">
                            </div>
                            <div class="form-group">
                                <label>Document Category</label>
                                <select name="document_category" class="form-control" required>
                                    <option value="report">Medical Report</option>
                                    <option value="prescription">Prescription</option>
                                    <option value="xray">X-Ray</option>
                                    <option value="scan">CT/MRI Scan</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select File</label>
                                <input type="file" name="document" class="form-control" required accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                                <small>Allowed: JPG, PNG, PDF, DOC (Max 10MB)</small>
                            </div>
                            <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem;">
                                <i class="fas fa-upload"></i> Upload Document
                            </button>
                        </form>
                    </div>
                    
                    <!-- Filter Bar -->
                    <div class="filter-bar">
                        <a href="?tab=documents&filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> All Documents (<?php echo $count_all; ?>)
                        </a>
                        <a href="?tab=documents&filter=reports" class="filter-btn <?php echo $filter == 'reports' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i> Reports (<?php echo $count_reports; ?>)
                        </a>
                        <a href="?tab=documents&filter=prescriptions" class="filter-btn <?php echo $filter == 'prescriptions' ? 'active' : ''; ?>">
                            <i class="fas fa-prescription-bottle"></i> Prescriptions (<?php echo $count_prescriptions; ?>)
                        </a>
                        <a href="?tab=documents&filter=xray" class="filter-btn <?php echo $filter == 'xray' ? 'active' : ''; ?>">
                            <i class="fas fa-x-ray"></i> X-Rays & Scans (<?php echo $count_xrays; ?>)
                        </a>
                    </div>
                    
                    <!-- Document List -->
                    <div class="info-card">
                        <div class="document-list">
                            <?php if (mysqli_num_rows($documents_result) > 0): ?>
                                <?php while ($doc = mysqli_fetch_assoc($documents_result)): ?>
                                    <div class="document-item">
                                        <div class="document-info">
                                            <div class="document-icon">
                                                <?php
                                                $icon = 'fa-file';
                                                if (in_array($doc['document_type'], ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'fa-file-image';
                                                elseif ($doc['document_type'] == 'pdf') $icon = 'fa-file-pdf';
                                                elseif (in_array($doc['document_type'], ['doc', 'docx'])) $icon = 'fa-file-word';
                                                ?>
                                                <i class="fas <?php echo $icon; ?>"></i>
                                            </div>
                                            <div class="document-details">
                                                <h4><?php echo htmlspecialchars($doc['document_name']); ?></h4>
                                                <p>
                                                    Uploaded: <?php echo date('d M Y, h:i A', strtotime($doc['uploaded_at'])); ?>
                                                    | Size: <?php echo round($doc['file_size'] / 1024, 2); ?> KB
                                                </p>
                                                <?php if(isset($doc['document_category'])): ?>
                                                <span class="document-category category-<?php echo $doc['document_category']; ?>">
                                                    <?php echo ucfirst($doc['document_category']); ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="document-actions">
                                            <a href="<?php echo $doc['file_path']; ?>" target="_blank" class="btn-sm btn-outline">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="<?php echo $doc['file_path']; ?>" download class="btn-sm btn-outline">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                            <a href="?delete_doc=<?php echo $doc['id']; ?>&tab=documents&filter=<?php echo $filter; ?>" 
                                               class="btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this document?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="text-align: center; padding: 2rem; color: #666;">
                                    <i class="fas fa-folder-open" style="font-size: 48px; margin-bottom: 1rem; display: block;"></i>
                                    No documents found. Upload your first document above.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Medicines -->
                <!-- Prescriptions / Medicines -->
<div id="medicines" class="tab-content">
    <h2 class="section-title"><i class="fas fa-prescription-bottle"></i> My Prescriptions</h2>
    
    <?php 
    // Re-fetch prescriptions to ensure latest data
    $prescriptions_query = "SELECT p.*, 
                            CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                            d.specialization
                            FROM prescriptions p
                            LEFT JOIN doctors d ON p.doctor_id = d.id
                            WHERE p.patient_id = '$user_id'
                            ORDER BY p.prescription_date DESC, p.created_at DESC";
    $prescriptions_result = mysqli_query($con, $prescriptions_query);
    $total_prescriptions = mysqli_num_rows($prescriptions_result);
    ?>
    
    <?php if ($total_prescriptions > 0): ?>
        <?php while($prescription = mysqli_fetch_assoc($prescriptions_result)): 
            // Fetch medicines for this prescription
            $medicines_query = "SELECT * FROM prescription_medicines WHERE prescription_id = '{$prescription['id']}'";
            $medicines_result = mysqli_query($con, $medicines_query);
            $medicines_count = mysqli_num_rows($medicines_result);
            
            $status_class = '';
            $status_icon = '';
            if ($prescription['status'] == 'active') {
                $status_class = 'badge-success';
                $status_icon = 'fas fa-check-circle';
            } elseif ($prescription['status'] == 'completed') {
                $status_class = 'badge-info';
                $status_icon = 'fas fa-check-double';
            } else {
                $status_class = 'badge-error';
                $status_icon = 'fas fa-clock';
            }
        ?>
            <div class="info-card" style="margin-bottom: 1.5rem;">
                <div class="info-header">
                    <h3 style="color: #1976d2;">
                        <i class="fas fa-stethoscope"></i> 
                        Prescription #<?php echo $prescription['id']; ?>
                        <span style="font-size: 0.8rem; font-weight: normal; margin-left: 0.5rem;">
                            (<?php echo $medicines_count; ?> medicine<?php echo $medicines_count > 1 ? 's' : ''; ?>)
                        </span>
                    </h3>
                    <span class="badge <?php echo $status_class; ?>">
                        <i class="<?php echo $status_icon; ?>"></i>
                        <?php echo ucfirst($prescription['status']); ?>
                    </span>
                </div>
                
                <div style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e0e0e0;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                        <div><strong><i class="fas fa-calendar"></i> Date:</strong> <?php echo date('d M Y', strtotime($prescription['prescription_date'])); ?></div>
                        <div><strong><i class="fas fa-user-md"></i> Doctor:</strong> Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?></div>
                        <div><strong><i class="fas fa-stethoscope"></i> Specialization:</strong> <?php echo htmlspecialchars($prescription['specialization']); ?></div>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <strong><i class="fas fa-notes-medical"></i> Diagnosis:</strong>
                    <p style="margin-top: 0.25rem; background: #f5f7fa; padding: 0.75rem; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?>
                    </p>
                </div>
                
                <div>
                    <strong><i class="fas fa-pills"></i> Prescribed Medicines:</strong>
                    <table class="appointments-table" style="margin-top: 0.5rem;">
                        <thead>
                            <tr>
                                <th style="background: #e3f2fd;">Medicine Name</th>
                                <th style="background: #e3f2fd;">Dosage</th>
                                <th style="background: #e3f2fd;">Frequency</th>
                                <th style="background: #e3f2fd;">Duration</th>
                                <th style="background: #e3f2fd;">Instructions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($medicines_result) > 0): ?>
                                <?php while($med = mysqli_fetch_assoc($medicines_result)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($med['medicine_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($med['frequency']); ?></td>
                                        <td><?php echo htmlspecialchars($med['duration']); ?></td>
                                        <td><?php echo htmlspecialchars($med['instructions'] ?: '-'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center;">No medicines listed</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($prescription['notes'])): ?>
                    <div style="margin-top: 1rem;">
                        <strong><i class="fas fa-comment"></i> Additional Notes:</strong>
                        <p style="margin-top: 0.25rem; padding: 0.75rem; background: #fff8e1; border-radius: 8px; color: #666;">
                            <?php echo nl2br(htmlspecialchars($prescription['notes'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 1rem; padding-top: 0.5rem; border-top: 1px solid #e0e0e0;">
                    <small style="color: #6b7280;">
                        <i class="fas fa-clock"></i> Prescribed on: <?php echo date('d M Y, h:i A', strtotime($prescription['created_at'])); ?>
                    </small>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="info-card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-prescription-bottle" style="font-size: 3rem; color: #1976d2; opacity: 0.5;"></i>
            <p style="margin-top: 1rem; color: #6b7280;">No prescriptions yet. Your doctor will prescribe medications here.</p>
        </div>
    <?php endif; ?>
</div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                document.querySelectorAll('.sidebar-nav .nav-link').forEach(el => {
                    el.classList.remove('active');
                });
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Get the href attribute
                let href = this.getAttribute('href');
                
                // Show the selected tab content
                const targetTab = document.querySelector(href);
                if (targetTab) {
                    targetTab.classList.add('active');
                }
                
                // Handle filter parameter for View Reports link
                const filterParam = this.getAttribute('data-filter');
                if (filterParam) {
                    // Update URL with filter parameter and reload to apply filter
                    window.location.href = '?tab=documents&filter=' + filterParam;
                }
            });
        });
        
        // Handle filter parameter on page load
        const urlParams = new URLSearchParams(window.location.search);
        const filterParam = urlParams.get('filter');
        const tabParam = urlParams.get('tab');
        
        if (filterParam && tabParam === 'documents') {
            // Activate documents tab
            const docsLink = document.querySelector('.sidebar-nav .nav-link[href="#documents"]');
            if (docsLink) {
                docsLink.classList.add('active');
            }
            const targetTab = document.querySelector('#documents');
            if (targetTab) {
                document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
                targetTab.classList.add('active');
            }
        } else if (tabParam && tabParam !== 'dashboard') {
            // Activate the specified tab
            const targetLink = document.querySelector(`.sidebar-nav .nav-link[href="#${tabParam}"]`);
            if (targetLink) {
                targetLink.click();
            }
        }
        
        // Profile dropdown toggle
        const profileBtn = document.querySelector('.profile-btn');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        
        if (profileBtn) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });
        }
        
        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.profile-dropdown') && dropdownMenu) {
                dropdownMenu.classList.remove('show');
            }
        });
        
        // Mobile menu toggle
        const hamburger = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".nav-menu");
        const sidebar = document.querySelector(".sidebar");

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
    </script>
</body>
</html>