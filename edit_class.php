<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$id = $_GET['id'];
if (isset($_POST['update_class'])) {
    $name = clean_input($_POST['class_name']); // Using our new security function
    $sec = clean_input($_POST['section']);
    $conn->query("UPDATE classes SET class_name='$name', section='$sec' WHERE id='$id'");
    header("Location: classes.php");
}

$data = $conn->query("SELECT * FROM classes WHERE id='$id'")->fetch_assoc();
?>

<div class="content" style="margin-left:0; padding:50px;">
    <div class="card" style="max-width:500px; margin:auto;">
        <div class="card-header">Edit Class</div>
        <div class="card-body">
            <form method="POST">
                <input type="text" name="class_name" class="form-control mb-3" value="<?php echo $data['class_name']; ?>" required>
                <input type="text" name="section" class="form-control mb-3" value="<?php echo $data['section']; ?>" required>
                <button type="submit" name="update_class" class="btn btn-primary w-100">Update</button>
            </form>
        </div>
    </div>
</div>