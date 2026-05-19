<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.db.php';

if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}

if (!function_exists('logout')) {
    function logout() {
        session_unset();
        session_destroy();
        header("Location: dashboard.php");
        exit();
    }
}
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
if (!function_exists('softDeleteBarang')) {
    function softDeleteBarang($id) {
        global $conn;
        $stmt = $conn->prepare("UPDATE barang SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    logout();
}

$error = '';
$success = '';
$mode = isset($_GET['action']) && $_GET['action'] === 'register' ? 'register' : 'login';
$isAppMode = isset($_SESSION['user_id']);

if (!$isAppMode) {

    if (($_SERVER["REQUEST_METHOD"] ?? '') === 'POST' && isset($_POST['form_action'])) {
        if ($_POST['form_action'] === 'login') {
            $user = $_POST['username'];
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result ? $result->fetch_assoc() : null;
            } else {
                $data = null;
            }

            if ($data && password_verify($_POST['password'], $data['password'])) {
                $_SESSION['user_id'] = $data['id'];
                $_SESSION['role'] = $data['role'];
                $_SESSION['username'] = $data['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Username atau password salah.";
            }
        }

        if ($_POST['form_action'] === 'register') {
            $user = trim($_POST['username']);
            $password = $_POST['password'];
            $role = $_POST['role'];

            if ($user === '' || $password === '' || ($role !== 'user' && $role !== 'admin')) {
                $error = 'Isi semua kolom dengan benar.';
                $mode = 'register';
            } else {
                $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
                if ($check) {
                    $check->bind_param("s", $user);
                    $check->execute();
                    $checkResult = $check->get_result();
                } else {
                    $checkResult = false;
                }

                if ($checkResult && $checkResult->num_rows > 0) {
                    $error = 'Username sudah digunakan.';
                    $mode = 'register';
                } else {
                    $passHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("sss", $user, $passHash, $role);
                        if ($stmt->execute()) {
                            header("Location: dashboard.php?status=registered");
                            exit();
                        } else {
                            $error = 'Gagal mendaftar. Silakan coba lagi.';
                            $mode = 'register';
                        }
                    } else {
                        $error = 'Gagal mendaftar. Silakan coba lagi.';
                        $mode = 'register';
                    }
                }
            }
        }
    }

    if (isset($_GET['status']) && $_GET['status'] === 'registered') {
        $success = 'Pendaftaran berhasil. Silakan login.';
        $mode = 'login';
    }
} else {

    if (isset($_GET['action_user']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $barang_id = (int)$_GET['id'];
        $action = $_GET['action_user'];
        
        $nmStmt = $conn->prepare("SELECT nama_barang FROM barang WHERE id = ?");
        if ($nmStmt) {
            $nmStmt->bind_param("i", $barang_id);
            $nmStmt->execute();
            $resBarang = $nmStmt->get_result();
            $resBarang = $resBarang ? $resBarang->fetch_assoc() : null;
        } else {
            $resBarang = null;
        }
        $nama_barang = $resBarang ? $resBarang['nama_barang'] : 'Barang tidak diketahui';

        if ($action === 'pinjam') {
            $stmt = $conn->prepare("UPDATE barang SET stok = stok - 1 WHERE id = ? AND stok > 0");
            if ($stmt) {
                $stmt->bind_param("i", $barang_id);
                if ($stmt->execute() && $conn->affected_rows > 0) {
                    $log = $conn->prepare("INSERT INTO riwayat (user_id, username, barang_id, nama_barang, aksi) VALUES (?, ?, ?, ?, 'pinjam')");
                    if ($log) {
                        $log->bind_param("isis", $_SESSION['user_id'], $_SESSION['username'], $barang_id, $nama_barang);
                        $log->execute();
                    }
                    header('Location: dashboard.php?status=success_pinjam');
                } else {
                    header('Location: dashboard.php?status=failed_stok');
                }
            } else {
                header('Location: dashboard.php?status=failed_stok');
            }
            exit();
        }
        
        if ($action === 'kembali') {
            $stmt = $conn->prepare("UPDATE barang SET stok = stok + 1 WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $barang_id);
                if ($stmt->execute()) {
                    $log = $conn->prepare("INSERT INTO riwayat (user_id, username, barang_id, nama_barang, aksi) VALUES (?, ?, ?, ?, 'kembali')");
                    if ($log) {
                        $log->bind_param("isis", $_SESSION['user_id'], $_SESSION['username'], $barang_id, $nama_barang);
                        $log->execute();
                    }
                    header('Location: dashboard.php?status=success_kembali');
                }
            }
            exit();
        }
    }


    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        if (!isAdmin()) { header("Location: dashboard.php"); exit(); }
        if (softDeleteBarang((int)$_GET['delete'])) {
            header('Location: dashboard.php?status=deleted');
            exit();
        }
    }

    $statusMessage = '';
    if (isset($_GET['status'])) {
        if ($_GET['status'] === 'success_update') $statusMessage = 'Data barang berhasil diperbarui!';
        if ($_GET['status'] === 'success_added') $statusMessage = 'Barang dan Gambar berhasil ditambahkan!';
        if ($_GET['status'] === 'deleted') $statusMessage = 'Barang berhasil dihapus.';
        if ($_GET['status'] === 'success_pinjam') $statusMessage = 'Barang berhasil dipinjam (Stok -1).';
        if ($_GET['status'] === 'success_kembali') $statusMessage = 'Barang berhasil dikembalikan (Stok +1).';
        if ($_GET['status'] === 'failed_stok') $statusMessage = 'Gagal! Stok barang habis.';
    }

    $isEditMode = false;
    $isAddMode = false;
    $editData = null;
    $addData = ['nama_barang' => '', 'deskripsi' => '', 'stok' => '', 'harga' => '', 'kategori' => ''];


    if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['form_action'])) {
        

        if ($_POST['form_action'] === 'edit') {
            if (!isAdmin()) { header("Location: dashboard.php"); exit(); }

            $id = (int) $_POST['id'];
            $nama = $_POST['nama_barang'];
            $deskripsi = $_POST['deskripsi'];
            $stok = (int) $_POST['stok'];
            $harga = (double) $_POST['harga'];
            $kategori = $_POST['kategori'];
            $gambar_nama = $_POST['gambar_lama'];


            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
                $gambar_nama = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar_nama);
            }

            $stmt = $conn->prepare("UPDATE barang SET nama_barang=?, deskripsi=?, stok=?, harga=?, kategori=?, gambar=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param("ssidssi", $nama, $deskripsi, $stok, $harga, $kategori, $gambar_nama, $id);
                if ($stmt->execute()) {
                    header("Location: dashboard.php?status=success_update");
                    exit();
                }
            }
        }


        if ($_POST['form_action'] === 'add') {
            if (!isAdmin()) { header("Location: dashboard.php"); exit(); }
            $nama = $_POST['nama_barang'];
            $deskripsi = $_POST['deskripsi'];
            $stok = (int) $_POST['stok'];
            $harga = (double) $_POST['harga'];
            $kategori = $_POST['kategori'];
            $gambar_nama = 'default.png'; 


            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
                $gambar_nama = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar_nama);
            }

            $stmt = $conn->prepare("INSERT INTO barang (nama_barang, deskripsi, stok, harga, kategori, gambar) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssidss", $nama, $deskripsi, $stok, $harga, $kategori, $gambar_nama);
                if ($stmt->execute()) {
                    header("Location: dashboard.php?status=success_added");
                    exit();
                }
            }
        }
    }

    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        if (!isAdmin()) { header("Location: dashboard.php"); exit(); }
        $stmt = $conn->prepare("SELECT * FROM barang WHERE id = ?");
        if ($stmt) {
            $editId = (int) $_GET['edit'];
            $stmt->bind_param("i", $editId);
            $stmt->execute();
            $resultEdit = $stmt->get_result();
            $editData = $resultEdit ? $resultEdit->fetch_assoc() : null;
            if ($editData) $isEditMode = true;
        }
    }

    if (isset($_GET['add']) && $_GET['add'] === '1') {
        if (!isAdmin()) { header("Location: dashboard.php"); exit(); }
        $isAddMode = true;
    }


    $result = $conn->query("SELECT * FROM barang");


    if (isAdmin()) {
        $riwayatResult = $conn->query("SELECT * FROM riwayat ORDER BY tanggal DESC");
    } else {
        $riwayatResult = $conn->query("SELECT * FROM riwayat WHERE user_id = " . (int)$_SESSION['user_id'] . " ORDER BY tanggal DESC");
    }
}


