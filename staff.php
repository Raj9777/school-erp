<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$msg = "";

// --- 1. SINGLE ADD ---
if (isset($_POST['add_staff'])) {
    $name = clean_input($_POST['full_name']);
    $role = clean_input($_POST['role']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $pass = "12345"; // Default password
    $date = date('Y-m-d');

    $check = $conn->query("SELECT * FROM staff WHERE email='$email'");
    if ($check->num_rows > 0) { 
        $msg = "<div class='alert alert-warning'>⚠️ Email already registered!</div>"; 
    } else {
        $sql = "INSERT INTO staff (full_name, role, email, phone, password, joining_date) VALUES ('$name', '$role', '$email', '$phone', '$pass', '$date')";
        if($conn->query($sql)) {
            $msg = "<div class='alert alert-success'>✅ Staff Member Added!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

// --- 2. IMPORT CSV ---
if (isset($_POST['import_csv'])) {
    if ($_FILES['csv_file']['name']) {
        $filename = explode(".", $_FILES['csv_file']['name']);
        if (end($filename) == "csv") {
            $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
            fgetcsv($handle); // Skip Header Row
            while ($data = fgetcsv($handle)) {
                // CSV Format: Name, Role, Email, Phone
                $nam = clean_input($data[0]);
                $rol = clean_input($data[1]);
                $ema = clean_input($data[2]);
                $pho = clean_input($data[3]);
                $pass = "12345"; 
                $date = date('Y-m-d');
                
                $sql = "INSERT INTO staff (full_name, role, email, phone, password, joining_date) VALUES ('$nam', '$rol', '$ema', '$pho', '$pass', '$date')";
                $conn->query($sql);
            }
            fclose($handle);
            $msg = "<div class='alert alert-success'>✅ Bulk Import Successful!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ Please upload a valid CSV file.</div>";
        }
    }
}

// --- 3. EXPORT CSV ---
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=staff_list.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Name', 'Role', 'Email', 'Phone', 'Join Date'));
    $rows = $conn->query("SELECT id, full_name, role, email, phone, joining_date FROM staff");
    while ($row = $rows->fetch_assoc()) { fputcsv($output, $row); }
    fclose($output);
    exit();
}

// --- 4. DELETE STAFF (FIXED) ---
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);

    // STEP 1: Remove Teacher Allocations (The error you just saw)
    $conn->query("DELETE FROM subject_allocation WHERE teacher_id='$id'");

    // STEP 2: Remove Timetable Entries (To prevent future errors)
    $conn->query("DELETE FROM timetable WHERE teacher_id='$id'");

    // STEP 3: Now safe to delete the Staff member
    if ($conn->query("DELETE FROM staff WHERE id='$id'")) {
        header("Location: staff.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// --- 5. SEARCH LOGIC ---
$search_query = "";
if (isset($_GET['search'])) {
    $s = clean_input($_GET['search']);
    $search_query = " WHERE full_name LIKE '%$s%' OR email LIKE '%$s%' OR role LIKE '%$s%'";
}

$staff_list = $conn->query("SELECT * FROM staff $search_query ORDER BY role ASC, full_name ASC");
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="staff.php" class="active"><i class="fas fa-users-cog"></i> Staff Management</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4">Staff Directory</h3>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card card-box shadow-sm mb-3">
                <div class="card-header bg-primary text-white">Add New Staff</div>
                <div class="card-body">
                    <?php if($msg) echo $msg; ?>
                    <form method="POST">
                        <div class="mb-2"><input type="text" name="full_name" class="form-control" placeholder="Full Name" required></div>
                        <div class="mb-2">
                            <select name="role" class="form-select" required>
                                <option value="teacher">Teacher</option>
                                <option value="accountant">Accountant</option>
                                <option value="librarian">Librarian</option>
                                <option value="receptionist">Receptionist</option>
                            </select>
                        </div>
                        <div class="mb-2"><input type="email" name="email" class="form-control" placeholder="Email Address" required></div>
                        <div class="mb-2"><input type="text" name="phone" class="form-control" placeholder="Phone Number" required></div>
                        <button type="submit" name="add_staff" class="btn btn-primary w-100">Add Staff Member</button>
                    </form>
                </div>
            </div>
            
            <div class="card card-box shadow-sm">
                <div class="card-header bg-success text-white">Bulk Import</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <small class="text-muted d-block mb-2">CSV Format: Name, Role, Email, Phone</small>
                        <input type="file" name="csv_file" class="form-control mb-2" required>
                        <button type="submit" name="import_csv" class="btn btn-success w-100"><i class="fas fa-file-upload"></i> Upload CSV</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>Staff List</span>
                    <form method="POST"><button type="submit" name="export_csv" class="btn btn-sm btn-light"><i class="fas fa-download"></i> Export CSV</button></form>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email or role..." value="<?php echo $_GET['search'] ?? ''; ?>">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                            <?php if(isset($_GET['search'])): ?><a href="staff.php" class="btn btn-outline-danger">Clear</a><?php endif; ?>
                        </div>
                    </form>

                    <?php if ($staff_list->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle table-sm">
                            <thead><tr><th>Role</th><th>Name</th><th>Contact</th><th>Joined</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php while($row = $staff_list->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($row['role']); ?></span></td>
                                    <td><strong><?php echo $row['full_name']; ?></strong><br><small class="text-muted"><?php echo $row['email']; ?></small></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['joining_date'])); ?></td>
                                    <td>
                                        <a href="edit_staff.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                data-href="staff.php?delete=<?php echo $row['id']; ?>">
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
                            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Staff Found</h5>
                            <p class="text-muted">Add new staff using the form on the left.</p>
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