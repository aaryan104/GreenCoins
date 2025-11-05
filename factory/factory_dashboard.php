<?php
// Factory Dashboard — modern UI

require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../header.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'factory') {
  redirect(BASE_URL . '/auth/login.php');
}

$userId = (int)$_SESSION['user_id'];

// Fetch factory profile for this user
$factory = null;
$stmt = $conn->prepare("
  SELECT f.id AS factory_id, f.name, f.location, f.production_type, f.registration_date,
         u.name AS owner_name, u.email AS owner_email
  FROM factories f
  JOIN users u ON u.user_id = f.user_id
  WHERE f.user_id = ?
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$factory = $res->fetch_assoc();
$stmt->close();

if (!$factory) {
  // If a logged-in 'factory' user has no row in factories, guide them.
  ?>
  <div class="container py-5">
    <div class="alert alert-warning">
      <div class="d-flex align-items-center gap-2">
        <div class="fs-5">⚠️</div>
        <div><strong>Factory profile missing.</strong> Please ask admin to register your factory details.</div>
      </div>
    </div>
  </div>
  <?php require_once __DIR__ . '/../footer.php'; exit; 
}

// Credits for this factory
$credits = 0;
$stmt = $conn->prepare("SELECT credits FROM green_credits WHERE factory_id=?");
$stmt->bind_param("i", $factory['factory_id']);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) { $credits = (int)$row['credits']; }
$stmt->close();

// Stats: pending/approved/rejected requests
$stats = ['pending'=>0,'approved'=>0,'rejected'=>0];
$stmt = $conn->prepare("
  SELECT status, COUNT(*) c 
  FROM credit_transactions 
  WHERE factory_id=? AND type='request'
  GROUP BY status
");
$stmt->bind_param("i", $factory['factory_id']);
$stmt->execute();
$res = $stmt->get_result();
while($r = $res->fetch_assoc()){
  $stats[$r['status']] = (int)$r['c'];
}
$stmt->close();

// Recent transactions (latest 5)
$recent = [];
$stmt = $conn->prepare("
  SELECT id, credits, status, created_at
  FROM credit_transactions
  WHERE factory_id=? AND type='request'
  ORDER BY created_at DESC, id DESC
  LIMIT 5
");
$stmt->bind_param("i", $factory['factory_id']);
$stmt->execute();
$recent = $stmt->get_result();
?>

<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h2 class="mb-0">🏭 <?= e($factory['name']) ?></h2>
      <div class="text-muted">Welcome, <?= e($factory['owner_name']) ?> (<?= e($factory['owner_email']) ?>)</div>
      <br>
    </div>
    <a class="btn btn-primary" href="<?= e(BASE_URL) ?>/factory/buy_credits.php">Buy Credits</a>
    <br>
    <a href="../auth/logout.php"
        class="btn btn-primary">
        <i class="ri-logout-box-line">LOGOUT</i>
    </a>
  </div>

  <!-- Stats cards -->
  <div class="row g-3">
    <div class="col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="text-muted mb-1">Current Balance</div>
          <div class="display-6 fw-bold"><?= (int)$credits ?></div>
          <div class="small text-muted">Green Credits</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="text-muted mb-1">Pending Requests</div>
          <div class="h3 mb-0"><?= (int)$stats['pending'] ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="text-muted mb-1">Approved</div>
          <div class="h3 mb-0"><?= (int)$stats['approved'] ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="text-muted mb-1">Rejected</div>
          <div class="h3 mb-0"><?= (int)$stats['rejected'] ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile + Quick links -->
  <div class="row g-3 mt-1">
    <div class="col-lg-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white fw-semibold">Factory Profile</div>
        <div class="card-body">
          <div class="mb-1"><span class="text-muted">Location:</span> <?= e($factory['location'] ?: '—') ?></div>
          <div class="mb-1"><span class="text-muted">Production Type:</span> <?= e($factory['production_type'] ?: '—') ?></div>
          <div class="mb-1"><span class="text-muted">Registered:</span> <?= e($factory['registration_date'] ?? '—') ?></div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white fw-semibold">Quick Links</div>
        <div class="list-group list-group-flush">
          <a class="list-group-item list-group-item-action" href="<?= e(BASE_URL) ?>/factory/buy_credits.php">💳 Request to Buy Credits</a>
          <a class="list-group-item list-group-item-action" href="<?= e(BASE_URL) ?>/factory/credits_request_history.php">📜 Credit Request History</a>
          <a class="list-group-item list-group-item-action" href="<?= e(BASE_URL) ?>/dashboard/pollution_data.php">🌫️ Upload Pollution Data</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Requests -->
  <div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-white fw-semibold">Recent Credit Requests</div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Credits</th>
            <th>Status</th>
            <th>Requested At</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($recent && $recent->num_rows): $i=1; while($row = $recent->fetch_assoc()): ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><?= (int)$row['credits'] ?></td>
            <td>
              <?php if ($row['status']==='pending'): ?>
                <span class="badge bg-warning text-dark">Pending</span>
              <?php elseif ($row['status']==='approved'): ?>
                <span class="badge bg-success">Approved</span>
              <?php else: ?>
                <span class="badge bg-danger">Rejected</span>
              <?php endif; ?>
            </td>
            <td><?= e($row['created_at']) ?></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="4" class="text-center text-muted">No requests yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
