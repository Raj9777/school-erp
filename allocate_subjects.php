<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// --- 1. HANDLE ALLOCATION ---
if (isset($_POST['assign_teacher'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];

    // Check if this subject is already assigned in this class
    $check = $conn->query("SELECT * FROM subject_allocation WHERE class_id='$class_id' AND subject_id='$subject_id'");
    
    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-warning'>⚠️ This subject is already assigned to a teacher! Delete the old assignment first.</div>";
    } else {
        $sql = "INSERT INTO subject_allocation (class_id, subject_id, teacher_id) VALUES ('$class_id', '$subject_id', '$teacher_id')";
        if ($conn->query($sql)) {
            $msg = "<div class='alert alert-success'>✅ Teacher Assigned Successfully!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

// --- 2. DELETE ASSIGNMENT ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM subject_allocation WHERE id=$id");
    header("Location: allocate_subjects.php");
    exit();
}

// --- 3. FETCH DATA ---
$allocations = $conn->query("SELECT subject_allocation.id, classes.class_name, classes.section, subjects.subject_name, staff.full_name 
                             FROM subject_allocation 
                             JOIN classes ON subject_allocation.class_id = classes.id 
                             JOIN subjects ON subject_allocation.subject_id = subjects.id 
                             JOIN staff ON subject_allocation.teacher_id = staff.id 
                             ORDER BY classes.class_name ASC");

// Fetch Dropdowns
$classes = $conn->query("SELECT * FROM classes");
$staff = $conn->query("SELECT * FROM staff WHERE role='teacher'");
?>

<div class="sidebar">
    <div class="sidebar-header"><h4>🏫 ADMIN PANEL</h4></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="staff.php"><i class="fas fa-users-cog"></i> Staff</a>
    <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
    <a href="classes.php"><i class="fas fa-school"></i> Classes</a>
    <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
    <a href="allocate_subjects.php" class="active"><i class="fas fa-exchange-alt"></i> Assign Subjects</a> <a href="attendance_report.php"><i class="fas fa-calendar-check"></i> Attendance</a>
    <a href="fees.php"><i class="fas fa-money-bill-wave"></i> Fees</a>
    <a href="login.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4"><i class="fas fa-exchange-alt text-primary"></i> Assign Subjects to Teachers</h3>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-primary text-white">New Assignment</div>
                <div class="card-body">
                    <?php if(isset($msg)) echo $msg; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label>Select Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Class --</option>
                                <?php while($c = $classes->fetch_assoc()) { echo "<option value='" . $c['id'] . "'>" . $c['class_name'] . " (" . $c['section'] . ")</option>"; } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Select Subject</label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">-- Subject --</option>
                                <?php 
                                // Fetch ALL subjects
                                $all_subs = $conn->query("SELECT * FROM subjects");
                                while($s = $all_subs->fetch_assoc()) { 
                                    echo "<option value='" . $s['id'] . "'>" . $s['subject_name'] . "</option>"; 
                                } 
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Assign Teacher</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">-- Teacher --</option>
                                <?php while($t = $staff->fetch_assoc()) { echo "<option value='" . $t['id'] . "'>" . $t['full_name'] . "</option>"; } ?>
                            </select>
                        </div>

                        <button type="submit" name="assign_teacher" class="btn btn-primary w-100">Assign Teacher</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white">Current Allocations</div>
                <div class="card-body">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($allocations->num_rows > 0) {
                                while($row = $allocations->fetch_assoc()) {
                                    echo "<tr>
                                            <td class='fw-bold'>" . $row['class_name'] . "-" . $row['section'] . "</td>
                                            <td>" . $row['subject_name'] . "</td>
                                            <td class='text-primary'>" . $row['full_name'] . "</td>
                                            <td>
                                                <a href='allocate_subjects.php?delete=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Remove allocation?\")'><i class='fas fa-trash'></i></a>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center text-muted'>No teachers assigned yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>