<?php
// ===================================================
// ISOLASI SESSION KHUSUS ADMIN SIAKAD
// ===================================================
session_name('KAMPUS_ADMIN');
session_start();
date_default_timezone_set('Asia/Jakarta');

require_once 'config/db.php';
require_once 'upload_helpers.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    if (isset($_SESSION['role'])) {
        header("Location: " . ($_SESSION['role'] === 'lecturer' ? "dosen_dashboard.php" : "index.php"));
    } else {
        header("Location: auth_admin.php");
    }
    exit;
}

// ===================================================
// AUTO-MIGRASI STRUKTUR DATABASE AKADEMIK KAMPUS
// ===================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_name VARCHAR(20) NOT NULL,
    semester ENUM('Ganjil', 'Genap') NOT NULL,
    is_active TINYINT(1) DEFAULT 0
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_code VARCHAR(10) UNIQUE NOT NULL,
    faculty_name VARCHAR(100) NOT NULL
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(15) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    prodi VARCHAR(100) NOT NULL,
    semester INT NOT NULL
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    lecturer_id INT NOT NULL,
    day VARCHAR(15) NOT NULL,
    time_start TIME NOT NULL,
    time_end TIME NOT NULL,
    room VARCHAR(30) NOT NULL,
    class_name VARCHAR(20) NOT NULL
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS ukt_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('Lunas', 'Belum Lunas') DEFAULT 'Belum Lunas',
    due_date DATE NOT NULL
)");

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN class_name VARCHAR(50) DEFAULT NULL AFTER prodi");
} catch (PDOException $e) {}

// NAVIGASI TAB
$tab         = $_GET['tab'] ?? 'dashboard';
$subtab      = $_GET['subtab'] ?? 'student';
$prodiFilter = trim($_GET['prodi_filter'] ?? '');
$searchUser  = trim($_GET['search_user'] ?? '');
$msg         = $_GET['msg'] ?? '';

// ===================================================
// PROSES AKSI ADMIN
// ===================================================

// 1. SET TAHUN AKADEMIK AKTIF
if (isset($_POST['set_academic_year'])) {
    $pdo->exec("UPDATE academic_years SET is_active = 0");
    $stmt = $pdo->prepare("UPDATE academic_years SET is_active = 1 WHERE id = ?");
    $stmt->execute([$_POST['year_id']]);
    header("Location: admin_dashboard.php?tab=academic&msg=year_active");
    exit;
}

if (isset($_POST['add_academic_year'])) {
    $stmt = $pdo->prepare("INSERT INTO academic_years (year_name, semester) VALUES (?, ?)");
    $stmt->execute([trim($_POST['year_name']), $_POST['semester']]);
    header("Location: admin_dashboard.php?tab=academic&msg=year_added");
    exit;
}

// 2. KELOLA MATAKULIAH
if (isset($_POST['add_course'])) {
    $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_name, sks, prodi, semester) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([strtoupper(trim($_POST['course_code'])), trim($_POST['course_name']), $_POST['sks'], $_POST['prodi'], $_POST['semester']]);
    header("Location: admin_dashboard.php?tab=courses&msg=course_added");
    exit;
}

if (isset($_GET['delete_course'])) {
    $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$_GET['delete_course']]);
    header("Location: admin_dashboard.php?tab=courses&msg=course_deleted");
    exit;
}

