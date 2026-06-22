<?php
// ⚠️  INTENTIONALLY VULNERABLE - SQL Injection + CSRF Lab Demo
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = $_POST['email'];
    $username  = $_SESSION['user'];

    // ❌ NO CSRF TOKEN CHECK (for CSRF lab later)
    // ❌ VULNERABLE SQL: Direct string interpolation
    $query = "UPDATE users SET email = '$new_email' WHERE username = '$username'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['email'] = $new_email;
        $success = "Email updated to: $new_email";
    } else {
        $error = "Update failed.";
    }
}

// Fetch current email
$res = mysqli_query($conn, "SELECT email FROM users WHERE username = '" . $_SESSION['user'] . "'");
$current = mysqli_fetch_assoc($res);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Email</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Update Email</h2>
    <a href="dashboard.php">← Back to Dashboard</a><br><br>

    <p>Current email: <strong><?php echo htmlspecialchars($current['email']); ?></strong></p>

    <?php if ($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <!-- ❌ NO CSRF TOKEN in form (for CSRF lab later with Burp Suite) -->
    <form method="POST" action="update_email.php">
        <label>New Email:</label>
        <input type="email" name="email" required>
        <button type="submit">Update Email</button>
    </form>

    <br>
    <small>
        <strong>Note for CSRF lab (Burp Suite):</strong><br>
        This form has no CSRF token. An attacker page can submit this form on behalf of a logged-in victim.
    </small>
</div>
</body>
</html>
