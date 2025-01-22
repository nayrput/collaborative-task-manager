<?php
include 'config/database.php';

if ($conn) {
    echo "Koneksi berhasil ke database ryan_task_db!";
} else {
    echo "Koneksi gagal.";
}
?>
