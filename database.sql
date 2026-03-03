-- Database: toko_online
CREATE DATABASE IF NOT EXISTS toko_online;
USE toko_online;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel produk
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel keranjang
CREATE TABLE IF NOT EXISTS keranjang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
);

-- Tabel transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'selesai', 'batal') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel detail transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_saat_ini DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
);

-- Insert data user admin dan user biasa
INSERT INTO users (nama, email, password, role) VALUES 
('Administrator', 'admin@toko.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Pengguna Biasa', 'user@toko.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert data produk contoh
INSERT INTO produk (nama_produk, deskripsi, harga, stok, gambar) VALUES 
('Sepatu Sneakers', 'Sepatu sneakers nyaman untuk harian', 150000, 50, 'sepatu.jpg'),
('Tas Ransel', 'Tas ransel berkualitas tinggi', 200000, 30, 'tas.jpg'),
('Jam Tangan', 'Jam tangan analog elegan', 350000, 25, 'jam.jpg'),
('Kamera Digital', 'Kamera dengan kualitas HD', 2500000, 15, 'kamera.jpg'),
('Headphone', 'Headphone dengan bass yang kuat', 180000, 40, 'headphone.jpg'),
('Laptop', 'Laptop untuk kerja dan gaming', 5500000, 10, 'laptop.jpg'),
('Keyboard Mechanical', 'Keyboard gaming mekanikal', 450000, 20, 'keyboard.jpg'),
('Mouse Wireless', 'Mouse wireless ergonomis', 120000, 35, 'mouse.jpg');

