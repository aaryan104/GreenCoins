<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/header.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) 
{
  $action = $_POST['action'];
  $requestId = intval($_POST['request_id']);

  $stmt = $conn->prepare("SELECT request_id, factory_id, credits, status FROM factory_requests WHERE request_id = ? FOR UPDATE");
  $stmt->bind_param("i", $requestId);
  $stmt->execute();
  $res = $stmt->get_result();
  $req = $res->fetch_assoc();
  $stmt->close();

  if (!$req) 
  {
      $errors[] = "Request not found.";
  } 
  elseif ($req['status'] !== 'pending') 
  {
      $errors[] = "Request already processed.";
  } 
  else 
  {
      if ($action === 'approve') 
      {
        $conn->begin_transaction();
        try 
        {
          $factoryId = (int)$req['factory_id'];
          $credits = (int)$req['credits'];

          $admQ = $conn->query("SELECT user_id FROM users WHERE role='admin' ORDER BY user_id ASC LIMIT 1");
          if (!$admQ || $admQ->num_rows === 0) throw new Exception("Admin user not found.");
          $admRow = $admQ->fetch_assoc();
          $adminId = (int)$admRow['user_id'];

          $s = $conn->prepare("SELECT credits FROM user_credits WHERE user_id = ? FOR UPDATE");
          $s->bind_param("i", $adminId);
          $s->execute();
          $r = $s->get_result();
          $adminCredits = 0;
          if ($row = $r->fetch_assoc()) $adminCredits = (int)$row['credits'];
          $s->close();

          if ($adminCredits < $credits) 
          {
            throw new Exception("Admin does not have enough credits to approve this request (required: {$credits}, available: {$adminCredits}).");
          }

          $u = $conn->prepare("UPDATE user_credits SET credits = credits - ? WHERE user_id = ?");
          $u->bind_param("ii", $credits, $adminId);
          $u->execute();
          $u->close();

          $g = $conn->prepare("SELECT id, credits FROM green_credits WHERE factory_id = ? FOR UPDATE");
          $g->bind_param("i", $factoryId);
          $g->execute();
          $gres = $g->get_result();
          if ($grow = $gres->fetch_assoc()) 
          {
            $upd = $conn->prepare("UPDATE green_credits SET credits = credits + ? WHERE factory_id = ?");
            $upd->bind_param("ii", $credits, $factoryId);
            $upd->execute();
            $upd->close();
          } 
          else 
          {
            $ins = $conn->prepare("INSERT INTO green_credits (factory_id, credits) VALUES (?, ?)");
            $ins->bind_param("ii", $factoryId, $credits);
            $ins->execute();
            $ins->close();
          }
          $g->close();

          $p = $conn->prepare("UPDATE factory_requests SET status = 'approved' WHERE request_id = ?");
          $p->bind_param("i", $requestId);
          $p->execute();
          $p->close();

          $log = $conn->prepare("INSERT INTO credit_transactions (user_id, factory_id, type, credits, status, created_at) VALUES (?, ?, 'request', ?, 'approved', NOW())");
          $log->bind_param("iii", $adminId, $factoryId, $credits);
          $log->execute();
          $log->close();

          $conn->commit();
          $success = "Request #{$requestId} approved and {$credits} credits transferred to factory.";
        } 
        catch (Exception $e) 
        {
          $conn->rollback();
          $errors[] = "Approval failed: " . $e->getMessage();
        }
      } 
      elseif ($action === 'reject') 
      {
        $stmt = $conn->prepare("UPDATE factory_requests SET status = 'rejected' WHERE request_id = ?");
        $stmt->bind_param("i", $requestId);
        if ($stmt->execute()) 
        {
          $success = "Request #{$requestId} rejected.";
        } 
        else 
        {
          $errors[] = "Failed to reject request.";
        }
        $stmt->close();
      } 
      else 
      {
        $errors[] = "Unknown action.";
      }
  }
}

  $listQ = "
    SELECT r.request_id, r.factory_id, r.credits, r.status, r.created_at,
          f.name AS factory_name, u.email AS owner_email
    FROM factory_requests r
    JOIN factories f ON f.id = r.factory_id
    LEFT JOIN users u ON u.user_id = f.user_id
    ORDER BY r.created_at DESC
  ";
  $result = $conn->query($listQ);
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
  <div class="container mt-4">
    <h2>Manage Factory Credit Requests</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Factory</th>
            <th>Owner Email</th>
            <th>Credits Requested</th>
            <th>Status</th>
            <th>Requested At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows): $i=1; while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['factory_name']) ?></td>
              <td><?= htmlspecialchars($row['owner_email'] ?? '') ?></td>
              <td><?= (int)$row['credits'] ?></td>
              <td>
                <?php if ($row['status'] === 'pending'): ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif ($row['status'] === 'approved'): ?>
                  <span class="badge bg-success">Approved</span>
                <?php else: ?>
                  <span class="badge bg-danger">Rejected</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td>
                <?php if ($row['status'] === 'pending'): ?>
                  <form method="post" style="display:inline-block">
                    <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
                    <button name="action" value="approve" class="btn btn-sm btn-success" onclick="return confirm('Approve this request?')">Approve</button>
                  </form>
                  <form method="post" style="display:inline-block">
                    <input type="hidden" name="request_id" value="<?= (int)$row['request_id'] ?>">
                    <button name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this request?')">Reject</button>
                  </form>
                <?php else: ?>
                  <em>No action</em>
                <?php endif; ?> 
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-center text-muted">No requests found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php require_once __DIR__ . '/footer.php'; ?>
