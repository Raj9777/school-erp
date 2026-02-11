<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
// Get Exams the student has appeared for
$exams = $conn->query("SELECT DISTINCT exams.id, exams.exam_name 
                       FROM marks 
                       JOIN exams ON marks.exam_id = exams.id 
                       WHERE marks.student_id = '$student_id'");
?>

<div class="sidebar d-print-none">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="student_dashboard.php"><i class="fas fa-home"></i> My Dashboard</a>
    <a href="my_attendance.php"><i class="fas fa-calendar-check"></i> Attendance History</a>
    <a href="my_report_card.php" class="active"><i class="fas fa-file-alt"></i> Report Card</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4 d-print-none">Report Cards</h3>
    
    <?php if ($exams->num_rows > 0): ?>
        <?php while($exam = $exams->fetch_assoc()): 
            $exam_id = $exam['id'];
            $marks = $conn->query("SELECT subjects.subject_name, marks.marks_obtained 
                                   FROM marks 
                                   JOIN subjects ON marks.subject_id = subjects.id 
                                   WHERE marks.student_id = '$student_id' AND marks.exam_id = '$exam_id'");
        ?>
        <div class="card card-box shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><?php echo $exam['exam_name']; ?></span>
                <button onclick="window.print()" class="btn btn-sm btn-light d-print-none">Print</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead><tr><th>Subject</th><th>Marks</th><th>Grade</th></tr></thead>
                    <tbody>
                        <?php while($row = $marks->fetch_assoc()): 
                            $m = $row['marks_obtained'];
                            $grade = ($m>=90)?'A+':(($m>=80)?'A':(($m>=60)?'B':(($m>=40)?'C':'F')));
                        ?>
                        <tr>
                            <td><?php echo $row['subject_name']; ?></td>
                            <td><?php echo $m; ?>/100</td>
                            <td class="fw-bold"><?php echo $grade; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No marks uploaded yet.</div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>