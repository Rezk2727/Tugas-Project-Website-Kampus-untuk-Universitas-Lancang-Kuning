<?php
// MENGUNCI SESSION KHUSUS MAHASISWA
session_name('KAMPUS_MAHASISWA');
session_start();
require_once 'config/db.php';

$error = '';

if (isset($_POST['login'])) {
    $identity_code = trim($_POST['identity_code']);
    $password      = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE identity_code = ? AND role = 'student'");
    $stmt->execute([$identity_code]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'banned') {
            $error = "Akun Mahasiswa Anda ditangguhkan/banned oleh Admin!";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];
            $pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")->execute([$user['id']]);
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "NIM atau Password Mahasiswa salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portal Mahasiswa - Universitas Lancang Kuning</title>
    <link rel="stylesheet" href="assets/css/pixel.css">
    <style>
        .btn-pixel { background: #50fa7b; border: var(--pixel-border); color: #000; padding: 10px; cursor: pointer; width: 100%; font-family: 'VT323', monospace; font-size: 22px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin: 8px 0; background: rgba(68, 71, 90, 0.9); color: #fff; border: 2px solid #000; box-sizing: border-box; }
    </style>
</head>
<body>

    <video autoplay muted loop class="bg-video">
        <source src="assets/video/video.mp4" type="video/mp4">
    </video>

    <div class="navbar-campus">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Logo Kampus" class="logo-campus-img">
            <span style="font-weight:bold; color:#000; font-size:22px;">UNIVERSITAS LANCANG KUNING</span>
        </div>
    </div>

    <div class="auth-container">
        <div class="pixel-card">
            <h2 style="text-align:center; color:#50fa7b; margin-top:0;">PORTAL MAHASISWA</h2>

            <?php if($error): ?><p style="color:#ff5555; text-align:center;"><?= $error ?></p><?php endif; ?>

            <form method="POST">
                <h3 style="margin-bottom:5px;">Masuk Mahasiswa</h3>
                <input type="text" name="identity_code" placeholder="Nomor Induk Mahasiswa (NIM)" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="btn-pixel">MASUK MAHASISWA</button>
            </form>
        </div>
    </div>
</body>
</html>