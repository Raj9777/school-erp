<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// --- 1. SAVE SETTINGS ---
if (isset($_POST['save_settings'])) {
    $name = $_POST['school_name'];
    $addr = $_POST['school_address'];
    $phone = $_POST['school_phone'];
    $email = $_POST['school_email'];
    $session = $_POST['current_session'];
    $footer = $_POST['footer_text'];

    // Update the single row (ID=1)
    $sql = "UPDATE settings SET 
            school_name='$name', 
            school_address='$addr', 
            school_phone='$phone', 
            school_email='$email', 
            current_session='$session',
            footer_text='$footer' 
            WHERE id=1";

    if ($conn->query($sql)) {
        $msg = "<div class='alert alert-success'>✅ Settings Updated! Refresh to see changes.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// --- 2. FETCH CURRENT SETTINGS ---
$settings = $conn->query("SELECT * FROM settings WHERE id=1")->fetch_assoc();
?>

<div class="sidebar">
    <div class="sidebar-header"><h4>🏫 ADMIN PANEL</h4></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="staff.php"><i class="fas fa-users-cog"></i> Staff</a>
    <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
    <a href="classes.php"><i class="fas fa-school"></i> Classes</a>
    <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
    <a href="exams.php"><i class="fas fa-edit"></i> Exams</a>
    <a href="fees.php"><i class="fas fa-money-bill-wave"></i> Fees</a>
    <a href="settings.php" class="active"><i class="fas fa-cogs"></i> System Settings</a> <a href="login.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4"><i class="fas fa-cogs text-primary"></i> General Settings</h3>
    <?php if(isset($msg)) echo $msg; ?>

    <div class="card card-box shadow-sm">
        <div class="card-header bg-dark text-white">School Information</div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>School Name</label>
                        <input type="text" name="school_name" class="form-control" value="<?php echo $settings['school_name']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Current Academic Session</label>
                        <input type="text" name="current_session" class="form-control" value="<?php echo $settings['current_session']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Address</label>
                    <input type="text" name="school_address" class="form-control" value="<?php echo $settings['school_address']; ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Contact Phone</label>
                        <input type="text" name="school_phone" class="form-control" value="<?php echo $settings['school_phone']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Contact Email</label>
                        <input type="text" name="school_email" class="form-control" value="<?php echo $settings['school_email']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Footer Text</label>
                    <input type="text" name="footer_text" class="form-control" value="<?php echo $settings['footer_text']; ?>">
                </div>

                <button type="submit" name="save_settings" class="btn btn-primary">💾 Save Configuration</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>