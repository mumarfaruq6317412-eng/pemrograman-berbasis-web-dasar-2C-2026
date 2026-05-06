<?php
$articles = [
    "html" => [
        "judul" => "Belajar HTML Pertama Kali",
        "tanggal" => "10 Maret 2026",
        "isi" => "HTML adalah langkah awal saya di dunia teknologi. Memahami struktur tag terasa seperti menyusun puzzle yang seru.",
        "img" => "bljrprtm.png",
        "link" => "https://www.w3schools.com/html/" 
    ],
    "error" => [
        "judul" => "Menghadapi Error Pertama",
        "tanggal" => "15 Maret 2026",
        "isi" => "Dulu saya panik melihat layar penuh pesan error, sekarang saya sadar bahwa error adalah guru terbaik dalam coding.",
        "img" => "errorprtm.png",
        "link" => "https://www.w3schools.com/" 
    ],
    "sql" => [
        "judul" => "Serunya Belajar Database",
        "tanggal" => "02 Mei 2026",
        "isi" => "Menghubungkan antar tabel dengan JOIN memberikan kepuasan tersendiri saat data berhasil muncul dengan tepat.",
        "img" => "sql.png",
        "link" => "https://www.w3schools.com/sql/" 
    ]
];

$quotes = [
    "Coding adalah seni memecahkan masalah.",
    "Jangan takut salah, takutlah jika tidak mencoba.",
    "Satu-satunya cara belajar bahasa pemrograman adalah dengan menulis kode.",
    "Error adalah cara kode berkata 'tolong perbaiki aku'."
];
$random_quote = $quotes[array_rand($quotes)];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Blog Reflektif Developer</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .blog-header {
            background: #2c3e50;
            color: white;
            width: 100%;
            padding: 30px 0;
            text-align: center;
        }

        .quote-text {
            display: block;
            margin-top: 10px;
            font-style: italic;
            color: #bdc3c7; 
            font-size: 16px;
        }

        .blog-wrapper {
            display: flex;
            max-width: 1000px;
            width: 90%;
            margin: 30px 0;
            gap: 20px;
        }

        .sidebar {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: fit-content;
        }

        .sidebar h3 { color: #2c3e50; margin-bottom: 15px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { margin-bottom: 10px; }
        .sidebar a {
            text-decoration: none;
            color: #555;
            display: block;
            padding: 8px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background: #f0f0f0;
            color: #333;
        }

        .content-area {
            flex: 2;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            min-height: 400px;
        }

        .article-img, .welcome-img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #eee;
        }

        .date { color: #999; font-size: 14px; }
        .btn-nav {
            margin-top: 20px;
            text-decoration: none;
            color: #7f8c8d;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="blog-header">
    <h1>Blog Reflektif Developer</h1>
    <span class="quote-text">
        "<?php echo $random_quote; ?>"
    </span>
</div>

<div class="blog-wrapper">
    <aside class="sidebar">
        <h3>Menu Utama</h3>
        <ul>
            <li><a href="tugasblog.php" style="font-weight: bold;">Beranda</a></li>
        </ul>
        <hr>
        <h3>Daftar Artikel</h3>
        <ul>
            <?php foreach ($articles as $id => $data): ?>
                <li><a href="tugasblog.php?id=<?php echo $id; ?>"><?php echo $data['judul']; ?></a></li>
            <?php endforeach; ?>
        </ul>
        <hr>
        <a href="tugastimeline.php" class="btn-nav">⬅ Kembali ke Timeline</a>
    </aside>

    <main class="content-area">
        <?php
        if (isset($_GET['id']) && array_key_exists($_GET['id'], $articles)) {
            $selected = $articles[$_GET['id']];
            echo "<span class='date'> Diposting pada: " . $selected['tanggal'] . "</span>";
            echo "<h2>" . $selected['judul'] . "</h2>";
            echo "<img src='" . $selected['img'] . "' class='article-img'>";
            echo "<p style='font-size: 18px; color: #444;'>" . $selected['isi'] . "</p>";
            
           
            if (isset($selected['link'])) {
                echo "<p style='margin-top: 30px; font-size: 16px;'>";
                echo "<strong>Referensi:</strong> <a href='" . $selected['link'] . "' target='_blank' style='color: #2980b9;'>Pelajari lebih lanjut di sini</a>";
                echo "</p>";
            }

        } else {
            echo "<div style='text-align:center;'>";
            echo "<h2>Selamat Datang di Blog Saya!</h2>";
            echo "<p style='font-size: 18px; color: #666;'>Silakan pilih judul artikel di samping untuk membaca pengalaman belajar saya.</p>";
            echo "<img src='welcome.png.png' class='welcome-img'>";
            echo "</div>";
        }
        ?>
    </main>
</div>

</body>
</html>
