<?php
session_start();
require 'config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$judul_tugas = '';
$deskripsi = '';
$tenggat_waktu = '';

// Jika task_id diberikan, ambil data tugas untuk diedit
if ($task_id) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
        $judul_tugas = $task['judul_tugas'];
        $deskripsi = $task['deskripsi'];
        $tenggat_waktu = $task['tenggat_waktu'];
    } else {
        header('Location: dashboard.php');
        exit;
    }
}

// Proses Simpan Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_tugas = trim($_POST['judul_tugas']);
    $deskripsi = trim($_POST['deskripsi']);
    $tenggat_waktu = $_POST['tenggat_waktu'];

    if (!empty($judul_tugas) && !empty($tenggat_waktu)) {
        if ($task_id) {
            // Update tugas
            $stmt = $conn->prepare("UPDATE tasks SET judul_tugas = ?, deskripsi = ?, tenggat_waktu = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssii", $judul_tugas, $deskripsi, $tenggat_waktu, $task_id, $user_id);
        } else {
            // Tambah tugas baru
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, judul_tugas, deskripsi, tenggat_waktu, status) VALUES (?, ?, ?, ?, 'Belum Selesai')");
            $stmt->bind_param("isss", $user_id, $judul_tugas, $deskripsi, $tenggat_waktu);
        }
        $stmt->execute();
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $task_id ? 'Edit Tugas' : 'Tambah Tugas'; ?></title>
    <link rel="stylesheet" href="assets/css/pages/task.css">
</head>
<body>
    <div class="task-container">
        <h1><?php echo $task_id ? 'Edit Tugas' : 'Tambah Tugas'; ?></h1>
        <form method="POST" action="task.php<?php echo $task_id ? '?task_id=' . $task_id : ''; ?>">
            <label for="judul_tugas">Judul Tugas:</label>
            <input type="text" id="judul_tugas" name="judul_tugas" value="<?php echo htmlspecialchars($judul_tugas); ?>" required>

            <label for="deskripsi">Deskripsi:</label>
            <textarea id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($deskripsi); ?></textarea>

            <label for="tenggat_waktu">Tenggat Waktu:</label>
            <input type="date" id="tenggat_waktu" name="tenggat_waktu" value="<?php echo htmlspecialchars($tenggat_waktu); ?>" required>

            <button type="submit">Simpan</button>
        </form>
        <a href="dashboard.php" class="back-btn">Kembali ke Dashboard</a>
    </div>
</body>
</html>
