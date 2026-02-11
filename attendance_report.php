<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch Records if filter applied
$attendance_data = [];
if (isset($_GET['view_report'])) {
    $date = $_GET['date'];
    $class_id = $_GET['class_id'];
    
    $sql = "SELECT attendance.*, students.full_name, students.admission_no 
            FROM attendance 
            JOIN students ON attendance.student_id = students.id 
            WHERE attendance.date = '$date' AND students.class_id = '$class_id'";
    
    $attendance_data = $conn->query($sql);
}

$class_list = $conn->query("SELECT * FROM classes");
?>

<div class="sidebar">
    <div class="sidebar-header"><h4>🏫 ADMIN PANEL</h4></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="staff.php"><i class="fas fa-users-cog"></i> Staff</a>
    <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
    <a href="attendance_report.php" class="active"><i class="fas fa-calendar-check"></i> Attendance</a>
    <a href="fees.php"><i class="fas fa-money-bill-wave"></i> Fees</a>
    <a href="login.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
    <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
</div>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fas fa-chart-bar text-primary"></i> Attendance Report</h3>
        <a href="take_attendance.php" class="btn btn-primary"><i class="fas fa-plus"></i> Take New Attendance</a>
    </div>

    <div class="card card-box shadow-sm mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <label>Select Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">-- Class --</option>
                            <?php while($c = $class_list->fetch_assoc()) { echo "<option value='" . $c['id'] . "'>" . $c['class_name'] . "</option>"; } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Select Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="view_report" class="btn btn-dark w-100">View Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($attendance_data)): ?>
    <div class="card card-box shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Report for <?php echo date('d M Y', strtotime($_GET['date'])); ?></h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Adm No</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($attendance_data->num_rows > 0) {
                        while($row = $attendance_data->fetch_assoc()) {
                            $status_color = ($row['status'] == 'present') ? 'text-success' : (($row['status'] == 'absent') ? 'text-danger' : 'text-warning');
                            echo "<tr>
                                    <td>" . $row['admission_no'] . "</td>
                                    <td>" . $row['full_name'] . "</td>
                                    <td class='fw-bold $status_color'>" . strtoupper($row['status']) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center text-muted'>Attendance not taken for this date.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>