<?php
require_once __DIR__ . '/header.php';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_user'])) {
    $uid = (int)$_POST['user_id'];
    if ($uid > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success'] = 'User deleted successfully!';
        header('Location: user.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenCoin User Manage</title>
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
<body>
    <div class="user-management-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded" role="alert">
                <p><?= $_SESSION['success'] ?></p>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Page Header -->
    <div class="page-header mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">User Management</h1>
        <p class="text-gray-600">Manage all users, individuals, and NGOs in the system</p>
    </div>

    <!-- Search and Filter -->
    <div class="search-filters bg-white rounded-xl shadow-sm p-6 mb-8 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Search & Filter</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <!-- Search Input -->
            <div class="relative">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1.5">Search Users</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none mt-7">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        id="search"
                        placeholder="Name, email, or ID..." 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
                    >
                </div>
            </div>

            <!-- Role Filter -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1.5">User Role</label>
                <div class="relative">
                    <select 
                        id="role"
                        class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2.5 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
                    >
                        <option value="">All Roles</option>
                        <option value="individual">Individual</option>
                        <option value="ngo">NGO</option>
                        <option value="factory">Factory</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 mt-1">
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <div class="relative">
                    <select 
                        id="status"
                        class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2.5 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 mt-1">
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end space-x-3">
                <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="ri-filter-line mr-2"></i>
                    Apply Filters
                </button>
                <button class="text-gray-500 hover:text-gray-700 p-2 rounded-full hover:bg-gray-100 transition duration-200">
                    <i class="ri-refresh-line text-lg"></i>
                </button>
            </div>
        </div>
    </div>
    <!-- Users Grid -->
    <div class="users-grid">
        <?php
        $q = $conn->query("SELECT 
            u.user_id, 
            u.name, 
            u.email, 
            u.role,
            u.created_at,
            f.name AS factory_name
        FROM users u 
        LEFT JOIN factories f ON f.user_id = u.user_id
        WHERE u.role IN ('individual', 'ngo')
        ORDER BY u.user_id DESC");

        if ($q->num_rows === 0): ?>
            <div class="empty-state">
                <i class="ri-user-search-line"></i>
                <h3>No users found</h3>
                <p>There are no users to display at the moment.</p>
            </div>
        <?php else:
            while($user = $q->fetch_assoc()):
                $roleClasses = [
                    'individual' => 'role-user',
                    'ngo' => 'role-ngo'
                ];
                $roleIcon = [
                    'individual' => 'ri-user-line',
                    'ngo' => 'ri-team-line'
                ];
                $roleClass = $roleClasses[$user['role']] ?? '';
                $iconClass = $roleIcon[$user['role']] ?? 'ri-user-line';
        ?>
        <div class="user-card fade-in">
            <div class="user-card-header">
                <div class="user-avatar">
                    <i class="<?= $iconClass ?> text-lg"></i>
                </div>
                <div class="user-info">
                    <h3 class="user-name"><?= htmlspecialchars($user['name'] ?: 'Unnamed User') ?></h3>
                    <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <span class="role-badge <?= $roleClass ?>">
                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                </span>
            </div>

            <div class="user-card-body">
                <div class="user-detail">
                    <span class="user-detail-label">Member Since</span>
                    <span class="user-detail-value"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                </div>
                
                <?php if ($user['factory_name']): ?>
                <div class="user-detail">
                    <span class="user-detail-label">Factory</span>
                    <span class="user-detail-value"><?= htmlspecialchars($user['factory_name']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="user-card-footer">
                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-edit">
                    <i class="ri-edit-line mr-1"></i> Edit
                </a>
                <form method="post" onsubmit="return confirm('Are you sure you want to delete this user?');" class="inline">
                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                    <button type="submit" name="delete_user" class="btn btn-danger">
                        <i class="ri-delete-bin-line mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        <?php 
            endwhile;
        endif; 
        ?>
    </div>
</div>

  
<?php require_once __DIR__ . '/footer.php'; ?>


<!-- <h2>Users</h2>
<table>
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Factory</th><th>Actions</th></tr>
  <php
  $q = $conn->query("SELECT u.user_id, 
                            u.name, 
                            u.email, 
                            u.role, 
                            f.name AS factory_name
                     FROM users u 
                     LEFT JOIN factories f ON f.user_id=u.user_id
                     WHERE u.role IN ('individual','ngo')
                     ORDER BY u.user_id DESC");
  while($u=$q->fetch_assoc()):
  ?>
    <tr>
      <td><= $u['user_id'] ?></td>
      <td><= htmlspecialchars($u['name'] ?: $u['email']) ?></td>
      <td><= htmlspecialchars($u['email']) ?></td>
      <td><span class="muted"><= htmlspecialchars($u['role']) ?></span></td>
      <td><= htmlspecialchars($u['factory_name'] ?? '—') ?></td>
      <td>
        <form method="post" class="inline" 
              onsubmit="return confirm('Are you sure you want to delete user ID <= $u['user_id'] ?>?');">
          <input type="hidden" name="user_id" value="<= $u['user_id'] ?>">
          <button class="btn btn-danger" name="delete_user">Delete</button>
        </form>
      </td>
    </tr>
  <php endwhile; ?>
</table>

<?php require_once __DIR__ . '/footer.php'; ?> -->
