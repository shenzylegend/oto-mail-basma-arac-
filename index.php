<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Türkiye'nin saat dilimi
date_default_timezone_set('Europe/Istanbul');

$config = include('config.php');

// SMTP ayarlarını güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['host'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $port = $_POST['port'];
    $encryption = $_POST['encryption'];

    $configData = "<?php\nreturn [\n" .
        "    'host' => '$host',\n" .
        "    'username' => '$username',\n" .
        "    'password' => '$password',\n" .
        "    'port' => $port,\n" .
        "    'encryption' => '$encryption',\n" .
        "];\n";

    if (file_put_contents('config.php', $configData)) {
        echo "<script>alert('SMTP ayarları başarıyla güncellendi!');</script>";
    } else {
        echo "<script>alert('SMTP ayarları güncellenemedi!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <style>
        /* Bildirim için stil */
        .notification {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            font-size: 16px;
            border-radius: 5px;
            z-index: 9999;
            display: none; /* Başlangıçta gizli */
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .notification.show {
            display: block;
            opacity: 1;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Admin Paneli</h1>

    <!-- SMTP Ayarları -->
    <h2>SMTP Ayarları</h2>
    <form action="index.php" method="POST">
        <label for="host">Sunucu:</label>
        <input type="text" id="host" name="host" value="<?php echo $config['host']; ?>" required>

        <label for="username">Kullanıcı Adı:</label>
        <input type="text" id="username" name="username" value="<?php echo $config['username']; ?>" required>

        <label for="password">Şifre:</label>
        <input type="password" id="password" name="password" value="<?php echo $config['password']; ?>" required>

        <label for="port">Port:</label>
        <input type="number" id="port" name="port" value="<?php echo $config['port']; ?>" required>

        <label for="encryption">Şifreleme:</label>
        <select id="encryption" name="encryption">
            <option value="tls" <?php echo $config['encryption'] == 'tls' ? 'selected' : ''; ?>>TLS</option>
            <option value="ssl" <?php echo $config['encryption'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
        </select>

        <button type="submit">Ayarları Güncelle</button>
    </form>

    <h2>Mail Gönderme</h2>
    <form id="mailForm" action="send_mail.php" method="POST" enctype="multipart/form-data">
        <label for="from_name">Gönderen Adı:</label>
        <input type="text" id="from_name" name="from_name" required>

        <label for="from_email">Gönderen E-posta:</label>
        <input type="email" id="from_email" name="from_email" required>

        <label for="to_email">Alıcı E-posta(lar):</label>
        <textarea id="to_email" name="to_email" rows="3" placeholder="Virgülle ayrılmış liste" required></textarea>

        <label for="subject">Konu:</label>
        <input type="text" id="subject" name="subject" required>

        <label for="body">Mesaj:</label>
        <textarea id="body" name="body" rows="6" required></textarea>

        <label for="attachment">Dosya Ekle:</label>
        <input type="file" id="attachment" name="attachment">

        <button type="submit">Gönder</button>
    </form>

    <div id="statusMessage"></div> <!-- Status Message: Gönderiliyor, Gönderildi -->

    <h2>Gönderilen Mailler</h2>
    <div class="mail-log">
    <?php
    
    if (file_exists('logs/mail_log.txt')) {
        $logs = file_get_contents('logs/mail_log.txt');
        $log_entries = explode("\n", $logs);
        
        // Her bir log kaydını işler
        if (count($log_entries) > 0) {
            echo "<ul>";
            foreach ($log_entries as $log_entry) {
                if (!empty($log_entry)) {
                    $log_data = json_decode($log_entry, true);
                    // JSON verisi düzgünse, mail bilgilerini göster
                    if (isset($log_data['from'], $log_data['to'], $log_data['subject'], $log_data['timestamp'])) {

                        $formatted_time = date('d-m-Y H:i:s', strtotime($log_data['timestamp']));
                        echo "<li>";
                        echo "<strong>Gönderen:</strong> " . htmlspecialchars($log_data['from']) . "<br>";
                        echo "<strong>Alıcı:</strong> " . htmlspecialchars($log_data['to']) . "<br>";
                        echo "<strong>Konu:</strong> " . htmlspecialchars($log_data['subject']) . "<br>";
                        echo "<strong>Zaman:</strong> " . $formatted_time . "<br>";
                        echo "</li><hr>";
                    }
                }
            }
            echo "</ul>";
        }
    } else {
        echo "Henüz gönderilmiş mail yok.";
    }
    ?>
    </div>

</div>

<script>
$(document).ready(function() {
    $("#mailForm").on("submit", function(event) {
        event.preventDefault(); // Formu normal şekilde göndermemek için
        var formData = new FormData(this);

        // Mail gönderiliyor yazısı ile bildirim göster
        showNotification('Mail gönderiliyor...', 'info'); 

        $.ajax({
            url: 'send_mail.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var responseObj = JSON.parse(response);
                if (responseObj.status == 'success') {
                    showNotification('Mail başarıyla gönderildi!', 'success');
                } else {
                    showNotification('Mail gönderilemedi: ' + responseObj.message, 'error');
                }
            },
            error: function() {
                showNotification('Bir hata oluştu.', 'error');
            }
        });
    });

    // Bildirim gösterme fonksiyonu
    function showNotification(message, type) {
        // Bildirim alanını ayarlama
        var notification = $('<div class="notification"></div>');
        notification.text(message);

        // Notification tipine göre renk ayarlama
        if (type == 'success') {
            notification.css('background-color', '#4CAF50');
        } else if (type == 'error') {
            notification.css('background-color', '#f44336');
        } else
          {
            notification.css('background-color', '#2196F3');
        }
// emeğe saygı coded shenzylayne
        // Bildirimi ekleme
        $('body').append(notification);
        
        // Bildirimi göstermek
        notification.addClass('show');
        
        // 3 saniye sonra gizle
        setTimeout(function() {
            notification.removeClass('show');
            notification.remove();
        }, 3000);
    }
});
</script>

</body>
</html>