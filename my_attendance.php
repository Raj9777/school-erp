<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
// Query must match the dashboard logic
$attendance = $conn->query("SELECT * FROM attendance WHERE student_id = '$student_id' ORDER BY date DESC");
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="student_dashboard.php"><i class="fas fa-home"></i> My Dashboard</a>
    <a href="my_attendance.php" class="active"><i class="fas fa-calendar-check"></i> Attendance History</a>
    <a href="my_report_card.php"><i class="fas fa-file-alt"></i> Report Card</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4">Attendance History</h3>
    <div class="card card-box shadow-sm">
        <div class="card-body">
            <?php if ($attendance->num_rows > 0): ?>
            <table class="table table-striped">
                <thead><tr><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                    <?php while($row = $attendance->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                        <td>
                            <?php if($row['status'] == 'present'): ?>
                                <span class="badge bg-success">Present</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Absent</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert alert-warning">No attendance records found yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>