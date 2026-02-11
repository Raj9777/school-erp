<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$students = [];
if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    $students = $conn->query("SELECT * FROM students WHERE class_id = '$class_id' ORDER BY admission_no ASC");
}

$class_list = $conn->query("SELECT * FROM classes");
?>

<div class="sidebar">
    <div class="sidebar-header">
        <small><?php echo $SCHOOL_SETTINGS['school_name'] ?? 'School ERP'; ?></small>
    </div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="staff.php"><i class="fas fa-users-cog"></i> Staff</a>
    <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
    <a href="id_cards.php" class="active"><i class="fas fa-id-card"></i> ID Cards</a> <a href="classes.php"><i class="fas fa-school"></i> Classes</a>
    <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
    <a href="exams.php"><i class="fas fa-edit"></i> Exams</a>
    <a href="noticeboard.php"><i class="fas fa-bullhorn"></i> Noticeboard</a>
    <a href="fees.php"><i class="fas fa-money-bill-wave"></i> Fees</a>
    <a href="login.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4"><i class="fas fa-id-card text-primary"></i> ID Card Generator</h3>

    <div class="card card-box shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label>Select Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Choose Class --</option>
                        <?php while($c = $class_list->fetch_assoc()) { 
                            $sel = (isset($_GET['class_id']) && $_GET['class_id'] == $c['id']) ? 'selected' : '';
                            echo "<option value='" . $c['id'] . "' $sel>" . $c['class_name'] . " (" . $c['section'] . ")</option>"; 
                        } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Load Students</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['class_id']) && $students->num_rows > 0): ?>
    <div class="card card-box shadow-sm">
        <div class="card-header bg-dark text-white">Generate ID Cards</div>
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Adm No</th>
                        <th>Student Name</th>
                        <th>Father's Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['admission_no']; ?></td>
                        <td><strong><?php echo $row['full_name']; ?></strong></td>
                        <td><?php echo $row['father_name']; ?></td>
                        <td>
                            <a href="print_id_card.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-info text-white">
                                <i class="fas fa-print"></i> Generate Card
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php elseif(isset($_GET['class_id'])): ?>
        <div class="alert alert-warning">No students found.</div>
    <?php endif; ?>
</div>
</body>
</html>