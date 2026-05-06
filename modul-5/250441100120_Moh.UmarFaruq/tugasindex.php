<?php
function tampilkanHasil($nama, $frameworkArray, $cerita, $tools, $minat, $skill) {
    echo "<div class='result-box'>";
    echo "<h3> Hasil Input Data</h3>";
    echo "<b>Nama:</b> " . $nama . "<br>";
    echo "<b>Framework:</b> " . implode(", ", $frameworkArray) . "<br>";
    
    $daftar_tools = !empty($tools) ? implode(", ", $tools) : "-";
    echo "<b>Tools Penunjang:</b> " . $daftar_tools . "<br>";
    
    echo "<b>Minat:</b> " . $minat . "<br>";
    echo "<b>Skill Level:</b> " . $skill . "<br>";
    echo "<b>Cerita:</b><p>" . $cerita . "</p>";
    echo "</div>";
}

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = "Moh. Umar Faruq"; 
    $input_framework = $_POST['framework'];
    $pengalaman = $_POST['pengalaman'];
    
    $tools_dipilih = isset($_POST['tools']) ? $_POST['tools'] : [];
    $minat_user = isset($_POST['minat']) ? $_POST['minat'] : "Belum memilih";
    $skill_user = $_POST['skill'];

    if (!empty($input_framework) && !empty($pengalaman)) {
        $dataArray = explode(",", $input_framework);
        if (count($dataArray) > 2) {
            $pesan = "<div class='alert success'> Skill Anda cukup luas di bidang development!</div>";
        }
    } else {
        $pesan = "<div class='alert danger'> Mohon isi semua form agar data dapat diproses!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Interaktif Developer</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
            padding: 40px;
        }

        .container {
            max-width: 700px;
            background: white;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2, h3 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        table tr:nth-child(even) { background-color: #f9f9f9; }

        label { font-weight: bold; display: block; margin-top: 15px; }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            width: 100%;
        }
        
        button:hover { background-color: #2980b9; }

        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .result-box {
            background-color: transparent;
            padding: 10px 0;
            border-left: none;
            margin-top: 20px;
            border-top: 1px solid #eee;
        }

        .nav-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Profil Developer</h2>
    
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 16px;">
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; font-weight: bold; color: #34495e; width: 30%;">Nama</td>
            <td style="padding: 12px; color: #555;">Moh. Umar Faruq</td>
        </tr>
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; font-weight: bold; color: #34495e;">ID Developer</td>
            <td style="padding: 12px; color: #555; font-family: 'Courier New', Courier, monospace;">250441100120</td>
        </tr>

        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; font-weight: bold; color: #34495e;">Kota/Tgl Lahir</td>
            <td style="padding: 12px; color: #555;">Bangkalan, 16-12-2005</td>
        </tr>
  
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; font-weight: bold; color: #34495e;">Email</td>
            <td style="padding: 12px; color: #555;">mumarfaruq6317412@gmail.com</td>
        </tr>
        <tr>
            <td style="padding: 12px; font-weight: bold; color: #34495e;">No. WhatsApp</td>
            <td style="padding: 12px; color: #555;">085859737185</td>
        </tr>
    </table>

    <h3>Form Isian</h3>
    <form method="post">
        <label>Framework (pisahkan dengan koma):</label>
        <input type="text" name="framework" placeholder="bootstrap, Tailwind, dll.">
        
        <label>Pengalaman Belajar:</label>
        <textarea name="pengalaman" rows="4"></textarea>
        
        <label>Tools Penunjang:</label>
        <input type="checkbox" name="tools[]" value="VS Code"> VS Code
        <input type="checkbox" name="tools[]" value="GitHub"> GitHub
        
        <label>Minat:</label>
        <input type="radio" name="minat" value="Frontend"> Frontend
        <input type="radio" name="minat" value="Backend"> Backend
        
        <label>Skill Level:</label>
        <select name="skill">
            <option value="Dasar">Dasar (Beginner)</option>
            <option value="Cukup">Cukup (Intermediate)</option>
        </select>
        
        <button type="submit">Proses Data Saya</button>
    </form>

    <?php 
    if ($pesan !== "") echo $pesan;
    
    if (isset($dataArray)) {
        tampilkanHasil($nama, $dataArray, $pengalaman, $tools_dipilih, $minat_user, $skill_user);
    }
    ?>

    <a href="tugastimeline.php" class="nav-link">Lanjut ke Timeline Perjalanan →</a>
</div>

</body>
</html>
