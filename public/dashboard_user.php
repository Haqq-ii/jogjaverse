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
  <title>Dashboard User - JogjaVerse</title>
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f7fa;
    }
    
    .navbar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .navbar h1 {
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .navbar .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .badge {
        background: rgba(255,255,255,0.2);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .logout-btn {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 8px 20px;
        border: 2px solid white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-block;
    }
    
    .logout-btn:hover {
        background: white;
        color: #667eea;
    }
    
    .container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .welcome-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        animation: slideUp 0.5s ease;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .welcome-card h2 {
        color: #333;
        margin-bottom: 10px;
        font-size: 28px;
    }
    
    .welcome-card p {
        color: #666;
        line-height: 1.6;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }
    
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #667eea;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
    }
    
    .info-card h3 {
        color: #667eea;
        margin-bottom: 10px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    .info-card p {
        color: #333;
        font-size: 18px;
        font-weight: 600;
        word-break: break-word;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-aktif {
        background: #51cf66;
        color: white;
    }
    
    .status-nonaktif {
        background: #ff6b6b;
        color: white;
    }
    
    .role-badge {
        display: inline-block;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .role-admin {
        background: #ff6b6b;
        color: white;
    }
    
    .role-user {
        background: #4ecdc4;
        color: white;
    }
    
    @media (max-width: 768px) {
        .navbar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        
        .navbar .user-info {
            flex-direction: column;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
  </style>
</head>
<body>

<div class="navbar">
    <h1>🏛️ JogjaVerse</h1>
    <div class="user-info">
        <span>Halo, <strong><?= htmlspecialchars($user['nama_lengkap']) ?></strong></span>
        <span class="badge"><?= strtoupper($user['peran']) ?></span>
        <a href="<?= BASE_URL ?>/public/logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="container">
    <div class="welcome-card">
        <h2>Selamat Datang! 👋</h2>
        <p>Selamat datang di dashboard JogjaVerse, <strong><?= htmlspecialchars($user['nama_lengkap']) ?></strong>. Ini adalah halaman utama Anda dimana Anda dapat melihat informasi akun dan mengakses berbagai fitur yang tersedia.</p>
    </div>
    
    <div class="info-grid">
        <div class="info-card">
            <h3>Nama Lengkap</h3>
            <p><?= htmlspecialchars($user['nama_lengkap']) ?></p>
        </div>
        
        <div class="info-card">
            <h3>Username</h3>
            <p><?= htmlspecialchars($user['username']) ?></p>
        </div>
        
        <div class="info-card">
            <h3>Email</h3>
            <p><?= htmlspecialchars($user['email']) ?></p>
        </div>
        
        <div class="info-card">
            <h3>Nomor HP</h3>
            <p><?= $user['nomor_hp'] ? htmlspecialchars($user['nomor_hp']) : '-' ?></p>
        </div>
        
        <div class="info-card">
            <h3>Role</h3>
            <p>
                <span class="role-badge <?= $user['peran'] === 'admin' ? 'role-admin' : 'role-user' ?>">
                    <?= strtoupper($user['peran']) ?>
                </span>
            </p>
        </div>
        
        <div class="info-card">
            <h3>Status Akun</h3>
            <p>
                <span class="status-badge <?= $user['status_aktif'] ? 'status-aktif' : 'status-nonaktif' ?>">
                    <?= $user['status_aktif'] ? 'AKTIF' : 'NONAKTIF' ?>
                </span>
            </p>
        </div>
        
        <div class="info-card">
            <h3>Terdaftar Sejak</h3>
            <p><?= date('d M Y, H:i', strtotime($user['dibuat_pada'])) ?></p>
        </div>
        
        <div class="info-card">
            <h3>Login Terakhir</h3>
            <p><?= date('d M Y, H:i', strtotime($user['diubah_pada'])) ?></p>
        </div>
    </div>
</div>

</body>
</html>