<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
if (!$id) { header("Location: students.php"); }

// --- HANDLE UPDATE ---
if (isset($_POST['update_student'])) {
    $name = $_POST['full_name'];
    $father = $_POST['father_name'];
    $phone = $_POST['phone'];
    $class_id = $_POST['class_id'];
    
    $sql = "UPDATE students SET full_name='$name', father_name='$father', phone='$phone', class_id='$class_id' WHERE id='$id'";
    
    if ($conn->query($sql)) {
        echo "<script>alert('Updated Successfully!'); window.location.href='students.php';</script>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Fetch Current Data
$data = $conn->query("SELECT * FROM students WHERE id='$id'")->fetch_assoc();
$classes = $conn->query("SELECT * FROM classes");
?>

<div class="content" style="margin-left: 0;">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark"><strong>Edit Student: <?php echo $data['admission_no']; ?></strong></div>
                    <div class="card-body">
                        <?php if(isset($msg)) echo $msg; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label>Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo $data['full_name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Father Name</label>
                                <input type="text" name="father_name" class="form-control" value="<?php echo $data['father_name']; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $data['phone']; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Class</label>
                                <select name="class_id" class="form-select">
                                    <?php while($c = $classes->fetch_assoc()) { 
                                        $sel = ($data['class_id'] == $c['id']) ? 'selected' : '';
                                        echo "<option value='".$c['id']."' $sel>".$c['class_name']."</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="students.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="update_student" class="btn btn-primary">Update Details</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>