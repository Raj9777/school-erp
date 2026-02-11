<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch Widget Data
$count_students = $conn->query("SELECT count(id) as total FROM students")->fetch_assoc()['total'];
$count_staff = $conn->query("SELECT count(id) as total FROM staff")->fetch_assoc()['total'];
$count_classes = $conn->query("SELECT count(id) as total FROM classes")->fetch_assoc()['total'];
$income = $conn->query("SELECT sum(amount) as total FROM fees")->fetch_assoc()['total'];
?>

<div class="sidebar">
    <div class="sidebar-header">
        <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Admin Panel</small>
        <h5 class="mt-1"><?php echo $SCHOOL_SETTINGS['school_name'] ?? 'School ERP'; ?></h5>
    </div>
    
    <a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
    
    <small class="text-muted ms-3 mt-3 d-block text-uppercase" style="font-size: 10px;">People</small>
    <a href="staff.php"><i class="fas fa-users-cog"></i> Staff Management</a>
    <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
    <a href="id_cards.php"><i class="fas fa-id-card"></i> Generate ID Cards</a>

    <small class="text-muted ms-3 mt-3 d-block text-uppercase" style="font-size: 10px;">Academics</small>
    <a href="classes.php"><i class="fas fa-school"></i> Classes</a>
    <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
<a href="timetable.php"><i class="fas fa-calendar-alt"></i> Class Routine</a>

    <a href="allocate_subjects.php"><i class="fas fa-exchange-alt"></i> Assign Subjects</a>
    <a href="exams.php"><i class="fas fa-edit"></i> Exams</a>
    <a href="enter_marks.php"><i class="fas fa-pen"></i> Enter Marks</a>

    <small class="text-muted ms-3 mt-3 d-block text-uppercase" style="font-size: 10px;">Operations</small>
    <a href="staff_attendance.php"><i class="fas fa-clock"></i> Staff Attendance</a>
    <a href="noticeboard.php"><i class="fas fa-bullhorn"></i> Noticeboard</a>
    <a href="fees.php"><i class="fas fa-money-bill-wave"></i> Fees</a>
    <a href="settings.php"><i class="fas fa-cogs"></i> Settings</a>

    <a href="profile.php"><i class="fas fa-user-shield"></i> My Profile</a>
    <a href="login.php" class="text-warning mt-4"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <nav class="navbar navbar-light bg-white shadow-sm mb-4 rounded">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Dashboard Overview</span>
            <div class="d-flex align-items-center">
                <span class="me-3 fw-bold text-secondary">Welcome, Admin</span>
                <img src="https://via.placeholder.com/40" class="rounded-circle border">
            </div>
        </div>
    </nav>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="stat-card bg-blue">
                <h3><?php echo $count_students; ?></h3>
                <p>Total Students</p>
                <i class="fas fa-users stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card bg-green">
                <h3><?php echo $count_staff; ?></h3>
                <p>Total Staff</p>
                <i class="fas fa-chalkboard-teacher stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card bg-orange">
                <h3><?php echo $count_classes; ?></h3>
                <p>Classes</p>
                <i class="fas fa-book-open stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card bg-red">
                <h3>₹<?php echo number_format($income ?? 0); ?></h3>
                <p>Total Income</p>
                <i class="fas fa-wallet stat-icon"></i>
            </div>
        </div>
    </div>
</div>
</body>
</html>