<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (empty($password)) $errors[] = "Please enter your password.";

    if (empty($errors)) {
        $conn = db();
        $stmt = $conn->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['role'] = $row['role'];
                unset($row['password_hash']);
                $_SESSION['user'] = $row;

                // Redirect based on role
                $redirects = [
                    'individual' => '/dashboard/index.php',
                    'NGO' => '/ngo/verify_proofs.php',
                    'admin' => '/admin/admin_dashboard.php',
                    'factory' => '/factory/factory_dashboard.php'
                ];

                if (isset($redirects[$row['role']])) {
                    redirect(BASE_URL . $redirects[$row['role']]);
                } else {
                    $errors[] = "Invalid user role.";
                }
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GreenCoin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL . '/assets/css/login.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <div class="auth-container">
        <!-- Decorative Side (visible on larger screens) -->
        

        <!-- Login Form -->
        <div class="auth-form-container">
            <form method="post" class="auth-form">
                <div class="auth-logo">
                    <h1>GreenCoin</h1>
                    <p>Sign in to your account</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Error</strong>
                            <ul>
                                <?php foreach($errors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           placeholder=" " 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>"
                           required>
                    <label>Email Address</label>
                </div>

                <div class="form-group">
                    <input type="password" 
                           name="password" 
                           class="form-control" 
                           placeholder=" " 
                           required>
                    <label>Password</label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Sign In</span>
                    </button>
                </div>

                <div class="text-center mt-4">
                    <p>Don't have an account? <a href="register.php" class="btn-link">Create one</a></p>
                    <p class="mt-2"><a href="forgot-password.php" class="btn-link">Forgot password?</a></p>
                </div>
                <div class="text-center mt-6">
                    <a href="../index.html" class="text-green-600 hover:underline text-sm" style="text-decoration: none;color:">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
