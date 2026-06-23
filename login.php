<?php
// INTENTIONALLY VULNERABLE - SQL Injection
session_start();
include 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // VULNERABLE: Raw input directly in SQL query
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    $debug_query = $query;

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user']  = $user['username'];
        $_SESSION['email'] = $user['email'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrimeWatch — Sign In</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="badge">🔍</div>
      <span>CrimeWatch Portal</span>
    </div>

    <h1>Welcome back</h1>
    <p class="subtitle">Sign in to access the crime reporting dashboard.</p>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠ <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($debug_query)): ?>
      <div class="debug-strip">SQL: <?php echo htmlspecialchars($debug_query); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter your username" required autocomplete="off">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary">Sign In</button>
    </form>
  </div>
</div>
</body>
</html>
