<?php /* header.php — common head + navbar (updated for Day2) */ ?>
<?php require_once __DIR__ . '/functions.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GreenCoin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
      <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e(BASE_URL) ?>/index.php">🌳 GreenCoin</a>
        <div>
        <?php if (isLoggedIn()): ?>
          <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/dashboard/index.php">Dashboard</a>
          <a class="btn btn-sm btn-outline-danger ms-2" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        <?php else: ?>
          <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/auth/login.php">Login</a>
          <a class="btn btn-sm btn-outline-success ms-2" href="<?= BASE_URL ?>/auth/register.php">Register</a>
        <?php endif; ?>
        </div>
      </div>
    </nav>
