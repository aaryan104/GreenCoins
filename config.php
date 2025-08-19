<?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    define('BASE_URL', '/dashboard/greencoin');

    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'green_credits_system');

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    $conn->set_charset('utf8mb4');

    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_NAME);

    function db() { global $conn; return $conn; }
?>