// 3. KELOLA JADWAL KULIAH
if (isset($_POST['add_schedule'])) {
    $stmt = $pdo->prepare("INSERT INTO schedules (course_id, lecturer_id, day, time_start, time_end, room, class_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['course_id'], $_POST['lecturer_id'], $_POST['day'], $_POST['time_start'], $_POST['time_end'], trim($_POST['room']), trim($_POST['class_name'])]);
    header("Location: admin_dashboard.php?tab=schedules&msg=schedule_added");
    exit;
}

if (isset($_GET['delete_schedule'])) {
    $pdo->prepare("DELETE FROM schedules WHERE id = ?")->execute([$_GET['delete_schedule']]);
    header("Location: admin_dashboard.php?tab=schedules&msg=schedule_deleted");
    exit;
}

// 4. KELOLA USER & RESET PASSWORD
if (isset($_POST['add_user_by_admin'])) {
    $hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, identity_code, prodi, class_name, subject) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([trim($_POST['name']), trim($_POST['email']), $hashed, $_POST['role'], trim($_POST['identity_code']), trim($_POST['prodi']), trim($_POST['class_name'] ?? ''), trim($_POST['subject'] ?? '')]);
    header("Location: admin_dashboard.php?tab=users&subtab=" . $_POST['role'] . "&msg=success_user");
    exit;
}

if (isset($_POST['reset_user_password'])) {
    $hashed = password_hash(trim($_POST['new_password']), PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $_POST['user_id']]);
    header("Location: admin_dashboard.php?tab=users&subtab=" . $_POST['current_subtab'] . "&msg=pass_reset");
    exit;
}

if (isset($_GET['delete_user'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$_GET['delete_user']]);
    header("Location: admin_dashboard.php?tab=users&subtab=" . ($_GET['subtab'] ?? 'student'));
    exit;
}

if (isset($_GET['toggle_status'])) {
    $new_status = ($_GET['current'] === 'active') ? 'banned' : 'active';
    $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new_status, $_GET['toggle_status']]);
    header("Location: admin_dashboard.php?tab=users&subtab=" . ($_GET['subtab'] ?? 'student'));
    exit;
}

// 5. KEGIATAN & RUNNING TEXT
if (isset($_POST['save_announcement'])) {
    $pdo->exec("UPDATE announcements SET is_active = 0");
    $pdo->prepare("INSERT INTO announcements (message, is_active) VALUES (?, 1)")->execute([trim($_POST['announcement_msg'])]);
    header("Location: admin_dashboard.php?tab=announcement&msg=saved");
    exit;
}

if (isset($_POST['add_event'])) {
    $stmt = $pdo->prepare("INSERT INTO events (category, title, description, event_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['category'], trim($_POST['title']), trim($_POST['description']), $_POST['event_date']]);
    header("Location: admin_dashboard.php?tab=events&msg=saved");
    exit;
}

if (isset($_GET['delete_event'])) {
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$_GET['delete_event']]);
    header("Location: admin_dashboard.php?tab=events");
    exit;
}

