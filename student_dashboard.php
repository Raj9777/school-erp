<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// 1. GET STUDENT DETAILS
$student = $conn->query("SELECT students.*, classes.class_name, classes.section 
                         FROM students 
                         JOIN classes ON students.class_id = classes.id 
                         WHERE students.id = '$student_id'")->fetch_assoc();

// 2. GET ATTENDANCE STATS
$att_total = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE student_id = '$student_id'")->fetch_assoc()['count'];
$att_present = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE student_id = '$student_id' AND status = 'present'")->fetch_assoc()['count'];
$att_percent = ($att_total > 0) ? round(($att_present / $att_total) * 100) : 0;

// 3. GET EXAM MARKS
$marks = $conn->query("SELECT exams.exam_name, subjects.subject_name, marks.marks_obtained 
                       FROM marks 
                       JOIN exams ON marks.exam_id = exams.id 
                       JOIN subjects ON marks.subject_id = subjects.id 
                       WHERE marks.student_id = '$student_id' 
                       ORDER BY exams.start_date DESC LIMIT 5");

// 4. GET PENDING FEES
$fees = $conn->query("SELECT SUM(amount) as pending FROM fees WHERE student_id = '$student_id' AND status = 'pending'")->fetch_assoc()['pending'];
$fee_status = ($fees > 0) ? "<span class='badge bg-danger'>₹$fees Pending</span>" : "<span class='badge bg-success'>No Dues</span>";
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="student_dashboard.php" class="active"><i class="fas fa-home"></i> My Dashboard</a>
    <a href="my_attendance.php"><i class="fas fa-calendar-check"></i> Attendance History</a>
    <a href="my_report_card.php"><i class="fas fa-file-alt"></i> Report Card</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold">Welcome, <?php echo $student['full_name']; ?>!</h3>
            <p class="text-muted">Class: <?php echo $student['class_name']; ?>-<?php echo $student['section']; ?> | Adm No: <?php echo $student['admission_no']; ?></p>
        </div>
        <div class="text-end">
            <?php echo $fee_status; ?>
            <div class="small text-muted mt-1"><?php echo date('l, d M Y'); ?></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-box shadow-sm mb-4 h-100">
                <div class="card-header bg-primary text-white"><i class="fas fa-chart-pie"></i> Attendance</div>
                <div class="card-body text-center">
                    <h1 class="display-4 fw-bold text-primary"><?php echo $att_percent; ?>%</h1>
                    <p class="text-muted">Overall Attendance</p>
                    
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $att_percent; ?>%"></div>
                    </div>
                    <div class="mt-3 small">
                        <span class="text-success"><i class="fas fa-check-circle"></i> Present: <?php echo $att_present; ?></span> &bull; 
                        <span class="text-danger"><i class="fas fa-times-circle"></i> Total Days: <?php echo $att_total; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm mb-4 h-100">
                <div class="card-header bg-dark text-white"><i class="fas fa-graduation-cap"></i> Recent Performance</div>
                <div class="card-body p-0">
                    <?php if ($marks->num_rows > 0): ?>
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Subject</th>
                                <th>Score</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $marks->fetch_assoc()): 
                                $m = $row['marks_obtained'];
                                // Simple Grading Logic
                                $grade = ($m >= 90) ? 'A+' : (($m >= 80) ? 'A' : (($m >= 60) ? 'B' : (($m >= 40) ? 'C' : 'F')));
                                $color = ($grade == 'F') ? 'text-danger fw-bold' : 'text-success';
                            ?>
                            <tr>
                                <td><?php echo $row['exam_name']; ?></td>
                                <td><?php echo $row['subject_name']; ?></td>
                                <td><strong><?php echo $m; ?></strong>/100</td>
                                <td class="<?php echo $color; ?>"><?php echo $grade; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-clipboard fa-2x mb-2"></i>
                            <p>No marks uploaded yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-warning text-dark"><i class="fas fa-bullhorn"></i> School Noticeboard</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>📢 <strong>Half Yearly Exams</strong> start from next Monday. Please collect admit cards.</span>
                            <span class="badge bg-secondary rounded-pill">Today</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>⚽ Inter-School Football selection trials on Friday.</span>
                            <span class="badge bg-secondary rounded-pill">Yesterday</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>
</body>
</html>