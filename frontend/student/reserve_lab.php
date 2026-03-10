<?php
include("../includes/header.php");
include("../../backend/config/helpers.php");
checkRole('student');

// Handle submission
if(isset($_POST['reserve'])){
    $lab_id = $_POST['lab'];
    $date = $_POST['date'];
    $time_slot = $_POST['time_slot'];
    $user_id = $_SESSION['user']['id'];

    // Check conflict
    $check = $conn->query("SELECT * FROM reservations WHERE lab_id=$lab_id AND date='$date' AND time_slot='$time_slot'");
    if($check->num_rows > 0){
        setFlash("Selected slot is already booked!", "error");
    } else {
        $conn->query("INSERT INTO reservations(user_id, lab_id, date, time_slot, status) VALUES($user_id,$lab_id,'$date','$time_slot','Pending')");
        setFlash("Reservation request submitted!", "success");
    }
}

$labs = $conn->query("SELECT * FROM laboratories");
?>

<h2>Reserve Lab</h2>
<?php echo getFlash(); ?>

<form method="POST" id="reserveLabForm">
    <select name="lab" required>
        <option value="">Select Lab</option>
        <?php while($lab = $labs->fetch_assoc()){ ?>
        <option value="<?php echo $lab['id']; ?>"><?php echo $lab['lab_name']; ?></option>
        <?php } ?>
    </select>
    <input type="date" name="date" required>
    <select name="time_slot" required>
        <option value="">Select Time Slot</option>
        <option value="7:30-9:00">7:30-9:00</option>
        <option value="9:00-10:30">9:00-10:30</option>
        <option value="10:30-12:00">10:30-12:00</option>
        <option value="13:00-14:30">13:00-14:30</option>
        <option value="14:30-16:00">14:30-16:00</option>
    </select>
    <button name="reserve" type="submit">Reserve</button>
</form>

<script>
// JS: confirm before submitting
document.getElementById('reserveLabForm').addEventListener('submit', function(e){
    if(!confirm("Submit reservation request?")) e.preventDefault();
});
</script>

<?php include("../includes/footer.php"); ?>