function renderLoginForm($error, $success) {
    echo '<div class="row justify-content-center"><div class="col-md-4"><div class="card shadow border-0 mt-5"><div class="card-header bg-primary text-white"><h4>Login</h4></div><div class="card-body">';
    if ($error) echo '<div class="alert alert-danger">' . $error . '</div>';
    if ($success) echo '<div class="alert alert-success">' . $success . '</div>';
    echo '<form method="POST"><input type="hidden" name="form_action" value="login"><div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div><div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div><button type="submit" class="btn btn-primary w-100">Login</button></form><div class="mt-3 text-center"><a href="dashboard.php?action=register">Daftar Akun Baru</a></div></div></div></div></div>';
}

function renderRegisterForm($error, $success) {
    echo '<div class="row justify-content-center"><div class="col-md-4"><div class="card shadow border-0 mt-5"><div class="card-header bg-primary text-white"><h4>Registrasi Akun</h4></div><div class="card-body">';
    if ($error) echo '<div class="alert alert-danger">' . $error . '</div>';
    echo '<form method="POST"><input type="hidden" name="form_action" value="register"><div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div><div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div><div class="mb-3"><label>Role</label><select name="role" class="form-select"><option value="user">User</option><option value="admin">Admin</option></select></div><button type="submit" class="btn btn-primary w-100">Daftar</button></form><div class="mt-3 text-center"><a href="dashboard.php">Kembali ke Login</a></div></div></div></div></div>';
}


