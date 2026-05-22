<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email System Test</h2>";

// Test 1: Check PHPMailer
echo "<h3>1. Checking PHPMailer:</h3>";
$phpmailer_path = 'PHPMailer/src/PHPMailer.php';
if (file_exists($phpmailer_path)) {
    echo "✅ PHPMailer found at: $phpmailer_path<br>";
    require_once $phpmailer_path;
    echo "✅ PHPMailer loaded successfully<br>";
} else {
    echo "❌ PHPMailer NOT found at: $phpmailer_path<br>";
    echo "Current directory: " . __DIR__ . "<br>";
    echo "Files in directory: <pre>" . print_r(scandir('.'), true) . "</pre>";
}

// Test 2: Try sending a test email
echo "<h3>2. Sending Test Email:</h3>";

// Update these with YOUR credentials
$test_username = 'your-email@gmail.com';  // 🔴 YOUR EMAIL
$test_password = 'your-app-password';      // 🔴 APP PASSWORD

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Shows detailed output
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $test_username;
    $mail->Password = $test_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom($test_username, 'Test');
    $mail->addAddress($test_username); // Send to yourself
    $mail->Subject = 'Test Email from Localhost';
    $mail->Body = 'This is a test email from your localhost setup!';
    
    if ($mail->send()) {
        echo "✅ Test email sent successfully!<br>";
    } else {
        echo "❌ Failed to send test email<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $mail->ErrorInfo . "<br>";
}
?>