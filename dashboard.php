<?php
// INTENTIONALLY VULNERABLE - Stored XSS (output not escaped)
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
include 'config.php';

$result       = mysqli_query($conn, "SELECT * FROM reports ORDER BY created_at DESC");
$total        = mysqli_num_rows($result);
$result_count = mysqli_query($conn, "SELECT COUNT(*) as c FROM reports");
$row_count    = mysqli_fetch_assoc($result_count);

$user_result  = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
$user_count   = mysqli_fetch_assoc($user_result);

// Re-run for table display
$result = mysqli_query($conn, "SELECT * FROM reports ORDER BY created_at DESC");
$initial = strtoupper(substr($_SESSION['user'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrimeWatch — Dashboard</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="badge">🔍</div>
      <span>CrimeWatch<br>Portal</span>
    </div>
    <div class="nav-section">
      <div class="nav-label">Menu</div>
      <a href="dashboard.php" class="nav-item active"><span class="icon">📋</span> Dashboard</a>
      <a href="post_report.php" class="nav-item"><span class="icon">➕</span> Post Report</a>
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

  <!-- Main -->
  <main class="main">
    <div class="page-header">
      <h1>Dashboard</h1>
      <p>Overview of all submitted crime reports.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card accent">
        <div class="label">Total Reports</div>
        <div class="value"><?php echo $row_count['c']; ?></div>
        <div class="sub">All time submissions</div>
      </div>
      <div class="stat-card">
        <div class="label">Registered Users</div>
        <div class="value"><?php echo $user_count['c']; ?></div>
        <div class="sub">Active accounts</div>
      </div>
      <div class="stat-card">
        <div class="label">Logged In As</div>
        <div class="value" style="font-size:18px;"><?php echo htmlspecialchars($_SESSION['user']); ?></div>
        <div class="sub"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>All Crime Reports</h3>
        <a href="post_report.php" class="btn btn-secondary btn-sm">+ New Report</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Description</th>
              <th>Location</th>
              <th>Posted By</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><span class="badge-id">#<?php echo $row['id']; ?></span></td>
              <!-- VULNERABLE: Not escaped — Stored XSS -->
              <td><?php echo $row['title']; ?></td>
              <td><?php echo $row['description']; ?></td>
              <td class="text-muted"><?php echo $row['location']; ?></td>
              <td><?php echo htmlspecialchars($row['posted_by']); ?></td>
              <td class="text-muted text-small"><?php echo $row['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</body>
</html>
