<?php
require_once __DIR__ . '/header.php';

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (isset($_POST['create'])) {
    $user_id = (int)$_POST['user_id'];
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $ptype = trim($_POST['production_type']);
    $emission = (float)$_POST['carbon_emission'];
    $credits = (float)$_POST['credits_purchased'];
    
    $stmt = $conn->prepare("INSERT INTO factories (user_id, name, location, production_type, carbon_emission, credits_purchased) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('isssdd', $user_id, $name, $location, $ptype, $emission, $credits);
    $stmt->execute(); $stmt->close();
    
    $_SESSION['success'] = 'Factory created successfully!';
    header('Location: factories.php');
    exit();
  }
  
  if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $ptype = trim($_POST['production_type']);
    $emission = (float)$_POST['carbon_emission'];
    $credits = (float)$_POST['credits_purchased'];
    
    $stmt = $conn->prepare("UPDATE factories SET name=?, location=?, production_type=?, carbon_emission=?, credits_purchased=? WHERE id=?");
    $stmt->bind_param('sssddi', $name, $location, $ptype, $emission, $credits, $id);
    $stmt->execute(); $stmt->close();
    
    $_SESSION['success'] = 'Factory updated successfully!';
    header('Location: factories.php');
    exit();
  }
  
  if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM factories WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute(); $stmt->close();
    
    $_SESSION['success'] = 'Factory deleted successfully!';
    header('Location: factories.php');
    exit();
  }
}

