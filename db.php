<?php
// Algoritma Koneksi Database PDO
$host     = "localhost";
$db_name  = "kampus_pixel";
$username = "root";
$password = ""; // Default XAMPP kosong

try {
    // Membuat instance PDO untuk interaksi aman dengan MySQL (Mencegah SQL Injection)
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lempar exception jika terjadi error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query otomatis berbentuk array asosiatif
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Menggunakan native prepared statements
    ]);
} catch (PDOException $e) {
    // Tangkap error jika koneksi gagal
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>