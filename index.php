<?php
require_once 'config/db.php';

// Ambil Berita & Pengumuman Terbaru
$news = $pdo->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 3")->fetchAll();
$activeAnnounce = $pdo->query("SELECT message FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universitas Lancang Kuning - Unggul & Berkualitas</title>
    <link rel="stylesheet" href="assets/css/pixel.css">
    <style>
        body { font-family: 'VT323', monospace; margin: 0; padding: 0; background-color: #1e1e2e; color: #fff; }
        .top-bar { background: #11111b; padding: 8px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; font-size: 14px; }
        .top-bar a { color: #50fa7b; text-decoration: none; font-weight: bold; }
        .hero { text-align: center; padding: 60px 20px; background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/img/hero_bg.jpg'); background-size: cover; border-bottom: 3px solid #000; }
        .hero h1 { font-size: 42px; color: #f1fa8c; margin-bottom: 10px; }
        .hero p { font-size: 20px; color: #f8f8f2; max-width: 700px; margin: 0 auto 20px; }
        .btn-portal { display: inline-block; padding: 10px 20px; background: #ff79c6; color: #000; text-decoration: none; font-weight: bold; border: 2px solid #000; font-size: 18px; }
        .section { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card-fakultas { background: #282a36; border: 2px solid #000; padding: 20px; border-radius: 4px; }
        .card-fakultas h3 { color: #8be9fd; margin-top: 0; }
        .footer { background: #11111b; border-top: 2px solid #000; padding: 20px; text-align: center; margin-top: 50px; font-size: 14px; }
    </style>
</head>
<body>

    <!-- NAVBAR ATAS -->
    <div class="top-bar">
        <div>📍 Jl. Yos Sudarso No.12, Riau | 📞 (0761) 123456</div>
        <div>
            <a href="auth_mahasiswa.php">[ Portal Mahasiswa ]</a> | 
            <a href="auth_dosen.php">[ Portal Dosen ]</a> | 
            <a href="auth_admin.php" style="color:#ff79c6;">[ Portal Admin ]</a>
        </div>
    </div>

    <div class="navbar-campus" style="background:#282a36; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #000;">
        <div class="logo-container" style="display:flex; align-items:center; gap:10px;">
            <img src="assets/img/logo.png" alt="Logo Kampus" class="logo-campus-img" style="height:50px;">
            <span style="font-weight:bold; color:#f1fa8c; font-size:24px;">UNIVERSITAS LANCANG KUNING</span>
        </div>
    </div>

    <!-- RUNNING TEXT PENGUMUMAN -->
    <?php if($activeAnnounce): ?>
        <div class="running-text-box" style="background:#ffb86c; color:#000; padding:8px; font-weight:bold; border-bottom:2px solid #000;">
            <marquee scrollamount="6">📢 PENGUMUMAN RESMI: <?= htmlspecialchars($activeAnnounce) ?></marquee>
        </div>
    <?php endif; ?>

    <!-- HERO BANNER -->
    <div class="hero">
        <h1>Selamat Datang di Universitas Lancang Kuning</h1>
        <p>Menciptakan Generasi Unggul, Berkarakter, dan Berdaya Saing di Bidang Teknologi & Bisnis.</p>
        <a href="auth_mahasiswa.php" class="btn-portal">MASUK PORTAL AKADEMIK (SIAKAD)</a>
    </div>

    <!-- SECTION FAKULTAS -->
    <div class="section">
        <h2 style="text-align:center; color:#50fa7b; border-bottom:2px dashed #44475a; padding-bottom:10px;">🏛️ FAKULTAS & PROGRAM STUDI</h2>
        <div class="grid-3" style="margin-top:20px;">
            <div class="card-fakultas">
                <h3>💻 Fakultas Ilmu Komputer</h3>
                <ul>
                    <li>S1 Teknik Informatika</li>
                    <li>S1 Sistem Informasi</li>
                    <li>D3 Rekayasa Perangkat Lunak</li>
                </ul>
            </div>
            <div class="card-fakultas">
                <h3>📈 Fakultas Ekonomi & Bisnis</h3>
                <ul>
                    <li>S1 Manajemen</li>
                    <li>S1 Akuntansi</li>
                    <li>S1 Bisnis Digital</li>
                </ul>
            </div>
            <div class="card-fakultas">
                <h3>⚖️ Fakultas Hukum & Ilmu Sosial</h3>
                <ul>
                    <li>S1 Ilmu Hukum</li>
                    <li>S1 Ilmu Komunikasi</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- SECTION BERITA & KEGIATAN -->
    <div class="section">
        <h2 style="text-align:center; color:#ff79c6; border-bottom:2px dashed #44475a; padding-bottom:10px;">📢 BERITA & AGENDA KAMPUS</h2>
        <div class="grid-3" style="margin-top:20px;">
            <?php if(empty($news)): ?>
                <p style="text-align:center; grid-column: 1/-1;">Belum ada berita terpublikasi.</p>
            <?php endif; ?>
            <?php foreach($news as $n): ?>
                <div class="card-fakultas">
                    <span style="background:#bd93f9; color:#000; padding:2px 6px; font-weight:bold; font-size:12px;"><?= strtoupper($n['category']) ?></span>
                    <h4 style="margin:10px 0 5px 0; color:#50fa7b;"><?= htmlspecialchars($n['title']) ?></h4>
                    <small style="color:#8be9fd;">🗓️ <?= date('d M Y', strtotime($n['event_date'])) ?></small>
                    <p style="font-size:14px; margin-top:10px;"><?= htmlspecialchars(substr($n['description'], 0, 100)) ?>...</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>&copy; <?= date('Y') ?> Universitas Lancang Kuning. All Rights Reserved.</p>
    </div>

</body>
</html>