<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// --- SAVE ATTENDANCE ---
if (isset($_POST['save_attendance'])) {
    $date = $_POST['date'];
    $staff_ids = $_POST['staff_id'];
    $statuses = $_POST['status'];

    foreach ($staff_ids as $id) {
        $status = $statuses[$id];
        
        // Check existing
        $check = $conn->query("SELECT * FROM staff_attendance WHERE staff_id='$id' AND date='$date'");
        
        if ($check->num_rows > 0) {
            $conn->query("UPDATE staff_attendance SET status='$status' WHERE staff_id='$id' AND date='$date'");
        } else {
            $conn->query("INSERT INTO staff_attendance (staff_id, date, status) VALUES ('$id', '$date', '$status')");
        }
    }
    $msg = "<div class='alert alert-success'>✅ Staff Attendance Saved for $date</div>";
}

// --- FETCH STAFF LIST ---
$staff_list = $conn->query("SELECT * FROM staff ORDER BY role ASC, full_name ASC");
?>

<div class="sidebar">
    <div class="sidebar-header"><h4>🏫 ADMIN PANEL</h4></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="staff.php"><i class="fas fa-users-cog"></i> Staff</a>
    <a href="staff_attendance.php" class="active"><i class="fas fa-clock"></i> Staff Attendance</a> <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
    <a href="classes.php"><i class="fas fa-school"></i> Classes</a>
    <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
    <a href="exams.php"><i class="fas fa-edit"></i> Exams</a>
    <a href="fees.php"><i class="fas fa-money-bill-wave"></i> Fees</a>
    <a href="login.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4"><i class="fas fa-clock text-primary"></i> Staff Attendance</h3>
    <?php if($msg) echo $msg; ?>

    <div class="card card-box shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Mark Attendance</span>
            <form method="GET" class="d-flex">
                <input type="date" name="date" class="form-control form-control-sm" value="<?php echo $date; ?>" onchange="this.form.submit()">
            </form>
        </div>
        <div class="card-body p-0">
            <form method="POST">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Role</th>
                            <th>Staff Name</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($staff_list->num_rows > 0) {
                            while($row = $staff_list->fetch_assoc()) {
                                $sid = $row['id'];
                                
                                // Fetch existing status if any
                                $exist = $conn->query("SELECT status FROM staff_attendance WHERE staff_id='$sid' AND date='$date'")->fetch_assoc();
                                $status = $exist ? $exist['status'] : 'present';

                                // Role Badge Color
                                $badge = ($row['role'] == 'teacher') ? 'bg-success' : 'bg-secondary';
                                ?>
                                <tr>
                                    <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($row['role']); ?></span></td>
                                    <td><strong><?php echo $row['full_name']; ?></strong></td>
                                    <td class="text-center">
                                        <input type="hidden" name="staff_id[]" value="<?php echo $sid; ?>">
                                        
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="p_<?php echo $sid; ?>" value="present" <?php if($status=='present') echo 'checked'; ?>>
                                            <label class="btn btn-outline-success btn-sm" for="p_<?php echo $sid; ?>">P</label>

                                            <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="a_<?php echo $sid; ?>" value="absent" <?php if($status=='absent') echo 'checked'; ?>>
                                            <label class="btn btn-outline-danger btn-sm" for="a_<?php echo $sid; ?>">A</label>

                                            <input type="radio" class="btn-check" name="status[<?php echo $sid; ?>]" id="l_<?php echo $sid; ?>" value="late" <?php if($status=='late') echo 'checked'; ?>>
                                            <label class="btn btn-outline-warning btn-sm" for="l_<?php echo $sid; ?>">L</label>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center p-3'>No staff members found. Add them first!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="d-grid p-3">
                    <button type="submit" name="save_attendance" class="btn btn-primary">💾 Save Daily Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>