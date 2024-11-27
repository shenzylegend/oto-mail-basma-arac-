<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// SMTP ayarlarını config.php dosyasından al
$config = include('config.php');

// Mail verilerini al
$from_name = $_POST['from_name'];
$from_email = $_POST['from_email'];
$to_emails = explode(',', $_POST['to_email']);
$subject = $_POST['subject'];
$body = $_POST['body'];

$uploaded_file = '';
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
    $target_dir = "uploads/";
    $uploaded_file = $target_dir . basename($_FILES["attachment"]["name"]);
    move_uploaded_file($_FILES["attachment"]["tmp_name"], $uploaded_file);
}

$mail = new PHPMailer(true);
try {

    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['encryption'];
    $mail->Port = $config['port'];

    
    $mail->setFrom($from_email, $from_name);

    
    foreach ($to_emails as $to_email) {
        $mail->addAddress(trim($to_email));
    }

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    if ($uploaded_file) {
        $mail->addAttachment($uploaded_file);
    }

    if ($mail->send()) {
       
        $log_entry = [
            'from' => $from_name,
            'to' => implode(', ', $to_emails),
            'subject' => $subject,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $log = json_encode($log_entry) . "\n";
        file_put_contents('logs/mail_log.txt', $log, FILE_APPEND);

        echo json_encode(['status' => 'success', 'message' => 'Mail başarıyla gönderildi!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Mail gönderilemedi.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Mail gönderilemedi: ' . $mail->ErrorInfo]);
}
?>