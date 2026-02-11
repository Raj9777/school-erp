<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

// Security: Must be logged in
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$id = $_SESSION['user_id'];
$role = $_SESSION['role']; // admin, staff, student

// Determine which table to update
if ($role == 'admin') { $table = "admins"; }
elseif ($role == 'staff') { $table = "staff"; }
else { $table = "students"; }

$msg = "";

// --- HANDLE PASSWORD UPDATE ---
if (isset($_POST['change_password'])) {
    $new_pass = clean_input($_POST['new_password']);
    $confirm_pass = clean_input($_POST['confirm_password']);

    if (strlen($new_pass) < 5) {
        $msg = "<div class='alert alert-warning'>⚠️ Password must be at least 5 characters long.</div>";
    } elseif ($new_pass !== $confirm_pass) {
        $msg = "<div class='alert alert-danger'>❌ Passwords do not match!</div>";
    } else {
        // Update Password
        // In a real production app, use password_hash($new_pass, PASSWORD_DEFAULT)
        $sql = "UPDATE $table SET password='$new_pass' WHERE id='$id'";
        
        if ($conn->query($sql)) {
            $msg = "<div class='alert alert-success'>✅ Password Changed Successfully!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}
?>

<div class="sidebar">
    <div class="sidebar-header"><small><?php echo $SCHOOL_SETTINGS['school_name']; ?></small></div>
    
    <?php 
    $dash = ($role == 'admin') ? 'admin_dashboard.php' : (($role == 'student') ? 'student_dashboard.php' : 'staff_dashboard.php');
    ?>
    <a href="<?php echo $dash; ?>"><i class="fas fa-home"></i> Dashboard</a>
    <a href="profile.php" class="active"><i class="fas fa-user-shield"></i> My Profile</a>
    <a href="logout.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-lock"></i> Security Settings
                    </div>
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Change Password</h5>
                        <?php if($msg) echo $msg; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Minimum 5 characters" required>
                            </div>
                            <div class="mb-3">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Retype password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <small><i class="fas fa-info-circle"></i> Use this page to update your default password (12345) to something secure.</small>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>