<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$msg = "";

// --- ADD EXAM ---
if (isset($_POST['add_exam'])) {
    $name = clean_input($_POST['exam_name']);
    $date = clean_input($_POST['exam_date']);

    $sql = "INSERT INTO exams (exam_name, start_date) VALUES ('$name', '$date')";
    if ($conn->query($sql)) {
        $msg = "<div class='alert alert-success'>✅ Exam Created Successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// --- DELETE EXAM (FIXED) ---
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);

    // STEP 1: Delete all marks linked to this exam (The Fix)
    $conn->query("DELETE FROM marks WHERE exam_id='$id'");

    // STEP 2: Now safe to delete the exam
    if ($conn->query("DELETE FROM exams WHERE id='$id'")) {
        header("Location: exams.php");
        exit();
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

$exams = $conn->query("SELECT * FROM exams ORDER BY start_date DESC");
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="exams.php" class="active"><i class="fas fa-edit"></i> Exam Management</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4">Exam Management</h3>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-box shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Create New Exam</div>
                <div class="card-body">
                    <?php if($msg) echo $msg; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Exam Name</label>
                            <input type="text" name="exam_name" class="form-control" placeholder="e.g. Final Term 2026" required>
                        </div>
                        <div class="mb-3">
                            <label>Start Date</label>
                            <input type="date" name="exam_date" class="form-control" required>
                        </div>
                        <button type="submit" name="add_exam" class="btn btn-primary w-100">Create Exam</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white">Scheduled Exams</div>
                <div class="card-body">
                    <?php if ($exams->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead><tr><th>Exam Name</th><th>Date</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php while($row = $exams->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['exam_name']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['start_date'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                data-href="exams.php?delete=<?php echo $row['id']; ?>">
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
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Exams Found</h5>
                            <p class="text-muted">Create an exam to start scheduling tests.</p>
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