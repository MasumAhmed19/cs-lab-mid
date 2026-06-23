<?php
// INTENTIONALLY VULNERABLE - SQL Injection + Reflected XSS
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
include 'config.php';

$results = [];
$search  = "";
$debug_query = "";

if (isset($_GET['q'])) {
    $search = $_GET['q'];

    // VULNERABLE: Raw input in SQL query
    $query = "SELECT * FROM reports WHERE title LIKE '%$search%' OR description LIKE '%$search%'";
    $debug_query = $query;
    $res = mysqli_query($conn, $query);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) { $results[] = $row; }
    }
}

$initial = strtoupper(substr($_SESSION['user'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrimeWatch — Search</title>
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
      <a href="search.php" class="nav-item active"><span class="icon">🔎</span> Search</a>
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
      <h1>Search Reports</h1>
      <p>Search by title or description keywords.</p>
    </div>

    <form method="GET" action="search.php">
      <div class="search-bar">
        <!-- VULNERABLE: $search echoed without escaping — Reflected XSS -->
        <input type="text" name="q" value="<?php echo $search; ?>" placeholder="Search reports...">
        <button type="submit" class="btn btn-primary" style="width:auto;">Search</button>
      </div>
    </form>

    <?php if ($debug_query): ?>
      <div class="debug-strip">SQL: <?php echo htmlspecialchars($debug_query); ?></div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
      <div class="card">
        <div class="card-header">
          <h3>Results <span class="text-muted">(<?php echo count($results); ?> found)</span></h3>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>ID</th><th>Title</th><th>Description</th><th>Location</th><th>Posted By</th></tr>
            </thead>
            <tbody>
              <?php foreach ($results as $row): ?>
              <tr>
                <td><span class="badge-id">#<?php echo $row['id']; ?></span></td>
                <!-- VULNERABLE: Stored XSS reflected -->
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td class="text-muted"><?php echo $row['location']; ?></td>
                <td><?php echo htmlspecialchars($row['posted_by']); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php elseif ($search !== ""): ?>
      <div class="card">
        <div class="empty-state">
          <div class="icon">🔎</div>
          <!-- VULNERABLE: Reflected XSS — $search printed without escaping -->
          <p>No results found for: <?php echo $search; ?></p>
        </div>
      </div>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
