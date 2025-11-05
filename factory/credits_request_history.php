<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['factory_id'])) {
    header("Location: ../login.php");
    exit();
}

$factory_id = $_SESSION['factory_id'];

// Fetch buy requests
$buy_stmt = $conn->prepare("SELECT id, credits, status, created_at FROM factory_requests WHERE factory_id = ? ORDER BY created_at DESC");
$buy_stmt->bind_param("i", $factory_id);
$buy_stmt->execute();
$buy_result = $buy_stmt->get_result();

// Fetch sell requests
// $sell_stmt = $conn->prepare("SELECT id, credits, status, created_at FROM sell_requests WHERE factory_id = ? ORDER BY created_at DESC");
// $sell_stmt->bind_param("i", $factory_id);
// $sell_stmt->execute();
// $sell_result = $sell_stmt->get_result();
?>
<link rel="stylesheet" href="/assets/css/styles.css"> <!-- optional: your css -->
<div class="container" style="max-width:1000px;margin:24px auto;padding:12px;">
  <h2 style="margin-bottom:12px;">Credits Request History</h2>

  <section style="margin-bottom:28px;">
    <h3>Buy Requests</h3>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#f5f5f5;text-align:left;">
          <th style="padding:8px;border:1px solid #ddd;">Request ID</th>
          <th style="padding:8px;border:1px solid #ddd;">Credits</th>
          <th style="padding:8px;border:1px solid #ddd;">Status</th>
          <th style="padding:8px;border:1px solid #ddd;">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($buy_result->num_rows === 0): ?>
          <tr><td colspan="4" style="padding:12px;border:1px solid #ddd;">No buy requests found.</td></tr>
        <?php else: ?>
          <?php while ($r = $buy_result->fetch_assoc()): ?>
            <tr>
              <td style="padding:8px;border:1px solid #ddd;"><?= htmlspecialchars($r['id']) ?></td>
              <td style="padding:8px;border:1px solid #ddd;"><?= htmlspecialchars($r['credits']) ?></td>
              <td style="padding:8px;border:1px solid #ddd;">
                <?php
                  $s = $r['status'];
                  // simple color labels
                  if (strtolower($s) === 'pending') echo "<span style='color:#d97706;font-weight:600;'>Pending</span>";
                  elseif (strtolower($s) === 'approved') echo "<span style='color:#16a34a;font-weight:600;'>Approved</span>";
                  elseif (strtolower($s) === 'rejected') echo "<span style='color:#dc2626;font-weight:600;'>Rejected</span>";
                  else echo htmlspecialchars($s);
                ?>
              </td>
              <td style="padding:8px;border:1px solid #ddd;"><?= htmlspecialchars($r['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </section>

  <!-- <section>
    <h3>Sell Requests</h3>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#f5f5f5;text-align:left;">
          <th style="padding:8px;border:1px solid #ddd;">Request ID</th>
          <th style="padding:8px;border:1px solid #ddd;">Credits</th>
          <th style="padding:8px;border:1px solid #ddd;">Status</th>
          <th style="padding:8px;border:1px solid #ddd;">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($sell_result->num_rows === 0): ?>
          <tr><td colspan="4" style="padding:12px;border:1px solid #ddd;">No sell requests found.</td></tr>
        <?php else: ?>
          <?php while ($r = $sell_result->fetch_assoc()): ?>
            <tr>
              <td style="padding:8px;border:1px solid #ddd;"><?= htmlspecialchars($r['id']) ?></td>
              <td style="padding:8px;border:1px solid #ddd;"><?= htmlspecialchars($r['credits']) ?></td>
              <td style="padding:8px;border:1px solid #ddd;">
                <?php
                  $s = $r['status'];
                  if (strtolower($s) === 'pending') echo "<span style='color:#d97706;font-weight:600;'>Pending</span>";
                  elseif (strtolower($s) === 'approved') echo "<span style='color:#16a34a;font-weight:600;'>Approved</span>";
                  elseif (strtolower($s) === 'rejected') echo "<span style='color:#dc2626;font-weight:600;'>Rejected</span>";
                  else echo htmlspecialchars($s);
                ?>
              </td>
              <td style="padding:8px;border:1px solid #ddd;"><?= htmlspecialchars($r['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </section> -->

</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
