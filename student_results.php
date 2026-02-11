<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Student
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// 1. Get List of Exams this student has marks for
$exams = $conn->query("SELECT DISTINCT exams.id, exams.exam_name, exams.start_date 
                       FROM marks 
                       JOIN exams ON marks.exam_id = exams.id 
                       WHERE marks.student_id = '$student_id' 
                       ORDER BY exams.start_date DESC");
?>

<div class="sidebar d-print-none">
    <div class="sidebar-header"><h4>🎓 STUDENT</h4></div>
    <a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="student_results.php" class="active"><i class="fas fa-file-alt"></i> Exam Results</a> <a href="login.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <div class="d-print-none mb-4">
        <h3 class="fw-bold"><i class="fas fa-trophy text-warning"></i> My Report Cards</h3>
        <p class="text-muted">View and print your exam results.</p>
    </div>

    <?php 
    if ($exams->num_rows > 0) {
        while($exam = $exams->fetch_assoc()) {
            $exam_id = $exam['id'];
            
            // 2. Fetch Marks for this specific exam
            $marks_query = $conn->query("SELECT marks.*, subjects.subject_name, subjects.subject_code 
                                         FROM marks 
                                         JOIN subjects ON marks.subject_id = subjects.id 
                                         WHERE marks.student_id = '$student_id' AND marks.exam_id = '$exam_id'");

            // Calculate Totals
            $total_obtained = 0;
            $total_max = 0;
            ?>

            <div class="card card-box shadow-sm mb-5">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo $exam['exam_name']; ?></h5>
                    <small><?php echo date('d M Y', strtotime($exam['start_date'])); ?></small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Subject</th>
                                    <th>Max Marks</th>
                                    <th>Obtained</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                while($row = $marks_query->fetch_assoc()) {
                                    $total_obtained += $row['marks_obtained'];
                                    $total_max += $row['total_marks'];
                                    
                                    // Basic Grading Logic
                                    $pct = ($row['marks_obtained'] / $row['total_marks']) * 100;
                                    $grade = ($pct >= 90) ? 'A+' : (($pct >= 80) ? 'A' : (($pct >= 60) ? 'B' : (($pct >= 40) ? 'C' : 'F')));
                                    $color = ($grade == 'F') ? 'text-danger fw-bold' : 'text-dark';

                                    echo "<tr>
                                            <td class='text-start'>" . $row['subject_name'] . " <small class='text-muted'>(" . $row['subject_code'] . ")</small></td>
                                            <td>" . $row['total_marks'] . "</td>
                                            <td class='fw-bold'>" . $row['marks_obtained'] . "</td>
                                            <td class='$color'>$grade</td>
                                          </tr>";
                                }
                                
                                // Calculate Percentage
                                $final_pct = ($total_max > 0) ? round(($total_obtained / $total_max) * 100, 2) : 0;
                                $status = ($final_pct >= 40) ? "<span class='badge bg-success'>PASSED</span>" : "<span class='badge bg-danger'>FAILED</span>";
                                ?>
                                <tr class="table-secondary fw-bold">
                                    <td class="text-start">TOTAL</td>
                                    <td><?php echo $total_max; ?></td>
                                    <td><?php echo $total_obtained; ?></td>
                                    <td><?php echo $final_pct; ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <strong>Result Status:</strong> <?php echo $status; ?>
                        </div>
                        <button onclick="window.print()" class="btn btn-outline-primary btn-sm d-print-none"><i class="fas fa-print"></i> Print Report</button>
                    </div>
                </div>
            </div>

            <?php 
        }
    } else {
        echo "<div class='alert alert-info'>No exam results declared yet.</div>";
    }
    ?>
</div>

<style>
@media print {
    .sidebar, .d-print-none { display: none !important; }
    .content { margin-left: 0; padding: 0; }
    .card { border: 1px solid #000; box-shadow: none; }
    .card-header { background: #eee !important; color: #000 !important; }
}
</style>

</body>
</html>