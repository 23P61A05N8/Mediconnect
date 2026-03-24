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

// Get all appointments
$appointments_query = "SELECT a.*, p.first_name, p.last_name, p.phone, p.email 
                      FROM appointments a 
                      LEFT JOIN patients p ON a.patient_id = p.id 
                      WHERE a.doctor_id = '$doctor_id' 
                      ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$appointments_result = mysqli_query($con, $appointments_query);

// Get appointment statistics
$today = date('Y-m-d');
$today_query = "SELECT COUNT(*) as today_count FROM appointments WHERE doctor_id = '$doctor_id' AND appointment_date = '$today'";
$today_result = mysqli_query($con, $today_query);
$today_appointments = mysqli_fetch_assoc($today_result)['today_count'];

$upcoming_query = "SELECT COUNT(*) as upcoming_count FROM appointments WHERE doctor_id = '$doctor_id' AND appointment_date > '$today' AND status = 'scheduled'";
$upcoming_result = mysqli_query($con, $upcoming_query);
$upcoming_appointments = mysqli_fetch_assoc($upcoming_result)['upcoming_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments - MediConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Enhanced Base Styles */
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Enhanced Header */
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

        .nav-link:hover {
            color: #1976d2;
        }

        .nav-link.active {
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
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.3);
        }

        /* Enhanced Appointments Container */
        .appointments-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin: 2rem auto;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out;
        }

        .appointments-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(25, 118, 210, 0.05) 0%, rgba(227, 242, 253, 0.1) 100%);
            z-index: 0;
        }

        .appointments-container > * {
            position: relative;
            z-index: 1;
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

        /* Enhanced Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-left: 4px solid #1976d2;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(25, 118, 210, 0.05), transparent);
            transition: left 0.5s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .stat-card i {
            font-size: 2.5rem;
            color: #1976d2;
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-card p {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1976d2;
            margin: 0;
        }

        /* Enhanced Appointments Table */
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .appointments-table th, 
        .appointments-table td {
            padding: 1.25rem;
            text-align: left;
            border-bottom: 1px solid rgba(25, 118, 210, 0.1);
        }

        .appointments-table th {
            background: linear-gradient(135deg, rgba(25, 118, 210, 0.1), rgba(66, 165, 245, 0.05));
            font-weight: 600;
            color: #1976d2;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .appointments-table tr:last-child td {
            border-bottom: none;
        }

        .appointments-table tr:hover td {
            background: rgba(25, 118, 210, 0.03);
        }

        /* Enhanced Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #16a34a;
        }

        .badge-warning {
            background: linear-gradient(135deg, #ffedd5, #fed7aa);
            color: #ea580c;
        }

        .badge-error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #dc2626;
        }

        .badge-info {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
        }

        /* Enhanced Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1976d2, #0d47a1);
            color: white;
            box-shadow: 0 4px 15px rgba(25, 118, 210, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #1976d2;
            color: #1976d2;
        }

        .btn-outline:hover {
            background: #1976d2;
            color: white;
        }

        /* Enhanced Empty State */
        .empty-state {
            background: rgba(255, 255, 255, 0.8);
            padding: 3rem 2rem;
            border-radius: 16px;
            text-align: center;
            color: #6b7280;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 4rem;
            color: #1976d2;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            color: #1976d2;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
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

        /* Enhanced Background Animation */
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

        /* Mobile Menu Styles */
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

        /* Responsive Design */
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

            .appointments-container {
                padding: 2rem;
                margin: 1rem auto;
            }

            .section-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .appointments-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 480px) {
            .appointments-container {
                padding: 1.5rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .appointments-table th, 
            .appointments-table td {
                padding: 0.75rem;
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
                    <li><a href="doctordashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="appointments.php" class="nav-link active">Appointments</a></li>
                    <li><a href="patients.php" class="nav-link">Patients</a></li>
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
        <div class="appointments-container">
            <h2 class="section-title">Appointments Management</h2>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-calendar-day"></i>
                    <h3>Today's Appointments</h3>
                    <p><?php echo $today_appointments; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-week"></i>
                    <h3>Upcoming Appointments</h3>
                    <p><?php echo $upcoming_appointments; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Appointments</h3>
                    <p><?php echo mysqli_num_rows($appointments_result); ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Pending Actions</h3>
                    <p><?php echo $upcoming_appointments; ?></p>
                </div>
            </div>

            <h3 style="color: #1976d2; margin-bottom: 1.5rem; font-size: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-list"></i> All Appointments
            </h3>
            
            <?php if (mysqli_num_rows($appointments_result) > 0): ?>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Patient Name</th>
                            <th>Contact</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($appointment = mysqli_fetch_assoc($appointments_result)): 
                            $status_class = '';
                            $status_icon = '';
                            switch($appointment['status']) {
                                case 'scheduled': 
                                    $status_class = 'badge-warning';
                                    $status_icon = 'fas fa-clock';
                                    break;
                                case 'completed': 
                                    $status_class = 'badge-success';
                                    $status_icon = 'fas fa-check';
                                    break;
                                case 'cancelled': 
                                    $status_class = 'badge-error';
                                    $status_icon = 'fas fa-times';
                                    break;
                                default: 
                                    $status_class = 'badge-info';
                                    $status_icon = 'fas fa-info';
                            }
                        ?>
                            <tr>
                                <td style="font-weight: 600; color: #1976d2;">
                                    <div><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></div>
                                    <div style="font-size: 0.9rem; color: #666;">
                                        <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>
                                        <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
                                    <div style="font-size: 0.9rem; color: #666;"><?php echo htmlspecialchars($appointment['email']); ?></div>
                                </td>
                                <td>
                                    <i class="fas fa-phone" style="margin-right: 0.5rem; color: #1976d2;"></i>
                                    <?php echo htmlspecialchars($appointment['phone']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['reason']); ?></td>
                                <td>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <i class="<?php echo $status_icon; ?>"></i>
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] == 'scheduled'): ?>
                                        <a href="dconsultation.php?appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-play"></i> Start
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-outline">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Appointments Found</h3>
                    <p>You don't have any appointments scheduled yet.</p>
                    <a href="dschedule.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-calendar-plus"></i> Manage Schedule
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="auth-footer" style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(224, 224, 224, 0.8);">
                <p><a href="doctordashboard.php" style="color: #1976d2; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a></p>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const hamburger = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".nav-menu");

        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("active");
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll(".nav-link").forEach(n => n.addEventListener("click", () => {
            hamburger.classList.remove("active");
            navMenu.classList.remove("active");
        }));

        // Add animation to elements when they come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                }
            });
        }, observerOptions);

        // Observe all stat cards and table rows
        document.querySelectorAll('.stat-card, .appointments-table tbody tr').forEach(element => {
            element.style.opacity = '0';
            observer.observe(element);
        });
    </script>
</body>
</html>
<?php mysqli_close($con); ?>