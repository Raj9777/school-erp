<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

// --- 1. HANDLE SINGLE ADD ---
if (isset($_POST['add_student'])) {
    $adm_no = clean_input($_POST['admission_no']);
    $name = clean_input($_POST['full_name']);
    $class_id = clean_input($_POST['class_id']);
    $father = clean_input($_POST['father_name']);
    $phone = clean_input($_POST['phone']);
    $dob = clean_input($_POST['dob']);
    $gender = clean_input($_POST['gender']);
    $password = "12345"; 

    $check = $conn->query("SELECT * FROM students WHERE admission_no='$adm_no'");
    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-warning'>⚠️ Admission No exists!</div>";
    } else {
        $sql = "INSERT INTO students (admission_no, full_name, class_id, father_name, phone, dob, gender, password) 
                VALUES ('$adm_no', '$name', '$class_id', '$father', '$phone', '$dob', '$gender', '$password')";
        if ($conn->query($sql)) { $msg = "<div class='alert alert-success'>✅ Student Added!</div>"; } 
        else { $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>"; }
    }
}

// --- 2. HANDLE CSV IMPORT ---
if (isset($_POST['import_csv'])) {
    if ($_FILES['csv_file']['name']) {
        $filename = explode(".", $_FILES['csv_file']['name']);
        if (end($filename) == "csv") {
            $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
            fgetcsv($handle); // Skip Header Row
            while ($data = fgetcsv($handle)) {
                // CSV Format: AdmNo, Name, FatherName, Phone, ClassID
                $adm = clean_input($data[0]);
                $nam = clean_input($data[1]);
                $fat = clean_input($data[2]);
                $pho = clean_input($data[3]);
                $cid = clean_input($data[4]); 
                $pass = "12345";

                $sql = "INSERT INTO students (admission_no, full_name, father_name, phone, class_id, password) 
                        VALUES ('$adm', '$nam', '$fat', '$pho', '$cid', '$pass')";
                $conn->query($sql);
            }
            fclose($handle);
            $msg = "<div class='alert alert-success'>✅ Bulk Import Successful!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ Please upload a CSV file only.</div>";
        }
    }
}

// --- 3. HANDLE CSV EXPORT ---
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=students_list.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Admission No', 'Name', 'Class', 'Father Name', 'Phone'));
    
    $rows = $conn->query("SELECT students.id, students.admission_no, students.full_name, classes.class_name, students.father_name, students.phone 
                          FROM students LEFT JOIN classes ON students.class_id = classes.id");
    while ($row = $rows->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// --- 4. HANDLE DELETE (FIXED) ---
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    
    // STEP 1: Delete Attendance Records (The error you saw)
    $conn->query("DELETE FROM attendance WHERE student_id='$id'");

    // STEP 2: Delete Marks
    $conn->query("DELETE FROM marks WHERE student_id='$id'");

    // STEP 3: Delete Fee Records
    $conn->query("DELETE FROM fees WHERE student_id='$id'");

    // STEP 4: Delete Library/Book Issues (if table exists)
    // $conn->query("DELETE FROM book_issues WHERE student_id='$id'");

    // STEP 5: Finally, Delete the Student
    if ($conn->query("DELETE FROM students WHERE id='$id'")) {
        header("Location: students.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- 5. SEARCH LOGIC ---
$search_query = "";
if (isset($_GET['search'])) {
    $search = clean_input($_GET['search']);
    $search_query = " WHERE students.full_name LIKE '%$search%' OR students.admission_no LIKE '%$search%'";
}

$students = $conn->query("SELECT students.*, classes.class_name, classes.section 
                          FROM students 
                          LEFT JOIN classes ON students.class_id = classes.id 
                          $search_query 
                          ORDER BY students.id DESC");
$class_list = $conn->query("SELECT * FROM classes");
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="students.php" class="active"><i class="fas fa-user-graduate"></i> Students</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4">Student Management</h3>
    <div class="row">
        <div class="col-md-4">
            <div class="card card-box shadow-sm mb-3">
                <div class="card-header bg-primary text-white">Add Single Student</div>
                <div class="card-body">
                    <?php if(isset($msg)) echo $msg; ?>
                    <form method="POST">
                        <div class="mb-2"><input type="text" name="admission_no" class="form-control" placeholder="Admission No" required></div>
                        <div class="mb-2"><input type="text" name="full_name" class="form-control" placeholder="Full Name" required></div>
                        <div class="mb-2">
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Class --</option>
                                <?php 
                                $class_list->data_seek(0);
                                while($c = $class_list->fetch_assoc()) { echo "<option value='" . $c['id'] . "'>" . $c['class_name'] . " (" . $c['section'] . ")</option>"; } 
                                ?>
                            </select>
                        </div>
                        <div class="mb-2"><input type="text" name="father_name" class="form-control" placeholder="Father Name"></div>
                        <div class="mb-2"><input type="text" name="phone" class="form-control" placeholder="Phone"></div>
                        
                        <div class="mb-2"><input type="date" name="dob" class="form-control" required></div>
                        <div class="mb-2">
                            <select name="gender" class="form-select">
                                <option>Male</option><option>Female</option>
                            </select>
                        </div>

                        <button type="submit" name="add_student" class="btn btn-primary w-100">Add Student</button>
                    </form>
                </div>
            </div>

            <div class="card card-box shadow-sm">
                <div class="card-header bg-success text-white">Bulk Import (CSV)</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <small class="text-muted d-block mb-2">Format: AdmNo, Name, Father, Phone, ClassID</small>
                        <input type="file" name="csv_file" class="form-control mb-2" required>
                        <button type="submit" name="import_csv" class="btn btn-success w-100">Upload & Import</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>Student Directory</span>
                    <form method="POST"><button type="submit" name="export_csv" class="btn btn-sm btn-light"><i class="fas fa-file-csv"></i> Export CSV</button></form>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by Name or Admission No..." value="<?php echo $_GET['search'] ?? ''; ?>">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                            <?php if(isset($_GET['search'])): ?><a href="students.php" class="btn btn-outline-danger">Clear</a><?php endif; ?>
                        </div>
                    </form>

                    <?php if ($students->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle table-sm">
                            <thead><tr><th>ID</th><th>Name</th><th>Class</th><th>Contact</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php while($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['admission_no']; ?></td>
                                    <td><strong><?php echo $row['full_name']; ?></strong><br><small><?php echo $row['father_name']; ?></small></td>
                                    <td><?php echo $row['class_name']; ?>-<?php echo $row['section']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td>
                                        <a href='edit_student.php?id=<?php echo $row['id']; ?>' class='btn btn-sm btn-warning'><i class='fas fa-edit'></i></a>
                                        
                                        <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                data-href="students.php?delete=<?php echo $row['id']; ?>">
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
                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Students Found</h5>
                            <p class="text-muted">Add students to manage fees, marks, and attendance.</p>
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