function renderAddForm() {
    echo '<div class="card shadow border-0 mb-4"><div class="card-header bg-success text-white"><h4 class="mb-0">Tambah Barang & Upload Foto</h4></div><div class="card-body">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="form_action" value="add">
        <div class="mb-3"><label class="form-label">Nama Barang</label><input type="text" name="nama_barang" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Stok</label><input type="number" name="stok" class="form-control" required min="0"></div><div class="col-md-6 mb-3"><label class="form-label">Harga</label><input type="number" name="harga" class="form-control" required min="0"></div></div>
        <div class="mb-3"><label class="form-label">Kategori</label><select name="kategori" class="form-select" required><option value="Elektronik">Elektronik</option><option value="Perabotan">Perabotan</option><option value="Alat Tulis">Alat Tulis</option></select></div>
        
        <!-- ELEMENT INPUT FILE UPLOAD -->
        <div class="mb-3 bg-light p-3 border rounded">
            <label class="form-label text-primary">Pilih Gambar Barang (.jpg / .png)</label>
            <input type="file" name="gambar" class="form-control" accept="image/*" required>
            <small class="text-muted">File akan otomatis tersimpan di dalam folder <code>uploads/</code></small>
        </div>
        
        <button type="submit" class="btn btn-success">Simpan Barang</button> <a href="dashboard.php" class="btn btn-secondary">Batal</a>
    </form></div></div>';
}


function renderEditForm($editData) {
    $id = $editData['id'];
    $nm = htmlspecialchars($editData['nama_barang']);
    $ds = htmlspecialchars($editData['deskripsi']);
    $st = $editData['stok'];
    $hr = $editData['harga'];
    $gl = htmlspecialchars($editData['gambar']);
    
    echo "<div class='card shadow border-0 mb-4'><div class='card-header bg-warning'><h4 class='mb-0'>Edit Barang & Gambar</h4></div><div class='card-body'>
    <form method='POST' enctype='multipart/form-data'>
        <input type='hidden' name='form_action' value='edit'>
        <input type='hidden' name='id' value='$id'>
        <input type='hidden' name='gambar_lama' value='$gl'>
        <div class='mb-3'><label class='form-label'>Nama Barang</label><input type='text' name='nama_barang' class='form-control' value='$nm' required></div>
        <div class='mb-3'><label class='form-label'>Deskripsi</label><textarea name='deskripsi' class='form-control' rows='2'>$ds</textarea></div>
        <div class='row'><div class='col-md-6 mb-3'><label class='form-label'>Stok</label><input type='number' name='stok' class='form-control' value='$st' required></div><div class='col-md-6 mb-3'><label class='form-label'>Harga</label><input type='number' name='harga' class='form-control' value='$hr' required></div></div>
        
        <!-- ELEMENT EDIT FILE UPLOAD -->
        <div class='mb-3 bg-light p-3 border rounded'>
            <label class='form-label'>Foto Saat Ini:</label><br>
            <img src='uploads/$gl' class='img-thumbnail mb-2' style='max-width:100px;'><br>
            <label class='form-label text-warning'>Ganti Gambar Baru (Kosongkan jika tidak ingin mengubah)</label>
            <input type='file' name='gambar' class='form-control' accept='image/*'>
        </div>
        
        <button type='submit' class='btn btn-primary'>Update Data</button> <a href='dashboard.php' class='btn btn-secondary'>Batal</a>
    </form></div></div>";
}

