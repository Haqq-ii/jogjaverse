<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

// Ambil data user dari database
$user_id = $_SESSION['id_pengguna'];
$stmt = $koneksi->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - JogjaVerse</title>
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: #F7F5F2;
        color: #333;
    }
    
    /* Header */
    .header {
        background: #4D2832;
        color: white;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    
    .header-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: white;
        font-size: 22px;
        font-weight: 700;
    }
    
    .logo span {
        color: #E88324;
    }
    
    .header-nav {
        display: flex;
        align-items: center;
        gap: 30px;
    }
    
    .header-nav a {
        color: white;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s;
    }
    
    .header-nav a:hover {
        color: #E88324;
    }
    
    .user-menu {
        position: relative;
    }
    
    .user-button {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255,255,255,0.1);
        padding: 8px 15px;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s;
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .user-button:hover {
        background: rgba(255,255,255,0.2);
    }
    
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #E88324;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    
    /* Container */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    
    /* Breadcrumb */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .breadcrumb a {
        color: #6F2232;
        text-decoration: none;
    }
    
    .breadcrumb a:hover {
        text-decoration: underline;
    }
    
    /* Main Layout */
    .main-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 30px;
        align-items: start;
    }
    
    /* Sidebar */
    .sidebar {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        position: sticky;
        top: 90px;
    }
    
    .sidebar-header {
        padding: 25px 20px;
        background: linear-gradient(135deg, #6F2232 0%, #4D2832 100%);
        color: white;
        text-align: center;
    }
    
    .sidebar-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #E88324;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        border: 3px solid rgba(255,255,255,0.3);
    }
    
    .sidebar-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .sidebar-email {
        font-size: 13px;
        opacity: 0.9;
    }
    
    .sidebar-menu {
        list-style: none;
    }
    
    .sidebar-menu li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
        border-left: 3px solid transparent;
    }
    
    .sidebar-menu li a:hover,
    .sidebar-menu li a.active {
        background: #F7F5F2;
        color: #6F2232;
        border-left-color: #E88324;
    }
    
    .sidebar-menu li a svg {
        width: 20px;
        height: 20px;
    }
    
    /* Content Area */
    .content-area {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        min-height: 500px;
    }
    
    .content-header {
        border-bottom: 2px solid #F7F5F2;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }
    
    .content-header h1 {
        font-size: 28px;
        color: #4D2832;
        margin-bottom: 8px;
    }
    
    .content-header p {
        color: #666;
        font-size: 14px;
    }
    
    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #F7F5F2 0%, #EDE9E4 100%);
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #E88324;
    }
    
    .stat-card-icon {
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        color: #6F2232;
    }
    
    .stat-card-value {
        font-size: 24px;
        font-weight: 700;
        color: #4D2832;
        margin-bottom: 5px;
    }
    
    .stat-card-label {
        font-size: 13px;
        color: #666;
    }
    
    /* Info Grid */
    .info-section {
        margin-bottom: 30px;
    }
    
    .info-section h2 {
        font-size: 18px;
        color: #4D2832;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .info-item {
        padding: 15px;
        background: #F7F5F2;
        border-radius: 8px;
    }
    
    .info-item-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .info-item-value {
        font-size: 15px;
        color: #333;
        font-weight: 500;
    }
    
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .badge-success {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-danger {
        background: #f8d7da;
        color: #721c24;
    }
    
    .badge-primary {
        background: #e8d5d5;
        color: #6F2232;
    }
    
    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 30px;
    }
    
    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #6F2232 0%, #4D2832 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(111, 34, 50, 0.3);
    }
    
    .btn-secondary {
        background: white;
        color: #6F2232;
        border: 2px solid #EDE9E4;
    }
    
    .btn-secondary:hover {
        background: #F7F5F2;
        border-color: #E88324;
    }
    
    .btn-logout {
        background: white;
        color: #dc3545;
        border: 2px solid #dc3545;
    }
    
    .btn-logout:hover {
        background: #dc3545;
        color: white;
    }
    
    /* Quick Links */
    .quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .quick-link {
        padding: 20px;
        background: #F7F5F2;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .quick-link:hover {
        background: white;
        border-color: #E88324;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(232, 131, 36, 0.2);
    }
    
    .quick-link-icon {
        font-size: 32px;
        margin-bottom: 10px;
    }
    
    .quick-link-text {
        font-size: 13px;
        font-weight: 600;
    }
    
    /* Responsive */
    @media (max-width: 968px) {
        .main-layout {
            grid-template-columns: 1fr;
        }
        
        .sidebar {
            position: static;
        }
        
        .header-nav {
            display: none;
        }
    }
    
    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
  </style>
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="header-content">
        <a href="<?= BASE_URL ?>/public/user/php/landingpageclean.php" class="logo">
            Jogja<span>Verse</span>
        </a>
        <nav class="header-nav">
            <a href="<?= BASE_URL ?>/public/user/php/landingpageclean.php">Beranda</a>
            <a href="#">Destinasi</a>
            <a href="#">Event</a>
            <a href="#">Kuliner</a>
        </nav>
        <div class="user-menu">
            <div class="user-button">
                <div class="user-avatar"><?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?></div>
                <span><?= htmlspecialchars(explode(' ', $user['nama_lengkap'])[0]) ?></span>
            </div>
        </div>
    </div>
