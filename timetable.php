<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

// --- ADD ROUTINE ---
if (isset($_POST['add_routine'])) {
    $class_id = $_POST['class_id'];
    $day = $_POST['day_name'];
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $room = $_POST['room_no'];

    $sql = "INSERT INTO timetable (class_id, day_name, subject_id, teacher_id, start_time, end_time, room_no) 
            VALUES ('$class_id', '$day', '$subject_id', '$teacher_id', '$start', '$end', '$room')";
    
    if ($conn->query($sql)) {
        $msg = "<div class='alert alert-success'>✅ Class Scheduled!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// --- DELETE ROUTINE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM timetable WHERE id=$id");
    header("Location: timetable.php");
    exit();
}

// Fetch Classes
$classes = $conn->query("SELECT * FROM classes");
$subjects = $conn->query("SELECT * FROM subjects");
$teachers = $conn->query("SELECT * FROM staff WHERE role='teacher'");

// Fetch Timetable if Class Selected
$routine = [];
$selected_class = "";
if (isset($_GET['class_id'])) {
    $selected_class = $_GET['class_id'];
    $sql = "SELECT timetable.*, subjects.subject_name, staff.full_name 
            FROM timetable 
            JOIN subjects ON timetable.subject_id = subjects.id 
            JOIN staff ON timetable.teacher_id = staff.id 
            WHERE timetable.class_id = '$selected_class' 
            ORDER BY FIELD(day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time ASC";
    $routine = $conn->query($sql);
}
?>

<div class="sidebar">
    <div class="sidebar-header">
        <small><?php echo $SCHOOL_SETTINGS['school_name'] ?? 'School ERP'; ?></small>
    </div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="classes.php"><i class="fas fa-school"></i> Classes</a>
    <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
    <a href="timetable.php" class="active"><i class="fas fa-calendar-alt"></i> Timetable</a> <a href="admin_dashboard.php" class="mt-4"><i class="fas fa-arrow-left"></i> Back to Admin</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4"><i class="fas fa-calendar-alt text-primary"></i> Class Routine</h3>

    <div class="card card-box shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label>View Routine For:</label>
                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Class --</option>
                        <?php 
                        // Reset pointer for loop
                        $classes->data_seek(0);
                        while($c = $classes->fetch_assoc()) { 
                            $sel = ($selected_class == $c['id']) ? 'selected' : '';
                            echo "<option value='".$c['id']."' $sel>".$c['class_name']." (".$c['section'].")</option>"; 
                        } ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php if ($selected_class): ?>
        <div class="col-md-4">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-primary text-white">Add Schedule</div>
                <div class="card-body">
                    <?php if($msg) echo $msg; ?>
                    <form method="POST">
                        <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                        <div class="mb-3">
                            <label>Day</label>
                            <select name="day_name" class="form-select">
                                <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                                <option>Thursday</option><option>Friday</option><option>Saturday</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Subject</label>
                            <select name="subject_id" class="form-select" required>
                                <?php 
                                $subjects->data_seek(0);
                                while($s = $subjects->fetch_assoc()) { echo "<option value='".$s['id']."'>".$s['subject_name']."</option>"; } 
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Teacher</label>
                            <select name="teacher_id" class="form-select" required>
                                <?php 
                                $teachers->data_seek(0);
                                while($t = $teachers->fetch_assoc()) { echo "<option value='".$t['id']."'>".$t['full_name']."</option>"; } 
                                ?>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6"><label>Start</label><input type="time" name="start_time" class="form-control" required></div>
                            <div class="col-6"><label>End</label><input type="time" name="end_time" class="form-control" required></div>
                        </div>
                        <div class="mb-3">
                            <label>Room No</label>
                            <input type="text" name="room_no" class="form-control" placeholder="e.g. 101">
                        </div>
                        <button type="submit" name="add_routine" class="btn btn-primary w-100">Add to Routine</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white">Weekly Schedule</div>
                <div class="card-body">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Subject / Teacher</th>
                                <th>Room</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($routine && $routine->num_rows > 0) {
                                while($row = $routine->fetch_assoc()) {
                                    echo "<tr>
                                            <td class='fw-bold'>" . $row['day_name'] . "</td>
                                            <td>" . date('h:i A', strtotime($row['start_time'])) . " - " . date('h:i A', strtotime($row['end_time'])) . "</td>
                                            <td>
                                                <span class='badge bg-info text-dark'>" . $row['subject_name'] . "</span><br>
                                                <small class='text-muted'>" . $row['full_name'] . "</small>
                                            </td>
                                            <td>" . $row['room_no'] . "</td>
                                            <td>
                                                <a href='timetable.php?class_id=$selected_class&delete=" . $row['id'] . "' class='btn btn-sm btn-danger'><i class='fas fa-trash'></i></a>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center text-muted'>No schedule found. Add entries on the left.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info">Please select a class above to manage its timetable.</div></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>