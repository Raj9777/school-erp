<?php
session_start();
include 'db_connect.php';

$msg = "";
$step = 1; // Step 1: Verify, Step 2: Reset

if (isset($_POST['verify_identity'])) {
    $role = clean_input($_POST['role']);
    $username = clean_input($_POST['username']); // Email or Admission No
    $security_check = clean_input($_POST['security_check']); // Phone No

    // Check Database based on Role
    if ($role == 'student') {
        $sql = "SELECT * FROM students WHERE admission_no='$username' AND phone='$security_check'";
        $table = "students";
    } elseif ($role == 'staff') {
        $sql = "SELECT * FROM staff WHERE email='$username' AND phone='$security_check'";
        $table = "staff";
    } else {
        $sql = "SELECT * FROM admins WHERE email='$username' AND phone='$security_check'";
        $table = "admins";
    }

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Identity Verified! Move to Step 2
        $user = $result->fetch_assoc();
        $_SESSION['reset_id'] = $user['id'];
        $_SESSION['reset_table'] = $table;
        $step = 2;
    } else {
        $msg = "<div class='alert alert-danger'>❌ Details did not match our records.</div>";
    }
}

// --- HANDLE RESET ---
if (isset($_POST['reset_password'])) {
    $new_pass = clean_input($_POST['new_password']);
    $id = $_SESSION['reset_id'];
    $table = $_SESSION['reset_table'];

    if (strlen($new_pass) < 5) {
        $msg = "<div class='alert alert-warning'>Password too short.</div>";
        $step = 2; // Stay on step 2
    } else {
        $conn->query("UPDATE $table SET password='$new_pass' WHERE id='$id'");
        // Clear session and redirect
        session_destroy();
        echo "<script>alert('✅ Password Reset Successfully! Please Login.'); window.location.href='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | School ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { width: 400px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="card bg-white p-4">
    <div class="text-center mb-4">
        <h4 class="fw-bold text-primary"><i class="fas fa-key"></i> Password Recovery</h4>
        <p class="text-muted small">Verify your identity to reset password</p>
    </div>

    <?php if($msg) echo $msg; ?>

    <?php if ($step == 1): ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Select Your Role</label>
            <select name="role" class="form-select" required>
                <option value="student">Student</option>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">User ID / Email</label>
            <input type="text" name="username" class="form-control" placeholder="Admin No (Student) or Email (Staff)" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Registered Phone Number</label>
            <input type="text" name="security_check" class="form-control" placeholder="Enter your phone number" required>
            <div class="form-text text-muted">This serves as your security question.</div>
        </div>

        <button type="submit" name="verify_identity" class="btn btn-primary w-100">Verify Identity</button>
    </form>
    
    <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">Back to Login</a>
    </div>

    <?php elseif ($step == 2): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> Identity Verified! Set a new password.</div>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" placeholder="Min 5 chars" required>
        </div>
        <button type="submit" name="reset_password" class="btn btn-success w-100">Reset Password</button>
    </form>
    <?php endif; ?>
</div>

</body>
</html>