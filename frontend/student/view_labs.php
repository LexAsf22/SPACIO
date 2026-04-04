<?php
include("../includes/header.php");
checkRole('student');

// Pagination setup
$perPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Determine current time slot
$currentTime = date("H:i");
if ($currentTime >= "07:30" && $currentTime < "09:00") $slot = "7:30-9:00";
elseif ($currentTime >= "09:00" && $currentTime < "10:30") $slot = "9:00-10:30";
elseif ($currentTime >= "10:30" && $currentTime < "12:00") $slot = "10:30-12:00";
elseif ($currentTime >= "13:00" && $currentTime < "14:30") $slot = "13:00-14:30";
elseif ($currentTime >= "14:30" && $currentTime < "16:00") $slot = "14:30-16:00";
else $slot = null;

// Count total labs
$totalLabs = $conn->query("SELECT COUNT(*) AS total FROM laboratories")->fetch_assoc()['total'];
$totalPages = ceil($totalLabs / $perPage);

// Fetch labs with status
$labs = $conn->query("
    SELECT l.*,
        CASE WHEN r.id IS NOT NULL THEN 'In Use' ELSE 'Available' END AS computed_status
    FROM laboratories l
    LEFT JOIN reservations r 
        ON l.id = r.lab_id
        AND r.status = 'Approved'
        AND r.date = CURDATE()
        ".($slot ? "AND r.time_slot = '$slot'" : "")."
    ORDER BY l.lab_name
    LIMIT $offset, $perPage
");
?>

<style>
/* Table styling */
table { width: 100%; border-collapse: collapse; margin-top: 10px; font-family: Arial, sans-serif; }
th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
th { background: #f2f2f2; }

/* Search box */
#searchLabs { padding: 10px; width: 50%; margin-bottom: 10px; display: block; }

/* Pagination */
.pagination { text-align: center; margin-top: 20px; }
.pagination a { padding: 5px 12px; margin: 0 5px; border: 1px solid #ccc; text-decoration: none; color: #333; border-radius:5px; }
.pagination a.disabled { color: #aaa; border-color: #aaa; pointer-events: none; }
.pagination a:hover:not(.disabled) { background:#2c5f2e; color:white; }
.pagination span { margin: 0 10px; font-weight: bold; }
</style>

<h2>Laboratory Status</h2>

<input type="text" id="searchLabs" placeholder="Search labs...">

<table id="labsTable">
<thead>
<tr>
    <th>Lab Name</th>
    <th>Campus</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<?php while ($lab = $labs->fetch_assoc()):
    $status = $lab['computed_status'];
    $color = ($status == 'In Use') ? 'color:red; font-weight:bold;' : 'color:green; font-weight:bold;';
?>
<tr>
<td><?php echo htmlspecialchars($lab['lab_name']); ?></td>
<td><?php echo htmlspecialchars($lab['campus']); ?></td>
<td style="<?php echo $color; ?>"><?php echo $status; ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<!-- Pagination -->
<div class="pagination">
    <?php if($page > 1): ?>
        <a href="?page=<?php echo $page-1; ?>">&#8592; Prev</a>
    <?php else: ?>
        <a class="disabled">&#8592; Prev</a>
    <?php endif; ?>

    <span><?php echo $page; ?> / <?php echo $totalPages; ?></span>

    <?php if($page < $totalPages): ?>
        <a href="?page=<?php echo $page+1; ?>">Next &#8594;</a>
    <?php else: ?>
        <a class="disabled">Next &#8594;</a>
    <?php endif; ?>
</div>

<script>
// Live search filter
const searchInput = document.getElementById("searchLabs");
searchInput.addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("#labsTable tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
});
</script>

<?php include("../includes/footer.php"); ?>