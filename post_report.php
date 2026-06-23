<?php
// INTENTIONALLY VULNERABLE - Stored XSS
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
include 'config.php';

$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location    = mysqli_real_escape_string($conn, $_POST['location']);
    $posted_by   = $_SESSION['user'];

    // VULNERABLE: Stores raw HTML/JS — Stored XSS when displayed without escaping
    $query = "INSERT INTO reports (title, description, location, posted_by)
              VALUES ('$title', '$description', '$location', '$posted_by')";
    mysqli_query($conn, $query);
    $success = "Report submitted successfully.";
}

$initial = strtoupper(substr($_SESSION['user'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrimeWatch — Post Report</title>
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
      <a href="post_report.php" class="nav-item active"><span class="icon">➕</span> Post Report</a>
      <a href="search.php" class="nav-item"><span class="icon">🔎</span> Search</a>
      <a href="update_email.php" class="nav-item"><span class="icon">✉️</span> Update Email</a>
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
      <h1>Post a Crime Report</h1>
      <p>Submit a new incident for review.</p>
    </div>

    <div class="form-card">
      <?php if ($success): ?>
        <div class="alert alert-success">✓ <?php echo $success; ?></div>
      <?php endif; ?>

      <form method="POST" action="post_report.php">
        <div class="form-group">
          <label>Report Title</label>
          <input type="text" name="title" placeholder="e.g. Robbery at Central Mall" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" placeholder="Describe the incident in detail..." required></textarea>
        </div>
        <div class="form-group">
          <label>Location</label>
          <input type="text" name="location" placeholder="e.g. Main Street, Downtown">
        </div>
        <div class="divider"></div>
        <button type="submit" class="btn btn-primary">Submit Report</button>
      </form>
    </div>
  </main>
</div>
</body>
</html>
