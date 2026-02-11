<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$msg = "";

// --- ADD SUBJECT ---
if (isset($_POST['add_subject'])) {
    $name = clean_input($_POST['subject_name']);
    $code = clean_input($_POST['subject_code']);
    $class_id = clean_input($_POST['class_id']);

    $check = $conn->query("SELECT * FROM subjects WHERE subject_name='$name' AND class_id='$class_id'");
    
    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-warning'>⚠️ Subject already added for this class!</div>";
    } else {
        $sql = "INSERT INTO subjects (subject_name, subject_code, class_id) VALUES ('$name', '$code', '$class_id')";
        if ($conn->query($sql)) {
            $msg = "<div class='alert alert-success'>✅ Subject Added!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

// --- DELETE SUBJECT (COMPLETE FIX) ---
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);

    // STEP 1: Remove Teacher Allocations (The error you just saw)
    $conn->query("DELETE FROM subject_allocation WHERE subject_id='$id'");

    // STEP 2: Remove Marks
    $conn->query("DELETE FROM marks WHERE subject_id='$id'");

    // STEP 3: Remove Timetable entries
    $conn->query("DELETE FROM timetable WHERE subject_id='$id'");

    // STEP 4: Finally, Delete the Subject
    if ($conn->query("DELETE FROM subjects WHERE id='$id'")) {
        header("Location: subjects.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Critical Error: " . $conn->error . "</div>";
    }
}

$subjects = $conn->query("SELECT subjects.*, classes.class_name, classes.section 
                          FROM subjects 
                          JOIN classes ON subjects.class_id = classes.id 
                          ORDER BY classes.class_name ASC, subjects.subject_name ASC");

$class_list = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="subjects.php" class="active"><i class="fas fa-book"></i> Subject Management</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4">Subject Management</h3>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-box shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Add New Subject</div>
                <div class="card-body">
                    <?php if($msg) echo $msg; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Subject Name</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="e.g. Mathematics" required>
                        </div>
                        <div class="mb-3">
                            <label>Subject Code</label>
                            <input type="text" name="subject_code" class="form-control" placeholder="e.g. MTH-101">
                        </div>
                        <div class="mb-3">
                            <label>Assign to Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Select Class --</option>
                                <?php 
                                $class_list->data_seek(0);
                                while($c = $class_list->fetch_assoc()) {
                                    echo "<option value='" . $c['id'] . "'>" . $c['class_name'] . " (" . $c['section'] . ")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="add_subject" class="btn btn-primary w-100">Save Subject</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white">Subject List</div>
                <div class="card-body">
                    <?php if ($subjects->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead><tr><th>Class</th><th>Subject Name</th><th>Code</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php while($row = $subjects->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['class_name']; ?>-<?php echo $row['section']; ?></td>
                                    <td><?php echo $row['subject_name']; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $row['subject_code']; ?></span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                data-href="subjects.php?delete=<?php echo $row['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Subjects Found</h5>
                            <p class="text-muted">Add subjects to enable marks entry and exam scheduling.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>