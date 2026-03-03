<?php
require_once 'koneksi.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

// Handle Create/Update Produk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $nama = $_POST['nama_produk'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        
        $stmt = $conn->prepare("INSERT INTO produk (nama_produk, deskripsi, harga, stok) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $deskripsi, $harga, $stok]);
        
        redirect('admin.php?success=Produk%20berhasil%20ditambahkan');
    }
    
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama_produk'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        
        $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, deskripsi = ?, harga = ?, stok = ? WHERE id = ?");
        $stmt->execute([$nama, $deskripsi, $harga, $stok, $id]);
        
        redirect('admin.php?success=Produk%20berhasil%20diupdate');
    }
}

// Handle Delete Produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->execute([$id]);
    redirect('admin.php?success=Produk%20berhasil%20dihapus');
}

// Ambil semua produk
$stmt = $conn->query("SELECT * FROM produk ORDER BY created_at DESC");
$produk = $stmt->fetchAll();

// Ambil semua transaksi
$stmt = $conn->query("
    SELECT t.*, u.nama as nama_user 
    FROM transaksi t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC
    LIMIT 20
");
$transaksi = $stmt->fetchAll();

// Ambil semua user
$stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TokoOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #f97316;
            --success: #22c55e;
            --danger: #ef4444;
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
            background: linear-gradient(135deg, var(--dark) 0%, #0f172a 100%);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }
        
        .navbar-container {
            max-width: 1400px;
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--dark) 0%, #1e293b 100%);
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
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            background: var(--white);
            padding: 0.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }
        
        .tab-btn {
            flex: 1;
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            color: var(--gray);
            font-weight: 600;
            cursor: pointer;
            border-radius: 0.5rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .tab-btn.active {
            background: var(--primary);
            color: var(--white);
        }
        
        .tab-btn:hover:not(.active) {
            background: var(--light);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Cards */
        .card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-title i {
            color: var(--primary);
        }
        
        /* Form */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
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
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-success {
            background: var(--success);
            color: var(--white);
        }
        
        .btn-success:hover {
            background: #16a34a;
        }
        
        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        /* Table */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background: var(--light);
            font-weight: 600;
            color: var(--dark);
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        tr:hover {
            background: #f8fafc;
        }
        
        .stok-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stok-tersedia {
            background: #dcfce7;
            color: #166534;
        }
        
        .stok-habis {
            background: #fee2e2;
            color: #991b1b;
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
        
        .action-btns {
            display: flex;
            gap: 0.5rem;
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
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.blue {
            background: #dbeafe;
            color: var(--primary);
        }
        
        .stat-icon.green {
            background: #dcfce7;
            color: var(--success);
        }
        
        .stat-icon.orange {
            background: #fed7aa;
            color: var(--secondary);
        }
        
        .stat-icon.purple {
            background: #f3e8ff;
            color: #9333ea;
        }
        
        .stat-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stat-info p {
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="logo">
                <i class="fas fa-cog"></i>
                Admin Panel
            </a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <a href="keranjang.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                </a>
                <a href="transaksi.php" class="nav-link">
                    <i class="fas fa-receipt"></i> Transaksi
                </a>
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
            <i class="fas fa-tachometer-alt"></i>
            <div>
                <h1>Dashboard Admin</h1>
                <p>Kelola produk, transaksi, dan pengguna</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <?php
            $totalProduk = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch()['total'];
            $totalTransaksi = $conn->query("SELECT COUNT(*) as total FROM transaksi WHERE status = 'selesai'")->fetch()['total'];
            $totalUser = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'")->fetch()['total'];
            $totalPendapatan = $conn->query("SELECT COALESCE(SUM(total_harga), 0) as total FROM transaksi WHERE status = 'selesai'")->fetch()['total'];
            ?>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalProduk ?></h3>
                    <p>Total Produk</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalTransaksi ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalUser ?></h3>
                    <p>Total User</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3><?= formatRupiah($totalPendapatan) ?></h3>
                    <p>Total Pendapatan</p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('produk')">
                <i class="fas fa-box"></i> Produk
            </button>
            <button class="tab-btn" onclick="showTab('transaksi')">
                <i class="fas fa-shopping-cart"></i> Transaksi
            </button>
            <button class="tab-btn" onclick="showTab('pengguna')">
                <i class="fas fa-users"></i> Pengguna
            </button>
        </div>

        <!-- Tab Produk -->
        <div id="produk" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i> Tambah Produk Baru
                    </h3>
                </div>
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" name="nama_produk" placeholder="Masukkan nama produk" required>
                        </div>
                        <div class="form-group">
                            <label>Harga (Rp)</label>
                            <input type="number" name="harga" placeholder="Masukkan harga" required min="0">
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" placeholder="Jumlah stok" required min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" rows="3" placeholder="Masukkan deskripsi produk"></textarea>
                    </div>
                    <button type="submit" name="tambah" value="1" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Daftar Produk
                    </h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Produk</th>
                                <th>Deskripsi</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produk as $index => $p): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                                    <td><?= htmlspecialchars($p['deskripsi'] ?? '-') ?></td>
                                    <td><?= formatRupiah($p['harga']) ?></td>
                                    <td>
                                        <span class="stok-badge <?= $p['stok'] > 0 ? 'stok-tersedia' : 'stok-habis' ?>">
                                            <?= $p['stok'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <button class="btn btn-primary btn-sm" onclick="editProduk(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_produk']) ?>', '<?= htmlspecialchars($p['deskripsi'] ?? '') ?>', <?= $p['harga'] ?>, <?= $p['stok'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?hapus=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus produk ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($produk)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--gray);">
                                        <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                        <p>Belum ada produk</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Transaksi -->
        <div id="transaksi" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Riwayat Transaksi
                    </h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transaksi as $t): ?>
                                <tr>
                                    <td>#TRX-<?= str_pad($t['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars($t['nama_user']) ?></td>
                                    <td><?= formatRupiah($t['total_harga']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $t['status'] ?>">
                                            <?= ucfirst($t['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($t['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($transaksi)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--gray);">
                                        <p>Belum ada transaksi</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Pengguna -->
        <div id="pengguna" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Daftar Pengguna
                    </h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Terdaftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $u): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($u['nama']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $u['role'] == 'admin' ? 'status-selesai' : 'status-pending' ?>">
                                            <?= ucfirst($u['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Produk</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" id="edit_harga" required min="0">
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" id="edit_stok" required min="0">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                </div>
                <button type="submit" name="update" value="1" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function editProduk(id, nama, deskripsi, harga, stok) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('edit_stok').value = stok;
            document.getElementById('editModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>

