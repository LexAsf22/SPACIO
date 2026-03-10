<?php
include("../includes/header.php");
checkRole('student');

$reservations = $conn->query("
SELECT r.*, l.lab_name
FROM reservations r
LEFT JOIN laboratories l ON r.lab_id = l.id
WHERE r.user_id=".$_SESSION['user']['id']."
ORDER BY r.date DESC
");

if(!$reservations){
    die("SQL Error: ".$conn->error);
}
?>

<h2>My Reservations</h2>

<input type="text" id="searchRes" placeholder="Search..." style="padding:10px;margin:10px 0;width:50%;">

<table id="myResTable" border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse;width:100%;">
<tr>
<th>Type</th>
<th>Name</th>
<th>Date</th>
<th>Time Slot</th>
<th>Status</th>
</tr>

<?php if($reservations->num_rows > 0){ ?>
<?php while($r = $reservations->fetch_assoc()){ ?>
<tr>
<td>Lab</td>
<td><?php echo htmlspecialchars($r['lab_name']); ?></td>
<td><?php echo date("F d, Y", strtotime($r['date'])); ?></td>
<td><?php echo $r['time_slot']; ?></td>
<td><?php echo $r['status']; ?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr>
<td colspan="5">No reservations found.</td>
</tr>
<?php } ?>

</table>

<script>
// Search filter
document.getElementById('searchRes').addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#myResTable tr:not(:first-child)');

    rows.forEach(row=>{
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>