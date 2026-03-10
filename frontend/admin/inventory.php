<?php
include("../includes/header.php");
include(__DIR__ . "/../../backend/config/helpers.php");
checkRole('admin');

if(isset($_POST['add'])){
    $lab_id = $_POST['lab'];
    $name = $_POST['equipment_name'];
    $qty = $_POST['quantity'];
    $conn->query("INSERT INTO equipment(equipment_name, lab_id, quantity) VALUES('$name',$lab_id,$qty)");
    setFlash("Equipment added!", "success");
}

$equipment = $conn->query("SELECT e.*, l.lab_name FROM equipment e JOIN laboratories l ON e.lab_id=l.id");
$labs = $conn->query("SELECT * FROM laboratories");
?>

<h2>Inventory Management</h2>
<?php echo getFlash(); ?>

<form method="POST" style="margin-bottom:20px;">
    <h3>Add Equipment</h3>
    <select name="lab" required>
        <option value="">Select Lab</option>
        <?php while($lab = $labs->fetch_assoc()){ ?>
        <option value="<?php echo $lab['id']; ?>"><?php echo $lab['lab_name']; ?></option>
        <?php } ?>
    </select>
    <input type="text" name="equipment_name" placeholder="Equipment Name" required>
    <input type="number" name="quantity" placeholder="Quantity" required>
    <button name="add">Add Equipment</button>
</form>

<input type="text" id="searchEquipment" placeholder="Search equipment..." style="padding:10px;margin-bottom:10px;width:50%;">
<table id="equipmentTable" border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse;width:100%;">
<tr>
<th>ID</th>
<th>Name</th>
<th>Lab</th>
<th>Quantity</th>
<th>Status</th>
</tr>
<?php while($e = $equipment->fetch_assoc()){ ?>
<tr>
    <td><?php echo $e['id']; ?></td>
    <td><?php echo $e['equipment_name']; ?></td>
    <td><?php echo $e['lab_name']; ?></td>
    <td><?php echo $e['quantity']; ?></td>
    <td><?php echo $e['status']; ?></td>
</tr>
<?php } ?>
</table>

<script>
// Equipment search
document.getElementById('searchEquipment').addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#equipmentTable tr:not(:first-child)');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php include("../includes/footer.php"); ?>