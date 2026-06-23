<?php
// INTENTIONALLY VULNERABLE - CSRF (no token) + SQL Injection
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
include 'config.php';

$success = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = $_POST['email'];
    $username  = $_SESSION['user'];

    // VULNERABLE: No CSRF token check + raw string in SQL
    $query = "UPDATE users SET email = '$new_email' WHERE username = '$username'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['email'] = $new_email;
        $success = "Email address updated successfully.";
    } else {
        $error = "Failed to update email.";
    }
}

$res     = mysqli_query($conn, "SELECT email FROM users WHERE username = '" . $_SESSION['user'] . "'");
$current = mysqli_fetch_assoc($res);
$initial = strtoupper(substr($_SESSION['user'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrimeWatch — Update Email</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="badge">🔍</div>
      <span>CrimeWatch<br>Portal</span>
    </div>
    <div class="nav-section">
      <div class="nav-label">Menu</div>
      <a href="dashboard.php" class="nav-item"><span class="icon">📋</span> Dashboard</a>
      <a href="post_report.php" class="nav-item"><span class="icon">➕</span> Post Report</a>
      <a href="search.php" class="nav-item"><span class="icon">🔎</span> Search</a>
      <a href="update_email.php" class="nav-item active"><span class="icon">✉️</span> Update Email</a>
    </div>
    <div class="sidebar-footer">
      <div class="user-chip">
        <div class="user-avatar"><?php echo $initial; ?></div>
        <div class="user-info">
          <div class="name"><?php echo htmlspecialchars($_SESSION['user']); ?></div>
          <div class="role">Officer</div>
        </div>
        <a href="logout.php" title="Logout" style="color:var(--muted);font-size:16px;text-decoration:none;">⏏</a>
      </div>
    </div>
  </aside>

  <main class="main">
    <div class="page-header">
      <h1>Update Email</h1>
      <p>Change the email address linked to your account.</p>
    </div>

    <div class="form-card">
      <div class="form-group">
        <label>Current Email</label>
        <input type="text" value="<?php echo htmlspecialchars($current['email']); ?>" disabled style="opacity:0.5;">
      </div>

      <div class="divider"></div>

      <?php if ($success): ?>
        <div class="alert alert-success">✓ <?php echo $success; ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?php echo $error; ?></div>
      <?php endif; ?>

      <!-- VULNERABLE: No CSRF token in this form -->
      <form method="POST" action="update_email.php">
        <div class="form-group">
          <label>New Email Address</label>
          <input type="email" name="email" placeholder="Enter new email address" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Email</button>
      </form>
    </div>
  </main>
</div>
</body>
</html>