// Get filter values
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';
$industry_filter = isset($_GET['industry']) ? $_GET['industry'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!-- Add Tailwind CSS -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="container mx-auto px-4 py-6">
  <!-- Success Message -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
      <p><?= $_SESSION['success'] ?></p>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <!-- Header Section -->
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-800">Factory Management</h1>
      <p class="text-gray-600">Manage and monitor factory emissions and credits</p>
    </div>
    <button onclick="document.getElementById('addFactoryModal').classList.remove('hidden')" 
            class="mt-4 md:mt-0 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200 flex items-center">
      <i class="fas fa-plus mr-2"></i> Add New Factory
    </button>
  </div>

  <!-- Search and Filter Section -->
  <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="md:col-span-2">
        <div class="relative">
          <input type="text" id="searchInput" placeholder="Search factories..." 
                 class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                 value="<?= htmlspecialchars($search) ?>">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
      </div>
      <select id="locationFilter" class="border border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
        <option value="">All Locations</option>
        <?php
        $locations = $conn->query("SELECT DISTINCT location FROM factories ORDER BY location");
        while($loc = $locations->fetch_assoc()) {
          $selected = $location_filter === $loc['location'] ? 'selected' : '';
          echo '<option value="'.htmlspecialchars($loc['location']).'" '.$selected.'>'.htmlspecialchars($loc['location']).'</option>';
        }
        ?>
      </select>
      <select id="industryFilter" class="border border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
        <option value="">All Industries</option>
        <?php
        $industries = $conn->query("SELECT DISTINCT production_type FROM factories ORDER BY production_type");
        while($ind = $industries->fetch_assoc()) {
          $selected = $industry_filter === $ind['production_type'] ? 'selected' : '';
          echo '<option value="'.htmlspecialchars($ind['production_type']).'" '.$selected.'>'.htmlspecialchars($ind['production_type']).'</option>';
        }
        ?>
      </select>
    </div>
  </div>

  <!-- Factories Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    // Build the query with filters and join with related tables
    $query = "SELECT 
                f.*, 
                u.name as owner_name, 
                u.email,
                COALESCE(SUM(pd.emission_tons), 0) as carbon_emission,
                COALESCE(gc.credits, 0) as credits_purchased
              FROM factories f 
              JOIN users u ON u.user_id = f.user_id
              LEFT JOIN pollution_data pd ON pd.factory_id = f.id
              LEFT JOIN green_credits gc ON gc.factory_id = f.id
              WHERE 1=1
              GROUP BY f.id, u.name, u.email, gc.credits";
    
    $params = [];
    $types = '';
    
    if (!empty($location_filter)) {
      $query .= " AND f.location = ?";
      $params[] = $location_filter;
      $types .= 's';
    }
    
    if (!empty($industry_filter)) {
      $query .= " AND f.production_type = ?";
      $params[] = $industry_filter;
      $types .= 's';
    }
    
    if (!empty($search)) {
      $query .= " AND (f.name LIKE ? OR f.location LIKE ? OR f.production_type LIKE ?)";
      $search_param = "%$search%";
      $params = array_merge($params, [$search_param, $search_param, $search_param]);
      $types .= 'sss';
    }
    
    $query .= " ORDER BY f.id DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($factory = $result->fetch_assoc()):
      $emission = (float)$factory['carbon_emission'];
      $credits = (float)$factory['credits_purchased'];
      $usage_percentage = $emission > 0 ? min(100, ($credits / $emission) * 100) : 0;
      $progress_color = $usage_percentage >= 80 ? 'bg-red-500' : ($usage_percentage >= 50 ? 'bg-yellow-500' : 'bg-green-500');
    ?>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200 border border-gray-100">
      <div class="p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($factory['name']) ?></h3>
            <p class="text-gray-500 text-sm"><?= htmlspecialchars($factory['production_type']) ?></p>
          </div>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <?= htmlspecialchars($factory['location']) ?>
          </span>
        </div>
        
        <div class="space-y-4 mt-6">
          <div>
            <div class="flex justify-between text-sm text-gray-600 mb-1">
              <span>Carbon Emission</span>
              <span class="font-medium"><?= number_format($emission, 2) ?> tons</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <?php 
              // Calculate width percentage based on emission level (capped at 100%)
              $max_emission = 2000; // Maximum emission value for 100% width
              $emission_width = min(100, ($emission / $max_emission) * 100);
              ?>
              <div class="h-2 rounded-full <?= $emission > 1000 ? 'bg-red-500' : ($emission > 500 ? 'bg-yellow-500' : 'bg-green-500') ?>" 
                   style="width: <?= $emission_width ?>%; transition: width 0.3s ease;"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between text-sm text-gray-600 mb-1">
              <span>Credits Purchased</span>
              <span class="font-medium"><?= number_format($credits, 2) ?> GC</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="h-2 rounded-full bg-blue-500" 
                   style="width: <?= min(100, $credits / 20) ?>%"></div>
            </div>
          </div>
          
          <div class="pt-2">
            <div class="flex justify-between text-sm mb-1">
              <span class="font-medium">Credit Usage</span>
              <span class="font-medium"><?= number_format($usage_percentage, 1) ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
              <div class="h-2.5 rounded-full <?= $progress_color ?>" 
                   style="width: <?= $usage_percentage ?>%"></div>
            </div>
          </div>
        </div>
        
        <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center">
          <div class="text-sm text-gray-500">
            <i class="far fa-user mr-1"></i> <?= htmlspecialchars($factory['owner_name']) ?>
          </div>
          <div class="flex space-x-2">
            <button onclick="editFactory(<?= htmlspecialchars(json_encode($factory)) ?>)" 
                    class="p-2 text-gray-500 hover:text-green-600 transition-colors duration-200"
                    title="Edit">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteFactory(<?= $factory['id'] ?>, '<?= htmlspecialchars(addslashes($factory['name'])) ?>')" 
                    class="p-2 text-gray-500 hover:text-red-600 transition-colors duration-200"
                    title="Delete">
              <i class="fas fa-trash"></i>
            </button>
            <a href="factory_reports.php?id=<?= $factory['id'] ?>" 
               class="p-2 text-gray-500 hover:text-blue-600 transition-colors duration-200"
               title="View Reports">
              <i class="fas fa-chart-line"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
    
    <?php if ($result->num_rows === 0): ?>
    <div class="col-span-full text-center py-12">
      <div class="text-gray-400 mb-4">
        <i class="fas fa-industry text-5xl"></i>
      </div>
      <h3 class="text-lg font-medium text-gray-700">No factories found</h3>
      <p class="text-gray-500 mt-1">Try adjusting your search or add a new factory</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add/Edit Factory Modal -->
<div id="addFactoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-xl w-full max-w-md">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Add New Factory</h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>
      
      <form id="factoryForm" method="post" class="space-y-4">
        <input type="hidden" name="id" id="factoryId">
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
          <select name="user_id" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <option value="">Select owner</option>
            <?php
            $users = $conn->query("SELECT user_id, name, email FROM users ORDER BY name");
            while($user = $users->fetch_assoc()) {
              echo '<option value="'.$user['user_id'].'">'.htmlspecialchars($user['name']).' ('.htmlspecialchars($user['email']).')</option>';
            }
            ?>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Factory Name</label>
          <input type="text" name="name" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
          <input type="text" name="location" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Industry Type</label>
          <input type="text" name="production_type" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Carbon Emission (tons)</label>
            <input type="number" name="carbon_emission" step="0.01" min="0" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Credits Purchased</label>
            <input type="number" name="credits_purchased" step="0.01" min="0" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
        </div>
        
        <div class="pt-4 flex justify-end space-x-3">
          <button type="button" onclick="closeModal()" 
                  class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            Cancel
          </button>
          <button type="submit" name="create" id="submitButton"
                  class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            Add Factory
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-xl w-full max-w-md p-6">
    <div class="text-center">
      <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
        <i class="fas fa-exclamation text-red-600 text-xl"></i>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Factory</h3>
      <p class="text-gray-500 mb-6">Are you sure you want to delete <span id="factoryName" class="font-medium"></span>? This action cannot be undone.</p>
      
      <div class="flex justify-center space-x-3">
        <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" 
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
          Cancel
        </button>
        <form id="deleteForm" method="post" class="inline">
          <input type="hidden" name="id" id="deleteId">
          <button type="submit" name="delete" 
                  class="px-6 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            Delete
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle modals
function closeModal() {
  document.getElementById('addFactoryModal').classList.add('hidden');
  document.getElementById('deleteModal').classList.add('hidden');
  document.getElementById('factoryForm').reset();
  document.getElementById('factoryId').value = '';
  document.getElementById('submitButton').name = 'create';
  document.getElementById('modalTitle').textContent = 'Add New Factory';
  document.getElementById('submitButton').textContent = 'Add Factory';
}

// Edit factory
function editFactory(factory) {
  document.getElementById('modalTitle').textContent = 'Edit Factory';
  document.getElementById('submitButton').name = 'update';
  document.getElementById('submitButton').textContent = 'Update Factory';
  
  document.getElementById('factoryId').value = factory.id;
  document.querySelector('select[name="user_id"]').value = factory.user_id;
  document.querySelector('input[name="name"]').value = factory.name;
  document.querySelector('input[name="location"]').value = factory.location;
  document.querySelector('input[name="production_type"]').value = factory.production_type;
  document.querySelector('input[name="carbon_emission"]').value = factory.carbon_emission || '0';
  document.querySelector('input[name="credits_purchased"]').value = factory.credits_purchased || '0';
  
  document.getElementById('addFactoryModal').classList.remove('hidden');
}

// Delete factory confirmation
function deleteFactory(id, name) {
  document.getElementById('deleteId').value = id;
  document.getElementById('factoryName').textContent = '"' + name + '"';
  document.getElementById('deleteModal').classList.remove('hidden');
}

// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchInput');
  const locationFilter = document.getElementById('locationFilter');
  const industryFilter = document.getElementById('industryFilter');
  
  function applyFilters() {
    const params = new URLSearchParams();
    
    if (searchInput.value) params.set('search', searchInput.value);
    if (locationFilter.value) params.set('location', locationFilter.value);
    if (industryFilter.value) params.set('industry', industryFilter.value);
    
    window.location.href = 'factories.php?' + params.toString();
  }
  
  // Debounce search input
  let searchTimeout;
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
  });
  
  locationFilter.addEventListener('change', applyFilters);
  industryFilter.addEventListener('change', applyFilters);
  
  // Close modal on ESC key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeModal();
    }
  });
  
  // Close modal when clicking outside
  window.onclick = function(event) {
    const addModal = document.getElementById('addFactoryModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === addModal) {
      closeModal();
    } else if (event.target === deleteModal) {
      deleteModal.classList.add('hidden');
    }
  };
});
</script>

