<?php 
include 'header.php'; 
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff')) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['collect_fee'])) {
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount'];
    $month = $_POST['month_name'];
    $date = $_POST['payment_date'];

    $sql = "INSERT INTO fees (student_id, amount, month_name, payment_date) VALUES ('$student_id', '$amount', '$month', '$date')";
    if ($conn->query($sql)) {
        $last_id = $conn->insert_id;
        $msg = "<div class='alert alert-success'>✅ Payment Recorded! <a href='print_receipt.php?id=$last_id' target='_blank' class='btn btn-sm btn-dark ms-3'>Print Receipt</a></div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

$students = $conn->query("SELECT * FROM students ORDER BY full_name ASC");
$recent_fees = $conn->query("SELECT fees.*, students.full_name FROM fees JOIN students ON fees.student_id = students.id ORDER BY fees.id DESC LIMIT 10");
?>

<div class="sidebar">
    <div class="sidebar-header"><h4>🏦 FINANCE</h4></div>
    
    <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="staff.php"><i class="fas fa-users-cog"></i> Staff</a>
        <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="classes.php"><i class="fas fa-school"></i> Classes</a>
        <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a> <a href="fees.php" class="active"><i class="fas fa-money-bill-wave"></i> Fees</a>
    <?php else: ?>
        <a href="staff_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="fees.php" class="active"><i class="fas fa-money-bill-wave"></i> Collect Fees</a>
    <?php endif; ?>

    <a href="login.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h3 class="fw-bold mb-4"><i class="fas fa-cash-register text-success"></i> Fee Collection</h3>
    <div class="row">
        <div class="col-md-5">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-success text-white">Record Payment</div>
                <div class="card-body">
                    <?php if(isset($msg)) echo $msg; ?>
                    <form method="POST">
                        <div class="mb-3"><label>Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">-- Choose --</option>
                                <?php while($row = $students->fetch_assoc()) { echo "<option value='" . $row['id'] . "'>" . $row['full_name'] . " (" . $row['admission_no'] . ")</option>"; } ?>
                            </select>
                        </div>
                        <div class="mb-3"><label>Amount (₹)</label><input type="number" name="amount" class="form-control" required></div>
                        <div class="mb-3"><label>Month/Description</label><input type="text" name="month_name" class="form-control" required></div>
                        <div class="mb-3"><label>Date</label><input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                        <button type="submit" name="collect_fee" class="btn btn-success w-100">Make Payment</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card card-box shadow-sm">
                <div class="card-header bg-dark text-white">Recent Transactions</div>
                <div class="card-body">
                    <table class="table table-striped align-middle table-sm">
                        <thead><tr><th>ID</th><th>Student</th><th>Amount</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php 
                            if ($recent_fees->num_rows > 0) {
                                while($row = $recent_fees->fetch_assoc()) {
                                    echo "<tr><td>" . $row['id'] . "</td>
                                            <td>" . $row['full_name'] . "<br><small>" . $row['month_name'] . "</small></td>
                                            <td class='text-success fw-bold'>₹" . number_format($row['amount']) . "</td>
                                            <td>" . date('d M', strtotime($row['payment_date'])) . "</td>
                                            <td><a href='print_receipt.php?id=" . $row['id'] . "' target='_blank' class='btn btn-sm btn-secondary'><i class='fas fa-print'></i></a></td></tr>";
                                }
                            } else { echo "<tr><td colspan='5' class='text-center'>No payments.</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>