<?php
include 'includes/header.php'; // Include header file
require 'config/database.php'; // Koneksi ke database

// Inisialisasi variabel
$error = '';
$success = '';

// Proses form pendaftaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($nama) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error = 'Password harus memiliki huruf besar, kecil, angka, karakter khusus, dan minimal 8 karakter.';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak sesuai.';
    } else {
        // Cek apakah username atau email sudah digunakan
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = 'Username atau email sudah terdaftar.';
        } else {
            // Simpan data ke database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama, email, username, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $email, $username, $hashed_password);
            if ($stmt->execute()) {
                $success = 'Pendaftaran berhasil. Silakan login.';
                header('Location: login.php');
                exit;
            } else {
                $error = 'Terjadi kesalahan, silakan coba lagi.';
            }
        }
    }
}
?>

<div class="signup-container">
    <h1>Daftar Akun Baru</h1>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php elseif ($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
    <form action="signup.php" method="POST">
        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Konfirmasi Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Daftar</button>
    </form>
</div>

<?php
include 'includes/footer.php'; // Include footer file
?>
