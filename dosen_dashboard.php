<?php
// MENGUNCI SESSION KHUSUS DOSEN
session_name('KAMPUS_DOSEN');
session_start();
date_default_timezone_set('Asia/Jakarta');

require_once 'config/db.php';
require_once 'upload_helpers.php';

// ===================================================
// ALGORITMA ISOLASI KETAT DOSEN (ANTI BYPASS)
// ===================================================
if (!isset($_SESSION['user_id'])) {
    header("Location: auth_dosen.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// VERIFIKASI REAL-TIME DARI DATABASE
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$currentUser = $stmtUser->fetch();

// TOLAK JIKA USER TIDAK ADA, BUKAN DOSEN, ATAU DIBANNED
if (!$currentUser || $currentUser['role'] !== 'lecturer' || $currentUser['status'] === 'banned') {
    session_destroy();
    header("Location: auth_dosen.php");
    exit;
}

$pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")->execute([$user_id]);

// 1. Update Foto Profil Dosen Saja
if (isset($_POST['update_profile_photo'])) {
    if (!empty($_FILES['photo']['name'])) {
        $uploadResult = processUpload(['name' => $_FILES['photo']['name'], 'tmp_name' => $_FILES['photo']['tmp_name']], 'uploads/');
        if ($uploadResult['status'] && $uploadResult['fileType'] === 'image') {
            $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?")->execute([$uploadResult['fileName'], $user_id]);
        }
    }
    header("Location: dosen_dashboard.php");
    exit;
}

// 2. Algoritma Buat / Edit Kuis Baru
if (isset($_POST['save_quiz'])) {
    $quiz_id  = $_POST['quiz_id'] ?? null;
    $title    = trim($_POST['title']);
    $subject  = trim($_POST['subject']);
    $prodi    = trim($_POST['prodi']);
    $deadline = $_POST['deadline'];

    if ($quiz_id) {
        $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, subject = ?, prodi = ?, deadline = ? WHERE id = ? AND lecturer_id = ?");
        $stmt->execute([$title, $subject, $prodi, $deadline, $quiz_id, $user_id]);
    } else {
        if (!empty($_FILES['file_question']['name'])) {
            $upload = processUpload(['name' => $_FILES['file_question']['name'], 'tmp_name' => $_FILES['file_question']['tmp_name']], 'uploads/');
            if ($upload['status']) {
                $stmt = $pdo->prepare("INSERT INTO quizzes (lecturer_id, title, subject, prodi, file_question, deadline) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $title, $subject, $prodi, $upload['fileName'], $deadline]);
            }
        }
    }
    header("Location: dosen_dashboard.php");
    exit;
}

// 3. Algoritma Hapus Kuis
if (isset($_GET['delete_quiz'])) {
    $q_id = $_GET['delete_quiz'];
    $stmt = $pdo->prepare("SELECT file_question FROM quizzes WHERE id = ? AND lecturer_id = ?");
    $stmt->execute([$q_id, $user_id]);
    $q = $stmt->fetch();
    if ($q) {
        @unlink('uploads/' . $q['file_question']);
        $pdo->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$q_id]);
    }
    header("Location: dosen_dashboard.php");
    exit;
}

// 4. Algoritma Beri / Edit Nilai Kuis
if (isset($_POST['grade_submission'])) {
    $sub_id = $_POST['submission_id'];
    $grade  = (int)$_POST['grade'];

    $stmt = $pdo->prepare("UPDATE quiz_submissions SET grade = ? WHERE id = ?");
    $stmt->execute([$grade, $sub_id]);
    header("Location: dosen_dashboard.php");
    exit;
}

$myQuizzes      = $pdo->query("SELECT * FROM quizzes WHERE lecturer_id = $user_id ORDER BY created_at DESC")->fetchAll();
$activeAnnounce = $pdo->query("SELECT message FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portal Kuis Dosen - Universitas Lancang Kuning</title>
    <link rel="stylesheet" href="assets/css/pixel.css">
    <style>
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0; background: #282a36; color: #fff; border: 2px solid #000; box-sizing: border-box; }
        .avatar-pixel { width: 64px; height: 64px; border: 3px solid #000; border-radius: 8px; object-fit: cover; }
    </style>
</head>
<body>

    <div class="navbar-campus">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Logo Kampus" class="logo-campus-img">
            <span style="font-weight:bold; color:#000; font-size:22px;">UNIVERSITAS LANCANG KUNING</span>
        </div>
        <div>
            <span style="color:#000; font-weight:bold; margin-right:10px;">Halo, <?= htmlspecialchars($currentUser['name']) ?></span>
            <a href="logout.php?role=lecturer" style="color:#ff5555; text-decoration:none; font-weight:bold; background:#000; padding:6px 12px; border:2px solid #000;">[ KELUAR ]</a>
        </div>
    </div>

    <!-- RUNNING TEXT PENGUMUMAN UNTUK DOSEN -->
    <?php if($activeAnnounce): ?>
        <div class="running-text-box">
            <div class="running-text-content">📢 PENGUMUMAN: <?= htmlspecialchars($activeAnnounce) ?></div>
        </div>
    <?php endif; ?>

    <div class="container" style="max-width: 900px; margin:20px auto;">
        
        <!-- PROFIL DOSEN DENGAN TAMPILAN INFORMASI DARI ADMIN -->
        <div class="pixel-card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:15px;">
                <img src="uploads/<?= htmlspecialchars($currentUser['profile_photo']) ?>" class="avatar-pixel" onerror="this.src='assets/img/default_pixel.png'">
                <div>
                    <h3 style="margin:0;"><?= htmlspecialchars($currentUser['name']) ?></h3>
                    <small style="color:var(--pixel-cyan);">
                        NIDN: <strong><?= htmlspecialchars($currentUser['identity_code'] ?? 'Belum Diatur Admin') ?></strong> | 
                        Prodi: <strong><?= htmlspecialchars($currentUser['prodi'] ?? 'Belum Diatur Admin') ?></strong> | 
                        Matkul: <strong><?= htmlspecialchars($currentUser['subject'] ?? 'Belum Diatur Admin') ?></strong>
                    </small>
                </div>
            </div>

            <!-- FORM HANYA GANTI FOTO PROFIL -->
            <form method="POST" enctype="multipart/form-data" style="display:flex; gap:5px; align-items:center;">
                <input type="file" name="photo" accept="image/*" style="font-size:12px; width:160px;" required>
                <button type="submit" name="update_profile_photo" class="btn-action" style="background:var(--pixel-cyan);">Ganti Foto</button>
            </form>
        </div>

        <!-- FORM BUAT KUIS BARU -->
        <div class="pixel-card">
            <h3>📝 Buat Kuis Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Judul Kuis (misal: Kuis 1 Algoritma)" required>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="subject" placeholder="Mata Kuliah" value="<?= htmlspecialchars($currentUser['subject'] ?? '') ?>" required>
                    <input type="text" name="prodi" placeholder="Prodi Tujuan" value="<?= htmlspecialchars($currentUser['prodi'] ?? '') ?>" required>
                </div>
                <label>Upload File Soal Softcopy (Gambar/PDF/Word):</label>
                <input type="file" name="file_question" required>
                
                <label>Batas Tenggat Pengumpulan (Deadline):</label>
                <input type="datetime-local" name="deadline" required>

                <button type="submit" name="save_quiz" class="btn-action" style="background:var(--pixel-green); width:100%; margin-top:10px;">UNGGAH KUIS</button>
            </form>
        </div>

        <!-- DAFTAR KUIS & HASIL -->
        <div class="pixel-card">
            <h3>📜 Daftar Kuis Terpublikasi</h3>
            <?php foreach ($myQuizzes as $q): 
                $stmtSub = $pdo->prepare("SELECT s.*, u.name, u.identity_code FROM quiz_submissions s JOIN users u ON s.student_id = u.id WHERE s.quiz_id = ? ORDER BY s.submitted_at DESC");
                $stmtSub->execute([$q['id']]);
                $submissions = $stmtSub->fetchAll();
            ?>
                <div style="border:2px solid #000; padding:15px; margin-bottom:15px; background:#282a36;">
                    <div style="display:flex; justify-content:space-between;">
                        <h3 style="margin:0; color:var(--pixel-cyan);"><?= htmlspecialchars($q['title']) ?></h3>
                        <a href="?delete_quiz=<?= $q['id'] ?>" style="color:#ff5555; font-weight:bold;" onclick="return confirm('Hapus kuis ini?')">[Hapus Kuis]</a>
                    </div>
                    <small>Matkul: <?= htmlspecialchars($q['subject']) ?> | Prodi: <?= htmlspecialchars($q['prodi']) ?> | ⏰ Deadline: <strong><?= date('d M Y H:i', strtotime($q['deadline'])) ?> WIB</strong></small>
                    <p><a href="uploads/<?= $q['file_question'] ?>" target="_blank" style="color:var(--pixel-green);">📄 Unduh Soal Softcopy</a></p>

                    <h4>📥 Jawaban Mahasiswa Masuk (<?= count($submissions) ?>):</h4>
                    <table class="table-pixel" style="font-size:16px;">
                        <tr>
                            <th>Mahasiswa / NIM</th>
                            <th>Jawaban Softcopy</th>
                            <th>Waktu Kirim</th>
                            <th>Nilai (0-100)</th>
                        </tr>
                        <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td><?= htmlspecialchars($sub['name']) ?><br><small>NIM: <?= htmlspecialchars($sub['identity_code']) ?></small></td>
                            <td><a href="uploads/<?= $sub['file_answer'] ?>" target="_blank" style="color:var(--pixel-cyan);">📁 Lihat File Jawaban</a></td>
                            <td><?= date('d/m/Y H:i', strtotime($sub['submitted_at'])) ?></td>
                            <td>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                    <input type="number" name="grade" min="0" max="100" value="<?= $sub['grade'] ?>" placeholder="0" style="width:60px; padding:2px;">
                                    <button type="submit" name="grade_submission" class="btn-action" style="padding:2px 6px;">Simpan</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</body>
</html>