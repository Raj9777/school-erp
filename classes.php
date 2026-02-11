<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$msg = "";

// --- ADD CLASS ---
if (isset($_POST['add_class'])) {
    $name = clean_input($_POST['class_name']);
    $sec = clean_input($_POST['section']);

    $check = $conn->query("SELECT * FROM classes WHERE class_name='$name' AND section='$sec'");
    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-warning'>⚠️ Class already exists!</div>";
    } else {
        $sql = "INSERT INTO classes (class_name, section) VALUES ('$name', '$sec')";
        if($conn->query($sql)) {
            $msg = "<div class='alert alert-success'>✅ Class Added Successfully!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

// --- DELETE CLASS ---
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    $conn->query("DELETE FROM classes WHERE id='$id'");
    header("Location: classes.php");
    exit();
}

$classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="classes.php" class="active"><i class="fas fa-school"></i> Classes & Sections</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4">Class Management</h3>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-box shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Create New Class</div>
                <div class="card-body">
                    <?php if($msg) echo $msg; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Class Name / Grade</label>
                            <select name="class_name" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option>Class 1</option><option>Class 2</option><option>Class 3</option>
                                <option>Class 4</option><option>Class 5</option><option>Class 6</option>
                                <option>Class 7</option><option>Class 8</option><option>Class 9</option>
                                <option>Class 10</option><option>Class 11</option><option>Class 12</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Section</label>
                            <input type="text" name="section" class="form-control" placeholder="e.g. A, B, Science" required>
                        </div>
                        <button type="submit" name="add_class" class="btn btn-primary w-100">Save Class</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white">Active Classes</div>
                <div class="card-body">
                    <?php if ($classes->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead><tr><th>Class Name</th><th>Section</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php while($row = $classes->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['class_name']; ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo $row['section']; ?></span></td>
                                    <td>
                                        <a href="edit_class.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                data-href="classes.php?delete=<?php echo $row['id']; ?>">
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
                            <i class="fas fa-school fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Classes Added</h5>
                            <p class="text-muted">Create a class to start adding students.</p>
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