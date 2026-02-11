<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get Fee + Student + Class details
    $sql = "SELECT fees.*, students.full_name, students.admission_no, students.father_name, classes.class_name 
            FROM fees 
            JOIN students ON fees.student_id = students.id 
            LEFT JOIN classes ON students.class_id = classes.id 
            WHERE fees.id = '$id'";
            
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo $data['id']; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #eee; padding: 30px; }
        .receipt { width: 400px; background: white; padding: 20px; margin: 0 auto; border: 1px dashed #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .total { border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 10px 0; margin-top: 10px; font-weight: bold; font-size: 18px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #555; }
        .btn-print { display: block; width: 100%; padding: 10px; margin-top: 20px; background: #333; color: white; border: none; cursor: pointer; }
        @media print { .btn-print { display: none; } body { background: white; } }
    </style>
</head>
<body>

<div class="receipt">
    <div class="header">
        <h2>MY SCHOOL ERP</h2>
        <p>Receipt Voucher</p>
    </div>

    <div class="row"><span>Receipt No:</span> <strong>#<?php echo $data['id']; ?></strong></div>
    <div class="row"><span>Date:</span> <span><?php echo date('d-M-Y', strtotime($data['payment_date'])); ?></span></div>
    <hr>
    
    <div class="row"><span>Student:</span> <span><?php echo $data['full_name']; ?></span></div>
    <div class="row"><span>Adm No:</span> <span><?php echo $data['admission_no']; ?></span></div>
    <div class="row"><span>Class:</span> <span><?php echo $data['class_name']; ?></span></div>
    <div class="row"><span>Father:</span> <span><?php echo $data['father_name']; ?></span></div>
    
    <br>
    <div class="row"><span>Payment For:</span> <span><?php echo $data['month_name']; ?></span></div>
    
    <div class="row total">
        <span>TOTAL PAID:</span>
        <span>₹<?php echo number_format($data['amount']); ?></span>
    </div>

    <div class="footer">
        <p>Authorized Signature</p>
        <p>Thank you for the payment.</p>
    </div>

    <button onclick="window.print()" class="btn-print">PRINT RECEIPT</button>
</div>

</body>
</html>