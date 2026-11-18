<?php
/**
 * FILE KONFIGURASI KONEKSI DATABASE
 * Sesuaikan dengan konfigurasi database Anda
 */

// Konfigurasi Database
$db_host = 'localhost';      // Host database
$db_user = 'root';           // Username database
$db_pass = '';               // Password database
$db_name = 'prediksi_kelulusan_db';  // Nama database

// Buat koneksi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if($conn->connect_error){
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>