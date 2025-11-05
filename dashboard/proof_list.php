<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Get user factory_id
// $stmt = $conn->prepare("SELECT f.id AS user_id 
//                         FROM users u 
//                         JOIN factories f ON u.user_id = f.user_id 
//                         WHERE u.user_id = ?");
// $stmt->bind_param("i", $_SESSION['user_id']);
// $stmt->execute();
// $stmt->bind_result($user_id);
// $stmt->fetch();
// $stmt->close();

// if (!$user_id) {
//     echo "Factory not found.";
//     exit;
// }


// Fetch planting proofs
$sql = "SELECT p.id, p.tree_species, p.geo_location, p.tree_count, p.land_type, 
            p.photo_url, p.qrcode_file, p.uploaded_at, 
            v.is_verified, v.verification_date, v.verified_by
        FROM planting_proofs p
        LEFT JOIN verifications v ON p.id = v.proof_id
        WHERE p.user_id = ?
        ORDER BY p.uploaded_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Planting Proofs</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: "#10b981", secondary: "#059669" },
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .table-scroll {
            scrollbar-width: thin;
            scrollbar-color: #e5e7eb #f9fafb;
        }

        .table-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .table-scroll::-webkit-scrollbar-track {
            background: #f9fafb;
            border-radius: 4px;
        }

        .table-scroll::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 4px;
        }

        .table-scroll::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }

        .photo-hover:hover {
            transform: scale(1.05);
        }

        .qr-hover:hover {
            transform: scale(1.2);
        }

        .row-hover:hover {
            background-color: #f8fafc;
        }

        .status-verified {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .status-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-secondary/5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 flex items-center justify-center bg-primary/10 rounded-lg">
                            <i class="ri-plant-line text-primary text-lg"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            My Planting Proofs
                        </h1>
                    </div>
                    <button onclick="goBack()"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-button hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200 shadow-sm !rounded-button whitespace-nowrap">
                        <div class="w-4 h-4 flex items-center justify-center mr-2">
                            <i class="ri-arrow-left-line text-sm"></i>
                        </div>
                        Go Back
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="relative" id="sortDropdown">
                            <button onclick="toggleDropdown('sortOptions')"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-button hover:bg-gray-50 transition-colors duration-200 shadow-sm !rounded-button whitespace-nowrap">
                                <div class="w-4 h-4 flex items-center justify-center mr-2">
                                    <i class="ri-sort-line"></i>
                                </div>
                                Sort by
                                <div class="w-4 h-4 flex items-center justify-center ml-2">
                                    <i class="ri-arrow-down-s-line"></i>
                                </div>
                            </button>
                            <div id="sortOptions"
                                class="hidden absolute left-0 mt-2 w-48 rounded-lg bg-white shadow-lg border border-gray-200 z-10">
                                <div class="py-1">
                                    <button onclick="sortTable('name', 'asc')"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="w-4 h-4 flex items-center justify-center mr-2">
                                            <i class="ri-sort-asc"></i>
                                        </div>
                                        Name (A-Z)
                                    </button>
                                    <button onclick="sortTable('name', 'desc')"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="w-4 h-4 flex items-center justify-center mr-2">
                                            <i class="ri-sort-desc"></i>
                                        </div>
                                        Name (Z-A)
                                    </button>
                                    <button onclick="sortTable('date', 'desc')"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="w-4 h-4 flex items-center justify-center mr-2">
                                            <i class="ri-calendar-line"></i>
                                        </div>
                                        Latest Date
                                    </button>
                                    <button onclick="sortTable('count', 'desc')"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="w-4 h-4 flex items-center justify-center mr-2">
                                            <i class="ri-number-1"></i>
                                        </div>
                                        Highest Count
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="relative" id="filterDropdown">
                            <button onclick="toggleDropdown('filterOptions')"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-button hover:bg-gray-50 transition-colors duration-200 shadow-sm !rounded-button whitespace-nowrap">
                                <div class="w-4 h-4 flex items-center justify-center mr-2">
                                    <i class="ri-filter-line"></i>
                                </div>
                                Filter
                                <div class="w-4 h-4 flex items-center justify-center ml-2">
                                    <i class="ri-arrow-down-s-line"></i>
                                </div>
                            </button>
                            <div id="filterOptions"
                                class="hidden absolute left-0 mt-2 w-48 rounded-lg bg-white shadow-lg border border-gray-200 z-10">
                                <div class="py-1">
                                    <button onclick="filterStatus('all')"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="w-4 h-4 flex items-center justify-center mr-2">
                                            <i class="ri-apps-line"></i>
                                        </div>
                                        All
                                    </button>
                                    <button onclick="filterStatus('verified')"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="w-4 h-4 flex items-center justify-center mr-2">
                                            <i class="ri-checkbox-circle-line text-primary"></i>
                                        </div>
                                        Verified
                                    </button>
                                    <button onclick="filterStatus('pending')"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="w-4 h-4 flex items-center justify-center mr-2">
                                            <i class="ri-time-line text-orange-500"></i>
                                        </div>
                                        Pending
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto table-scroll">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Tree Species
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Location
                            </th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Count
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Land Type
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date
                            </th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Photo
                            </th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                QR Code
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="row-hover transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 flex items-center justify-center bg-green-100 rounded-lg mr-3">
                                            <i class="ri-leaf-line text-green-600 text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['tree_species']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono text-gray-600"><?php echo htmlspecialchars($row['geo_location']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full"><?php echo htmlspecialchars($row['tree_count']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><?php echo htmlspecialchars($row['land_type']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900"><?php echo date("d M Y", strtotime($row['uploaded_at'])); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="relative group">
                                        <?php if ($row['photo_url']): ?>
                                            <img src="../<?= $row['photo_url'] ?>" alt="Proof Photo"
                                                class="w-24 h-24 rounded object-cover cursor-pointer transition-transform hover:scale-105"
                                                onclick="showFullImage(this.src)">
                                        <?php else: ?> N/A <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-block cursor-pointer qr-hover transition-transform duration-200"
                                        onclick="enlargeQR(this)">
                                        <div class="w-24 h-24 bg-gray-100 rounded flex items-center justify-center cursor-pointer">
                                            <?php if ($row['qrcode_file']): ?>
                                                <img src="../<?= $row['qrcode_file'] ?>" alt="QR Code">
                                            <?php else: ?> N/A <?php endif; ?>
                                        </div>
                                        <div
                                            class="hidden group-hover:flex absolute -top-2 right-0 bg-white shadow-lg rounded-lg p-2 z-10 gap-2">
                                            <button
                                                class="flex items-center gap-1 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 rounded-button">
                                                <i class="ri-eye-line"></i> View
                                            </button>
                                            <button onclick="downloadQRCode(this)"
                                                class="flex items-center gap-1 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50 rounded-button">
                                                <i class="ri-download-line"></i> Download
                                            </button>
                                        </div>
                                        <canvas id="qr-canvas" class="hidden"></canvas>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex flex-col">
                                            <?php
                                                if (is_null($row['is_verified'])) {
                                                    echo "⏳ Pending";
                                                } 
                                                elseif ($row['is_verified'] == 1) {
                                                    echo "✅ Verified by " . htmlspecialchars($row['verified_by']);
                                                } 
                                                else {
                                                    echo "❌ Rejected by " . htmlspecialchars($row['verified_by']);
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span></span>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 status-verified rounded-full"></div>
                            <span>Verified</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 status-pending rounded-full"></div>
                            <span>Pending</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="photoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl p-4 max-w-2xl max-h-[80vh] overflow-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Planting Proof Photo
                </h3>
                <button onclick="closeModal('photoModal')"
                    class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                    <i class="ri-close-line text-gray-600"></i>
                </button>
            </div>
            <img id="enlargedPhoto" src="" alt="Enlarged photo" class="w-full h-auto rounded-lg" />
        </div>
    </div>
    <div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl p-4 max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">QR Code</h3>
                <button onclick="closeModal('qrModal')"
                    class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                    <i class="ri-close-line text-gray-600"></i>
                </button>
            </div>
            <img id="enlargedQR" src="" alt="Enlarged QR code" class="w-full h-auto rounded-lg" />
        </div>
    </div>
    <script id="navigation">
        function goBack() {
            window.history.back();
        }
    </script>
    <script id="modal-functionality">
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll('[id$="Options"]');
            allDropdowns.forEach((d) => {
                if (d.id !== id) d.classList.add("hidden");
            });
            dropdown.classList.toggle("hidden");
        }

        document.addEventListener("click", function (e) {
            const sortDropdown = document.getElementById("sortDropdown");
            const filterDropdown = document.getElementById("filterDropdown");
            const sortOptions = document.getElementById("sortOptions");
            const filterOptions = document.getElementById("filterOptions");

            if (!sortDropdown.contains(e.target)) {
                sortOptions.classList.add("hidden");
            }
            if (!filterDropdown.contains(e.target)) {
                filterOptions.classList.add("hidden");
            }
        });

        function sortTable(column, direction) {
            const tbody = document.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((a, b) => {
                let aValue, bValue;

                if (column === "name") {
                    aValue = a.querySelector("td:first-child .text-sm").textContent;
                    bValue = b.querySelector("td:first-child .text-sm").textContent;
                } else if (column === "date") {
                    aValue = new Date(
                        a.querySelector("td:nth-child(5) .text-sm").textContent,
                    );
                    bValue = new Date(
                        b.querySelector("td:nth-child(5) .text-sm").textContent,
                    );
                } else if (column === "count") {
                    aValue = parseInt(
                        a.querySelector("td:nth-child(3) .text-sm").textContent,
                    );
                    bValue = parseInt(
                        b.querySelector("td:nth-child(3) .text-sm").textContent,
                    );
                }

                if (direction === "asc") {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });

            rows.forEach((row) => tbody.appendChild(row));
        }

        function filterStatus(status) {
            const rows = document.querySelectorAll("tbody tr");
            rows.forEach((row) => {
                const statusText = row
                    .querySelector("td:last-child .text-sm")
                    .textContent.toLowerCase();
                if (status === "all" || statusText === status.toLowerCase()) {
                    row.classList.remove("hidden");
                } else {
                    row.classList.add("hidden");
                }
            });
        }

        function enlargePhoto(img) {
            const modal = document.getElementById("photoModal");
            const enlargedPhoto = document.getElementById("enlargedPhoto");
            enlargedPhoto.src = img.src.replace(
                "width=80&height=60",
                "width=600&height=400",
            );
            modal.classList.remove("hidden");
        }
        function enlargeQR(img) {
            const modal = document.getElementById("qrModal");
            const enlargedQR = document.getElementById("enlargedQR");
            enlargedQR.src = img.src.replace(
                "width=40&height=40",
                "width=300&height=300",
            );
            modal.classList.remove("hidden");
        }
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add("hidden");
        }
        document.addEventListener("DOMContentLoaded", function () {
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape") {
                    closeModal("photoModal");
                    closeModal("qrModal");
                }
            });
            document.getElementById("photoModal").addEventListener("click", function (e) {
                if (e.target === this) {
                    closeModal("photoModal");
                }
            });
            document.getElementById("qrModal").addEventListener("click", function (e) {
                if (e.target === this) {
                    closeModal("qrModal");
                }
            });
        });

        function showFullImage(src) {
            const modal = document.getElementById("photoModal");
            const modalImg = document.getElementById("modalImage");
            modalImg.src = src;
            modal.style.display = "flex";
        }
    </script>
    <script id="qr-code-functionality">
        function downloadQRCode(button) {
            const row = button.closest("tr");
            const id = row.cells[0].textContent;
            const username = row.cells[1].textContent;
            const species = row.cells[2].textContent;
            const location = row.cells[3].textContent;
            const treeCount = row.cells[4].textContent;

            const qrData = JSON.stringify({
                id: id,
                username: username,
                species: species,
                location: location,
                treeCount: treeCount,
            });

            const canvas = button.parentElement.nextElementSibling;
            const qr = new QRious({
                element: canvas,
                value: qrData,
                size: 300,
                backgroundAlpha: 1,
                foreground: "#000000",
                background: "#ffffff",
                level: "H",
            });

            const link = document.createElement("a");
            link.download = `tree-proof-qr-${id.padStart(3, "0")}.png`;
            link.href = canvas.toDataURL("image/png");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>