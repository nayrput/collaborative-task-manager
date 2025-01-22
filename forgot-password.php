<?php
require 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Cek apakah email terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $reset_token = bin2hex(random_bytes(16));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Simpan token reset di database
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['id'], $reset_token, $expires_at);
            $stmt->execute();

            // Kirim email dengan tautan reset
            $reset_link = "http://localhost/collaborative-task-manager php/reset-password.php?token=$reset_token";
            $subject = "Reset Password Anda";
            $message = "Klik tautan berikut untuk mereset password Anda: $reset_link";
            $headers = "From: no-reply@example.com";

            if (mail($email, $subject, $message, $headers)) {
                $success = 'Tautan reset password telah dikirim ke email Anda.';
            } else {
                $error = 'Gagal mengirim email. Coba lagi nanti.';
            }
        } else {
            $error = 'Email tidak ditemukan.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <link rel="stylesheet" href="assets/css/pages/forgot-password.css">
</head>
<body>
    <div class="forgot-password-container">
        <h1>Lupa Password</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST" action="forgot-password.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Kirim Tautan Reset</button>
        </form>
    </div>
    
</body>
</html>
