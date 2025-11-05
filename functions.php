<?php
// functions.php — common helpers (updated)
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

function e($str){ return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
function redirect($url){ header("Location: " . $url); exit; }

function isLoggedIn(){ return !empty($_SESSION['user_id']); }
function currentUser(){ return $_SESSION['user'] ?? null; }

// Simple example scoring: you can refine this later
function calculateTreeCoins($tree_name){
    $name = strtolower(trim($tree_name));
    if (in_array($name, ['neem','banyan','peepal'])) return 20;
    if (in_array($name, ['mango','jamun'])) return 15;
    return 10; // default
}
?>
