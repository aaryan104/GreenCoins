<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'individual') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch available credits
$credits = 0;
$stmt = $conn->prepare("SELECT credits FROM user_credits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $credits = $row['credits'];
}

// Handle form submission
$successMsg = "";
$errorMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creditsToSell = intval($_POST['credits_to_sell']);

    if ($creditsToSell <= 0) {
        $errorMsg = "❌ Please enter valid credits.";
    } elseif ($creditsToSell > $credits) {
        $errorMsg = "❌ You don't have enough credits.";
    } else {
        // Pehle transaction record karna
        $insert = $conn->prepare("INSERT INTO credit_transactions (user_id, type, credits, status) VALUES (?, 'sell', ?, 'pending')");
        $insert->bind_param("ii", $user_id, $creditsToSell);

        if ($insert->execute()) {
            // 🔹 User ke credits turant ghatana
            // $update = $conn->prepare("UPDATE user_credits SET credits = credits - ? WHERE user_id = ?");
            // $update->bind_param("ii", $creditsToSell, $user_id);
            // $update->execute();

            $successMsg = "Your request to sell {$creditsToSell} credits has been submitted successfully!";

            // Balance refresh karke dikhana
            $stmt = $conn->prepare("SELECT credits FROM user_credits WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $userCredits = $res->fetch_assoc();
            $availableCredits = $userCredits ? $userCredits['credits'] : 0;
        } else {
            $errorMsg = "Invalid credits entered. You can sell up to {$credits} credits.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sell Credits</title>
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Sell Credits</h1>
                    <p class="text-gray-600">
                        Convert your credits into cash quickly and securely
                    </p>
                </div>

                <!-- Available Credits -->
                <div class="bg-gradient-to-r from-primary/5 to-secondary/5 rounded-xl p-6 mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                            <i class="ri-wallet-line text-xl text-primary"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Available Credits</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo number_format($credits); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Success / Error Messages -->
                <?php if ($successMsg): ?>
                    <div class="mb-6 p-4 bg-green-100 border border-green-200 rounded-lg text-green-800">
                        <?php echo htmlspecialchars($successMsg); ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                    <div class="mb-6 p-4 bg-red-100 border border-red-200 rounded-lg text-red-800">
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </div>
                <?php endif; ?>

                <!-- Sell Credits Form -->
                <form id="sellCreditsForm" method="POST" class="space-y-6">
                    <div>
                        <label for="creditsInput" class="block text-sm font-semibold text-gray-700 mb-3">
                            Enter Credits to Sell
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ri-coins-line text-gray-400"></i>
                            </div>
                            <input type="number" id="creditsInput" name="credits_to_sell"
                                placeholder="Enter amount of credits"
                                min="1" max="<?php echo $credits; ?>"
                                class="w-full pl-12 pr-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-lg transition-all duration-200"
                                required />
                        </div>
                        <div class="flex justify-between mt-2 text-sm text-gray-500">
                            <span>Minimum: 1 credit</span>
                            <span>Maximum: <?php echo number_format($credits); ?> credits</span>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-primary hover:bg-primary/90 text-white font-semibold py-4 px-6 !rounded-button transition-all duration-200 whitespace-nowrap flex items-center justify-center space-x-2">
                        <i class="ri-send-plane-line"></i>
                        <span>Sell Credits</span>
                    </button>
                </form>
                <div class="text-center mt-6">
                    <a href="index.php  "
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
