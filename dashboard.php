<?php
session_start();
require 'config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Proses aksi tugas (Hapus atau Tandai Selesai)
if (isset($_GET['action'], $_GET['task_id'])) {
    $task_id = intval($_GET['task_id']);
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
        $stmt->execute();
    } elseif ($_GET['action'] === 'complete') {
        $stmt = $conn->prepare("UPDATE tasks SET status = 'Selesai' WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
        $stmt->execute();
    }
    header('Location: dashboard.php');
    exit;
}

// Ambil semua tugas pengguna
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'tenggat_waktu';
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$user_id];

if ($filter_status) {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY $order_by";
$stmt = $conn->prepare($sql);
if (count($params) == 2) {
    $stmt->bind_param("is", $params[0], $params[1]);
} else {
    $stmt->bind_param("i", $params[0]);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/pages/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Dashboard</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <a href="logout.php" class="logout-btn">Logout</a>

        <!-- Tambah Tugas -->
        <a href="task.php" class="add-task-btn">Tambah Tugas Baru</a>

        <!-- Filter dan Sort -->
        <div class="filter-sort">
            <form method="GET" action="dashboard.php">
                <label for="filter_status">Filter Status:</label>
                <select id="filter_status" name="filter_status">
                    <option value="">Semua</option>
                    <option value="Selesai" <?php echo $filter_status == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="Belum Selesai" <?php echo $filter_status == 'Belum Selesai' ? 'selected' : ''; ?>>Belum Selesai</option>
                </select>

                <label for="order_by">Urutkan:</label>
                <select id="order_by" name="order_by">
                    <option value="tenggat_waktu" <?php echo $order_by == 'tenggat_waktu' ? 'selected' : ''; ?>>Tenggat Waktu</option>
                    <option value="created_at" <?php echo $order_by == 'created_at' ? 'selected' : ''; ?>>Tanggal Dibuat</option>
                </select>
                <button type="submit">Terapkan</button>
            </form>
        </div>

        <!-- Tabel Tugas -->
        <table>
            <thead>
                <tr>
                    <th>Nama Tugas</th>
                    <th>Status</th>
                    <th>Tenggat Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($task = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['judul_tugas']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td><?php echo htmlspecialchars($task['tenggat_waktu']); ?></td>
                        <td>
                            <a href="task.php?task_id=<?php echo $task['id']; ?>">Edit</a>
                            <a href="dashboard.php?action=complete&task_id=<?php echo $task['id']; ?>">Tandai Selesai</a>
                            <a href="dashboard.php?action=delete&task_id=<?php echo $task['id']; ?>" onclick="return confirm('Apakah Anda yakin?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script>
// Waktu peringatan sebelum logout (4 menit 30 detik)
const warningTime = 270000; // 4 menit 30 detik dalam milidetik
const logoutTime = 300000; // 5 menit dalam milidetik

// Tampilkan peringatan sebelum logout otomatis
setTimeout(() => {
    alert("Anda akan logout otomatis dalam 30 detik jika tidak ada aktivitas.");
}, warningTime);

// Redirect ke halaman logout setelah 5 menit
setTimeout(() => {
    window.location.href = "logout.php";
}, logoutTime);
</script>

</body>
</html>
