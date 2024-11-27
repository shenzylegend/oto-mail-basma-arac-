<?php
session_start();

// Kullanıcı adı ve şifre
$admin_user = 'admin';
$admin_pass = 'layneshenzy';

// Giriş kontrolü
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Giriş başarı durumu
    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit();
    } else {
        // İzinsiz giriş bildirimini (sadece loglama) yapabiliriz.
        $error = 'Geçersiz kullanıcı adı veya şifre.';
        // İzinsiz giriş bilgilerini log dosyasına kaydedebilirsiniz.
        $log_file = 'logs/logins.txt';
        $log_message = "İzinsiz giriş denemesi. Kullanıcı Adı: $username, IP: " . $_SERVER['REMOTE_ADDR'] . " - Zaman: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            transform: translateY(30px);
            animation: slideUp 0.5s ease-in-out forwards;
        }

        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-container button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 20px;
            font-size: 14px;
            animation: errorAnimation 0.5s ease-out forwards;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes errorAnimation {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="shenzy.png" alt="Logo" style="width: 150px; height:150px; margin-bottom: 20px; animation: fadeIn 1s; border-radius:50%;">
        <h2>Admin Girişi</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label for="username">Kullanıcı Adı:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Şifre:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Giriş Yap</button>
        </form>
    </div>

</body>
</html>