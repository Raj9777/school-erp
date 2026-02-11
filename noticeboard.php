<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// --- 1. POST NOTICE ---
if (isset($_POST['post_notice'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);
    $target = $_POST['target_role'];

    $sql = "INSERT INTO noticeboard (title, message, target_role) VALUES ('$title', '$message', '$target')";
    if ($conn->query($sql)) {
        $msg = "<div class='alert alert-success'>✅ Notice Posted Successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// --- 2. DELETE NOTICE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM noticeboard WHERE id=$id");
    header("Location: noticeboard.php");
    exit();
}

// --- 3. FETCH NOTICES ---
$notices = $conn->query("SELECT * FROM noticeboard ORDER BY created_at DESC");
?>

<div class="sidebar">
    <div class="sidebar-header">
        <small><?php echo $SCHOOL_SETTINGS['school_name'] ?? 'School ERP'; ?></small>
    </div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="staff.php"><i class="fas fa-users-cog"></i> Staff</a>
    <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
    <a href="classes.php"><i class="fas fa-school"></i> Classes</a>
    <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
    <a href="exams.php"><i class="fas fa-edit"></i> Exams</a>
    <a href="noticeboard.php" class="active"><i class="fas fa-bullhorn"></i> Noticeboard</a> <a href="fees.php"><i class="fas fa-money-bill-wave"></i> Fees</a>
    <a href="settings.php"><i class="fas fa-cogs"></i> Settings</a>
    <a href="login.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4"><i class="fas fa-bullhorn text-primary"></i> Digital Noticeboard</h3>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-primary text-white">Create Announcement</div>
                <div class="card-body">
                    <?php if(isset($msg)) echo $msg; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Notice Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. School Closed" required>
                        </div>
                        <div class="mb-3">
                            <label>Message</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Write details here..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Target Audience</label>
                            <select name="target_role" class="form-select">
                                <option value="all">Everyone</option>
                                <option value="student">Students Only</option>
                                <option value="teacher">Teachers Only</option>
                                <option value="accountant">Accountants Only</option>
                            </select>
                        </div>
                        <button type="submit" name="post_notice" class="btn btn-primary w-100">📢 Publish Notice</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white">Active Notices</div>
                <div class="card-body">
                    <?php if ($notices->num_rows > 0): ?>
                        <div class="list-group">
                        <?php while($row = $notices->fetch_assoc()): 
                            $badge_color = ($row['target_role'] == 'all') ? 'bg-success' : 'bg-warning text-dark';
                        ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo $row['title']; ?></h5>
                                    <small class="text-muted"><?php echo date('d M Y', strtotime($row['created_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?php echo $row['message']; ?></p>
                                <small>Visible to: <span class="badge <?php echo $badge_color; ?>"><?php echo strtoupper($row['target_role']); ?></span></small>
                                <a href="noticeboard.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger float-end" onclick="return confirm('Delete this notice?')"><i class="fas fa-trash"></i></a>
                            </div>
                        <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No notices posted yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>