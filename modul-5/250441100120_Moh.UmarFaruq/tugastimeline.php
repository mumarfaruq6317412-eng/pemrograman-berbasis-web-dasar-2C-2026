<?php
$riwayat = [
    ["tahun" => 2025, "kegiatan" => "Masuk Kuliah di Prodi Sistem Informasi"],
    ["tahun" => 2026, "kegiatan" => "Mulai Belajar Dasar Web (HTML & CSS)"],
    ["tahun" => 2026, "kegiatan" => "Membuat Desain Landing Page dengan Tailwind"],
    ["tahun" => 2026, "kegiatan" => "Mendalami Database MySQL & Query JOIN"],
    ["tahun" => 2026, "kegiatan" => "Mengerjakan Tugas Besar PHP Interaktif"]
];

function beriWarna($tahun) {
    if ($tahun == 2026) {
        return "<div class='badge current'>Tahun Ini ($tahun)</div>";
    }
    return "<div class='badge'>$tahun</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Timeline Kotak Sederhana</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #eef2f7;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            text-align: center;
        }

        .flex-timeline {
            display: flex;
            flex-wrap: wrap; 
            justify-content: center; 
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-top: 5px solid #3498db;
            width: 250px; 
            min-height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .badge {
            background-color: #34495e;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .badge.current { background-color: #e67e22; }

        .kegiatan { font-size: 15px; color: #2c3e50; font-weight: 500; }

        .nav-box { margin-top: 50px; }
        .btn {
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            margin: 10px;
            display: inline-block;
            font-weight: bold;
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Perjalanan Belajar Coding</h2>

    <div class="flex-timeline">
        <?php foreach ($riwayat as $item): ?>
            <div class="card">
                <?php echo beriWarna($item['tahun']); ?>
                <span class="kegiatan"><?php echo $item['kegiatan']; ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="nav-box">
        <a href="tugasindex.php" class="btn" style="background:#bdc3c7; color:#333;"> Kembali</a>
        <a href="tugasblog.php" class="btn">Ke Blog </a>
    </div>
</div>

</body>
</html>
