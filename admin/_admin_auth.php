<?php
// Require admin login
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
