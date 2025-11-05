<?php
require_once __DIR__ . '/header.php';

// Handle filters
$selected_factory = isset($_GET['factory_id']) ? (int)$_GET['factory_id'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get summary stats
$stats_sql = "SELECT 
    COALESCE(SUM(CASE WHEN v.is_verified=1 THEN p.tree_count * 5 ELSE 0 END), 0) as total_credits_generated,
    COALESCE(SUM(CASE WHEN ct.type='sell' AND ct.status='approved' THEN ct.credits ELSE 0 END), 0) as credits_sold,
    (SELECT COALESCE(SUM(credits), 0) FROM green_credits) as credits_remaining,
    COALESCE(SUM(CASE WHEN v.is_verified=1 THEN p.tree_count * 0.5 ELSE 0 END), 0) as carbon_offset_tons
FROM verifications v 
JOIN planting_proofs p ON p.id=v.proof_id
LEFT JOIN credit_transactions ct ON ct.factory_id = (SELECT id FROM factories LIMIT 1)";
$stats = $conn->query($stats_sql)->fetch_assoc();
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
    <div class="credits-report-container">
        <div class="credits-header">
            <h1><i class="ri-line-chart-line"></i> Credits Report</h1>
            <p>Track and analyze green credits across the platform</p>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Credits Generated</h3>
                <p class="value"><?= number_format($stats['total_credits_generated']) ?></p>
            </div>
            <div class="stat-card">
                <h3>Credits Sold</h3>
                <p class="value"><?= number_format($stats['credits_sold']) ?></p>
            </div>
            <div class="stat-card">
                <h3>Credits Remaining</h3>
                <p class="value"><?= number_format($stats['credits_remaining']) ?></p>
            </div>
            <div class="stat-card">
                <h3>Carbon Offset (Tons)</h3>
                <p class="value"><?= number_format($stats['carbon_offset_tons'], 2) ?></p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="get" class="filter-form">
                <div class="form-group">
                    <label for="factory_id">Factory</label>
                    <select name="factory_id" id="factory_id" class="form-control" onchange="this.form.submit()">
                        <option value="0">All Factories</option>
                        <?php
                        $factories = $conn->query("SELECT id, name FROM factories ORDER BY name ASC");
                        while ($f = $factories->fetch_assoc()): 
                        ?>
                            <option value="<?= $f['id'] ?>" <?= ($selected_factory==$f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="verified" <?= $status_filter === 'verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                        value="<?= htmlspecialchars($start_date) ?>" onchange="this.form.submit()">
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                        value="<?= htmlspecialchars($end_date) ?>" onchange="this.form.submit()">
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn-primary w-100" onclick="exportToExcel()">
                        <i class="ri-download-line"></i> Export
                    </button>
                </div>
            </form>
        </div> 

    <div class="table-container">
        <div class="table-header">
            <h2><i class="ri-building-line"></i> Factories Credits Summary</h2>
        </div>
        <div class="table-responsive">
            <table class="credits-table">
                <thead>
                    <tr>
                        <th>Factory</th>
                        <th>Owner</th>
                        <th>Total Credits</th>
                        <th>Verified Proofs</th>
                        <th>Total Trees</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT f.id, f.name, u.name AS owner, 
                                COALESCE(SUM(gc.credits),0) AS credits,
                                SUM(CASE WHEN v.is_verified=1 THEN 1 ELSE 0 END) AS verified_proofs,
                                COALESCE(SUM(CASE WHEN v.is_verified=1 THEN p.tree_count ELSE 0 END),0) AS total_trees
                            FROM factories f
                            JOIN users u ON u.user_id=f.user_id
                            LEFT JOIN green_credits gc ON gc.factory_id=f.id
                            LEFT JOIN planting_proofs p ON p.user_id=u.user_id
                            LEFT JOIN verifications v ON v.proof_id=p.id";

                    if ($selected_factory > 0) {
                        $sql .= " WHERE f.id=$selected_factory";
                    }

                    $sql .= " GROUP BY f.id, f.name, u.name
                            ORDER BY credits DESC";

                    $rs = $conn->query($sql);
                    if ($rs->num_rows > 0) {
                        while ($r = $rs->fetch_assoc()):
                            $credits = (int)$r['credits'];
                            $trees = (int)$r['total_trees'];
                            $proofs = (int)$r['verified_proofs'];
                    ?>
                        <tr>
                            <td>
                                <div class="font-medium"><?= htmlspecialchars($r['name']) ?></div>
                                <div class="text-xs text-gray-500">ID: <?= $r['id'] ?></div>
                            </td>
                            <td><?= htmlspecialchars($r['owner']) ?></td>
                            <td class="font-medium">
                                <span class="text-lg"><?= number_format($credits) ?></span>
                                <span class="text-xs text-gray-500">credits</span>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= $proofs ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <i class="ri-leaf-line text-green-500 mr-1"></i>
                                    <span><?= number_format($trees) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-icon" title="View Details">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button class="btn btn-icon" title="Download Report">
                                        <i class="ri-download-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    } else {
                        echo '<tr>
                                <td colspan="6" class="text-center py-8">
                                    <div class="empty-state">
                                        <i class="ri-database-line"></i>
                                        <h4>No data available</h4>
                                        <p>No records found matching your criteria</p>
                                    </div>
                                </td>
                            </tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="ri-history-line"></i> Recent Credit Transactions</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="credits-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Proof</th>
                            <th>Trees</th>
                            <th>Status</th>
                            <th>Verified By</th>
                            <th>Credits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_sql = "SELECT v.verification_date, v.is_verified, v.verified_by, 
                                        p.id as proof_id, p.tree_count, u.name, u.user_id
                                FROM verifications v 
                                JOIN planting_proofs p ON p.id=v.proof_id
                                JOIN users u ON u.user_id=p.user_id";

                        if ($selected_factory > 0) {
                            $q_sql .= " WHERE u.user_id=(SELECT user_id FROM factories WHERE id=$selected_factory)";
                        }

                        $q_sql .= " ORDER BY v.verification_date DESC LIMIT 10";

                        $q = $conn->query($q_sql);
                        if ($q->num_rows > 0) {
                            while($x = $q->fetch_assoc()) {
                                $delta = ((int)$x['is_verified']===1) ? ((int)$x['tree_count'] * 5) : 0;
                                $isVerified = (int)$x['is_verified'] === 1;
                                $verificationDate = new DateTime($x['verification_date']);
                        ?>
                            <tr>
                                <td>
                                    <div class="text-sm font-medium text-gray-900"><?= $verificationDate->format('M d, Y') ?></div>
                                    <div class="text-xs text-gray-500"><?= $verificationDate->format('h:i A') ?></div>
                                </td>
                                <td>
                                    <div class="font-medium"><?= htmlspecialchars($x['name']) ?></div>
                                    <div class="text-xs text-gray-500">ID: <?= $x['user_id'] ?></div>
                                </td>
                                <td>
                                    <a href="#" class="text-blue-600 hover:text-blue-800">
                                        #<?= $x['proof_id'] ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <i class="ri-leaf-line text-green-500 mr-1"></i>
                                        <span><?= (int)$x['tree_count'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if($isVerified): ?>
                                        <span class="badge badge-success">
                                            <i class="ri-check-line mr-1"></i> Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">
                                            <i class="ri-close-line mr-1"></i> Rejected
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm text-gray-500">
                                    <?= htmlspecialchars($x['verified_by']) ?>
                                </td>
                                <td class="font-medium <?= $isVerified ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $isVerified ? '+' : '' ?><?= $delta ?>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr>
                                    <td colspan="7" class="text-center py-8">
                                        <div class="empty-state">
                                            <i class="ri-time-line"></i>
                                            <h4>No recent transactions</h4>
                                            <p>No verification history found</p>
                                        </div>
                                    </td>
                                </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
function exportToExcel() {
    // This is a placeholder for Excel export functionality
    // You can implement this using a library like SheetJS or TableExport
    alert('Export to Excel functionality will be implemented here');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