function renderList($result, $statusMessage, $riwayatResult) {
    if ($statusMessage) echo '<div class="alert alert-success alert-dismissible fade show">' . $statusMessage . '<button class="btn-close" data-bs-dismiss="alert"></button></div>';
    
    echo '<div class="card shadow border-0 p-4 mb-4 bg-white"><h2>Manajemen Inventaris Barang</h2>';
    echo '<p>Login sebagai: <span class="badge bg-dark">' . strtoupper($_SESSION['role']) . '</span></p>';
    
    if (isAdmin()) {
        echo '<div class="mb-3"><a href="dashboard.php?add=1" class="btn btn-success">➕ Tambah Barang Baru & Upload Gambar</a></div>';
    }
    
    echo '<div class="table-responsive"><table class="table table-striped align-middle"><thead class="table-dark"><tr><th>No</th><th>Gambar</th><th>Nama Barang</th><th>Stok</th><th>Harga</th><th>Aksi</th></tr></thead><tbody>';
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $nm = htmlspecialchars($row['nama_barang']);
        $imgFile = (!empty($row['gambar']) && file_exists('uploads/' . $row['gambar'])) ? 'uploads/' . $row['gambar'] : 'https://placehold.co/80x80?text=No+Foto';
        
        echo "<tr><td>$no</td><td><img src='$imgFile' class='img-thumbnail' style='width:60px; height:60px; object-fit:cover;'></td><td>$nm</td><td>{$row['stok']}</td><td>Rp" . number_format($row['harga'],0,',','.')."</td><td>";
        if (isAdmin()) {
            echo "<a href='dashboard.php?edit=$id' class='btn btn-sm btn-warning me-1'>Edit</a>";
            echo "<a href='dashboard.php?delete=$id' class='btn btn-sm btn-danger' onclick=\"return confirm('Hapus?')\">Hapus</a>";
        } else {
            echo "<a href='dashboard.php?action_user=pinjam&id=$id' class='btn btn-sm btn-primary me-1'>Pinjam</a>";
            echo "<a href='dashboard.php?action_user=kembali&id=$id' class='btn btn-sm btn-success'>Kembalikan</a>";
        }
        echo "</td></tr>";
        $no++;
    }
    echo '</tbody></table></div>';


    echo '<div class="mt-5"><h3> ' . (isAdmin() ? 'Semua Log Aktivitas Peminjaman' : 'Riwayat Peminjaman Saya') . '</h3>';
    echo '<table class="table table-bordered table-sm"><thead class="table-secondary"><tr><th>Waktu</th>' . (isAdmin() ? '<th>User</th>' : '') . '<th>Nama Barang</th><th>Aksi</th></tr></thead><tbody>';
    while ($rRow = $riwayatResult->fetch_assoc()) {
        $aksi = $rRow['aksi'] === 'pinjam' ? '<span class="badge bg-primary">Pinjam</span>' : '<span class="badge bg-success">Kembali</span>';
        echo "<tr><td>{$rRow['tanggal']}</td>" . (isAdmin() ? "<td>" . htmlspecialchars($rRow['username']) . "</td>" : "") . "<td>" . htmlspecialchars($rRow['nama_barang']) . "</td><td>$aksi</td></tr>";
    }
    echo '</tbody></table></div>';
    echo '<div class="mt-3"><a href="dashboard.php?logout=1" class="btn btn-danger btn-sm">Logout</a></div></div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <?php if (!$isAppMode): ?>
        <?php if ($mode === 'register') renderRegisterForm($error, $success); else renderLoginForm($error, $success); ?>
    <?php else: ?>
        <?php if ($isEditMode) renderEditForm($editData); ?>
        <?php if ($isAddMode) renderAddForm(); ?>
        <?php if (!$isEditMode && !$isAddMode) renderList($result, $statusMessage, $riwayatResult); ?>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
