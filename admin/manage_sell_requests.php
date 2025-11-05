<?php
session_start();
require_once __DIR__ . '/_admin_auth.php';
require_once __DIR__ . '/header.php';

$errors = [];
$success = '';

// ———————————————————————————————————————————————
// Helpers
// ———————————————————————————————————————————————
function get_admin_id(mysqli $conn): ?int {
    $q = $conn->query("SELECT user_id FROM users WHERE role='admin' ORDER BY user_id ASC LIMIT 1");
    if ($q && $q->num_rows > 0) {
        $r = $q->fetch_assoc();
        return (int)$r['user_id'];
    }
    return null;
}

// ———————————————————————————————————————————————
// POST: Approve / Reject
// ———————————————————————————————————————————————
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['transaction_id'])) {
    $transaction_id = (int)$_POST['transaction_id'];
    $action = $_POST['action'] === 'approve' ? 'approve' : 'reject';

    // Load transaction
    $stmt = $conn->prepare("SELECT user_id, factory_id, credits, status FROM credit_transactions WHERE id=?");
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $stmt->bind_result($user_id, $target_factory_id, $credits, $status);
    $rowFound = $stmt->fetch();
    $stmt->close();

    if (!$rowFound) {
        $errors[] = "Transaction not found.";
    } elseif ($status !== 'pending') {
        $errors[] = "Already processed.";
    } else {
        if ($action === 'approve') {
            $admin_id = get_admin_id($conn);
            if (!$admin_id) {
                $errors[] = "Admin account not found. Please create an admin user first.";
            } else {
                $conn->begin_transaction();
                try {
                    // ✅ Add credits to Admin wallet (not to factory)
                    $stmt = $conn->prepare("
                        INSERT INTO user_credits (user_id, credits)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE credits = credits + VALUES(credits)
                    ");
                    $stmt->bind_param("ii", $admin_id, $credits);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare("
                        INSERT INTO user_credits (user_id, credits)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE credits = credits - VALUES(credits)
                    ");
                    $stmt->bind_param("ii", $user_id, $credits);
                    $stmt->execute();
                    $stmt->close();

                    // ✅ Mark transaction approved
                    $stmt = $conn->prepare("UPDATE credit_transactions SET status='approved' WHERE id=?");
                    $stmt->bind_param("i", $transaction_id);
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit();
                    $success = "Approved: {$credits} credits moved to Admin account.";
                } catch (Exception $e) {
                    $conn->rollback();
                    $errors[] = "Approval failed: ".$e->getMessage();
                }
            }
        } else { // reject
            $conn->begin_transaction();
            try {
                // ✅ Mark transaction rejected
                $stmt = $conn->prepare("UPDATE credit_transactions SET status='rejected' WHERE id=?");
                $stmt->bind_param("i", $transaction_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                $success = "Rejected: {$credits} credits refunded to user.";
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = "Rejection failed: ".$e->getMessage();
            }
        }
    }
}

// ———————————————————————————————————————————————
// Fetch list
// ———————————————————————————————————————————————
$rs = $conn->query("
    SELECT t.id, u.name, u.email, t.credits, t.status, t.created_at, f.name AS factory_name
    FROM credit_transactions t
    JOIN users u ON u.user_id = t.user_id
    LEFT JOIN factories f ON f.id = t.factory_id
    WHERE t.type = 'sell'
    ORDER BY t.created_at DESC
");
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
<h2>Manage Sell Requests</h2>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger" style="margin-top:10px;">
      <ul style="margin:0; padding-left:18px;">
          <?php foreach($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
      </ul>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success" style="margin-top:10px;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<table class="table" border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%;">
  <tr>
    <th>ID</th>
    <th>User</th>
    <th>Email</th>
    <th>Credits</th>
    <th>Requested Factory</th> <!-- informational only -->
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
  </tr>

  <?php while($row = $rs->fetch_assoc()): ?>
    <tr>
      <td><?= (int)$row['id'] ?></td>
      <td><?= htmlspecialchars($row['name']) ?></td>
      <td><?= htmlspecialchars($row['email']) ?></td>
      <td><?= (int)$row['credits'] ?></td>
      <td><?= htmlspecialchars($row['factory_name'] ?? '-') ?></td>
      <td><?= htmlspecialchars($row['status']) ?></td>
      <td><?= htmlspecialchars($row['created_at']) ?></td>
      <td>
        <?php if ($row['status'] === 'pending'): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="transaction_id" value="<?= (int)$row['id'] ?>">
              <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              <button name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
            </form>
        <?php else: ?>
            <span class="badge bg-secondary">Processed</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<?php require_once __DIR__ . '/footer.php'; ?>
