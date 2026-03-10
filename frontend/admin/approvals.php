<?php
include("../includes/header.php");

// FIX: Include helpers.php
include(__DIR__ . "/../../backend/config/helpers.php");

checkRole('admin');

// Approve/Reject handling
if(isset($_GET['approve'])){
    $id = $_GET['approve'];
    $conn->query("UPDATE reservations SET status='Approved' WHERE id=$id");
    setFlash("Reservation approved!", "success");
}
if(isset($_GET['reject'])){
    $id = $_GET['reject'];
    $conn->query("UPDATE reservations SET status='Rejected' WHERE id=$id");
    setFlash("Reservation rejected!", "error");
}

// Fetch pending reservations
$reservations = $conn->query("
    SELECT r.*, u.name as student_name, l.lab_name 
    FROM reservations r
    JOIN users u ON r.user_id=u.id
    JOIN laboratories l ON r.lab_id=l.id
    WHERE r.status='Pending'
");
?>

<h2>Pending Reservations</h2>

<?php echo getFlash(); ?>

<input type="text" id="searchInput" placeholder="Search by student or lab" style="padding:10px;margin-bottom:10px;width:50%;">

<table id="approvalTable" border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse;width:100%;">
<tr>
<th>ID</th>
<th>Student</th>
<th>Lab</th>
<th>Date</th>
<th>Time Slot</th>
<th>Action</th>
</tr>
<?php while($row = $reservations->fetch_assoc()){ ?>
<tr>
    <td><?php echo htmlspecialchars($row['id']); ?></td>
    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
    <td><?php echo htmlspecialchars($row['lab_name']); ?></td>
    <td><?php echo formatDate($row['date']); ?></td>
    <td><?php echo htmlspecialchars($row['time_slot']); ?></td>
    <td>
        <a href="?approve=<?php echo $row['id']; ?>" class="approveBtn">Approve</a> | 
        <a href="?reject=<?php echo $row['id']; ?>" class="rejectBtn">Reject</a>
    </td>
</tr>
<?php } ?>
</table>

<script>
// Confirm Approve/Reject
document.querySelectorAll('.approveBtn').forEach(btn => {
    btn.addEventListener('click', function(e){
        if(!confirm("Are you sure you want to approve this reservation?")) e.preventDefault();
    });
});
document.querySelectorAll('.rejectBtn').forEach(btn => {
    btn.addEventListener('click', function(e){
        if(!confirm("Are you sure you want to reject this reservation?")) e.preventDefault();
    });
});

// Table search
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#approvalTable tr:not(:first-child)');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>