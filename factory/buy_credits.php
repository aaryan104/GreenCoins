<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Agar login nahi hai ya role factory nahi hai to redirect
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'factory') {
    header("Location: ../login.php");
    exit;
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credits = intval($_POST['credits'] ?? 0);

    if ($credits > 0) {
        $user_id = (int)$_SESSION['user_id']; // users table ka id

        // Pehle factories table se factory_id nikaalo
        $stmt = $conn->prepare("SELECT id FROM factories WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $factory = $res->fetch_assoc();
        $stmt->close();

        if ($factory) {
            $factory_id = (int)$factory['id'];

            // Request save karo factory_requests table me
            $stmt = $conn->prepare("INSERT INTO factory_requests (factory_id, credits, status, created_at) VALUES (?, ?, 'pending', NOW())");
            $stmt->bind_param("ii", $factory_id, $credits);

            if ($stmt->execute()) {
                $success = "Credits purchase request submitted successfully. Please wait for admin approval.";
            } else {
                $error = "Something went wrong. Try again later.";
            }
            $stmt->close();
        } else {
            $error = "Factory profile not found for this user.";
        }
    } else {
        $error = "Please enter a valid number of credits.";
    }
}
?>

<!-- <div class="container mt-5">
    <h2 class="mb-4">Buy Credits</h2>

    <form method="POST" action="">
        <div class="form-group mb-3">
            <label for="credits">Enter Credits to Purchase</label>
            <input type="number" name="credits" id="credits" class="form-control" min="1" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div> -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buy Credits</title>
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
                        primary: "#10b981",
                        secondary: "#3b82f6",
                    },
                    borderRadius: {
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

<body class="bg-gray-50 min-h-screen">
    <main class="flex-1 py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-coins-line text-2xl text-primary"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Buy Credits</h1>
                    <p class="text-gray-600">
                        Convert your Cash into Credits For Create a Eco-Friendly Environment
                    </p>
                </div>
                <!-- Sell Credits Form -->
                <form id="sellCreditsForm" method="POST" class="space-y-6">
                    <div>
                        <label for="credits" class="block text-sm font-semibold text-gray-700 mb-3">
                            Enter Credits to Buy
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ri-coins-line text-gray-400"></i>
                            </div>
                            <input type="number" id="credits" name="credits"
                                placeholder="Enter amount of credits"
                                min="1" max="<?php echo $credits; ?>"
                                class="w-full pl-12 pr-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-lg transition-all duration-200"
                                required />
                        </div>
                        <div class="flex justify-between mt-2 text-sm text-gray-500">
                            <span>Minimum: 1 credit</span>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-primary hover:bg-primary/90 text-white font-semibold py-4 px-6 !rounded-button transition-all duration-200 whitespace-nowrap flex items-center justify-center space-x-2">
                        <i class="ri-send-plane-line"></i>
                        <span>Buy Credits</span>
                    </button>
                </form>
                <div class="text-center mt-6">
                    <a href="factory_dashboard.php"
                    class="inline-flex items-center text-primary hover:text-primary/80 font-medium transition-colors duration-200">
                    <i class="ri-arrow-left-line mr-2"></i>
                    Go back to Home
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
