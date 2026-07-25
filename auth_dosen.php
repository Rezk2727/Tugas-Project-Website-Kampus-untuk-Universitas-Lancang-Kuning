<?php
// MENGUNCI SESSION KHUSUS DOSEN
session_name('KAMPUS_DOSEN');
session_start();
require_once 'config/db.php';

$error = '';

if (isset($_POST['login'])) {
    $identity_code = trim($_POST['identity_code']);
    $password      = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE identity_code = ? AND role = 'lecturer'");
    $stmt->execute([$identity_code]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'banned') {
            $error = "Akun Dosen Anda ditangguhkan/banned oleh Admin!";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];
            $pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")->execute([$user['id']]);
            header("Location: dosen_dashboard.php");
            exit;
        }
    } else {
        $error = "NIDN atau Password Dosen salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portal Dosen - Universitas Lancang Kuning</title>
    <link rel="stylesheet" href="assets/css/pixel.css">
    <style>
        .btn-pixel { background: #8be9fd; border: var(--pixel-border); color: #000; padding: 10px; cursor: pointer; width: 100%; font-family: 'VT323', monospace; font-size: 22px; font-weight: bold; }
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
            <h2 style="text-align:center; color:#8be9fd; margin-top:0;">PORTAL DOSEN</h2>

            <?php if($error): ?><p style="color:#ff5555; text-align:center;"><?= $error ?></p><?php endif; ?>

            <form method="POST">
                <h3 style="margin-bottom:5px;">Masuk Dosen</h3>
                <input type="text" name="identity_code" placeholder="Nomor Induk Dosen (NIDN)" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="btn-pixel">MASUK DOSEN</button>
            </form>
        </div>
    </div>
</body>
</html>