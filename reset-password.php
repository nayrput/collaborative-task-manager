<?php
require 'config/database.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password) || empty($confirm_password)) {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error = 'Password harus minimal 8 karakter, memiliki huruf besar, huruf kecil, angka, dan karakter khusus.';
    } else {
        // Validasi token reset
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $reset = $result->fetch_assoc();

            // Perbarui password pengguna
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $reset['user_id']);
            $stmt->execute();

            // Hapus token reset
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();

            $success = 'Password berhasil diperbarui. Anda dapat login dengan password baru.';
        } else {
            $error = 'Token reset tidak valid atau sudah kedaluwarsa.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/pages/reset-password.css">
</head>
<body>
    <div class="reset-password-container">
        <h1>Reset Password</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST" action="reset-password.php">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <label for="password">Password Baru:</label>
            <input type="password" id="password" name="password" required>
            <label for="confirm_password">Konfirmasi Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
