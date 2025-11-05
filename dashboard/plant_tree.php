<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Session se factory id le rahe hain
    $tree_species = $_POST['tree_species'];
    $geo_location = $_POST['geo_location'];
    $tree_count = $_POST['tree_count'];
    $land_type = $_POST['land_type'];

    // ====== 1. Photo Upload ======
    // ====== 1. Photo Upload ======
$photo_path = null;
if (!empty($_FILES['photo']['name'])) {
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $photoName = time() . '_' . basename($_FILES['photo']['name']);
    $targetFile = $uploadDir . $photoName;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        // relative path jo web se access ho sake
        $photo_path = 'uploads/' . $photoName;
    }
}


    // ====== 2. DB Insert ======
    $stmt = $conn->prepare("INSERT INTO planting_proofs (user_id, tree_species, geo_location, tree_count, land_type, photo_url) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $user_id, $tree_species, $geo_location, $tree_count, $land_type, $photo_path);
    $stmt->execute();
    $proof_id = $stmt->insert_id;
    $stmt->close();

    // ====== 3. QR Code Generate ======
    require_once __DIR__ . '/../assets/phpqrcode/qrlib.php';
    $qrDir = __DIR__ . '/../assets/qrcodes/';

    $qrFileName = 'qr_' . $proof_id . '.png';
    $qrFilePath = $qrDir . $qrFileName;
    $qrRelativePath = '/../assets/qrcodes/' . $qrFileName;

    QRcode::png("proof_id=" . $proof_id, $qrFilePath, QR_ECLEVEL_L, 5);

    // ====== 4. QR Path DB me Save ======
    $stmt = $conn->prepare("UPDATE planting_proofs SET qrcode_file = ? WHERE id = ?");
    $stmt->bind_param("si", $qrRelativePath, $proof_id);
    $stmt->execute();
    $stmt->close();

        // ====== 5. Green Credits Update ======
    // Example: 1 tree = 5 credits
    // $creditsToAdd = $tree_count * 5;

    // // Check if factory already has credits record
    // $check = $conn->prepare("SELECT id FROM green_credits WHERE factory_id = ?");
    // $check->bind_param("i", $factory_id);
    // $check->execute();
    // $check->store_result();

    // if ($check->num_rows > 0) {
    //     // Row exists → update credits
    //     $stmt = $conn->prepare("UPDATE green_credits SET credits = credits + ? WHERE factory_id = ?");
    //     $stmt->bind_param("ii", $creditsToAdd, $factory_id);
    //     $stmt->execute();
    //     $stmt->close();
    // } else {
    //     // Row doesn't exist → insert new row
    //     $stmt = $conn->prepare("INSERT INTO green_credits (factory_id, credits) VALUES (?, ?)");
    //     $stmt->bind_param("ii", $factory_id, $creditsToAdd);
    //     $stmt->execute();
    //     $stmt->close();
    // }
    // $check->close();


    $message = "✅ Planting proof submitted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Plant a Tree</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#22c55e",
                        secondary: "#16a34a",
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

        .gradient-bg {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 50%, #86efac 100%);
        }

        .form-step {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        }

        .custom-checkbox {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #22c55e;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            position: relative;
        }

        .custom-checkbox:checked {
            background: #22c55e;
        }

        .custom-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.875rem;
            font-weight: bold;
        }

        .custom-radio {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #22c55e;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            position: relative;
        }

        .custom-radio:checked {
            background: #22c55e;
        }

        .custom-radio:checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0.5rem;
            height: 0.5rem;
            background: white;
            border-radius: 50%;
        }

        .upload-zone {
            border: 2px dashed #22c55e;
            transition: all 0.3s ease;
        }

        .upload-zone:hover {
            border-color: #16a34a;
            background-color: #f0fdf4;
        }

        .upload-zone.dragover {
            border-color: #16a34a;
            background-color: #dcfce7;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="gradient-bg min-h-screen">
        <header class="w-full py-6 px-4">
            <div class="max-w-4xl mx-auto flex justify-between items-center">
                <h1 class="text-4xl font-bold text-green-800">Plant a Tree</h1>
                <div class="flex gap-4">
                    <a href="proof_list.php"
                        class="flex items-center gap-2 px-4 py-2 bg-white text-green-700 rounded-button hover:bg-green-50 transition-colors whitespace-nowrap">
                        <div class="w-5 h-5 flex items-center justify-center">
                            <i class="ri-file-list-3-line text-lg"></i>
                        </div>
                        View My Proof
                    </a>
                    <a href="index.php"
                        class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-button hover:bg-green-700 transition-colors whitespace-nowrap">
                        <div class="w-5 h-5 flex items-center justify-center">
                            <i class="ri-arrow-left-line text-lg"></i>
                        </div>
                        Go Back
                    </a>
                </div>
            </div>
        </header>
        <main class="max-w-4xl mx-auto px-4 pb-12">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        Step-by-Step Tree Planting Guide
                    </h2>
                    <p class="text-gray-600">
                        Follow these steps to submit your tree planting proof and
                        contribute to a greener future
                    </p>
                </div>
                <form id="plantTreeForm" class="space-y-8" method="post" enctype="multipart/form-data">
                    <div class="form-step rounded-lg p-6 border border-green-200">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold">
                                1
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">
                                Select Tree Species
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Choose the type of tree you
                                planted</label>
                            <div class="relative">
                                <select id="treeSpecies" name="tree_species"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm pr-8">
                                    <option value="">Select a tree species...</option>
                                    <option value="oak">Oak Tree</option>
                                    <option value="maple">Maple Tree</option>
                                    <option value="pine">Pine Tree</option>
                                    <option value="birch">Birch Tree</option>
                                    <option value="cedar">Cedar Tree</option>
                                    <option value="willow">Willow Tree</option>
                                    <option value="cherry">Cherry Tree</option>
                                    <option value="apple">Apple Tree</option>
                                    <option value="other">Other Species</option>
                                </select>
                            </div>
                            <div id="otherSpeciesInput" class="hidden">
                                <input type="text" id="customSpecies" placeholder="Please specify the tree species"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm" />
                            </div>
                        </div>
                    </div>
                    <div class="form-step rounded-lg p-6 border border-green-200">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold">
                                2
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">Geolocation</h3>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Provide the location where you
                                planted the tree</label>
                            <div class="flex gap-3">
                                <button type="button" id="detectLocation"
                                    class="flex items-center gap-2 px-4 py-3 bg-primary text-white rounded-button hover:bg-secondary transition-colors whitespace-nowrap">
                                    <div class="w-5 h-5 flex items-center justify-center">
                                        <i class="ri-map-pin-line text-lg"></i>
                                    </div>
                                    Auto-detect Location
                                </button>
                                <div class="flex-1">
                                    <input type="text" id="coordinates" name="geo_location"
                                        placeholder="Coordinates (e.g., 40.7128, -74.0060)"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm" />
                                </div>
                            </div>
                            <div id="locationStatus" class="text-sm text-gray-600"></div>
                        </div>
                    </div>
                    <div class="form-step rounded-lg p-6 border border-green-200">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold">
                                3
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">Tree Count</h3>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">How many trees did you plant?</label>
                            <div class="flex items-center gap-3">
                                <button type="button" id="decreaseCount"
                                    class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-colors">
                                    <div class="w-5 h-5 flex items-center justify-center">
                                        <i class="ri-subtract-line text-lg"></i>
                                    </div>
                                </button>
                                <input type="number" id="treeCount" value="1" min="1" max="1000" name="tree_count"
                                    class="w-24 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-center text-sm" />
                                <button type="button" id="increaseCount"
                                    class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-colors">
                                    <div class="w-5 h-5 flex items-center justify-center">
                                        <i class="ri-add-line text-lg"></i>
                                    </div>
                                </button>
                                <span class="text-sm text-gray-600 ml-2">trees planted</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-step rounded-lg p-6 border border-green-200">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold">
                                4
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">Land Type</h3>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">What type of land did you plant
                                on?</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label
                                    class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="land_type" value="public" class="custom-radio" />
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 flex items-center justify-center">
                                            <i class="ri-community-line text-lg text-primary"></i>
                                        </div>
                                        <span class="text-sm font-medium">Public Land</span>
                                    </div>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="land_type" value="private" class="custom-radio" />
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 flex items-center justify-center">
                                            <i class="ri-home-line text-lg text-primary"></i>
                                        </div>
                                        <span class="text-sm font-medium">Private Land</span>
                                    </div>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="land_type" value="forest" class="custom-radio" />
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 flex items-center justify-center">
                                            <i class="ri-tree-line text-lg text-primary"></i>
                                        </div>
                                        <span class="text-sm font-medium">Forest Area</span>
                                    </div>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="land_type" value="urban" class="custom-radio" />
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 flex items-center justify-center">
                                            <i class="ri-building-line text-lg text-primary"></i>
                                        </div>
                                        <span class="text-sm font-medium">Urban Area</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-step rounded-lg p-6 border border-green-200">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold">
                                5
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">
                                Upload Photo
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Upload a photo of your planted tree
                                as proof</label>
                            <div id="uploadZone" class="upload-zone rounded-lg p-8 text-center cursor-pointer">
                                <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                    <i class="ri-image-add-line text-4xl text-primary"></i>
                                </div>
                                <p class="text-lg font-medium text-gray-700 mb-2">
                                    Drag and drop your photo here
                                </p>
                                <p class="text-sm text-gray-500 mb-4">
                                    or click to browse files
                                </p>
                                <!-- <button type="button" id="browseFiles"
                                >
                                Choose File
                            </button> -->
                                <input type="file" name="photo" id="photoUpload" accept="image/*" required
                                class="px-6 py-2 bg-primary text-white rounded-button hover:bg-secondary transition-colors whitespace-nowrap">
                                <!-- <input type="file" name="photo"  accept="image/*" class="hidden" /> -->
                                <p class="text-xs text-gray-400 mt-3">
                                    Accepted formats: JPG, PNG, WEBP (Max size: 10MB)
                                </p>
                            </div>
                            <div id="photoPreview" class="hidden">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                    <img id="previewImage" src="" alt="Preview"
                                        class="w-20 h-20 object-cover rounded-lg" />
                                    <div class="flex-1">
                                        <p id="fileName" class="text-sm font-medium text-gray-700"></p>
                                        <p id="fileSize" class="text-xs text-gray-500"></p>
                                    </div>
                                    <button type="button" id="removePhoto"
                                        class="w-8 h-8 bg-red-100 hover:bg-red-200 rounded-full flex items-center justify-center transition-colors">
                                        <div class="w-4 h-4 flex items-center justify-center">
                                            <i class="ri-close-line text-lg text-red-600"></i>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-6">
                        <button type="submit" id="submitProof"
                            class="w-full py-4 bg-gradient-to-r from-primary to-secondary text-white text-lg font-semibold rounded-button hover:from-secondary hover:to-primary transition-all duration-300 transform hover:scale-105 whitespace-nowrap">
                            <span id="submitText">Submit Proof</span>
                            <div id="submitLoading" class="hidden flex items-center justify-center gap-2">
                                <div
                                    class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin">
                                </div>
                                <span>Submitting...</span>
                            </div>
                        </button>
                    </div>
                </form>
                <div id="successMessage" class="hidden mt-8 p-6 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center">
                            <i class="ri-check-line text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-green-800">
                                Tree Planting Proof Submitted Successfully!
                            </h4>
                            <p class="text-green-700">
                                Thank you for contributing to a greener planet. Your
                                submission has been recorded and will be reviewed shortly.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script id="form-interactions">
        document.addEventListener("DOMContentLoaded", function () {
            const treeSpeciesSelect = document.getElementById("treeSpecies");
            const otherSpeciesInput = document.getElementById("otherSpeciesInput");
            const customSpeciesInput = document.getElementById("customSpecies");
            treeSpeciesSelect.addEventListener("change", function () {
                if (this.value === "other") {
                    otherSpeciesInput.classList.remove("hidden");
                    customSpeciesInput.required = true;
                } else {
                    otherSpeciesInput.classList.add("hidden");
                    customSpeciesInput.required = false;
                    customSpeciesInput.value = "";
                }
            });
        });
    </script>
    <script id="location-detection">
        document.addEventListener("DOMContentLoaded", function () {
            const detectLocationBtn = document.getElementById("detectLocation");
            const coordinatesInput = document.getElementById("coordinates");
            const locationStatus = document.getElementById("locationStatus");
            detectLocationBtn.addEventListener("click", function () {
                if (navigator.geolocation) {
                    locationStatus.textContent = "Detecting your location...";
                    locationStatus.className = "text-sm text-blue-600";
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const lat = position.coords.latitude.toFixed(6);
                            const lng = position.coords.longitude.toFixed(6);
                            coordinatesInput.value = `${lat}, ${lng}`;
                            locationStatus.textContent = "Location detected successfully!";
                            locationStatus.className = "text-sm text-green-600";
                        },
                        function (error) {
                            locationStatus.textContent =
                                "Unable to detect location. Please enter coordinates manually.";
                            locationStatus.className = "text-sm text-red-600";
                        },
                    );
                } else {
                    locationStatus.textContent =
                        "Geolocation is not supported by this browser.";
                    locationStatus.className = "text-sm text-red-600";
                }
            });
        });
    </script>
    <script id="tree-counter">
        document.addEventListener("DOMContentLoaded", function () {
            const decreaseBtn = document.getElementById("decreaseCount");
            const increaseBtn = document.getElementById("increaseCount");
            const treeCountInput = document.getElementById("treeCount");
            decreaseBtn.addEventListener("click", function () {
                const currentValue = parseInt(treeCountInput.value);
                if (currentValue > 1) {
                    treeCountInput.value = currentValue - 1;
                }
            });
            increaseBtn.addEventListener("click", function () {
                const currentValue = parseInt(treeCountInput.value);
                if (currentValue < 1000) {
                    treeCountInput.value = currentValue + 1;
                }
            });
            treeCountInput.addEventListener("input", function () {
                const value = parseInt(this.value);
                if (value < 1) this.value = 1;
                if (value > 1000) this.value = 1000;
            });
        });
    </script>
    <script id="photo-upload">
        document.addEventListener("DOMContentLoaded", function () {
            const uploadZone = document.getElementById("uploadZone");
            const browseFilesBtn = document.getElementById("browseFiles");
            const photoUpload = document.getElementById("photoUpload");
            const photoPreview = document.getElementById("photoPreview");
            const previewImage = document.getElementById("previewImage");
            const fileName = document.getElementById("fileName");
            const fileSize = document.getElementById("fileSize");
            const removePhotoBtn = document.getElementById("removePhoto");
            uploadZone.addEventListener("click", function () {
                photoUpload.click();
            });
            browseFilesBtn.addEventListener("click", function (e) {
                e.stopPropagation();
                photoUpload.click();
            });
            uploadZone.addEventListener("dragover", function (e) {
                e.preventDefault();
                this.classList.add("dragover");
            });
            uploadZone.addEventListener("dragleave", function (e) {
                e.preventDefault();
                this.classList.remove("dragover");
            });
            uploadZone.addEventListener("drop", function (e) {
                e.preventDefault();
                this.classList.remove("dragover");
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                }
            });
            photoUpload.addEventListener("change", function (e) {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });
            removePhotoBtn.addEventListener("click", function () {
                photoUpload.value = "";
                photoPreview.classList.add("hidden");
                uploadZone.classList.remove("hidden");
            });
            function handleFileSelect(file) {
                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImage.src = e.target.result;
                        fileName.textContent = file.name;
                        fileSize.textContent = formatFileSize(file.size);
                        photoPreview.classList.remove("hidden");
                        uploadZone.classList.add("hidden");
                    };
                    reader.readAsDataURL(file);
                }
            }
            function formatFileSize(bytes) {
                if (bytes === 0) return "0 Bytes";
                const k = 1024;
                const sizes = ["Bytes", "KB", "MB", "GB"];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
            }
        });
    </script>
    <script id="form-submission">
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("plantTreeForm");
            const submitBtn = document.getElementById("submitProof");
            const submitText = document.getElementById("submitText");
            const submitLoading = document.getElementById("submitLoading");
            const successMessage = document.getElementById("successMessage");
            form.addEventListener("submit", function (e) {
                e.preventDefault();
                submitText.classList.add("hidden");
                submitLoading.classList.remove("hidden");
                submitBtn.disabled = true;
                setTimeout(function () {
                    submitText.classList.remove("hidden");
                    submitLoading.classList.add("hidden");
                    submitBtn.disabled = false;
                    form.classList.add("hidden");
                    successMessage.classList.remove("hidden");
                    window.scrollTo({ top: 0, behavior: "smooth" });
                }, 2000);
            });
        });
        alert("<?php echo $tree_species + $geo_location + $tree_count + $land_type; ?>");
    </script>
</body>

</html>