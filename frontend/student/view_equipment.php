<?php
include("../includes/header.php");
checkRole('student');

// Pagination setup
$perPage = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Total equipment count
$totalEquipments = $conn->query("SELECT COUNT(*) AS total FROM equipment")->fetch_assoc()['total'];
$totalPages = ceil($totalEquipments / $perPage);

// Fetch equipment with lab names
$equipments = $conn->query("
    SELECT e.*, l.lab_name
    FROM equipment e
    JOIN laboratories l ON e.lab_id = l.id
    ORDER BY e.equipment_name ASC
    LIMIT $offset, $perPage
");
?>

<style>
/* Table Styling */
table { width: 100%; border-collapse: collapse; margin-top: 10px; font-family: Arial, sans-serif; }
th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
th { background: #f2f2f2; }

/* Pagination Styling */
.pagination { margin-top: 20px; text-align: center; font-size: 16px; }
.pagination a { padding: 5px 12px; margin: 0 5px; border: 1px solid #ccc; text-decoration:none; color:#333; border-radius:5px; }
.pagination a.disabled { color:#aaa; border-color:#aaa; pointer-events:none; }
.pagination a:hover:not(.disabled) { background:#2c5f2e; color:white; }
.pagination span { margin: 0 10px; font-weight:bold; }

/* Search Box */
#searchEquipment { padding:10px; width:50%; margin-top:20px; margin-bottom:10px; display:block; }
</style>

<h2>View Equipment</h2>

<input type="text" id="searchEquipment" placeholder="Search equipment...">

<table id="equipmentTable">
    <thead>
        <tr>
            <th>Equipment Name</th>
            <th>Lab</th>
            <th>Quantity</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while($eq = $equipments->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($eq['equipment_name']); ?></td>
            <td><?php echo htmlspecialchars($eq['lab_name']); ?></td>
            <td><?php echo htmlspecialchars($eq['quantity']); ?></td>
            <td style="color:<?php echo ($eq['status']=='Available')?'green':'red'; ?>; font-weight:bold;">
                <?php echo htmlspecialchars($eq['status']); ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Centered Pagination -->
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
// Live Search Filter
const searchInput = document.getElementById("searchEquipment");
searchInput.addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("#equipmentTable tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
});
</script>

<?php include("../includes/footer.php"); ?>