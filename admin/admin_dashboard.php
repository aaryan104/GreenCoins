<?php
require_once __DIR__ . '/header.php';

// Quick stats
$stats = [
  'users' => 0, 'ngos' => 0, 'admins' => 0,
  'factories' => 0, 'pending_proofs' => 0, 'verified_proofs' => 0, 'total_credits' => 0
];

$res = $conn->query("SELECT role, COUNT(*) c FROM users GROUP BY role");
while ($r = $res->fetch_assoc()) {
  if ($r['role']==='individual') $stats['users'] = (int)$r['c'];  // yaha change kiya
  if ($r['role']==='NGO')        $stats['ngos']  = (int)$r['c'];
  if ($r['role']==='admin')      $stats['admins']= (int)$r['c'];
}

$stats['factories'] = (int)($conn->query("SELECT COUNT(*) c FROM factories")->fetch_assoc()['c'] ?? 0);
$stats['pending_proofs']  = (int)($conn->query("SELECT COUNT(*) c FROM planting_proofs p LEFT JOIN verifications v ON v.proof_id=p.id WHERE v.id IS NULL")->fetch_assoc()['c'] ?? 0);
$stats['verified_proofs'] = (int)($conn->query("SELECT COUNT(*) c FROM verifications WHERE is_verified=1")->fetch_assoc()['c'] ?? 0);
$stats['total_credits']   = (int)($conn->query("SELECT COALESCE(credits,0) s FROM user_credits WHERE user_id = 12")->fetch_assoc()['s'] ?? 0);
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

