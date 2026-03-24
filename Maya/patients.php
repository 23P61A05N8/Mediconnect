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

// Get doctor data for header
$doctor_id = $_SESSION['doctor_id'];
$doctor_query = "SELECT * FROM doctors WHERE id = '$doctor_id'";
$doctor_result = mysqli_query($con, $doctor_query);
$doctor = mysqli_fetch_assoc($doctor_result);

// Get patients with appointments for this doctor
$patients_query = "
    SELECT DISTINCT p.*, a.appointment_date, a.status 
    FROM patients p 
    INNER JOIN appointments a ON p.id = a.patient_id 
    WHERE a.doctor_id = '$doctor_id' 
    ORDER BY a.appointment_date DESC
";
$patients_result = mysqli_query($con, $patients_query);

// Count statistics
$total_patients_query = "SELECT COUNT(DISTINCT patient_id) as total FROM appointments WHERE doctor_id = '$doctor_id'";
$total_patients_result = mysqli_query($con, $total_patients_query);
$total_patients = mysqli_fetch_assoc($total_patients_result)['total'];

$new_patients_query = "SELECT COUNT(DISTINCT patient_id) as new_patients FROM appointments WHERE doctor_id = '$doctor_id' AND DATE(created_at) = CURDATE()";
$new_patients_result = mysqli_query($con, $new_patients_query);
$new_patients = mysqli_fetch_assoc($new_patients_result)['new_patients'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - MediConnect</title>
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
            max-width: 1200px;
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

        .main-content {
            background: white;
            border-radius: 0 0 15px 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            color: #333;
            font-size: 32px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
            opacity: 0.9;
        }

        .patients-table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-size: 20px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e1e5ee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .patient-avatar {
            width: 40px;
            height: 40px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .patient-info {
            display: flex;
            align-items: center;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-scheduled {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d1edff;
            color: #004085;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #667eea;
            color: white;
        }

        .btn-view:hover {
            background: #5a6fd8;
        }

        .no-patients {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-patients i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                display: block;
                overflow-x: auto;
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
                <a href="patients.php" class="active">Patients</a>
                <a href="appointments.php">Appointments</a>
                <a href="update.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Patient Management</h1>
                <div class="header-actions">
                    <button class="action-btn btn-view" onclick="refreshPatients()">Refresh</button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_patients; ?></div>
                    <div class="stat-label">Total Patients</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $new_patients; ?></div>
                    <div class="stat-label">New Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo mysqli_num_rows($patients_result); ?></div>
                    <div class="stat-label">Active Cases</div>
                </div>
            </div>

            <div class="patients-table">
                <div class="table-header">
                    Recent Patients
                </div>
                <?php if (mysqli_num_rows($patients_result) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Contact</th>
                                <th>Last Appointment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($patient = mysqli_fetch_assoc($patients_result)): ?>
                                <tr>
                                    <td>
                                        <div class="patient-info">
                                            <div class="patient-avatar">
                                                <?php echo strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></strong>
                                                <br>
                                                <small>ID: <?php echo htmlspecialchars($patient['id']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($patient['email']); ?><br>
                                        <small><?php echo htmlspecialchars($patient['phone']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($patient['appointment_date'])); ?>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo strtolower($patient['status']); ?>">
                                            <?php echo htmlspecialchars($patient['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn btn-view" onclick="viewPatient(<?php echo $patient['id']; ?>)">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-patients">
                        <div>👥</div>
                        <h3>No Patients Yet</h3>
                        <p>Patients will appear here once they book appointments with you.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function viewPatient(patientId) {
            alert('Viewing patient details for ID: ' + patientId);
            // Here you can implement modal or redirect to patient details page
        }

        function refreshPatients() {
            location.reload();
        }
    </script>
</body>
</html>