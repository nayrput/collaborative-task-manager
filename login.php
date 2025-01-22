<?php
session_start();
require 'config/database.php';

// Inisialisasi variabel
$error = '';

if (isset($_GET['timeout']) && $_GET['timeout'] == 'true') {
    echo "<p class='info'>Sesi Anda telah berakhir karena tidak aktif.</p>";
}

if (isset($_GET['logged_out']) && $_GET['logged_out'] == 'true') {
    echo "<p class='info'>Anda telah berhasil logout.</p>";
}


// Proses form login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    // Validasi input
    if (empty($username_or_email) || empty($password)) {
        $error = 'Semua field wajib diisi.';
    } else {
        // Cek data di database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set sesi login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Arahkan ke dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Username atau email tidak ditemukan.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label for="username_or_email">Username atau Email:</label>
            <input type="text" id="username_or_email" name="username_or_email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <p><a href="forgot-password.php">Lupa Password?</a></p>
    </div>
</body>
</html>
