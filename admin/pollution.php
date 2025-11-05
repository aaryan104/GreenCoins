<?php
require_once __DIR__ . '/header.php';

// Add new pollution entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $factory_id   = (int)$_POST['factory_id'];
    $source       = $_POST['source']; // government/self-reported
    $emission     = (float)$_POST['emission_tons'];
    $month        = $_POST['report_month'] . "-01"; // month input ko YYYY-MM-01 banaya

    if ($factory_id > 0 && $emission > 0 && in_array($source, ['government','self-reported'], true)) {
        $stmt = $conn->prepare("INSERT INTO pollution_data (factory_id, source, emission_tons, report_month) VALUES (?,?,?,?)");
        $stmt->bind_param("isds", $factory_id, $source, $emission, $month);
        if ($stmt->execute()) {
            echo '<div class="alert alert-success">
                    <i class="ri-checkbox-circle-line"></i>
                    <span>Pollution entry added successfully!</span>
                  </div>';
        } else {
            echo '<div class="alert alert-danger">
                    <i class="ri-error-warning-line"></i>
                    <span>Error: '.htmlspecialchars($conn->error).'</span>
                  </div>';
        }
        $stmt->close();
    }
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
  <div class="pollution-container">
    <div class="pollution-header">
      <h2><i class="ri-earth-line"></i> Pollution Data Management</h2>
      <p>Track and manage factory pollution emissions data</p>
    </div>

    <!-- Add Entry Form -->
    <div class="pollution-card">
      <div class="pollution-card-header">
        <h4><i class="ri-add-line"></i> Add New Pollution Entry</h4>
      </div>
      <div class="pollution-card-body">
        <form method="post" class="pollution-form">
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label">Factory</label>
              <select name="factory_id" class="form-select" required>
                <option value="">Select Factory</option>
                <?php
                $fq = $conn->query("SELECT id, name FROM factories ORDER BY name");
                while ($f = $fq->fetch_assoc()) {
                    echo '<option value="'.$f['id'].'">'.htmlspecialchars($f['name']).'</option>';
                }
                ?>
              </select>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label">Source</label>
              <select name="source" class="form-select" required>
                <option value="government">Government</option>
                <option value="self-reported">Self Reported</option>
              </select>
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">Emission (Tons)</label>
              <input type="number" step="0.01" name="emission_tons" class="form-control" required 
                    placeholder="0.00" min="0.01">
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">Report Month</label>
              <input type="month" name="report_month" class="form-control" required>
            </div>

            <div class="col-md-2 d-flex align-items-end mb-3">
              <button type="submit" name="create" class="btn btn-primary w-100">
                <i class="ri-save-line"></i> Save Entry
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Pollution Data Table -->
    <div class="pollution-card">
      <div class="pollution-card-header d-flex justify-content-between align-items-center">
        <h4><i class="ri-database-2-line"></i> All Pollution Records</h4>
        <div class="d-flex align-items-center">
          <button class="btn btn-icon me-2" title="Refresh">
            <i class="ri-refresh-line"></i>
          </button>
          <button class="btn btn-icon" title="Export Data">
            <i class="ri-download-line"></i>
          </button>
        </div>
      </div>
      <div class="pollution-card-body">
        <div class="table-responsive">
          <table class="pollution-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Factory</th>
                <th>Source</th>
                <th>Emission (Tons)</th>
                <th>Report Month</th>
                <th>Uploaded At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $q = $conn->query("SELECT p.id, p.source, p.emission_tons, p.report_month, p.uploaded_at, f.name AS factory_name 
                                FROM pollution_data p 
                                JOIN factories f ON f.id = p.factory_id 
                                ORDER BY p.report_month DESC, p.id DESC");
              if ($q->num_rows > 0) {
                while ($r = $q->fetch_assoc()) {
                    $sourceClass = $r['source'] === 'government' ? 'badge-government' : 'badge-self-reported';
                    $formattedDate = date('M d, Y', strtotime($r['uploaded_at']));
                    $formattedMonth = date('M Y', strtotime($r['report_month']));
                    
                    echo "<tr>
                      <td>#{$r['id']}</td>
                      <td>".htmlspecialchars($r['factory_name'])."</td>
                      <td><span class='badge {$sourceClass}'>".ucfirst(htmlspecialchars($r['source']))."</span></td>
                      <td class='font-medium'>".number_format($r['emission_tons'], 2)." <span class='text-muted'>tons</span></td>
                      <td>{$formattedMonth}</td>
                      <td><span class='text-muted' title='{$r['uploaded_at']}'>{$formattedDate}</span></td>
                      <td>
                        <div class='d-flex gap-2'>
                          <button class='btn btn-icon' title='Edit'>
                            <i class='ri-edit-line'></i>
                          </button>
                          <button class='btn btn-icon text-danger' title='Delete'>
                            <i class='ri-delete-bin-line'></i>
                          </button>
                        </div>
                      </td>
                    </tr>";
                }
              } else {
                echo '<tr>
                        <td colspan="7">
                          <div class="empty-state">
                            <i class="ri-database-line"></i>
                            <h4>No pollution records found</h4>
                            <p>Add your first pollution entry to get started</p>
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
  </div>

<?php require_once __DIR__ . '/footer.php'; ?>
