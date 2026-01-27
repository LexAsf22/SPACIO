<?php
include("../db.php");

$result = mysqli_query($conn,
    "SELECT bookings.id, users.name, inventory.item_name, bookings.date, bookings.status
     FROM bookings
     JOIN users ON bookings.user_id = users.id
     JOIN inventory ON bookings.item_id = inventory.id"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bookings</title>
    <style>
        /* ============================================
   SCHOOL LAB BOOKING SYSTEM - CSS STYLESHEET
   Professional Campus Website Design
   ============================================ */

/* CSS Variables for Campus Theme */
:root {
    /* Primary Colors - Academic Green */
    --primary-color: #1b5e20;
    --primary-dark: #0d3d11;
    --primary-light: #4caf50;
    
    /* Secondary Colors - School Gold/Yellow */
    --secondary-color: #ffc107;
    --secondary-dark: #f9a825;
    
    /* Accent Colors */
    --accent-blue: #2196f3;
    --accent-red: #f44336;
    
    /* Status Colors */
    --status-pending: #ff9800;
    --status-approved: #4caf50;
    --status-rejected: #f44336;
    --status-completed: #2196f3;
    
    /* Neutral Colors */
    --text-dark: #212121;
    --text-medium: #424242;
    --text-light: #666666;
    --bg-white: #ffffff;
    --bg-light: #f8f9fa;
    --bg-gray: #e9ecef;
    --border-color: #dee2e6;
    
    /* Shadows */
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
    --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
    --shadow-xl: 0 12px 32px rgba(0,0,0,0.15);
    
    /* Border Radius */
    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 16px;
    
    /* Transitions */
    --transition: all 0.3s ease;
    --transition-fast: all 0.15s ease;
}

/* ============================================
   RESET & BASE STYLES
   ============================================ */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    font-size: 16px;
    scroll-behavior: smooth;
}

body {
    font-family: "Segoe UI", "Inter", -apple-system, BlinkMacSystemFont, "Roboto", Arial, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #e8f5e9 100%);
    color: var(--text-dark);
    line-height: 1.6;
    min-height: 100vh;
    padding: 0;
    margin: 0;
}

/* ============================================
   PAGE CONTAINER & LAYOUT
   ============================================ */

.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* ============================================
   HEADER SECTION
   ============================================ */

.page-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 40px 30px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    transform: translate(30%, -30%);
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    position: relative;
    z-index: 1;
}

.page-header .subtitle {
    font-size: 1.1rem;
    opacity: 0.95;
    font-weight: 300;
    position: relative;
    z-index: 1;
}

/* ============================================
   MAIN CONTENT SECTION
   ============================================ */

.content-section {
    background: var(--bg-white);
    padding: 40px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 30px;
}

/* ============================================
   HEADINGS
   ============================================ */

h1 {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 25px;
    font-weight: 700;
}

h2 {
    color: var(--primary-color);
    margin-bottom: 25px;
    font-size: 2rem;
    font-weight: 600;
    border-left: 5px solid var(--secondary-color);
    padding-left: 20px;
    display: flex;
    align-items: center;
}

h2::before {
    content: "📚";
    margin-right: 12px;
    font-size: 1.8rem;
}

h3 {
    font-size: 1.5rem;
    color: var(--text-dark);
    margin-bottom: 15px;
    font-weight: 600;
}

/* ============================================
   TABLE STYLES - MODERN DESIGN
   ============================================ */

.table-container {
    overflow-x: auto;
    margin-bottom: 30px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: var(--bg-white);
    border-radius: var(--radius-md);
    overflow: hidden;
    font-size: 1rem;
}

thead {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    position: relative;
}

thead::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--secondary-color);
}

th {
    color: white;
    padding: 18px 20px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

tbody tr {
    transition: var(--transition);
    border-bottom: 1px solid var(--border-color);
}

tbody tr:last-child {
    border-bottom: none;
}

tbody tr:hover {
    background: linear-gradient(90deg, #f1f8f4 0%, transparent 100%);
    transform: scale(1.005);
    box-shadow: 0 2px 8px rgba(27, 94, 32, 0.1);
}

td {
    padding: 16px 20px;
    color: var(--text-medium);
    vertical-align: middle;
}

/* Alternating Row Colors */
tbody tr:nth-child(even) {
    background-color: #fafafa;
}

tbody tr:nth-child(even):hover {
    background: linear-gradient(90deg, #f1f8f4 0%, #fafafa 100%);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-light);
}

.empty-state::before {
    content: "📋";
    display: block;
    font-size: 4rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 1.1rem;
    margin: 0;
}

/* ============================================
   STATUS BADGES
   ============================================ */

.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
    letter-spacing: 0.3px;
    box-shadow: var(--shadow-sm);
}

/* Status: Pending */
td:last-child:contains("pending"),
.status-badge.pending,
td[data-status="pending"] {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    color: #e65100;
}

/* Status: Approved */
td:last-child:contains("approved"),
.status-badge.approved,
td[data-status="approved"] {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    color: #1b5e20;
}

/* Status: Rejected */
td:last-child:contains("rejected"),
.status-badge.rejected,
td[data-status="rejected"] {
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    color: #c62828;
}

/* Status: Completed */
td:last-child:contains("completed"),
.status-badge.completed,
td[data-status="completed"] {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    color: #1565c0;
}

/* Generic status styling for last column */
td:last-child {
    font-weight: 600;
}

/* ============================================
   NAVIGATION & LINKS
   ============================================ */

a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

a:hover {
    color: var(--primary-light);
}

a:focus {
    outline: 2px solid var(--accent-blue);
    outline-offset: 3px;
    border-radius: var(--radius-sm);
}

/* Back Button / Navigation Link */
.back-link,
a[href*="dashboard"] {
    display: inline-flex;
    align-items: center;
    padding: 12px 24px;
    background: var(--bg-white);
    border: 2px solid var(--primary-color);
    border-radius: var(--radius-md);
    color: var(--primary-color);
    font-weight: 600;
    font-size: 1rem;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    margin-top: 20px;
}

.back-link:hover,
a[href*="dashboard"]:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(-5px);
    box-shadow: var(--shadow-md);
}

.back-link::before,
a[href*="dashboard"]::before {
    font-size: 1.2rem;
    transition: var(--transition);
}

.back-link:hover::before,
a[href*="dashboard"]:hover::before {
    transform: translateX(-3px);
}

/* ============================================
   BUTTONS
   ============================================ */

button, .btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    display: inline-block;
}

