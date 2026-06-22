<?php
// ⚠️  INTENTIONALLY VULNERABLE - SQL Injection Lab Demo
session_start();
include 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ❌ VULNERABLE: Raw user input directly in SQL query (no sanitization)
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    // Debug: show the query (for lab demonstration)
    $debug_query = $query;

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crime Report - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Crime Report System - Login</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (!empty($debug_query)): ?>
        <div class="debug">
            <strong>DEBUG - SQL Query:</strong><br>
            <code><?php echo htmlspecialchars($debug_query); ?></code>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Username:</label>
        <input type="text" name="username" placeholder="e.g. admin" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <br>
    <small>
        <strong>Lab Hint (SQL Injection):</strong><br>
        Try username: <code>' OR '1'='1' --</code> and any password
    </small>
</div>
</body>
</html>
