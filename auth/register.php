<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../lib/mailer.php';

$errors = [];
$success = "";

// --- Step 1: Send OTP ---
if (isset($_POST['send_otp'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email before requesting OTP.";
    } else {
        $otp = rand(100000, 999999);
        $_SESSION['pending_email'] = $email;
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expire'] = time() + 300;

        $subject = "Your OTP for Registration - GreenCoin";
        $body = "<p>Hello,</p><p>Your OTP is: <b>$otp</b></p><p>Valid for 5 minutes.</p>";

        if (send_mail($email, $subject, $body)) {
            $success = "OTP has been sent to your email!";
        } else {
            $errors[] = "Failed to send OTP. Please try again.";
        }
    }
}

// --- Step 2: Registration with OTP check ---
if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $entered_otp = trim($_POST['otp'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';
    if (!isset($_SESSION['otp']) || !isset($_SESSION['pending_email'])) {
        $errors[] = "Please request OTP before registering.";
    } elseif ($_SESSION['pending_email'] !== $email) {
        $errors[] = "Email does not match OTP email.";
    } elseif (time() > $_SESSION['otp_expire']) {
        $errors[] = "OTP expired. Please request a new one.";
    } elseif ($entered_otp != $_SESSION['otp']) {
        $errors[] = "Invalid OTP. Please try again.";
    }

    // Profile image upload
    $profileImagePath = null;
    if (!empty($_FILES['profileImage']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $imageName = time() . '_' . basename($_FILES['profileImage']['name']);
        $profileImagePath = 'uploads/' . $imageName;
        move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadDir . $imageName);
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role, profile_image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hash, $role, $profileImagePath);
        $stmt->execute();
        $newUserId = $conn->insert_id;
        $stmt->close();

        // Factory -> insert extra
        if ($role === 'factory') {
            $stmt2 = $conn->prepare("INSERT INTO factories (user_id, name, location, production_type) VALUES (?, ?, 'Not Provided', 'Not Provided')");
            $factoryName = $name . " Factory";
            $stmt2->bind_param("is", $newUserId, $factoryName);
            $stmt2->execute();
            $factoryId = $conn->insert_id;
            $stmt2->close();

            $stmt3 = $conn->prepare("INSERT INTO green_credits (factory_id, credits) VALUES (?, 0)");
            $stmt3->bind_param("i", $factoryId);
            $stmt3->execute();
            $stmt3->close();
        }
        // Individual -> credits entry
        elseif ($role === 'individual') {
            $stmt4 = $conn->prepare("INSERT INTO user_credits (user_id, credits) VALUES (?, 0)");
            $stmt4->bind_param("i", $newUserId);
            $stmt4->execute();
            $stmt4->close();
        }

        unset($_SESSION['otp'], $_SESSION['otp_expire'], $_SESSION['pending_email']);
        redirect(BASE_URL . '/auth/login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Greencoin Registration</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }

        .otp-input {
            transition: all 0.3s ease;
        }

        .role-card {
            transition: all 0.2s ease;
        }

        .role-card:hover {
            transform: translateY(-2px);
        }

        .role-card.selected {
            border-color: #10b981;
            background-color: #f0fdf4;
        }

        .password-toggle {
            cursor: pointer;
        }

        input[type="file"] {
            display: none;
        }

        .image-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#10b981",
                        secondary: "#6b7280",
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
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Create Account</h1>
            <p class="text-gray-600">Join Greencoin and start your sustainable journey</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm"><ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-6">
            <!-- Full Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary" />
            </div>

            <!-- Email + OTP -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <div class="flex gap-2">
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary" />
                    <button type="submit" name="send_otp"
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-green-600">Send OTP</button>
                </div>
            </div>

            <!-- OTP -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">OTP Code</label>
                <input type="text" name="otp" maxlength="6"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-center text-lg tracking-widest"
                       placeholder="Enter 6-digit code" />
            </div>

            <!-- Role selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">I am a</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="role-card border-2 border-gray-200 rounded-lg p-4 text-center cursor-pointer" data-role="individual">
                        <i class="ri-user-line text-2xl text-gray-600"></i>
                        <span class="text-sm font-medium text-gray-700">Individual</span>
                    </div>
                    <div class="role-card border-2 border-gray-200 rounded-lg p-4 text-center cursor-pointer" data-role="ngo">
                        <i class="ri-hand-heart-line text-2xl text-gray-600"></i>
                        <span class="text-sm font-medium text-gray-700">NGO</span>
                    </div>
                    <div class="role-card border-2 border-gray-200 rounded-lg p-4 text-center cursor-pointer" data-role="factory">
                        <i class="ri-building-line text-2xl text-gray-600"></i>
                        <span class="text-sm font-medium text-gray-700">Factory</span>
                    </div>
                </div>
                <input type="hidden" id="selectedRole" name="role" value="<?= htmlspecialchars($_POST['role'] ?? '') ?>" required />
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                       placeholder="Create a password" />
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <input type="password" name="confirm_password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                       placeholder="Confirm your password" />
            </div>

            <!-- Profile Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Image</label>
                <div class="flex items-center gap-4">
                    <div id="imagePreview"
                         class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-full flex items-center justify-center bg-gray-50">
                        <i class="ri-camera-line text-2xl text-gray-400"></i>
                    </div>
                    <div class="flex-1">
                        <label for="profileImage"
                               class="inline-block px-4 py-2 bg-gray-100 text-gray-700 rounded-lg cursor-pointer hover:bg-gray-200 transition-colors">
                            Choose Image
                        </label>
                        <input type="file" id="profileImage" name="profileImage" accept="image/*" />
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF (max 5MB)</p>
                    </div>
                </div>
            </div>

            <!-- Register -->
            <button type="submit" name="register"
                    class="w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">Register User</button>
        </form>

        <div class="text-center mt-6">
            <a href="login.php" class="text-green-600 hover:underline text-sm">Back to Login</a>
        </div>
    </div>

    <script>
        // Role selection highlight
        document.querySelectorAll(".role-card").forEach(card => {
            card.addEventListener("click", function() {
                document.querySelectorAll(".role-card").forEach(c => c.classList.remove("selected"));
                this.classList.add("selected");
                document.getElementById("selectedRole").value = this.dataset.role;
            });
        });

        // Image preview
        const profileImageInput = document.getElementById("profileImage");
        const imagePreview = document.getElementById("imagePreview");
        profileImageInput.addEventListener("change", function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="image-preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
