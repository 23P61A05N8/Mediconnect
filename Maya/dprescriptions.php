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

// Handle prescription creation
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create_prescription') {
        $patient_id = mysqli_real_escape_string($con, $_POST['patient_id']);
        $prescription_date = mysqli_real_escape_string($con, $_POST['prescription_date']);
        $diagnosis = mysqli_real_escape_string($con, $_POST['diagnosis']);
        $notes = mysqli_real_escape_string($con, $_POST['notes']);
        
        $insert_query = "INSERT INTO prescriptions (doctor_id, patient_id, prescription_date, diagnosis, notes) 
                         VALUES ('$doctor_id', '$patient_id', '$prescription_date', '$diagnosis', '$notes')";
        
        if (mysqli_query($con, $insert_query)) {
            $prescription_id = mysqli_insert_id($con);
            
            // Insert medicines
            $medicine_names = $_POST['medicine_name'];
            $dosages = $_POST['dosage'];
            $frequencies = $_POST['frequency'];
            $durations = $_POST['duration'];
            $instructions = $_POST['instructions'];
            
            for ($i = 0; $i < count($medicine_names); $i++) {
                if (!empty($medicine_names[$i])) {
                    $medicine_name = mysqli_real_escape_string($con, $medicine_names[$i]);
                    $dosage = mysqli_real_escape_string($con, $dosages[$i]);
                    $frequency = mysqli_real_escape_string($con, $frequencies[$i]);
                    $duration = mysqli_real_escape_string($con, $durations[$i]);
                    $instruction = mysqli_real_escape_string($con, $instructions[$i]);
                    
                    $med_query = "INSERT INTO prescription_medicines (prescription_id, medicine_name, dosage, frequency, duration, instructions) 
                                  VALUES ('$prescription_id', '$medicine_name', '$dosage', '$frequency', '$duration', '$instruction')";
                    mysqli_query($con, $med_query);
                }
            }
            
            $success_message = "Prescription created successfully!";
        } else {
            $error_message = "Error creating prescription: " . mysqli_error($con);
        }
    }
    
    // Handle status update
    if ($_POST['action'] == 'update_status') {
        $prescription_id = mysqli_real_escape_string($con, $_POST['prescription_id']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        
        $update_query = "UPDATE prescriptions SET status = '$status' WHERE id = '$prescription_id' AND doctor_id = '$doctor_id'";
        if (mysqli_query($con, $update_query)) {
            $success_message = "Prescription status updated!";
        } else {
            $error_message = "Error updating status";
        }
    }
}

// Get all patients for dropdown
$patients_query = "SELECT id, first_name, last_name FROM patients ORDER BY first_name";
$patients_result = mysqli_query($con, $patients_query);

// Get all prescriptions with patient details
$prescriptions_query = "SELECT p.*, 
                        CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                        pat.phone as patient_phone,
                        pat.email as patient_email
                        FROM prescriptions p
                        LEFT JOIN patients pat ON p.patient_id = pat.id
                        WHERE p.doctor_id = '$doctor_id'
                        ORDER BY p.prescription_date DESC, p.created_at DESC";
$prescriptions_result = mysqli_query($con, $prescriptions_query);

// Get statistics
$total_query = "SELECT COUNT(*) as total FROM prescriptions WHERE doctor_id = '$doctor_id'";
$total_result = mysqli_query($con, $total_query);
$total_prescriptions = mysqli_fetch_assoc($total_result)['total'];

$active_query = "SELECT COUNT(*) as active FROM prescriptions WHERE doctor_id = '$doctor_id' AND status = 'active'";
$active_result = mysqli_query($con, $active_query);
$active_prescriptions = mysqli_fetch_assoc($active_result)['active'];

