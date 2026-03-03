<?php
require_once 'koneksi.php';

// Ambil produk dari database
$stmt = $conn->query("SELECT * FROM produk ORDER BY created_at DESC");
$produk = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Online - Beranda</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #f97316;
            --dark: #1e293b;
            --light: #f8fafc;
            --white: #ffffff;
            --gray: #64748b;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f1f5f9;
            min-height: 100vh;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logo i {
            color: var(--secondary);
        }
        
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-link:hover {
            color: var(--secondary);
            transform: translateY(-2px);
        }
        
        .cart-badge {
            background: var(--secondary);
            color: var(--white);
            padding: 0.2rem 0.5rem;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            border-radius: 1rem;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            color: var(--white);
            text-align: center;
            box-shadow: var(--shadow-lg);
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .section-title {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .product-card {
            background: var(--white);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--gray);
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .stok-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(34, 197, 94, 0.9);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stok-badge.habis {
            background: rgba(239, 68, 68, 0.9);
        }
        
        .product-info {
            padding: 1.25rem;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .product-desc {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--white);
            width: 100%;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-primary:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: var(--secondary);
            color: var(--white);
        }
        
        .btn-secondary:hover {
            background: #ea580c;
        }
        
        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        /* Footer */
        .footer {
            background: var(--dark);
            color: var(--white);
            padding: 2rem;
            text-align: center;
            margin-top: 3rem;
        }
        
        .footer p {
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 1.75rem;
            }
            
            .nav-menu {
                gap: 1rem;
            }
            
            .navbar-container {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="logo">
                <i class="fas fa-shopping-bag"></i>
                TokoOnline
            </a>
            <div class="nav-menu">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="nav-link">
                            <i class="fas fa-cog"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="keranjang.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Keranjang
                        <?php 
                        $cartCount = 0;
                        if (isLoggedIn()) {
                            $stmt = $conn->prepare("SELECT SUM(jumlah) as total FROM keranjang WHERE user_id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $result = $stmt->fetch();
                            $cartCount = $result['total'] ?? 0;
                        }
                        ?>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="transaksi.php" class="nav-link">
                        <i class="fas fa-receipt"></i> Transaksi
                    </a>
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="nav-link">
                        <i class="fas fa-user-plus"></i> Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <div class="hero">
            <h1>Selamat Datang di TokoOnline</h1>
            <p>Temukan produk terbaik dengan harga terjangkau</p>
        </div>

        <h2 class="section-title">
            <i class="fas fa-box-open"></i> Produk Terbaru
        </h2>

        <div class="product-grid">
            <?php foreach ($produk as $p): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($p['gambar']) && file_exists('img/' . $p['gambar'])): ?>
                            <img src="img/<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                        <?php else: ?>
                        <?php 
                        $icon = 'fa-box';
                        if (stripos($p['nama_produk'], 'sepatu') !== false) $icon = 'fa-shoe-prints';
                        elseif (stripos($p['nama_produk'], 'tas') !== false) $icon = 'fa-briefcase';
                        elseif (stripos($p['nama_produk'], 'jam') !== false) $icon = 'fa-clock';
                        elseif (stripos($p['nama_produk'], 'kamera') !== false) $icon = 'fa-camera';
                        elseif (stripos($p['nama_produk'], 'headphone') !== false) $icon = 'fa-headphones';
                        elseif (stripos($p['nama_produk'], 'laptop') !== false) $icon = 'fa-laptop';
                        elseif (stripos($p['nama_produk'], 'keyboard') !== false) $icon = 'fa-keyboard';
                        elseif (stripos($p['nama_produk'], 'mouse') !== false) $icon = 'fa-mouse';
                        ?>
                        <i class="fas <?= $icon ?>"></i>
                        <?php endif; ?>
                        <span class="stok-badge <?= $p['stok'] <= 0 ? 'habis' : '' ?>">
                            <?= $p['stok'] > 0 ? 'Stok: ' . $p['stok'] : 'Habis' ?>
                        </span>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($p['nama_produk']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars($p['deskripsi']) ?></p>
                        <div class="product-price"><?= formatRupiah($p['harga']) ?></div>
                        <?php if (isLoggedIn()): ?>
                            <?php if ($p['stok'] > 0): ?>
                                <a href="tambah_keranjang.php?id=<?= $p['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                </a>
                            <?php else: ?>
                                <button class="btn btn-primary" disabled>
                                    <i class="fas fa-times"></i> Stok Habis
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login untuk Beli
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($produk)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--gray);">
                    <i class="fas fa-box-open" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                    <p>Belum ada produk tersedia</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 TokoOnline. All rights reserved.</p>
    </footer>
</body>
</html>

