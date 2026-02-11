<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$id = $_GET['id'];
if (!$id) { header("Location: staff.php"); }

if (isset($_POST['update_staff'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    
    $conn->query("UPDATE staff SET full_name='$name', email='$email', phone='$phone', role='$role' WHERE id='$id'");
    echo "<script>alert('Updated!'); window.location.href='staff.php';</script>";
}

$data = $conn->query("SELECT * FROM staff WHERE id='$id'")->fetch_assoc();
?>

<div class="content" style="margin-left: 0;">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning"><strong>Edit Staff</strong></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3"><label>Name</label><input type="text" name="full_name" class="form-control" value="<?php echo $data['full_name']; ?>"></div>
                            <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?php echo $data['email']; ?>"></div>
                            <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?php echo $data['phone']; ?>"></div>
                            <div class="mb-3">
                                <label>Role</label>
                                <select name="role" class="form-select">
                                    <option value="teacher" <?php if($data['role']=='teacher') echo 'selected'; ?>>Teacher</option>
                                    <option value="accountant" <?php if($data['role']=='accountant') echo 'selected'; ?>>Accountant</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="staff.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="update_staff" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>