<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "manajemen_inventaris"; 
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit();
    }
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: dashboard.php");
    exit();
}

function softDeleteBarang($id) {
    global $conn;
    if (!isAdmin()) {
        return false;
    }

    $id = (int) $id;
    $stmt_select = $conn->prepare("SELECT nama_barang, stok, harga, kategori FROM barang WHERE id = ?");
    if (!$stmt_select) {
        return false;
    }
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $data = $result ? $result->fetch_assoc() : null;

    if (!$data) {
        return false;
    }

    $stmt_backup = $conn->prepare("INSERT INTO barang_terhapus (id, nama_barang, stok, harga, kategori) VALUES (?, ?, ?, ?, ?)");
    if ($stmt_backup) {
        $stmt_backup->bind_param("isids", $id, $data['nama_barang'], $data['stok'], $data['harga'], $data['kategori']);
        $stmt_backup->execute();
    }

    $stmt_delete = $conn->prepare("DELETE FROM barang WHERE id = ?");
    if (!$stmt_delete) {
        return false;
    }
    $stmt_delete->bind_param("i", $id);
    return $stmt_delete->execute();
}

function permanentDeleteBarang($id) {
    global $conn;
    if (!isAdmin()) {
        return false;
    }

    $id = (int) $id;
    $stmt = $conn->prepare("DELETE FROM barang_terhapus WHERE id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>

