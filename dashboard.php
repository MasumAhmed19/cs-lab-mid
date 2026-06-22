<?php
// ⚠️  INTENTIONALLY VULNERABLE - XSS Lab Demo
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$result = mysqli_query($conn, "SELECT * FROM reports ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crime Report - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="topbar">
        <h2>Crime Reports Dashboard</h2>
        <span>Welcome, <strong><?php echo $_SESSION['user']; ?></strong> |
            <a href="update_email.php">Update Email</a> |
            <a href="search.php">Search</a> |
            <a href="post_report.php">Post Report</a> |
            <a href="logout.php">Logout</a>
        </span>
    </div>

    <h3>All Crime Reports</h3>
    <table>
        <tr>
            <th>ID</th><th>Title</th><th>Description</th><th>Location</th><th>Posted By</th><th>Date</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>

            <!-- ❌ VULNERABLE: Output not escaped - allows Stored XSS -->
            <td><?php echo $row['title']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td><?php echo $row['location']; ?></td>

            <td><?php echo htmlspecialchars($row['posted_by']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
