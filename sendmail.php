<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if PHPMailer files exist
$has_phpmailer = true;
if (!file_exists('PHPMailer/src/Exception.php') || !file_exists('PHPMailer/src/PHPMailer.php') || !file_exists('PHPMailer/src/SMTP.php')) {
    error_log("PHPMailer files not found in the root directory.");
    $has_phpmailer = false;
} else {
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name  = trim(strip_tags($_POST['name']  ?? ''));
    $email = trim(strip_tags($_POST['email'] ?? ''));
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    $country_code = trim(strip_tags($_POST['country_code'] ?? '+91'));

    $name  = substr($name,  0, 100);
    $email = substr($email, 0, 100);
    $phone = substr($phone, 0, 20);

    $phone_digits = preg_replace('/\D/', '', $phone);
    if (strlen($phone_digits) < 7 || strlen($phone_digits) > 15) {
        http_response_code(400);
        echo "Invalid phone number.";
        exit();
    }

    $full_phone = $country_code . " " . $phone_digits;

    $spam_patterns = [
        '/https?:\/\//i',
        '/yandex\./i',
        '/t\.me\//i',
        '/bit\.ly\//i',
        '/wa\.me\//i',
        '/poll\//i',
        '/sex/i',
        '/dating/i',
        '/casino/i',
        '/loan.*whatsapp/i',
    ];
    foreach ($spam_patterns as $pattern) {
        if (preg_match($pattern, $name) || preg_match($pattern, $email)) {
            header("Location: thankyou.html");
            exit();
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Invalid email address.";
        exit();
    }

    if (!preg_match('/^[\p{L}\s.\-\']{2,100}$/u', $name)) {
        http_response_code(400);
        echo "Invalid name.";
        exit();
    }

    $safe_name  = htmlspecialchars($name,  ENT_QUOTES, 'UTF-8');
    $safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safe_phone = htmlspecialchars($full_phone, ENT_QUOTES, 'UTF-8');

    // 1. Log to local file as backup
    $log_data = "Name: $safe_name | Email: $safe_email | Phone: $safe_phone\n";
    file_put_contents('debug-log.txt', $log_data, FILE_APPEND);

    // 2. Send to Google Sheets FIRST
    $webhook_url = "https://script.google.com/macros/s/AKfycbxV-JrUsNd5dBfF_S5zFRlW4Vdf9vPmM_rNFeP5FK5bJNy5DJUDp-vWPLzYfjON8yZI/exec";
    $payload = json_encode([
        "name"         => $safe_name,
        "email"        => $safe_email,
        "phone"        => "'" . $safe_phone,
        "config"       => "Not Specified",
        "source"       => "Raymond Sea View Mahim Website",
        "submitted_at" => date("Y-m-d H:i:s")
    ]);

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    curl_close($ch);

    // 3. Try to send email
    $email_sent = false;

    if ($has_phpmailer) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'rock83694@gmail.com';
            $mail->Password   = 'eigvmkokcvihyboz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('rock83694@gmail.com', 'Website Lead');
            $mail->addAddress('salesconnect.teambb@gmail.com');
            $mail->addAddress('thegrowthmonks@gmail.com');
            $mail->addAddress('tgmshravan@gmail.com');

            $mail->isHTML(true);
            $mail->Subject = 'New Lead - Raymond Sea View Mahim';
            $mail->Body = "
                <h2>New Lead Submission</h2>
                <p><strong>Name:</strong> {$safe_name}</p>
                <p><strong>Email:</strong> {$safe_email}</p>
                <p><strong>Phone:</strong> {$safe_phone}</p>
                <p><strong>Source:</strong> Raymond Sea View Mahim Website</p>
            ";

            $mail->send();
            $email_sent = true;

        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
        }
    }

    if (!$email_sent) {
        // Fallback to standard PHP mail()
        $to = "salesconnect.teambb@gmail.com, thegrowthmonks@gmail.com, tgmshravan@gmail.com";
        $subject = "New Lead - Raymond Sea View Mahim";
        $message = "
            <h2>New Lead Submission</h2>
            <p><strong>Name:</strong> {$safe_name}</p>
            <p><strong>Email:</strong> {$safe_email}</p>
            <p><strong>Phone:</strong> {$safe_phone}</p>
            <p><strong>Source:</strong> Raymond Sea View Mahim Website</p>
        ";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: Website Lead <noreply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
        
        mail($to, $subject, $message, $headers);
    }

    // Finally redirect
    header("Location: thankyou.html");
    exit();

} else {
    http_response_code(405);
    header("Location: index.html");
    exit();
}