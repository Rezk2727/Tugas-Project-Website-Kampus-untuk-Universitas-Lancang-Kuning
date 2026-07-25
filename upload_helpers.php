<?php
// Algoritma Penanganan Upload Berkas Multi-Format
function processUpload($file, $targetDir) {
    // 1. Ekstrak ekstensi file dan ubah ke huruf kecil
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 2. Daftar format yang diizinkan (fleksibel)
    $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    $allowedVideos = ['mp4', 'mkv', 'avi', 'mov', 'webm'];
    
    // 3. Tentukan tipe file
    $fileType = null;
    if (in_array($fileExtension, $allowedImages)) {
        $fileType = 'image';
    } elseif (in_array($fileExtension, $allowedVideos)) {
        $fileType = 'video';
    } else {
        return ['status' => false, 'message' => 'Format file tidak didukung!'];
    }

    // 4. Generate nama file unik berbasis timestamp agar tidak pernah saling menimpa
    $newFileName = uniqid('pixel_media_', true) . '.' . $fileExtension;
    $targetPath = $targetDir . $newFileName;

    // 5. Pindahkan file dari temporary direktori ke direktori tujuan
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'status' => true,
            'fileName' => $newFileName,
            'fileType' => $fileType
        ];
    }

    return ['status' => false, 'message' => 'Gagal mengunggah berkas.'];
}
?>