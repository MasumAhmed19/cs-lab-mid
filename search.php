<?php
// ⚠️  INTENTIONALLY VULNERABLE - SQL Injection + Reflected XSS Lab Demo
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$results = [];
$search = "";
$debug_query = "";

if (isset($_GET['q'])) {
    $search = $_GET['q'];

    // ❌ VULNERABLE: Raw input in SQL query (SQL Injection)
    $query = "SELECT * FROM reports WHERE title LIKE '%$search%' OR description LIKE '%$search%'";
    $debug_query = $query;
    $res = mysqli_query($conn, $query);

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $results[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Reports</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Search Crime Reports</h2>
    <a href="dashboard.php">← Back to Dashboard</a><br><br>

    <form method="GET" action="search.php">
        <input type="text" name="q" value="<?php echo $search; ?>" placeholder="Search by title or description">
        <!-- ❌ VULNERABLE: $search echoed without escaping = Reflected XSS -->
        <button type="submit">Search</button>
    </form>

    <?php if ($debug_query): ?>
        <div class="debug">
            <strong>DEBUG - SQL Query:</strong><br>
            <code><?php echo htmlspecialchars($debug_query); ?></code>
        </div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <h3>Results:</h3>
        <table>
            <tr><th>ID</th><th>Title</th><th>Description</th><th>Location</th></tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <!-- ❌ VULNERABLE: Stored XSS reflected here -->
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo $row['location']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($search !== ""): ?>
        <!-- ❌ VULNERABLE: Reflected XSS - $search printed without escaping -->
        <p>No results found for: <?php echo $search; ?></p>
    <?php endif; ?>

    <br>
    <small>
        <strong>Lab Hints:</strong><br>
        SQL Injection: <code>' UNION SELECT 1,username,password,email,5,6 FROM users -- </code><br>
        Reflected XSS: <code>&lt;script&gt;alert(document.cookie)&lt;/script&gt;</code>
    </small>
</div>
</body>
</html>