</header>

<!-- Container -->
<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/public/user/php/landingpageclean.php">Home</a>
        <span>›</span>
        <span>My Account</span>
    </div>
    
    <!-- Main Layout -->
    <div class="main-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-avatar"><?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?></div>
                <div class="sidebar-name"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
                <div class="sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="active">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Account Overview
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        My Bookings
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        Favorites
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                        </svg>
                        Profile Settings
                    </a>
                </li>
                <li>
                    <a href="#">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        Settings
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Content -->
        <main class="content-area">
            <div class="content-header">
                <h1>Account Overview</h1>
                <p>Manage your personal information and preferences</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon">📅</div>
                    <div class="stat-card-value">0</div>
                    <div class="stat-card-label">Upcoming Trips</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon">✓</div>
                    <div class="stat-card-value">0</div>
                    <div class="stat-card-label">Completed Trips</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon">❤️</div>
                    <div class="stat-card-value">0</div>
                    <div class="stat-card-label">Saved Places</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon">⭐</div>
                    <div class="stat-card-value">0</div>
                    <div class="stat-card-label">Reviews Written</div>
                </div>
            </div>
            
            <!-- Personal Information -->
            <div class="info-section">
                <h2>
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Personal Information
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-item-label">Full Name</div>
                        <div class="info-item-value"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">Username</div>
                        <div class="info-item-value"><?= htmlspecialchars($user['username']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">Email Address</div>
                        <div class="info-item-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">Phone Number</div>
                        <div class="info-item-value"><?= $user['nomor_hp'] ? htmlspecialchars($user['nomor_hp']) : 'Not set' ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Account Details -->
            <div class="info-section">
                <h2>
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    Account Details
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-item-label">Account Type</div>
                        <div class="info-item-value">
                            <span class="badge badge-primary"><?= strtoupper($user['peran']) ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">Account Status</div>
                        <div class="info-item-value">
                            <span class="badge <?= $user['status_aktif'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $user['status_aktif'] ? '✓ Active' : '✗ Inactive' ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">Member Since</div>
                        <div class="info-item-value"><?= date('d M Y', strtotime($user['dibuat_pada'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">Last Login</div>
                        <div class="info-item-value"><?= date('d M Y, H:i', strtotime($user['diubah_pada'])) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="info-section">
                <h2>Quick Actions</h2>
                <div class="quick-links">
                    <a href="#" class="quick-link">
                        <div class="quick-link-icon">🏛️</div>
                        <div class="quick-link-text">Browse Destinations</div>
                    </a>
                    <a href="#" class="quick-link">
                        <div class="quick-link-icon">📅</div>
                        <div class="quick-link-text">View Events</div>
                    </a>
                    <a href="#" class="quick-link">
                        <div class="quick-link-icon">🍜</div>
                        <div class="quick-link-text">Find Culinary</div>
                    </a>
                    <a href="#" class="quick-link">
                        <div class="quick-link-icon">🗺️</div>
                        <div class="quick-link-text">Virtual Tour</div>
                    </a>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="#" class="btn btn-primary">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                    </svg>
                    Edit Profile
                </a>
                <a href="#" class="btn btn-secondary">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Change Password
                </a>
                <a href="<?= BASE_URL ?>/public/logout.php" class="btn btn-logout">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                    </svg>
                    Logout
                </a>
            </div>
        </main>
    </div>
</div>

</body>
</html>