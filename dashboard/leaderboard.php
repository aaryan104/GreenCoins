<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'individual') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// User info
$stmt = $conn->prepare("SELECT name, email, role, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


$sql = "
    SELECT 
        u.user_id,
        u.name AS user_name,
        COALESCE(SUM(pp.tree_count), 0) AS planted_trees,
        COALESCE(uc.credits, 0) AS credits
    FROM users u
    LEFT JOIN planting_proofs pp ON u.user_id = pp.user_id
    LEFT JOIN user_credits uc ON u.user_id = uc.user_id
    WHERE u.role = 'individual'
    GROUP BY u.user_id, u.name, uc.credits
    ORDER BY credits DESC
    LIMIT 10
";
$result = $conn->query($sql);

$credits = 0;
$cq = $conn->prepare("SELECT credits FROM user_credits WHERE user_id = ?");
$cq->bind_param("i", $user_id);
$cq->execute(); 
$res = $cq->get_result();
if ($row = $res->fetch_assoc()) {   
    $credits = $row['credits'];
}

$total_proofs = 0;
$pq = $conn->prepare("SELECT COUNT(*) AS total FROM planting_proofs WHERE user_id = ?");
$pq->bind_param("i", $user_id);
$pq->execute();
$proof_res = $pq->get_result();
if ($proof_row = $proof_res->fetch_assoc()) {
    $total_proofs = $proof_row['total'];
}

$rank = 0;
$rq = $conn->prepare("
    SELECT rank_position FROM (
        SELECT user_id, credits, DENSE_RANK() OVER (ORDER BY credits DESC) AS rank_position
        FROM user_credits
    ) AS ranked
    WHERE user_id = ?
");
$rq->bind_param("i", $user_id);
$rq->execute();
$rank_res = $rq->get_result();
if ($rank_row = $rank_res->fetch_assoc()) {
    $rank = $rank_row['rank_position'] ;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Leaderboard - Green Coin</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#2E7D32",
                        secondary: "#4CAF50",
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .custom-checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            position: relative;
        }

        .custom-checkbox:checked {
            background: #2E7D32;
            border-color: #2E7D32;
        }

        .custom-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .rank-badge {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #8B4513;
        }

        .rank-badge.silver {
            background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
            color: #4A4A4A;
        }

        .rank-badge.bronze {
            background: linear-gradient(135deg, #CD7F32, #B8860B);
            color: #654321;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="w-full px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <div class="text-2xl font-bold text-primary">GreenCoin</div>
                    <nav class="hidden md:flex items-center space-x-6">
                        <a href="index.php"
                            data-readdy="true"
                            class="text-gray-700 hover:text-primary transition-colors duration-200 font-medium">Dashboard</a>
                        <a href="plant_tree.php"
                            class="text-gray-700 hover:text-primary transition-colors duration-200 font-medium">Submit
                            Proof</a>
                        <a href="proof_list.php"
                            class="text-gray-700 hover:text-primary transition-colors duration-200 font-medium">My
                            Proofs</a>
                        <a href="sell_credits.php"
                            class="text-gray-700 hover:text-primary transition-colors duration-200 font-medium">Sell
                            Credits</a>
                        <a href="#" class="text-primary font-semibold">Leaderboard</a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="md:hidden w-8 h-8 flex items-center justify-center">
                        <i class="ri-menu-line text-xl text-gray-700"></i>
                    </button>
                    <a href="../auth/logout.php"
                        class="text-red-600 hover:text-red-700 transition-colors duration-200 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="w-full px-6 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Leaderboard</h1>
                <p class="text-gray-600">
                    See how you rank among the top environmental contributors
                </p>
            </div>

            <div class="bg-gradient-to-r from-primary to-secondary rounded-xl p-6 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                            <i class="ri-user-fill text-2xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Your Current Position</h3>
                            <p class="text-green-100"><?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">#<?php echo $rank; ?></div>
                        <div class="text-green-100 text-sm">
                            <?php echo $credits; ?> Credits • <?php echo $total_proofs; ?> Proofs
                        </div>
                    </div>
                    <a href="index.php"
                        data-readdy="true"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-medium px-4 py-2 !rounded-button transition-colors duration-200 whitespace-nowrap">
                        View Details
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rank
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Credits
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Proofs
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            if ($result && $result->num_rows > 0) {
                                $rank = 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='hover:bg-gray-50 transition-colors duration-150'>
                                            <td class='px-6 py-4 whitespace-nowrap'>
                                                <div class='flex items-center'>
                                                    <span class='w-8 h-8 rank-badge rounded-full flex items-center justify-center text-sm font-bold'>{$rank}</span>
                                                </div>
                                            </td>
                                            <td class='px-6 py-4 whitespace-nowrap'>
                                                <div class='flex items-center'>
                                                    <div class='w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center mr-3'>
                                                        <i class='ri-user-fill text-white'></i>
                                                    </div>
                                                    <div>
                                                        <div class='text-sm font-medium text-gray-900'>" . htmlspecialchars($row['user_name']) . "</div>
                                                        <div class='text-sm text-gray-500'>" . htmlspecialchars($row['user_id']) . "</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class='px-6 py-4 whitespace-nowrap'>
                                                <div class='flex items-center'>
                                                    <div class='w-4 h-4 flex items-center justify-center mr-2'>
                                                        <i class='ri-coin-fill text-yellow-500'></i>
                                                    </div>
                                                    <span class='text-sm font-medium text-gray-900'>" . htmlspecialchars($row['credits']) . "</span>
                                                </div>
                                            </td>
                                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['planted_trees']) . "</td>
                                        </tr>";
                                    $rank++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

<script id="mobile-navigation">
    document.addEventListener("DOMContentLoaded", function () {
        const mobileMenuButton = document.querySelector(".md\\:hidden");
        const navigation = document.querySelector("nav");
        if (mobileMenuButton && navigation) {
            mobileMenuButton.addEventListener("click", function () {
                navigation.classList.toggle("hidden");
                navigation.classList.toggle("flex");
            });
        }
    });
</script>

<script id="filter-functionality">
    document.addEventListener("DOMContentLoaded", function () {
        const filterButtons = document.querySelectorAll(".filter-btn");
        filterButtons.forEach((button) => {
            button.addEventListener("click", function () {
                filterButtons.forEach((btn) => {
                    btn.classList.remove(
                        "active",
                        "bg-primary",
                        "text-white",
                        "border-primary",
                    );
                    btn.classList.add("bg-white", "text-gray-700", "border-gray-300");
                });
                this.classList.add(
                    "active",
                    "bg-primary",
                    "text-white",
                    "border-primary",
                );
                this.classList.remove("bg-white", "text-gray-700", "border-gray-300");
            });
        });
        filterButtons[0].classList.add("bg-primary", "text-white", "border-primary");
        filterButtons[0].classList.remove(
            "bg-white",
            "text-gray-700",
            "border-gray-300",
        );
    });
</script>

<script id="search-functionality">
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.querySelector('input[type="text"]');
        const tableRows = document.querySelectorAll("tbody tr");
        if (searchInput) {
            searchInput.addEventListener("input", function () {
                const searchTerm = this.value.toLowerCase();
                tableRows.forEach((row) => {
                    const userName = row
                        .querySelector(".text-sm.font-medium, .text-sm.font-bold")
                        .textContent.toLowerCase();
                    const userEmail = row
                        .querySelector(".text-sm.text-gray-500")
                        .textContent.toLowerCase();
                    if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        }
    });
</script>

<script id="table-interactions">
    document.addEventListener("DOMContentLoaded", function () {
        const tableRows = document.querySelectorAll("tbody tr");
        tableRows.forEach((row) => {
            row.addEventListener("mouseenter", function () {
                if (!this.classList.contains("bg-green-50")) {
                    this.style.transform = "translateX(4px)";
                }
            });
            row.addEventListener("mouseleave", function () {
                this.style.transform = "translateX(0)";
            });
        });
    });
</script>
</body>
</html>
