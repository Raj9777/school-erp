<?php
session_start();
include 'db_connect.php';

$error = "";

if (isset($_POST['login_btn'])) {
    // SECURITY FIX: Wrap inputs in clean_input()
    $role = clean_input($_POST['role']); 
    $username = clean_input($_POST['username']); 
    $password = clean_input($_POST['password']); 

    // --- 1. ADMIN LOGIN ---
    if ($role == 'admin') {
        $sql = "SELECT * FROM admins WHERE email='$username' AND password='$password'";
        $redirect = "admin_dashboard.php";
        $session_role_name = "admin";
    } 
    
    // --- 2. STAFF LOGIN ---
    elseif ($role == 'staff') {
        $sql = "SELECT * FROM staff WHERE email='$username' AND password='$password'";
        $redirect = "staff_dashboard.php";
        $session_role_name = "staff";
    } 
    
    // --- 3. STUDENT LOGIN ---
    elseif ($role == 'student') {
        $sql = "SELECT * FROM students WHERE admission_no='$username' AND password='$password'";
        $redirect = "student_dashboard.php";
        $session_role_name = "student";
    }

    // Execute Query
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set Session Variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $session_role_name;
        
        // Handle name differences
        if ($role == 'admin') {
            $_SESSION['name'] = $user['name'];
        } else {
            $_SESSION['name'] = $user['full_name'];
        }
        
        header("Location: $redirect");
        exit();
    } else {
        $error = "❌ Invalid Credentials! Please check your details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | <?php echo $SCHOOL_SETTINGS['school_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            width: 400px;
        }
        .form-control, .form-select { height: 50px; }
        .btn-login { height: 50px; font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">🏫 Login Portal</h2>
        <p class="text-muted"><?php echo $SCHOOL_SETTINGS['school_name']; ?></p>
    </div>

    <?php if($error) echo "<div class='alert alert-danger text-center'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold">Select Role</label>
            <select name="role" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff (Teacher, Accountant)</option>
                <option value="student">Student</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Email or Admission No</label>
            <input type="text" name="username" class="form-control" placeholder="Enter ID or Email" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
        </div>

        <div class="mb-3 text-end">
    <a href="forgot_password.php" class="text-decoration-none small">Forgot Password?</a>
</div>

        <button type="submit" name="login_btn" class="btn btn-primary w-100 btn-login">Login Access</button>
    </form>
    
    <div class="text-center mt-3">
        <small class="text-muted">Secure Access System</small>
    </div>
</div>

</body>
</html>