<body class="bg-primary text-black min-h-screen">
    <main class="w-full px-6 py-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                Dashboard Overview
            </h2>
            <p class="text-gray-600">Monitor and manage your GreenCoin ecosystem</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['users'] ?></p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-50 rounded-lg">
                        <i class="ri-user-line text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">NGOs</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['ngos'] ?></p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-50 rounded-lg">
                        <i class="ri-heart-line text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Admins</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['admins'] ?></p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-50 rounded-lg">
                        <i class="ri-admin-line text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Factories</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['factories'] ?></p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-orange-50 rounded-lg">
                        <i class="ri-building-line text-2xl text-orange-600"></i>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">
                            Pending Proofs
                        </p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['pending_proofs'] ?></p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-yellow-50 rounded-lg">
                        <i class="ri-time-line text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">
                            Verified Proofs
                        </p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['verified_proofs'] ?></p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-50 rounded-lg">
                        <i class="ri-checkbox-circle-line text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">
                            Total Credits
                        </p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_credits'] ?></p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-indigo-50 rounded-lg">
                        <i class="ri-coin-line text-2xl text-indigo-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900">Pending Proofs</h3>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <div class="w-4 h-4 flex items-center justify-center">
                                    <i class="ri-search-line text-sm text-gray-400"></i>
                                </div>
                            </div>
                            <input type="text" placeholder="Search proofs..."
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>
                        <button
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-button hover:bg-blue-700 transition-colors duration-200 whitespace-nowrap !rounded-button">
                            <div class="w-4 h-4 flex items-center justify-center inline-block mr-2">
                                <i class="ri-refresh-line text-sm"></i>
                            </div>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-1">
                                    <span>ID</span>
                                    <div class="w-3 h-3 flex items-center justify-center">
                                        <i class="ri-arrow-up-down-line text-xs"></i>
                                    </div>
                                </div>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-1">
                                    <span>Factory</span>
                                    <div class="w-3 h-3 flex items-center justify-center">
                                        <i class="ri-arrow-up-down-line text-xs"></i>
                                    </div>
                                </div>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-1">
                                    <span>Species</span>
                                    <div class="w-3 h-3 flex items-center justify-center">
                                        <i class="ri-arrow-up-down-line text-xs"></i>
                                    </div>
                                </div>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-1">
                                    <span>Count</span>
                                    <div class="w-3 h-3 flex items-center justify-center">
                                        <i class="ri-arrow-up-down-line text-xs"></i>
                                    </div>
                                </div>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-1">
                                    <span>Date</span>
                                    <div class="w-3 h-3 flex items-center justify-center">
                                        <i class="ri-arrow-up-down-line text-xs"></i>
                                    </div>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                      $q = $conn->query("
                            (
                                SELECT p.id, p.tree_species, p.tree_count, p.uploaded_at, f.name AS owner_name
                                FROM planting_proofs p
                                JOIN factories f ON f.id = p.user_id
                                LEFT JOIN verifications v ON v.proof_id = p.id
                                WHERE v.id IS NULL
                            )
                            UNION
                            (
                                SELECT p.id, p.tree_species, p.tree_count, p.uploaded_at, u.name AS owner_name
                                FROM planting_proofs p
                                JOIN users u ON u.user_id = p.user_id
                                LEFT JOIN verifications v ON v.proof_id = p.id
                                WHERE v.id IS NULL
                            )
                            ORDER BY uploaded_at DESC
                            LIMIT 10
                        ");
                      while($row=$q->fetch_assoc()):
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?= $row['id'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Mr. <?= htmlspecialchars($row['owner_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($row['tree_species']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= (int)$row['tree_count'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($row['uploaded_at']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-button hover:bg-green-200 transition-colors duration-200 whitespace-nowrap !rounded-button">
                                        Verify
                                    </button>
                                    <button
                                        class="px-3 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-button hover:bg-red-200 transition-colors duration-200 whitespace-nowrap !rounded-button">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script id="search-functionality">
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.querySelector(
                'input[placeholder="Search proofs..."]',
            );
            const tableRows = document.querySelectorAll("tbody tr");

            searchInput.addEventListener("input", function () {
                const searchTerm = this.value.toLowerCase();

                tableRows.forEach((row) => {
                    const factory = row.cells[1].textContent.toLowerCase();
                    const species = row.cells[2].textContent.toLowerCase();

                    if (factory.includes(searchTerm) || species.includes(searchTerm)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        });
    </script>

    <script id="table-sorting">
        document.addEventListener("DOMContentLoaded", function () {
            const headers = document.querySelectorAll('th[class*="cursor-pointer"]');
            const tbody = document.querySelector("tbody");

            headers.forEach((header, index) => {
                header.addEventListener("click", function () {
                    const rows = Array.from(tbody.querySelectorAll("tr"));
                    const isAscending = header.classList.contains("sort-asc");

                    headers.forEach((h) => h.classList.remove("sort-asc", "sort-desc"));

                    if (isAscending) {
                        header.classList.add("sort-desc");
                    } else {
                        header.classList.add("sort-asc");
                    }

                    rows.sort((a, b) => {
                        const aValue = a.cells[index].textContent.trim();
                        const bValue = b.cells[index].textContent.trim();

                        if (index === 3) {
                            return isAscending
                                ? parseInt(bValue) - parseInt(aValue)
                                : parseInt(aValue) - parseInt(bValue);
                        }

                        return isAscending
                            ? bValue.localeCompare(aValue)
                            : aValue.localeCompare(bValue);
                    });

                    rows.forEach((row) => tbody.appendChild(row));
                });
            });
        });
    </script>

    <script id="action-buttons">
        document.addEventListener("DOMContentLoaded", function () {
            const verifyButtons = document.querySelectorAll('button:contains("Verify")');
            const rejectButtons = document.querySelectorAll('button:contains("Reject")');

            document.addEventListener("click", function (e) {
                if (e.target.textContent === "Verify") {
                    const row = e.target.closest("tr");
                    const id = row.cells[0].textContent;
                    alert(`Verifying proof ${id}`);
                }

                if (e.target.textContent === "Reject") {
                    const row = e.target.closest("tr");
                    const id = row.cells[0].textContent;
                    if (confirm(`Are you sure you want to reject proof ${id}?`)) {
                        alert(`Proof ${id} rejected`);
                    }
                }
            });
        });
    </script>
</body>

</html>

<?php require_once __DIR__ . '/footer.php'; ?>
