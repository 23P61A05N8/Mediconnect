<?php
session_start();

header('Content-Type: application/json');

$host = "localhost";
$username = "root";
$password = "";
$database = "mediconnect";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$patient_name = isset($_POST['patient_name']) ? mysqli_real_escape_string($con, $_POST['patient_name']) : '';
$input_password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($patient_name) || empty($input_password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter both Patient Name and Date of Birth']);
    exit();
}

// Search patient by name (first name or last name or full name)
$query = "SELECT * FROM patients WHERE 
          first_name LIKE '%$patient_name%' OR 
          last_name LIKE '%$patient_name%' OR 
          CONCAT(first_name, ' ', last_name) LIKE '%$patient_name%'";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Patient not found. Please verify the name and try again.']);
    exit();
}

if (mysqli_num_rows($result) > 1) {
    echo json_encode(['success' => false, 'message' => 'Multiple patients found. Please provide more specific name.']);
    exit();
}

$patient = mysqli_fetch_assoc($result);
$patient_id = $patient['id'];

// Verify password - Multiple methods
$valid = false;

// Method 1: Check using password_verify (for hashed passwords)
if (password_verify($input_password, $patient['password'])) {
    $valid = true;
}
// Method 2: Direct comparison (for plain text passwords)
elseif ($input_password === $patient['password']) {
    $valid = true;
}
// Method 3: Check if password matches Date of Birth (YYYY-MM-DD format)
elseif ($input_password === $patient['dob']) {
    $valid = true;
}
// Method 4: Check if password matches DOB without hyphens
elseif ($input_password === str_replace('-', '', $patient['dob'])) {
    $valid = true;
}

if ($valid) {
    echo json_encode([
        'success' => true, 
        'message' => 'Access granted',
        'patient_id' => $patient_id
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid date of birth. Please check and try again.'
    ]);
}

mysqli_close($con);
?>