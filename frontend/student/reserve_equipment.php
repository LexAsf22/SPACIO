<?php
session_start();
include("../../backend/config/database.php");
include("../../backend/config/auth.php");
include("../../backend/config/helpers.php"); // IMPORTANT
?>

// Handle submission
if(isset($_POST['reserve'])){
    $equipment_id = $_POST['equipment'];
    $date = $_POST['date'];
    $user_id = $_SESSION['user']['id'];

    // Check availability
    $check = $conn->query("SELECT * FROM reservations WHERE equipment_id=$equipment_id AND date='$date'");
    if($check->num_rows > 0){
        setFlash("Equipment already reserved!", "error");
    } else {
        $conn->query("INSERT INTO reservations(user_id, equipment_id, date, status) VALUES($user_id,$equipment_id,'$date','Pending')");
        setFlash("Equipment reservation submitted!", "success");
    }
}

$equipment = $conn->query("SELECT e.*, l.lab_name FROM equipment e JOIN laboratories l ON e.lab_id=l.id");
?>

<h2>Reserve Equipment</h2>
<?php echo getFlash(); ?>

<form method="POST" id="reserveEqForm">
    <select name="equipment" required>
        <option value="">Select Equipment</option>
        <?php while($e = $equipment->fetch_assoc()){ ?>
        <option value="<?php echo $e['id']; ?>"><?php echo $e['equipment_name']." (".$e['lab_name'].")"; ?></option>
        <?php } ?>
    </select>
    <input type="date" name="date" required>
    <button name="reserve" type="submit">Reserve</button>
</form>

<script>
// JS confirm
document.getElementById('reserveEqForm').addEventListener('submit', function(e){
    if(!confirm("Submit equipment reservation?")) e.preventDefault();
});
</script>

<?php include("../includes/footer.php"); ?>