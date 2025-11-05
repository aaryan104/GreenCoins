<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/auth/login.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $factory_name = trim($_POST['factory_name']);
    $location = trim($_POST['location']);
    $production_type = trim($_POST['production_type']);

    if (empty($factory_name) || empty($location) || empty($production_type)) {
        $errors[] = "All fields are required.";
    } else {
        $conn = db();

        // Check if user already linked to factory
        $stmt = $conn->prepare("SELECT id FROM factories WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "You already have a factory registered.";
        } else {
            $stmt = $conn->prepare("INSERT INTO factories (user_id, name, location, production_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $_SESSION['user_id'], $factory_name, $location, $production_type);
            $stmt->execute();
            $stmt->close();

            $success = "✅ Factory registered successfully!";
        }
    }
}
include __DIR__ . '/../header.php';
?>

<div class="container py-5">
  <h2>Register Factory</h2>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul><?php foreach($errors as $e) echo "<li>".e($e)."</li>"; ?></ul></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="post" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Factory Name</label>
      <input type="text" name="factory_name" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Location</label>
      <input type="text" name="location" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Production Type</label>
      <input type="text" name="production_type" class="form-control" required>
    </div>
    <div class="col-12">
      <button class="btn btn-success">Register Factory</button>
      <a class="btn btn-link" href="../dashboard/index.php">Back to Dashboard</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
