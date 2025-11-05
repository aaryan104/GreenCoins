<?php
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/_admin_auth.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin · GreenCoin</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin-styles.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/admin/css/user-management.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/admin/css/proofs.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/admin/css/pollution.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/admin/css/credits-report.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/admin/css/sell-requests.css">
  <style>
    /* Base Styles */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
      background-color: #f8fafc;
      color: #1e293b;
      line-height: 1.5;
    }
    
    /* Header & Navigation */
    header {
      background: white;
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 50;
    }
    
    .nav-container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 1rem;
    }
    
    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 4rem;
    }
    
    .logo {
      display: flex;
      align-items: center;
      font-weight: 700;
      font-size: 1.25rem;
      color: #0f172a;
      text-decoration: none;
    }
    
    .logo img {
      height: 2rem;
      margin-right: 0.75rem;
    }
    
    .nav-links {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }
    
    .nav-links a {
      color: #475569;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9375rem;
      padding: 0.5rem 1rem;
      border-radius: 0.375rem;
      transition: all 0.2s;
      white-space: nowrap;
    }
    
    .nav-links a:hover, 
    .nav-links a.active {
      background-color: #f1f5f9;
      color: #0f172a;
    }
    
    .nav-links a.active {
      color: #0ea5e9;
      font-weight: 600;
    }
    
    .user-menu {
      display: flex;
      align-items: center;
      margin-left: 1rem;
      position: relative;
    }
    
    .user-btn {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background: none;
      border: none;
      padding: 0.375rem 0.75rem;
      border-radius: 0.375rem;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .user-btn:hover {
      background-color: #f1f5f9;
    }
    
    .user-avatar {
      width: 2rem;
      height: 2rem;
      border-radius: 9999px;
      background-color: #e0f2fe;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #0369a1;
      font-weight: 600;
      font-size: 0.875rem;
    }
    
    .mobile-menu-btn {
      display: none;
      background: none;
      border: none;
      padding: 0.5rem;
      border-radius: 0.375rem;
      cursor: pointer;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
      .nav-links {
        display: none;
      }
      
      .mobile-menu-btn {
        display: block;
      }
      
      .mobile-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        padding: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border-top: 1px solid #e2e8f0;
        display: none;
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .mobile-menu.active {
        display: flex;
      }
      
      .mobile-menu a {
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
        color: #475569;
        text-decoration: none;
        transition: background-color 0.2s;
      }
      
      .mobile-menu a:hover,
      .mobile-menu a.active {
        background-color: #f1f5f9;
        color: #0f172a;
      }
    }
  </style>
</head>
<body>
<header>
  <div class="nav-container">
    <nav>
      <a href="<?= BASE_URL ?>" class="logo">
        <span>GreenCoin</span>
      </a>
      <div class="nav-links">
        <a href="<?= BASE_URL ?>/admin/admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>">
          Dashboard
        </a>
        <a href="<?= BASE_URL ?>/admin/user.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : '' ?>">
          Users
        </a>
        <a href="<?= BASE_URL ?>/admin/factories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'factories.php' ? 'active' : '' ?>">
          Factories
        </a>
        <a href="<?= BASE_URL ?>/admin/proofs.php" class="<?= basename($_SERVER['PHP_SELF']) == 'proofs.php' ? 'active' : '' ?>">
          Proofs
        </a>
        <a href="<?= BASE_URL ?>/admin/pollution.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pollution.php' ? 'active' : '' ?>">
          Pollution
        </a>
        <a href="<?= BASE_URL ?>/admin/credits_report.php" class="<?= basename($_SERVER['PHP_SELF']) == 'credits_report.php' ? 'active' : '' ?>">
          Credits
        </a>
        <a href="<?= BASE_URL ?>/admin/manage_sell_requests.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_sell_requests.php' ? 'active' : '' ?>">
          Sell Requests
        </a>
        <a href="<?= BASE_URL ?>/admin/manage_factory_requests.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_sell_requests.php' ? 'active' : '' ?>">
          Buy Requests
        </a>
      </div>
      <div class="flex items-center gap-4">
        <div class="user-menu">
          <button class="user-btn">
            <div class="user-avatar">
              <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
            </div>
            <span class="hidden md:inline"><?= $_SESSION['user_name'] ?? 'Admin' ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div class="user-dropdown hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
            <a href="<?= BASE_URL ?>/auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              Sign out
            </a>
          </div>
        </div>
        
        <button class="mobile-menu-btn md:hidden" id="mobileMenuBtn">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
          </svg>
        </button>
      </div>
    </nav>

<script>
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  const userMenuBtn = document.querySelector('.user-menu .user-btn');
  const userDropdown = document.querySelector('.user-dropdown');

  if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }

  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener('click', () => {
      userDropdown.classList.toggle('hidden');
    });
  }

  document.addEventListener('click', (e) => {
    if (userDropdown && !userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
      userDropdown.classList.add('hidden');
    }
  });
</script>

<div class="wrap">
