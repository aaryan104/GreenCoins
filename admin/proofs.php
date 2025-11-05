<?php
require_once __DIR__ . '/header.php';

// Admin verify/reject (optional override)
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'], $_POST['proof_id'])) {
    $proof_id = (int)$_POST['proof_id'];
    $action = $_POST['action']; // verify|reject

    // fetch factory + tree_count
    $stmt = $conn->prepare("SELECT user_id, tree_count FROM planting_proofs WHERE id=?");
    $stmt->bind_param('i', $proof_id);
    $stmt->execute();
    $stmt->bind_result($factory_id, $tree_count);
    $stmt->fetch(); 
    $stmt->close();

    // upsert verification
    $is_verified = ($action==='verify') ? 1 : 0;
    $verified_by = 'ADMIN: ' . ($_SESSION['user_name'] ?? 'admin');
    $stmt=$conn->prepare("INSERT INTO verifications (proof_id,is_verified,verified_by,verification_date,method)
                          VALUES (?,?,?,?, 'manual')
                          ON DUPLICATE KEY UPDATE is_verified=VALUES(is_verified), verified_by=VALUES(verified_by), verification_date=VALUES(verification_date)");
    $now = date('Y-m-d H:i:s');
    $stmt->bind_param('iiss', $proof_id, $is_verified, $verified_by, $now);
    $stmt->execute(); 
    $stmt->close();

    if ($is_verified===1) {
        // credits = tree_count * 5
        $credits = (int)$tree_count * 5;
        $stmt=$conn->prepare("INSERT INTO green_credits (factory_id, credits)
                              VALUES (?, ?)
                              ON DUPLICATE KEY UPDATE credits = credits + VALUES(credits)");
        $stmt->bind_param('ii', $factory_id, $credits);
        $stmt->execute(); 
        $stmt->close();
    }
    echo '<div class="card" style="margin-bottom:12px;color:green;">Action applied.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenCoin Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#f5f7fa",
                        secondary: "#e5e7eb",
                    },
                    borderRadius: {
                        none: "0px",
                        sm: "4px",
                        DEFAULT: "8px",
                        md: "12px",
                        lg: "16px",
                        xl: "20px",
                        "2xl": "24px",
                        "3xl": "32px",
                        full: "9999px",
                        button: "8px",
                    },
                },
            },
        };
    </script>
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }
    </style>
</head>
<body>
    <div class="proofs-container">
        <div class="proofs-header">
            <h2>Planting Proofs Management</h2>
        </div>

        <form method="get" class="proof-filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="status">Filter by Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Proofs</option>
                        <option value="pending" <?= ($_GET['status'] ?? '')==='pending'?'selected':'' ?>>Pending Review</option>
                        <option value="verified" <?= ($_GET['status'] ?? '')==='verified'?'selected':'' ?>>Verified</option>
                        <option value="rejected" <?= ($_GET['status'] ?? '')==='rejected'?'selected':'' ?>>Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-filter-line"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>

    <div class="table-responsive">
        <table class="proofs-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Factory/Owner</th>
                    <th>Tree Species</th>
                    <th>Tree Count</th>
                    <th>Status</th>
                    <th>Photo Proof</th>
                    <th>QR Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    <?php
    $where = '';
    $status = $_GET['status'] ?? '';
    if ($status==='pending')  $where = "WHERE v.id IS NULL";
    if ($status==='verified') $where = "WHERE v.is_verified=1";
    if ($status==='rejected') $where = "WHERE v.is_verified=0";

    $sql = "
        SELECT p.id, 
                p.tree_species, 
                p.tree_count, 
                p.photo_url, 
                p.qrcode_file,
                COALESCE(f.name, u.name) AS owner_name,
                v.is_verified, 
                v.verified_by, 
                v.verification_date
        FROM planting_proofs p
        LEFT JOIN factories f ON f.id = p.user_id
        LEFT JOIN users u ON u.user_id = p.user_id
        LEFT JOIN verifications v ON v.proof_id = p.id
        $where
        ORDER BY p.id DESC
    ";
    $rs = $conn->query($sql);
    while($r=$rs->fetch_assoc()):
        ?>
    <tr>
        <td>#<?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['owner_name']) ?></td>
        <td><?= !empty($r['tree_species']) ? htmlspecialchars($r['tree_species']) : '<span class="text-muted">Not specified</span>' ?></td>
        <td><span class="font-medium"><?= (int)$r['tree_count'] ?></span> trees</td>
        <td>
        <?php 
        $statusClass = '';
        $statusText = '';
        if (is_null($r['is_verified'])) {
            $statusClass = 'status-pending';
            $statusText = '⏳ Pending';
        } elseif ((int)$r['is_verified'] === 1) {
            $statusClass = 'status-verified';
            $statusText = '✅ Verified';
        } else {
            $statusClass = 'status-rejected';
            $statusText = '❌ Rejected';
        }
        ?>
        <div class="status-badge <?= $statusClass ?>">
            <?= $statusText ?>
        </div>
        <?php if (!empty($r['verified_by']) || !empty($r['verification_date'])): ?>
        <div class="verification-details">
            <?php 
            if (!empty($r['verified_by'])) {
                echo htmlspecialchars($r['verified_by']);
            }
            if (!empty($r['verification_date'])) {
                echo (!empty($r['verified_by']) ? ' · ' : '') . htmlspecialchars($r['verification_date']);
            }
            ?>
        </div>
        <?php endif; ?>
        </td>
        <td>
        <?php if (!empty($r['photo_url'])): ?>
            <img src="<?= BASE_URL.'/'.htmlspecialchars($r['photo_url']) ?>" width="100">
        <?php else: ?>
            —
            <?php endif; ?>
        </td>
        <td>
        <?php if (!empty($r['qrcode_file'])): 
            $qrPath = BASE_URL.'/'.htmlspecialchars($r['qrcode_file']);
        ?>
            <a href="<?= $qrPath ?>" download>
                <img src="<?= $qrPath ?>" width="100" alt="QR Code">
            </a>
        <?php else: ?>
            N/A
        <?php endif; ?>
        </td>
        <td>
        <div class="action-buttons">
            <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to verify this proof?');">
            <input type="hidden" name="proof_id" value="<?= $r['id'] ?>">
            <button type="submit" name="action" value="verify" class="btn btn-primary" title="Verify Proof">
                <i class="ri-check-line"></i> Verify
            </button>
            </form>
            <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to reject this proof?');">
            <input type="hidden" name="proof_id" value="<?= $r['id'] ?>">
            <button type="submit" name="action" value="reject" class="btn btn-danger" title="Reject Proof">
                <i class="ri-close-line"></i> Reject
            </button>
            </form>
        </div>
        </td>
    </tr>
    <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    </div> <!-- End .proofs-container -->

<?php require_once __DIR__ . '/footer.php'; ?>
