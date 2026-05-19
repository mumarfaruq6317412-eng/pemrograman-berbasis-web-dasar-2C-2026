<?php
include 'config.db.php';
requireLogin();

if (!isAdmin()) { header("Location: dashboard.php"); exit(); }

if (isset($_GET['permanent']) && $_GET['permanent'] == '1' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    if (permanentDeleteBarang($_GET['id'])) {
        header('Location: history.php?status=permanent_deleted');
        exit();
    } else {
        die('Gagal menghapus data secara permanen.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_history'])) {
    if ($conn->query("TRUNCATE TABLE barang_terhapus") === false) {
        die("Gagal mengosongkan riwayat: " . $conn->error);
    }
    header("Location: history.php?status=history_cleared");
    exit();
}

$result = $conn->query("SELECT * FROM barang_terhapus ORDER BY tanggal_hapus DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Hapus - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <?php if (isset($_GET['status']) && $_GET['status'] == 'permanent_deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Data telah dihapus secara permanen dari sistem.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'history_cleared'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Riwayat penghapusan telah dikosongkan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-trash3"></i> Riwayat Penghapusan Barang</h5>
                <div class="d-flex gap-2">
                    <form action="history.php" method="POST" class="d-inline" onsubmit="return confirm('Kosongkan semua riwayat penghapusan? Tindakan ini tidak dapat dibatalkan.');">
                        <input type="hidden" name="clear_history" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-light">Kosongkan Riwayat</button>
                    </form>
                    <a href="dashboard.php" class="btn btn-sm btn-light">Kembali ke Dashboard</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Waktu Dihapus</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        if ($result && $result->num_rows > 0): 
                            while($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_barang']) ?></strong></td>
                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                            <td class="text-muted"><?= date('d/m/Y H:i', strtotime($row['tanggal_hapus'])) ?></td>
                            <td class="text-center">
                                <a href="history.php?permanent=1&id=<?= $row['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm" 
                                   onclick="return confirm('Hapus permanen?')">Hapus Permanen</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada riwayat penghapusan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
