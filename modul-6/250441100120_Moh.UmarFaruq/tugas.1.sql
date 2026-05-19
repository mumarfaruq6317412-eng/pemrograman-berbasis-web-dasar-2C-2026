-- 1. Membuat Database (Opsional, sesuaikan dengan nama database Anda)
CREATE DATABASE IF NOT EXISTS manajemen_inventaris;
USE manajemen_inventaris;

-- 2. Membuat Tabel Users (Menampung Admin & User)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    PASSWORD VARCHAR(255) NOT NULL,
    ROLE ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

-- 3. Membuat Tabel Barang (Menampung Data Inventaris & Gambar)
CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(255) NOT NULL,
    deskripsi TEXT NULL,
    stok INT NOT NULL DEFAULT 0,
    harga DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    kategori VARCHAR(100) NOT NULL,
    gambar VARCHAR(255) DEFAULT 'default.png',
    is_deleted TINYINT(1) DEFAULT 0, -- Digunakan untuk fitur softDeleteBarang
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

-- 4. Membuat Tabel Riwayat (Menampung Log Transaksi Peminjaman)
CREATE TABLE IF NOT EXISTS riwayat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    barang_id INT NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    aksi ENUM('pinjam', 'kembali') NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;


INSERT INTO users (username, PASSWORD, ROLE) VALUES 
('admin_aplikasi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('user_biasa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
ON DUPLICATE KEY UPDATE username=username;

-- Data Dummy Awal untuk Tabel Barang
INSERT INTO barang (nama_barang, deskripsi, stok, harga, kategori, gambar) VALUES
('Laptop Asus ROG', 'Laptop gaming spesifikasi tinggi', 5, 15000000.00, 'Elektronik', 'default.png'),
('Kursi Kerja Hidrolik', 'Kursi nyaman untuk berlama-lama di depan komputer', 12, 750000.00, 'Perabotan', 'default.png'),
('Spidol Papan Tulis', 'Spidol warna hitam merek terkenal', 50, 12000.00, 'Alat Tulis', 'default.png');