$this_month_query = "SELECT COUNT(*) as this_month FROM prescriptions WHERE doctor_id = '$doctor_id' AND MONTH(prescription_date) = MONTH(CURDATE()) AND YEAR(prescription_date) = YEAR(CURDATE())";
$this_month_result = mysqli_query($con, $this_month_query);
$this_month_prescriptions = mysqli_fetch_assoc($this_month_result)['this_month'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - MediConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e3f2fd 100%);
            min-height: 100vh;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
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
            font-weight: 700;
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
            position: relative;
        }

        .nav-link:hover, .nav-link.active {
            color: #1976d2;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #1976d2;
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.3);
        }

        .btn-secondary {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: #bbdefb;
        }

        /* Main Container */
        .prescriptions-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            backdrop-filter: blur(15px);
            margin: 2rem auto;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #1976d2;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #1976d2;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card i {
            font-size: 2rem;
            color: #1976d2;
            margin-bottom: 0.5rem;
        }

        .stat-card h3 {
            font-size: 0.85rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card p {
            font-size: 2rem;
            font-weight: 700;
            color: #1976d2;
            margin-top: 0.5rem;
        }

        /* Form Styles */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e0e0e0;
        }

        .form-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #1976d2;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* Medicine Row */
        .medicine-row {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            position: relative;
        }

        .medicine-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 2fr;
            gap: 1rem;
            align-items: end;
        }

        .remove-medicine {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        /* Prescriptions Table */
        .prescriptions-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .prescriptions-table th,
        .prescriptions-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .prescriptions-table th {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: 600;
        }

        .prescriptions-table tr:hover td {
            background: #f5f7fa;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-active {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-completed {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-expired {
            background: #fee2e2;
            color: #dc2626;
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
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            width: 90%;
            max-width: 800px;
            border-radius: 16px;
            max-height: 80%;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .close {
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
        }

        .close:hover {
            opacity: 0.8;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
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
                transition: 0.3s;
                padding: 2rem 0;
            }
            
            .nav-menu.active {
                left: 0;
            }
            
            .medicine-grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .prescriptions-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="nav-brand">
                    <h2>MediConnect</h2>
                </div>
                <ul class="nav-menu">
                    <li><a href="doctordashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="appointments.php" class="nav-link">Appointments</a></li>
                    <li><a href="dprescriptions.php" class="nav-link active">Prescriptions</a></li>
                    <li><a href="doctordashboard.php" class="nav-link">Patients</a></li>
                    <li><a href="dprofile.php" class="nav-link">Profile</a></li>
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

    <div class="container">
        <div class="prescriptions-container">
            <div class="section-title">
                <i class="fas fa-prescription-bottle"></i>
                Prescriptions Management
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Total Prescriptions</h3>
                    <p><?php echo $total_prescriptions; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Active Prescriptions</h3>
                    <p><?php echo $active_prescriptions; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-month"></i>
                    <h3>This Month</h3>
                    <p><?php echo $this_month_prescriptions; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Patients</h3>
                    <p><?php echo mysqli_num_rows($patients_result); ?></p>
                </div>
            </div>

            <!-- Create Prescription Form -->
            <div class="form-card">
                <div class="form-title">
                    <i class="fas fa-plus-circle"></i>
                    Create New Prescription
                </div>
                <form method="POST" id="prescriptionForm">
                    <input type="hidden" name="action" value="create_prescription">
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Select Patient</label>
                        <select name="patient_id" class="form-control" required>
                            <option value="">-- Select Patient --</option>
                            <?php 
                            mysqli_data_seek($patients_result, 0);
                            while($patient = mysqli_fetch_assoc($patients_result)): ?>
                                <option value="<?php echo $patient['id']; ?>">
                                    <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Prescription Date</label>
                        <input type="date" name="prescription_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-stethoscope"></i> Diagnosis</label>
                        <textarea name="diagnosis" class="form-control" rows="3" placeholder="Describe the diagnosis..." required></textarea>
                    </div>
                    
                    <div class="form-title" style="margin-top: 1rem;">
                        <i class="fas fa-pills"></i>
                        Prescribed Medicines
                    </div>
                    
                    <div id="medicines-container">
                        <div class="medicine-row" data-index="0">
                            <div class="medicine-grid">
                                <div>
                                    <label>Medicine Name</label>
                                    <input type="text" name="medicine_name[]" class="form-control" placeholder="e.g., Paracetamol" required>
                                </div>
                                <div>
                                    <label>Dosage</label>
                                    <input type="text" name="dosage[]" class="form-control" placeholder="e.g., 500mg" required>
                                </div>
                                <div>
                                    <label>Frequency</label>
                                    <input type="text" name="frequency[]" class="form-control" placeholder="e.g., Twice daily" required>
                                </div>
                                <div>
                                    <label>Duration</label>
                                    <input type="text" name="duration[]" class="form-control" placeholder="e.g., 5 days" required>
                                </div>
                                <div>
                                    <label>Instructions</label>
                                    <input type="text" name="instructions[]" class="form-control" placeholder="e.g., After meals">
                                </div>
                            </div>
                            <button type="button" class="remove-medicine" onclick="removeMedicine(this)" style="display: none;">×</button>
                        </div>
                    </div>
                    
                    <button type="button" class="btn-secondary" onclick="addMedicine()" style="margin-top: 0.5rem;">
                        <i class="fas fa-plus"></i> Add Another Medicine
                    </button>
                    
                    <div class="form-group" style="margin-top: 1rem;">
                        <label><i class="fas fa-notes-medical"></i> Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional instructions for the patient..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="margin-top: 1rem; width: 100%;">
                        <i class="fas fa-save"></i> Create Prescription
                    </button>
                </form>
            </div>

            <!-- Prescriptions List -->
            <div class="form-title">
                <i class="fas fa-list"></i>
                Prescription History
            </div>
            
            <?php if (mysqli_num_rows($prescriptions_result) > 0): ?>
                <table class="prescriptions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Diagnosis</th>
                            <th>Medicines</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($prescription = mysqli_fetch_assoc($prescriptions_result)): 
                            // Get medicines for this prescription
                            $medicines_query = "SELECT * FROM prescription_medicines WHERE prescription_id = '{$prescription['id']}'";
                            $medicines_result = mysqli_query($con, $medicines_query);
                            $medicines_count = mysqli_num_rows($medicines_result);
                            
                            $status_class = '';
                            if ($prescription['status'] == 'active') $status_class = 'badge-active';
                            elseif ($prescription['status'] == 'completed') $status_class = 'badge-completed';
                            else $status_class = 'badge-expired';
                        ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($prescription['prescription_date'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($prescription['patient_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($prescription['patient_phone']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars(substr($prescription['diagnosis'], 0, 50)) . (strlen($prescription['diagnosis']) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <span class="badge badge-active">
                                        <i class="fas fa-pills"></i> <?php echo $medicines_count; ?> medicine(s)
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($prescription['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-secondary" style="padding: 0.4rem 0.8rem;" onclick="viewPrescription(<?php echo $prescription['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
                    <i class="fas fa-prescription-bottle" style="font-size: 3rem; color: #1976d2; opacity: 0.5;"></i>
                    <p style="margin-top: 1rem;">No prescriptions created yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Prescription Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-prescription-bottle"></i> Prescription Details</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal()">Close</button>
                <button class="btn-primary" onclick="printPrescription()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>

    <script>
        let medicineCount = 1;
        
        function addMedicine() {
            medicineCount++;
            const container = document.getElementById('medicines-container');
            const newRow = document.createElement('div');
            newRow.className = 'medicine-row';
            newRow.setAttribute('data-index', medicineCount);
            newRow.innerHTML = `
                <div class="medicine-grid">
                    <div>
                        <label>Medicine Name</label>
                        <input type="text" name="medicine_name[]" class="form-control" placeholder="e.g., Paracetamol" required>
                    </div>
                    <div>
                        <label>Dosage</label>
                        <input type="text" name="dosage[]" class="form-control" placeholder="e.g., 500mg" required>
                    </div>
                    <div>
                        <label>Frequency</label>
                        <input type="text" name="frequency[]" class="form-control" placeholder="e.g., Twice daily" required>
                    </div>
                    <div>
                        <label>Duration</label>
                        <input type="text" name="duration[]" class="form-control" placeholder="e.g., 5 days" required>
                    </div>
                    <div>
                        <label>Instructions</label>
                        <input type="text" name="instructions[]" class="form-control" placeholder="e.g., After meals">
                    </div>
                </div>
                <button type="button" class="remove-medicine" onclick="removeMedicine(this)">×</button>
            `;
            container.appendChild(newRow);
        }
        
        function removeMedicine(btn) {
            const row = btn.parentElement;
            if (document.querySelectorAll('.medicine-row').length > 1) {
                row.remove();
                medicineCount--;
            } else {
                alert('At least one medicine is required');
            }
        }
        
        function viewPrescription(id) {
            // Fetch prescription details via AJAX
            fetch('get_prescription.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalBody').innerHTML = data;
                    document.getElementById('viewModal').style.display = 'block';
                });
        }
        
        function closeModal() {
            document.getElementById('viewModal').style.display = 'none';
        }
        
        function printPrescription() {
            const printContent = document.getElementById('modalBody').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Prescription</title>
                        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
                        <style>
                            body { font-family: 'Inter', sans-serif; padding: 2rem; }
                            .prescription-header { text-align: center; margin-bottom: 2rem; }
                            .prescription-details { margin-bottom: 1rem; }
                            table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
                            th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
                            th { background: #1976d2; color: white; }
                            @media print { .no-print { display: none; } }
                        </style>
                    </head>
                    <body>
                        ${printContent}
                    </body>
                </html>
            `);
            printWindow.print();
        }
        
        // Mobile menu toggle
        const hamburger = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".nav-menu");
        
        if (hamburger) {
            hamburger.addEventListener("click", () => {
                hamburger.classList.toggle("active");
                navMenu.classList.toggle("active");
            });
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('viewModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php mysqli_close($con); ?>