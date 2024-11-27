<?php
// admin panelinde SMTP ayarlarını güncellemek için
$config = include('config.php');

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