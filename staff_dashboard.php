<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_info = $conn->query("SELECT * FROM staff WHERE id='$user_id'")->fetch_assoc();
$my_role = $user_info['role'];

// Fetch Notices (All + Role Specific)
$notices = $conn->query("SELECT * FROM noticeboard WHERE target_role IN ('all', '$my_role') ORDER BY created_at DESC LIMIT 5");
?>

<div class="sidebar">
    <div class="sidebar-header">
        <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;"><?php echo $SCHOOL_SETTINGS['school_name'] ?? 'School ERP'; ?></small>
        <h5 class="mt-1"><?php echo ucfirst($my_role); ?> Panel</h5>
    </div>
    
    <a href="staff_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>

    <?php if($my_role == 'teacher'): ?>
        <a href="take_attendance.php"><i class="fas fa-user-check"></i> Take Attendance</a>
        <a href="enter_marks.php"><i class="fas fa-marker"></i> Enter Marks</a>
    <?php endif; ?>

    <?php if($my_role == 'accountant'): ?>
        <a href="fees.php"><i class="fas fa-money-bill-wave"></i> Collect Fees</a>
    <?php endif; ?>

    <a href="profile.php"><i class="fas fa-user-shield"></i> My Profile</a>
    <a href="login.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>

</div>

<div class="content">
    <h3 class="fw-bold mb-4">Welcome, <?php echo $user_info['full_name']; ?></h3>

    <?php if ($notices->num_rows > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-bell"></i> <strong>Noticeboard</strong>
                </div>
                <div class="card-body">
                    <?php while($note = $notices->fetch_assoc()): ?>
                        <div class="alert alert-light border mb-2">
                            <strong><?php echo $note['title']; ?>:</strong> <?php echo $note['message']; ?>
                            <small class="text-muted float-end"><?php echo date('d M', strtotime($note['created_at'])); ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>