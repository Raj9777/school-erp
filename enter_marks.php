<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Only Staff (Teachers)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$msg = "";

// --- 1. INITIALIZE VARIABLES (THE FIX) ---
// We check if values exist in POST, otherwise keep them empty.
$selected_exam = isset($_POST['exam_id']) ? $_POST['exam_id'] : '';
$selected_class = isset($_POST['class_id']) ? $_POST['class_id'] : '';
$selected_subject = isset($_POST['subject_id']) ? $_POST['subject_id'] : '';
$students = [];

// --- 1. HANDLE EXPORT EXCEL (UPDATED) ---
if (isset($_POST['export_csv'])) {
    $exam_id = clean_input($_POST['exam_id']);
    $class_id = clean_input($_POST['class_id']);
    $subject_id = clean_input($_POST['subject_id']);

    if($exam_id && $class_id && $subject_id) {
        $sql = "SELECT students.admission_no, students.full_name, marks.marks_obtained 
                FROM students 
                LEFT JOIN marks ON students.id = marks.student_id 
                AND marks.exam_id = '$exam_id' 
                AND marks.subject_id = '$subject_id'
                WHERE students.class_id = '$class_id'
                ORDER BY students.admission_no ASC";
        
        $result = $conn->query($sql);

        // TELL BROWSER THIS IS AN EXCEL FILE
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=marks_list.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        // OUTPUT DATA AS HTML TABLE (Excel reads this natively)
        echo "<table border='1'>";
        echo "<tr style='background-color:#f2f2f2; font-weight:bold;'>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Marks Obtained</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            $mk = ($row['marks_obtained'] !== null) ? $row['marks_obtained'] : "Not Entered";
            
            // Highlight low marks in Red
            $color = (is_numeric($mk) && $mk < 33) ? "style='color:red; font-weight:bold;'" : "";
            
            echo "<tr>
                    <td>{$row['admission_no']}</td>
                    <td>{$row['full_name']}</td>
                    <td $color>$mk</td>
                  </tr>";
        }
        echo "</table>";
        exit();
    }
}

// --- 3. HANDLE SAVE MARKS ---
if (isset($_POST['save_marks'])) {
    $marks = $_POST['marks']; 
    foreach ($marks as $student_id => $mark_value) {
        $check = $conn->query("SELECT * FROM marks WHERE student_id='$student_id' AND exam_id='$selected_exam' AND subject_id='$selected_subject'");
        
        if ($mark_value !== "") { 
            if ($check->num_rows > 0) {
                $conn->query("UPDATE marks SET marks_obtained='$mark_value' WHERE student_id='$student_id' AND exam_id='$selected_exam' AND subject_id='$selected_subject'");
            } else {
                $conn->query("INSERT INTO marks (student_id, exam_id, class_id, subject_id, marks_obtained) VALUES ('$student_id', '$selected_exam', '$selected_class', '$selected_subject', '$mark_value')");
            }
        }
    }
    $msg = "<div class='alert alert-success'>✅ Marks Saved Successfully!</div>";
}

// --- 4. FETCH DATA FOR DROPDOWNS ---
$exams = $conn->query("SELECT * FROM exams ORDER BY start_date DESC");

// Get Classes assigned to this teacher
$classes = $conn->query("SELECT classes.* FROM classes 
                         JOIN subject_allocation ON classes.id = subject_allocation.class_id 
                         WHERE subject_allocation.teacher_id = '$teacher_id' 
                         GROUP BY classes.id");

// Get Subjects (Only if Class is Selected)
$subjects_query = "SELECT subjects.* FROM subjects 
                   JOIN subject_allocation ON subjects.id = subject_allocation.subject_id 
                   WHERE subject_allocation.class_id = '$selected_class' 
                   AND subject_allocation.teacher_id = '$teacher_id'";
$subjects = $conn->query($subjects_query);


// --- 5. FETCH STUDENTS (Only if Load/Save is clicked) ---
if (isset($_POST['load_students']) || isset($_POST['save_marks']) || isset($_POST['export_csv'])) {
    if ($selected_class && $selected_subject && $selected_exam) {
        $sql = "SELECT students.*, marks.marks_obtained 
                FROM students 
                LEFT JOIN marks ON students.id = marks.student_id 
                AND marks.exam_id = '$selected_exam' 
                AND marks.subject_id = '$selected_subject'
                WHERE students.class_id = '$selected_class' 
                ORDER BY students.admission_no ASC";
        $students = $conn->query($sql);
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ Please select Exam, Class and Subject.</div>";
    }
}
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="staff_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="take_attendance.php"><i class="fas fa-user-check"></i> Attendance</a>
    <a href="enter_marks.php" class="active"><i class="fas fa-marker"></i> Enter Marks</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4">Enter Student Marks</h3>

    <div class="card card-box shadow-sm mb-4">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-3">
                        <label>Select Exam</label>
                        <select name="exam_id" class="form-select" required>
                            <option value="">-- Exam --</option>
                            <?php 
                            $exams->data_seek(0);
                            while($e = $exams->fetch_assoc()) {
                                $sel = ($selected_exam == $e['id']) ? 'selected' : '';
                                echo "<option value='".$e['id']."' $sel>".$e['exam_name']."</option>";
                            } 
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label>Select Class</label>
                        <select name="class_id" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Class --</option>
                            <?php 
                            if($classes->num_rows > 0){
                                $classes->data_seek(0);
                                while($c = $classes->fetch_assoc()) {
                                    $sel = ($selected_class == $c['id']) ? 'selected' : '';
                                    echo "<option value='".$c['id']."' $sel>".$c['class_name']." (".$c['section'].")</option>";
                                } 
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label>Select Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">-- Subject --</option>
                            <?php 
                            if($subjects && $subjects->num_rows > 0) {
                                while($s = $subjects->fetch_assoc()) {
                                    $sel = ($selected_subject == $s['id']) ? 'selected' : '';
                                    echo "<option value='".$s['id']."' $sel>".$s['subject_name']."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" name="load_students" class="btn btn-primary w-100">Load Students</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if($msg) echo $msg; ?>
    
    <?php if (!empty($students) && $students->num_rows > 0): ?>
    <div class="card card-box shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Student List</span>
            <form method="POST">
                <input type="hidden" name="exam_id" value="<?php echo $selected_exam; ?>">
                <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                <button type="submit" name="export_csv" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Export CSV
                </button>
            </form>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="exam_id" value="<?php echo $selected_exam; ?>">
                <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">

                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Adm No</th>
                            <th>Student Name</th>
                            <th>Marks Obtained (Out of 100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['admission_no']; ?></td>
                            <td><strong><?php echo $row['full_name']; ?></strong></td>
                            <td>
                                <input type="number" name="marks[<?php echo $row['id']; ?>]" 
                                       class="form-control" style="width: 100px;" 
                                       value="<?php echo $row['marks_obtained']; ?>" 
                                       min="0" max="100">
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_marks" class="btn btn-success float-end mt-3">
                    <i class="fas fa-save"></i> Save Marks
                </button>
            </form>
        </div>
    </div>
    <?php elseif(isset($_POST['load_students'])): ?>
        <div class="alert alert-info">No students found or selection incomplete.</div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>