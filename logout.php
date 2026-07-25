<?php
// ALGORITMA LOGOUT TERISOLASI
$role = $_GET['role'] ?? 'student';

if ($role === 'admin') {
    session_name('KAMPUS_ADMIN');
    session_start();
    session_unset();
    session_destroy();
    header("Location: auth_admin.php");
} elseif ($role === 'lecturer') {
    session_name('KAMPUS_DOSEN');
    session_start();
    session_unset();
    session_destroy();
    header("Location: auth_dosen.php");
} else {
    session_name('KAMPUS_MAHASISWA');
    session_start();
    session_unset();
    session_destroy();
    header("Location: auth_mahasiswa.php");
}
exit;
?>