button:hover, .btn:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

button:active, .btn:active {
    transform: translateY(0);
}

button.secondary, .btn.secondary {
    background: var(--accent-blue);
}

button.secondary:hover, .btn.secondary:hover {
    background: #1976d2;
}

/* ============================================
   BREADCRUMBS
   ============================================ */

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
    padding: 12px 20px;
    background: var(--bg-white);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-sm);
    font-size: 0.95rem;
}

.breadcrumb a {
    color: var(--text-light);
    font-weight: 500;
}

.breadcrumb a:hover {
    color: var(--primary-color);
}

.breadcrumb span {
    color: var(--text-light);
}

.breadcrumb .current {
    color: var(--primary-color);
    font-weight: 600;
}

/* ============================================
   ALERTS & NOTIFICATIONS
   ============================================ */

.alert {
    padding: 16px 20px;
    border-radius: var(--radius-sm);
    margin-bottom: 25px;
    border-left: 4px solid;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert::before {
    font-size: 1.5rem;
}

.alert.success {
    background: #e8f5e9;
    border-color: var(--status-approved);
    color: var(--primary-dark);
}

.alert.success::before {
    content: "✅";
}

.alert.info {
    background: #e3f2fd;
    border-color: var(--accent-blue);
    color: #0d47a1;
}

.alert.info::before {
    content: "ℹ️";
}

.alert.warning {
    background: #fff3e0;
    border-color: var(--status-pending);
    color: #e65100;
}

.alert.warning::before {
    content: "⚠️";
}

.alert.error {
    background: #ffebee;
    border-color: var(--status-rejected);
    color: #c62828;
}

.alert.error::before {
    content: "❌";
}

/* ============================================
   LOADING STATE
   ============================================ */

.loading {
    text-align: center;
    padding: 40px;
    color: var(--text-light);
}

.spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid var(--bg-gray);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ============================================
   FOOTER
   ============================================ */

.page-footer {
    background: var(--primary-dark);
    color: white;
    text-align: center;
    padding: 30px 20px;
    margin-top: 60px;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.page-footer p {
    margin: 5px 0;
    opacity: 0.9;
}

.page-footer a {
    color: var(--secondary-color);
    font-weight: 600;
}

.page-footer a:hover {
    color: var(--secondary-dark);
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */

@media (max-width: 1024px) {
    .page-wrapper {
        padding: 30px 15px;
    }
    
    .content-section {
        padding: 30px 25px;
    }
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 2rem;
    }
    
    h2 {
        font-size: 1.6rem;
    }
    
    .content-section {
        padding: 25px 20px;
    }
    
    /* Responsive Table */
    .table-container {
        border-radius: 0;
        margin-left: -20px;
        margin-right: -20px;
    }
    
    table {
        font-size: 0.9rem;
    }
    
    th, td {
        padding: 12px 10px;
    }
    
    th {
        font-size: 0.8rem;
    }
    
    /* Stack table on mobile if needed */
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}

@media (max-width: 480px) {
    .page-header {
        padding: 30px 20px;
    }
    
    .page-header h1 {
        font-size: 1.7rem;
    }
    
    h2 {
        font-size: 1.4rem;
        padding-left: 15px;
    }
    
    .content-section {
        padding: 20px 15px;
    }
}

/* ============================================
   UTILITY CLASSES
   ============================================ */

.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

.mb-0 { margin-bottom: 0; }
.mb-1 { margin-bottom: 10px; }
.mb-2 { margin-bottom: 20px; }
.mb-3 { margin-bottom: 30px; }

.mt-0 { margin-top: 0; }
.mt-1 { margin-top: 10px; }
.mt-2 { margin-top: 20px; }
.mt-3 { margin-top: 30px; }

.p-0 { padding: 0; }
.p-1 { padding: 10px; }
.p-2 { padding: 20px; }
.p-3 { padding: 30px; }

/* ============================================
   PRINT STYLES
   ============================================ */

@media print {
    body {
        background: white;
    }
    
    .page-header,
    .back-link,
    a[href*="dashboard"],
    .page-footer {
        display: none;
    }
    
    .content-section {
        box-shadow: none;
        padding: 0;
    }
    
    table {
        box-shadow: none;
    }
    
    tbody tr:hover {
        background: transparent;
        transform: none;
    }
}
</style>
</head>
<body>

<h2>Lab Bookings</h2>

<table>
<tr>
    <th>Student</th>
    <th>Item</th>
    <th>Date</th>
    <th>Status</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['item_name'] ?></td>
    <td><?= $row['date'] ?></td>
    <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<a href="../dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>
