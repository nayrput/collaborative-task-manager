<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Kosongkan jika tidak ada password
$database = 'ryan_task_db'; // Nama database Anda

$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
