<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Staff
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$msg = "";
$date = date('Y-m-d'); // Default to today

// --- 1. HANDLE EXPORT EXCEL (UPDATED) ---
if (isset($_POST['export_csv'])) {
    $class_id = clean_input($_POST['class_id']);
    $date = clean_input($_POST['attendance_date']);

    if($class_id && $date) {
        $sql = "SELECT students.admission_no, students.full_name, attendance.status 
                FROM students 
                LEFT JOIN attendance ON students.id = attendance.student_id 
                AND attendance.date = '$date'
                WHERE students.class_id = '$class_id'
                ORDER BY students.admission_no ASC";
        
        $result = $conn->query($sql);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=attendance_$date.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<table border='1'>";
        echo "<tr style='background-color:#cfe2f3;'>
                <th>Date</th>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Status</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            $status = ($row['status'] !== null) ? strtoupper($row['status']) : "NOT MARKED";
            
            // Color Coding
            $bg = "";
            if($status == 'ABSENT') $bg = "style='background-color:#f8d7da; color:#721c24;'"; // Red
            if($status == 'PRESENT') $bg = "style='background-color:#d1e7dd; color:#0f5132;'"; // Green

            echo "<tr>
                    <td>$date</td>
                    <td>{$row['admission_no']}</td>
                    <td>{$row['full_name']}</td>
                    <td $bg>$status</td>
                  </tr>";
        }
        echo "</table>";
        exit();
    }
}

// --- 2. HANDLE SAVE ATTENDANCE ---
if (isset($_POST['save_attendance'])) {
    $class_id = $_POST['class_id'];
    $date = $_POST['attendance_date'];
    $attendance = $_POST['status']; // Array [student_id => status]

    foreach ($attendance as $student_id => $status) {
        // Check if already marked
        $check = $conn->query("SELECT * FROM attendance WHERE student_id='$student_id' AND date='$date'");
        
        if ($check->num_rows > 0) {
            // Update
            $conn->query("UPDATE attendance SET status='$status' WHERE student_id='$student_id' AND date='$date'");
        } else {
            // Insert
            $conn->query("INSERT INTO attendance (student_id, class_id, date, status) VALUES ('$student_id', '$class_id', '$date', '$status')");
        }
    }
    $msg = "<div class='alert alert-success'>✅ Attendance Saved for $date!</div>";
}

// --- 3. FETCH CLASSES ---
$classes = $conn->query("SELECT classes.* FROM classes 
                         JOIN subject_allocation ON classes.id = subject_allocation.class_id 
                         WHERE subject_allocation.teacher_id = '$teacher_id' 
                         GROUP BY classes.id");

// --- 4. FETCH STUDENTS ---
$students = [];
$selected_class = "";

if (isset($_POST['load_students']) || isset($_POST['save_attendance']) || isset($_POST['export_csv'])) {
    $selected_class = $_POST['class_id'];
    $date = $_POST['attendance_date'];
    
    // Fetch Students + Existing Status
    $sql = "SELECT students.*, attendance.status 
            FROM students 
            LEFT JOIN attendance ON students.id = attendance.student_id AND attendance.date = '$date' 
            WHERE students.class_id = '$selected_class' 
            ORDER BY students.admission_no ASC";
    $students = $conn->query($sql);
}
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="staff_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="take_attendance.php" class="active"><i class="fas fa-user-check"></i> Attendance</a>
    <a href="enter_marks.php"><i class="fas fa-marker"></i> Enter Marks</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4">Daily Attendance</h3>

    <div class="card card-box shadow-sm mb-4">
        <div class="card-body">
            <form method="POST">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label>Select Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">-- Choose Class --</option>
                            <?php 
                            $classes->data_seek(0);
                            while($c = $classes->fetch_assoc()) {
                                $sel = ($selected_class == $c['id']) ? 'selected' : '';
                                echo "<option value='".$c['id']."' $sel>".$c['class_name']." (".$c['section'].")</option>";
                            } 
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input type="date" name="attendance_date" class="form-control" value="<?php echo $date; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="load_students" class="btn btn-primary w-100">Load List</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if($msg) echo $msg; ?>

    <?php if (!empty($students) && $students->num_rows > 0): ?>
    <div class="card card-box shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Mark Attendance: <?php echo date('d M Y', strtotime($date)); ?></span>
            
            <form method="POST" target="_blank">
                <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                <input type="hidden" name="attendance_date" value="<?php echo $date; ?>">
                <button type="submit" name="export_csv" class="btn btn-sm btn-success">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </form>
        </div>
        
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                <input type="hidden" name="attendance_date" value="<?php echo $date; ?>">

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Adm No</th>
                                <th>Student Name</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['admission_no']; ?></td>
                                <td><strong><?php echo $row['full_name']; ?></strong></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <input type="radio" class="btn-check" name="status[<?php echo $row['id']; ?>]" id="p_<?php echo $row['id']; ?>" value="present" <?php if($row['status']=='present') echo 'checked'; ?> required>
                                        <label class="btn btn-outline-success" for="p_<?php echo $row['id']; ?>">Present</label>

                                        <input type="radio" class="btn-check" name="status[<?php echo $row['id']; ?>]" id="a_<?php echo $row['id']; ?>" value="absent" <?php if($row['status']=='absent') echo 'checked'; ?>>
                                        <label class="btn btn-outline-danger" for="a_<?php echo $row['id']; ?>">Absent</label>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <button type="submit" name="save_attendance" class="btn btn-primary float-end mt-3">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </form>
        </div>
    </div>
    <?php elseif(isset($_POST['load_students'])): ?>
        <div class="alert alert-info">No students found in this class.</div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>