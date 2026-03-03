<?php
require_once 'koneksi.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Ambil data transaksi
$stmt = $conn->prepare("
    SELECT t.*, u.nama as nama_user
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$transaksi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - TokoOnline</title>
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
        }
        
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-lg);
        }
        
        .page-header i {
            font-size: 2.5rem;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
        }
        
        .page-header p {
            opacity: 0.9;
        }
        
        .transactions-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .transaction-card {
            background: var(--white);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .transaction-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .transaction-header {
            background: var(--light);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .transaction-id {
            font-weight: 600;
            color: var(--dark);
        }
        
        .transaction-date {
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-selesai {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-batal {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .transaction-body {
            padding: 1.5rem;
        }
        
        .transaction-items {
            margin-bottom: 1rem;
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            color: var(--dark);
        }
        
        .item-qty {
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        .item-price {
            font-weight: 600;
            color: var(--primary);
        }
        
        .transaction-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 2px solid #e2e8f0;
            margin-top: 1rem;
        }
        
        .total-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .total-amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
            background: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }
        
        .empty-state i {
            font-size: 5rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }
        
        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: var(--dark);
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
            width: auto;
            margin-top: 1rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
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
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <a href="keranjang.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                </a>
                <?php if (isAdmin()): ?>
                    <a href="admin.php" class="nav-link">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
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

        <div class="page-header">
            <i class="fas fa-receipt"></i>
            <div>
                <h1>Riwayat Transaksi</h1>
                <p>Lihat semua transaksi Anda</p>
            </div>
        </div>

        <?php if (empty($transaksi)): ?>
            <div class="empty-state">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3>Belum Ada Transaksi</h3>
                <p>Anda belum melakukan transaksi apapun</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-store"></i> Belanja Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="transactions-list">
                <?php foreach ($transaksi as $t): ?>
                    <div class="transaction-card">
                        <div class="transaction-header">
                            <div>
                                <span class="transaction-id">#TRX-<?= str_pad($t['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                <span class="transaction-date" style="margin-left: 1rem;">
                                    <i class="fas fa-calendar"></i> <?= date('d M Y H:i', strtotime($t['created_at'])) ?>
                                </span>
                            </div>
                            <span class="status-badge status-<?= $t['status'] ?>">
                                <?= ucfirst($t['status']) ?>
                            </span>
                        </div>
                        <div class="transaction-body">
                            <?php 
                            // Ambil detail transaksi
                            $stmt = $conn->prepare("
                                SELECT dt.*, p.nama_produk
                                FROM detail_transaksi dt
                                JOIN produk p ON dt.produk_id = p.id
                                WHERE dt.transaksi_id = ?
                            ");
                            $stmt->execute([$t['id']]);
                            $details = $stmt->fetchAll();
                            ?>
                            <div class="transaction-items">
                                <?php foreach ($details as $d): ?>
                                    <div class="transaction-item">
                                        <div>
                                            <div class="item-name"><?= htmlspecialchars($d['nama_produk']) ?></div>
                                            <div class="item-qty"><?= $d['jumlah'] ?> x <?= formatRupiah($d['harga_saat_ini']) ?></div>
                                        </div>
                                        <div class="item-price">
                                            <?= formatRupiah($d['jumlah'] * $d['harga_saat_ini']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="transaction-footer">
                                <span class="total-label">Total Pembayaran</span>
                                <span class="total-amount"><?= formatRupiah($t['total_harga']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

