<?php
session_start();

if (!isset($_SESSION['doctor_id'])) {
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "mediconnect";

$con = mysqli_connect($host, $username, $password, $database);

$prescription_id = isset($_GET['id']) ? mysqli_real_escape_string($con, $_GET['id']) : 0;

$query = "SELECT p.*, 
          CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
          pat.dob, pat.gender, pat.phone, pat.email, pat.address,
          CONCAT(doc.first_name, ' ', doc.last_name) as doctor_name,
          doc.specialization
          FROM prescriptions p
          LEFT JOIN patients pat ON p.patient_id = pat.id
          LEFT JOIN doctors doc ON p.doctor_id = doc.id
          WHERE p.id = '$prescription_id'";
$result = mysqli_query($con, $query);

if ($prescription = mysqli_fetch_assoc($result)) {
    $medicines_query = "SELECT * FROM prescription_medicines WHERE prescription_id = '$prescription_id'";
    $medicines_result = mysqli_query($con, $medicines_query);
    ?>
    
    <div class="prescription-header" style="text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #1976d2; padding-bottom: 1rem;">
        <h2 style="color: #1976d2;">MediConnect Hospital</h2>
        <p>Digital Prescription</p>
    </div>
    
    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
        <div>
            <strong>Prescription ID:</strong> #<?php echo $prescription['id']; ?><br>
            <strong>Date:</strong> <?php echo date('d M Y', strtotime($prescription['prescription_date'])); ?>
        </div>
        <div>
            <strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?><br>
            <strong>Specialization:</strong> <?php echo htmlspecialchars($prescription['specialization']); ?>
        </div>
    </div>
    
    <div style="background: #f5f7fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <h3 style="color: #1976d2; margin-bottom: 0.5rem;">Patient Information</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
            <div><strong>Name:</strong> <?php echo htmlspecialchars($prescription['patient_name']); ?></div>
            <div><strong>DOB:</strong> <?php echo htmlspecialchars($prescription['dob']); ?></div>
            <div><strong>Gender:</strong> <?php echo ucfirst($prescription['gender']); ?></div>
            <div><strong>Phone:</strong> <?php echo htmlspecialchars($prescription['phone']); ?></div>
        </div>
    </div>
    
    <h3 style="color: #1976d2; margin-bottom: 1rem;">Diagnosis</h3>
    <p style="background: #f5f7fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?>
    </p>
    
    <h3 style="color: #1976d2; margin-bottom: 1rem;">Prescribed Medicines</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="background: #1976d2; color: white; padding: 0.75rem; text-align: left;">Medicine</th>
                <th style="background: #1976d2; color: white; padding: 0.75rem; text-align: left;">Dosage</th>
                <th style="background: #1976d2; color: white; padding: 0.75rem; text-align: left;">Frequency</th>
                <th style="background: #1976d2; color: white; padding: 0.75rem; text-align: left;">Duration</th>
                <th style="background: #1976d2; color: white; padding: 0.75rem; text-align: left;">Instructions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($med = mysqli_fetch_assoc($medicines_result)): ?>
            <tr>
                <td style="padding: 0.75rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($med['medicine_name']); ?></td>
                <td style="padding: 0.75rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($med['dosage']); ?></td>
                <td style="padding: 0.75rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($med['frequency']); ?></td>
                <td style="padding: 0.75rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($med['duration']); ?></td>
                <td style="padding: 0.75rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($med['instructions']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <?php if (!empty($prescription['notes'])): ?>
        <h3 style="color: #1976d2; margin-top: 1.5rem; margin-bottom: 0.5rem;">Additional Notes</h3>
        <p style="background: #f5f7fa; padding: 1rem; border-radius: 8px;">
            <?php echo nl2br(htmlspecialchars($prescription['notes'])); ?>
        </p>
    <?php endif; ?>
    
    <div style="margin-top: 2rem; text-align: center; padding-top: 1rem; border-top: 1px dashed #ddd;">
        <p>This is a computer-generated prescription. No signature required.</p>
        <p style="font-size: 0.8rem; color: #666;">Valid for 30 days from date of issue</p>
    </div>
    
    <?php
}

mysqli_close($con);
?>