<style>
/* Custom styles to enhance the design */
body {
  background-color: #f9fafb;
  color: #1f2937;
}

/* Smooth transitions */
button, a, input, select, textarea {
  transition: all 0.2s ease-in-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #9ca3af;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}

/* Animation for cards */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.bg-white {
  animation: fadeIn 0.3s ease-out forwards;
}

/* Hover effect for cards */
.hover\:shadow-md {
  transition: box-shadow 0.2s ease-in-out, transform 0.2s ease-in-out;
}

.hover\:shadow-md:hover {
  transform: translateY(-2px);
}

/* Custom focus styles */
input:focus, select:focus, textarea:focus, button:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Custom button styles */
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Custom form styles */
input, select, textarea {
  background-color: #fff;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  line-height: 1.5rem;
}

input:focus, select:focus, textarea:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Custom modal styles */
.modal-enter {
  opacity: 0;
  transform: translateY(-20px);
}

.modal-enter-active {
  opacity: 1;
  transform: translateY(0);
  transition: opacity 200ms, transform 200ms;
}

.modal-exit {
  opacity: 1;
  transform: translateY(0);
}

.modal-exit-active {
  opacity: 0;
  transform: translateY(-20px);
  transition: opacity 200ms, transform 200ms;
}

/* Custom tooltip */
[data-tooltip] {
  position: relative;
  cursor: pointer;
}

[data-tooltip]:hover::after {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background-color: #374151;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  white-space: nowrap;
  z-index: 10;
  margin-bottom: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .container {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>

<?php require_once __DIR__ . '/footer.php'; ?>