// QUERY ANALYTICS AKADEMIK
$totalStudents   = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalLecturers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'lecturer'")->fetchColumn();
$totalCourses    = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalSchedules  = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();
$activeYear      = $pdo->query("SELECT * FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();

// DATA USER LIST
$params = [$subtab];
$sqlUser = "SELECT u.*, (TIMESTAMPDIFF(SECOND, u.last_seen, NOW()) <= 120) AS is_online FROM users u WHERE u.role = ?";
if (!empty($searchUser)) {
    $sqlUser .= " AND (u.name LIKE ? OR u.identity_code LIKE ? OR u.email LIKE ? OR u.class_name LIKE ?)";
    array_push($params, "%$searchUser%", "%$searchUser%", "%$searchUser%", "%$searchUser%");
}
if (!empty($prodiFilter)) {
    $sqlUser .= " AND u.prodi = ?";
    $params[] = $prodiFilter;
}
$sqlUser .= " ORDER BY u.last_seen DESC";
$stmtU = $pdo->prepare($sqlUser); $stmtU->execute($params); $usersList = $stmtU->fetchAll();

$allProdi        = $pdo->query("SELECT DISTINCT prodi FROM users WHERE prodi IS NOT NULL AND prodi != ''")->fetchAll(PDO::FETCH_COLUMN);
$allLecturers    = $pdo->query("SELECT id, name FROM users WHERE role = 'lecturer' ORDER BY name ASC")->fetchAll();
$coursesList     = $pdo->query("SELECT * FROM courses ORDER BY prodi ASC, semester ASC")->fetchAll();
$schedulesList   = $pdo->query("SELECT s.*, c.course_name, c.course_code, u.name AS lecturer_name FROM schedules s JOIN courses c ON s.course_id = c.id JOIN users u ON s.lecturer_id = u.id ORDER BY FIELD(s.day, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), s.time_start ASC")->fetchAll();
$academicYears   = $pdo->query("SELECT * FROM academic_years ORDER BY id DESC")->fetchAll();
$events          = $pdo->query("SELECT * FROM events ORDER BY created_at DESC")->fetchAll();
$activeAnnounce  = $pdo->query("SELECT message FROM announcements WHERE is_active = 1 LIMIT 1")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIAKAD Admin - Universitas Lancang Kuning</title>
    <link rel="stylesheet" href="assets/css/pixel.css">
    <style>
        input, select, textarea { width: 100%; padding: 8px; margin: 4px 0; background: #282a36; color: #fff; border: 2px solid #000; box-sizing: border-box; }
        .subtab-btn { padding: 8px 16px; font-weight: bold; text-decoration: none; border: 2px solid #000; font-family: 'VT323', monospace; font-size: 18px; }
        .subtab-btn.active { background: var(--pixel-green); color: #000; }
        .subtab-btn.inactive { background: #44475a; color: #fff; }
        .badge-active { background: #50fa7b; color: #000; padding: 2px 8px; font-weight: bold; border: 1px solid #000; }
        .badge-inactive { background: #ff5555; color: #fff; padding: 2px 8px; font-weight: bold; border: 1px solid #000; }
    </style>
</head>
<body>

    <div class="navbar-campus">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Logo Kampus" class="logo-campus-img">
            <span style="font-weight:bold; color:#000; font-size:22px;">UNIVERSITAS LANCANG KUNING - PORTAL ADMINISTRATOR SIAKAD</span>
        </div>
        <div>
            <span style="color:#000; font-weight:bold; margin-right:10px;">👤 Admin: <?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="logout.php?role=admin" style="color:#ff5555; text-decoration:none; font-weight:bold; background:#000; padding:6px 12px; border:2px solid #000;">[ KELUAR ]</a>
        </div>
    </div>

    <div class="admin-layout">
        <div class="admin-sidebar">
            <a href="?tab=dashboard" class="sidebar-btn <?= $tab==='dashboard'?'active':'' ?>">📊 Dashboard Utama</a>
            <a href="?tab=academic" class="sidebar-btn <?= $tab==='academic'?'active':'' ?>">🏛️ Tahun Akademik</a>
            <a href="?tab=courses" class="sidebar-btn <?= $tab==='courses'?'active':'' ?>">📚 Master Matakuliah</a>
            <a href="?tab=schedules" class="sidebar-btn <?= $tab==='schedules'?'active':'' ?>">📅 Jadwal Perkuliahan</a>
            <a href="?tab=users" class="sidebar-btn <?= $tab==='users'?'active':'' ?>">👥 Manajemen User</a>
            <a href="?tab=events" class="sidebar-btn <?= $tab==='events'?'active':'' ?>">📢 Berita & Kegiatan</a>
            <a href="?tab=announcement" class="sidebar-btn <?= $tab==='announcement'?'active':'' ?>">📺 Running Text</a>
            <button onclick="window.print()" class="sidebar-btn" style="background:#50fa7b; margin-top:auto;">🖨️ Cetak Laporan SIAKAD</button>
        </div>

        <div class="admin-content">

            <!-- TAB 1: DASHBOARD UTAMA -->
            <?php if($tab === 'dashboard'): ?>
                <h2>📊 RINGKASAN SISTEM AKADEMIK KAMPUS</h2>
                <div class="stat-grid">
                    <div class="stat-card">
                        <small>TOTAL MAHASISWA</small>
                        <div class="stat-number"><?= $totalStudents ?></div>
                    </div>
                    <div class="stat-card">
                        <small>TOTAL DOSEN</small>
                        <div class="stat-number" style="color:var(--pixel-cyan);"><?= $totalLecturers ?></div>
                    </div>
                    <div class="stat-card">
                        <small>MATAKULIAH</small>
                        <div class="stat-number" style="color:var(--pixel-accent);"><?= $totalCourses ?></div>
                    </div>
                    <div class="stat-card">
                        <small>JADWAL KULIAH</small>
                        <div class="stat-number" style="color:var(--pixel-orange);"><?= $totalSchedules ?></div>
                    </div>
                </div>

                <div class="pixel-card">
                    <h3>🏛️ Semester Akademik Aktif:</h3>
                    <p style="font-size:20px; font-weight:bold; color:var(--pixel-green);">
                        <?= $activeYear ? htmlspecialchars($activeYear['year_name'] . ' - Semester ' . $activeYear['semester']) : 'Belum Ditentukan' ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- TAB 2: TAHUN AKADEMIK -->
            <?php if($tab === 'academic'): ?>
                <div class="pixel-card">
                    <h3>🏛️ Tambah Semester & Tahun Akademik</h3>
                    <form method="POST" style="display:flex; gap:10px;">
                        <input type="text" name="year_name" placeholder="Tahun Akademik (misal: 2026/2027)" required style="flex:1;">
                        <select name="semester" required style="width:150px;">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                        <button type="submit" name="add_academic_year" class="btn-action" style="background:#50fa7b;">+ Tambah</button>
                    </form>
                </div>

                <div class="pixel-card">
                    <h3>📜 Kelola Tahun Akademik SIAKAD</h3>
                    <table class="table-pixel">
                        <tr>
                            <th>Tahun Akademik</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Aksi Aktivasi</th>
                        </tr>
                        <?php foreach($academicYears as $ay): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($ay['year_name']) ?></strong></td>
                            <td><?= htmlspecialchars($ay['semester']) ?></td>
                            <td><span class="<?= $ay['is_active'] ? 'badge-active' : 'badge-inactive' ?>"><?= $ay['is_active'] ? 'AKTIF' : 'NON-AKTIF' ?></span></td>
                            <td>
                                <?php if(!$ay['is_active']): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="year_id" value="<?= $ay['id'] ?>">
                                        <button type="submit" name="set_academic_year" class="btn-action" style="background:var(--pixel-cyan); padding:2px 8px;">Aktifkan</button>
                                    </form>
                                <?php else: ?>
                                    <small style="color:#50fa7b;">🟢 Berjalan</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>

            <!-- TAB 3: MASTER MATAKULIAH -->
            <?php if($tab === 'courses'): ?>
                <div class="pixel-card">
                    <h3>📚 Tambah Matakuliah Baru</h3>
                    <form method="POST" style="display:grid; grid-template-columns: 1fr 2fr 1fr; gap:10px;">
                        <input type="text" name="course_code" placeholder="Kode Matkul (misal: IF101)" required>
                        <input type="text" name="course_name" placeholder="Nama Matakuliah" required>
                        <input type="number" name="sks" placeholder="SKS (1-6)" min="1" max="6" required>
                        <input type="text" name="prodi" placeholder="Program Studi" required>
                        <input type="number" name="semester" placeholder="Semester (1-8)" min="1" max="8" required>
                        <button type="submit" name="add_course" class="btn-action" style="background:#50fa7b; grid-column: span 3;">Simpan Matakuliah</button>
                    </form>
                </div>

                <div class="pixel-card">
                    <h3>📜 Kurikulum Matakuliah Kampus</h3>
                    <table class="table-pixel">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Matakuliah</th>
                            <th>SKS</th>
                            <th>Prodi</th>
                            <th>Sem</th>
                            <th>Aksi</th>
                        </tr>
                        <?php foreach($coursesList as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['course_code']) ?></strong></td>
                            <td><?= htmlspecialchars($c['course_name']) ?></td>
                            <td><?= $c['sks'] ?> SKS</td>
                            <td><?= htmlspecialchars($c['prodi']) ?></td>
                            <td>Sem <?= $c['semester'] ?></td>
                            <td><a href="?tab=courses&delete_course=<?= $c['id'] ?>" class="btn-action" style="background:#ff5555; color:#fff;" onclick="return confirm('Hapus matakuliah ini?')">Hapus</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>

            <!-- TAB 4: JADWAL PERKULIAHAN -->
            <?php if($tab === 'schedules'): ?>
                <div class="pixel-card">
                    <h3>📅 Buat Jadwal Kuliah Baru</h3>
                    <form method="POST" style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div>
                            <label>Matakuliah:</label>
                            <select name="course_id" required>
                                <?php foreach($coursesList as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label>Dosen Pengampu:</label>
                            <select name="lecturer_id" required>
                                <?php foreach($allLecturers as $l): ?>
                                    <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label>Hari:</label>
                            <select name="day" required>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>
                        <div>
                            <label>Jam Mulai & Selesai:</label>
                            <div style="display:flex; gap:5px;">
                                <input type="time" name="time_start" required>
                                <input type="time" name="time_end" required>
                            </div>

                            <label>Ruangan Kelas:</label>
                            <input type="text" name="room" placeholder="Gedung / Ruang (misal: R.301)" required>

                            <label>Kelas Mahasiswa:</label>
                            <input type="text" name="class_name" placeholder="Kelas (misal: IF-A Pagi)" required>
                        </div>
                        <button type="submit" name="add_schedule" class="btn-action" style="background:#50fa7b; grid-column: span 2;">Terbitkan Jadwal Kuliah</button>
                    </form>
                </div>

                <div class="pixel-card">
                    <h3>📜 Jadwal Perkuliahan Terjadwal</h3>
                    <table class="table-pixel">
                        <tr>
                            <th>Hari & Waktu</th>
                            <th>Matakuliah</th>
                            <th>Dosen</th>
                            <th>Ruang / Kelas</th>
                            <th>Hapus</th>
                        </tr>
                        <?php foreach($schedulesList as $s): ?>
                        <tr>
                            <td><strong><?= $s['day'] ?></strong><br><small><?= date('H:i', strtotime($s['time_start'])) ?> - <?= date('H:i', strtotime($s['time_end'])) ?></small></td>
                            <td><?= htmlspecialchars($s['course_name']) ?><br><small style="color:var(--pixel-cyan);"><?= htmlspecialchars($s['course_code']) ?></small></td>
                            <td><?= htmlspecialchars($s['lecturer_name']) ?></td>
                            <td>Ruang: <strong><?= htmlspecialchars($s['room']) ?></strong><br>Kelas: <?= htmlspecialchars($s['class_name']) ?></td>
                            <td><a href="?tab=schedules&delete_schedule=<?= $s['id'] ?>" class="btn-action" style="background:#ff5555; color:#fff;" onclick="return confirm('Hapus jadwal ini?')">🗑️</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>

            <!-- TAB 5: MANAJEMEN USER & RESET PASSWORD -->
            <?php if($tab === 'users'): ?>
                <div class="pixel-card">
                    <h3>➕ Pendaftaran Akun Resmi (Mahasiswa / Dosen)</h3>
                    <form method="POST">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                            <div>
                                <select name="role" required>
                                    <option value="student">Mahasiswa</option>
                                    <option value="lecturer">Dosen</option>
                                </select>
                                <input type="text" name="name" placeholder="Nama Lengkap" required>
                                <input type="email" name="email" placeholder="Email Kampus" required>
                                <input type="password" name="password" placeholder="Password" required>
                            </div>
                            <div>
                                <input type="text" name="identity_code" placeholder="NIM / NIDN" required>
                                <input type="text" name="prodi" placeholder="Program Studi" required>
                                <input type="text" name="class_name" placeholder="Kelas (Mahasiswa)">
                                <input type="text" name="subject" placeholder="Matkul Pengampu (Dosen)">
                            </div>
                        </div>
                        <button type="submit" name="add_user_by_admin" class="btn-action" style="background:#50fa7b; width:100%; margin-top:8px;">Daftarkan User</button>
                    </form>
                </div>

                <div class="pixel-card">
                    <div style="display:flex; gap:10px; margin-bottom:10px;">
                        <a href="?tab=users&subtab=student" class="subtab-btn <?= $subtab==='student'?'active':'inactive' ?>">Mahasiswa (<?= $totalStudents ?>)</a>
                        <a href="?tab=users&subtab=lecturer" class="subtab-btn <?= $subtab==='lecturer'?'active':'inactive' ?>">Dosen (<?= $totalLecturers ?>)</a>
                    </div>

                    <table class="table-pixel">
                        <tr>
                            <th>Status</th>
                            <th>Nama & Identitas</th>
                            <th>Reset Password</th>
                            <th>Aksi Status</th>
                            <th>Hapus</th>
                        </tr>
                        <?php foreach($usersList as $u): ?>
                        <tr>
                            <td><?= $u['is_online'] ? '🟢 Online' : '🔴 Offline' ?></td>
                            <td><strong><?= htmlspecialchars($u['name']) ?></strong><br><small><?= htmlspecialchars($u['identity_code']) ?> | <?= htmlspecialchars($u['prodi']) ?></small></td>
                            <td>
                                <form method="POST" style="display:flex; gap:2px;">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="current_subtab" value="<?= $subtab ?>">
                                    <input type="password" name="new_password" placeholder="Pass Baru..." required style="padding:2px; font-size:12px;">
                                    <button type="submit" name="reset_user_password" class="btn-action" style="background:#ffb86c; font-size:11px; padding:2px 4px;">Reset</button>
                                </form>
                            </td>
                            <td><a href="?tab=users&subtab=<?= $subtab ?>&toggle_status=<?= $u['id'] ?>&current=<?= $u['status'] ?>" class="btn-action" style="background:<?= $u['status']==='active'?'#ff5555':'#50fa7b' ?>;"><?= $u['status']==='active'?'Banned':'Pulihkan' ?></a></td>
                            <td><a href="?tab=users&subtab=<?= $subtab ?>&delete_user=<?= $u['id'] ?>" class="btn-action" style="background:#ff5555; color:#fff;" onclick="return confirm('Hapus permanen?')">🗑️</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>

            <!-- TAB 6: EVENT & RUNNING TEXT -->
            <?php if($tab === 'events'): ?>
                <div class="pixel-card">
                    <h3>📢 Upload Berita & Pengumuman Kampus</h3>
                    <form method="POST">
                        <select name="category" required>
                            <option value="seminar">Seminar</option>
                            <option value="workshop">Workshop</option>
                            <option value="lomba">Lomba</option>
                        </select>
                        <input type="text" name="title" placeholder="Judul Berita" required>
                        <textarea name="description" rows="3" placeholder="Isi Pengumuman..." required></textarea>
                        <input type="date" name="event_date" required>
                        <button type="submit" name="add_event" class="btn-action" style="background:#50fa7b; width:100%; margin-top:8px;">Publikasikan</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if($tab === 'announcement'): ?>
                <div class="pixel-card">
                    <h3>📺 Running Text Website Depan</h3>
                    <form method="POST">
                        <textarea name="announcement_msg" rows="3" required><?= htmlspecialchars($activeAnnounce) ?></textarea>
                        <button type="submit" name="save_announcement" class="btn-action" style="background:#50fa7b; width:100%; margin-top:8px;">Simpan Pengumuman</button>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>