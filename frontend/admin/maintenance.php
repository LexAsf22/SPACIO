<?php
include("../includes/header.php");
include(__DIR__ . "/../../backend/config/helpers.php");
checkRole('admin');

if(isset($_GET['status'])){
    $id = $_GET['id'];
    $status = $_GET['status'];
    $conn->query("UPDATE issues SET status='$status' WHERE id=$id");
    setFlash("Issue status updated!", "success");
}

$issues = $conn->query("SELECT i.*, u.name as teacher_name 
                        FROM issues i JOIN users u ON i.user_id=u.id 
                        ORDER BY i.created_at DESC");
?>

<h2>Maintenance Requests</h2>
<?php echo getFlash(); ?>

<table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse;width:100%;" id="issuesTable">
<tr>
<th>ID</th>
<th>Teacher</th>
<th>Room</th>
<th>Category</th>
<th>Priority</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php while($i = $issues->fetch_assoc()){ ?>
<tr>
    <td><?php echo $i['id']; ?></td>
    <td><?php echo $i['teacher_name']; ?></td>
    <td><?php echo $i['room']; ?></td>
    <td><?php echo $i['category']; ?></td>
    <td><?php echo $i['priority']; ?></td>
    <td><?php echo $i['status']; ?></td>
    <td>
        <a href="?id=<?php echo $i['id']; ?>&status=In Progress" class="statusBtn">In Progress</a> | 
        <a href="?id=<?php echo $i['id']; ?>&status=Done" class="statusBtn">Done</a>
    </td>
</tr>
<?php } ?>
</table>

<script>
// Confirm status change
document.querySelectorAll('.statusBtn').forEach(btn=>{
    btn.addEventListener('click', function(e){
        if(!confirm("Change status?")) e.preventDefault();
    });
});
</script>

<?php include("../includes/footer.php"); ?>