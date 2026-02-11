<?php
include 'db_connect.php';

// Fetch Global Settings
$settings = $conn->query("SELECT * FROM settings WHERE id=1")->fetch_assoc();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get Student + Class Details
    $sql = "SELECT students.*, classes.class_name, classes.section 
            FROM students 
            LEFT JOIN classes ON students.class_id = classes.id 
            WHERE students.id = '$id'";
    $student = $conn->query($sql)->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ID Card - <?php echo $student['full_name']; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #555; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; }
        
        /* The Card Container - Standard CR80 Size (85.6mm x 54mm) */
        .id-card {
            width: 350px;
            height: 220px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, #ffffff 60%, #e3f2fd 60%);
        }

        /* Header Design */
        .header {
            background: #0d6efd; /* School Primary Color */
            color: white;
            padding: 10px;
            text-align: center;
            height: 50px;
        }
        .header h3 { margin: 0; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; }
        .header small { font-size: 10px; opacity: 0.9; }

        /* Photo & Content */
        .content { display: flex; padding: 15px; align-items: center; }
        
        .photo-area {
            width: 90px;
            height: 110px;
            border: 2px solid #0d6efd;
            padding: 2px;
            margin-right: 15px;
            background: white;
        }
        .photo-area img { width: 100%; height: 100%; object-fit: cover; }

        .details { flex: 1; font-size: 12px; color: #333; }
        .details p { margin: 4px 0; }
        .details strong { color: #000; display: inline-block; width: 60px; }
        
        .st-name {
            font-size: 16px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: #333;
            color: white;
            text-align: center;
            padding: 5px;
            font-size: 10px;
        }

        /* Signature Area */
        .signature {
            position: absolute;
            bottom: 30px;
            right: 20px;
            text-align: center;
        }
        .signature img { width: 60px; height: auto; display: block; margin-bottom: 2px; }
        .signature span { font-size: 9px; border-top: 1px solid #333; padding-top: 2px; }

        .btn-print {
            position: fixed; top: 20px; right: 20px;
            padding: 10px 20px; background: white; border: none; font-weight: bold; cursor: pointer;
        }

        /* Print Settings to hide background and buttons */
        @media print {
            body { background: white; margin: 0; padding: 0; display: block; }
            .id-card { box-shadow: none; border: 1px solid #ddd; margin: 20px; page-break-inside: avoid; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> PRINT CARD</button>

<div class="id-card">
    <div class="header">
        <h3><?php echo $settings['school_name']; ?></h3>
        <small><?php echo $settings['school_address']; ?></small>
    </div>

    <div class="content">
        <div class="photo-area">
            <?php 
            $img_path = "uploads/" . $student['photo'];
            if (!file_exists($img_path) || $student['photo'] == "") {
                $img_path = "https://via.placeholder.com/90x110?text=Photo"; 
            }
            ?>
            <img src="<?php echo $img_path; ?>">
        </div>

        <div class="details">
            <div class="st-name"><?php echo $student['full_name']; ?></div>
            <p><strong>Class:</strong> <?php echo $student['class_name']; ?> (<?php echo $student['section']; ?>)</p>
            <p><strong>Adm No:</strong> <?php echo $student['admission_no']; ?></p>
            <p><strong>DOB:</strong> <?php echo $student['dob']; ?></p>
            <p><strong>Father:</strong> <?php echo $student['father_name']; ?></p>
            <p><strong>Phone:</strong> <?php echo $student['phone']; ?></p>
        </div>
    </div>

    <div class="signature">
        <span>Principal Signature</span>
    </div>

    <div class="footer">
        <?php echo $settings['school_email']; ?> | <?php echo $settings['school_phone']; ?>
    </div>
</div>

</body>
</html>