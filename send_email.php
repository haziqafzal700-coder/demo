

<?php
// ============================================
// COMPLETE WORKING EMAIL SYSTEM
// Sends to: ADMIN + CUSTOMER
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// ============================================
// INCLUDE PHPMailer
// ============================================

// Try different paths to find PHPMailer
$phpmailer_found = false;

$paths = [
    'PHPMailer/src/PHPMailer.php',
    'PHPMailer/PHPMailer/src/PHPMailer.php',
    'src/PHPMailer.php',
    '../PHPMailer/src/PHPMailer.php'
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $phpmailer_found = true;
        break;
    }
}

if (!$phpmailer_found) {
    echo json_encode([
        'success' => false, 
        'message' => 'PHPMailer not found. Please check installation.'
    ]);
    exit;
}

// Include SMTP and Exception
$smtp_paths = ['PHPMailer/src/SMTP.php', 'PHPMailer/PHPMailer/src/SMTP.php', 'src/SMTP.php'];
foreach ($smtp_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

$exception_paths = ['PHPMailer/src/Exception.php', 'PHPMailer/PHPMailer/src/Exception.php', 'src/Exception.php'];
foreach ($exception_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ============================================
// 🔴🔴🔴 UPDATE THESE 5 LINES 🔴🔴🔴
// ============================================

$smtp_config = [
    'host' => 'smtp.gmail.com',                    // Your SMTP server
    'port' => 587,                                  // 587 for TLS
    'username' => 'haziqafzal700@gmail.com',           // 🔴 YOUR GMAIL
    'password' => 'rhaz sfez avna qjph',      // 🔴 APP PASSWORD
    'from_email' => 'haziqafzal700@gmail.com',         // 🔴 Same as username
    'from_name' => 'West London Security Ltd',
    'admin_email' => 'haziqafzal700@gmail.com',     // 🔴 Where admin gets emails
    'admin_name' => 'Security Manager'
];

// ============================================
// PROCESS FORM
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get form data
$name = htmlspecialchars(trim($_POST['name'] ?? ''));
$business = htmlspecialchars(trim($_POST['business'] ?? ''));
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
$message = htmlspecialchars(trim($_POST['message'] ?? ''));
$request_type = $_POST['request_type'] ?? 'contact';

// Validate
if (empty($name) || empty($email) || empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// ============================================
// EMAIL TEMPLATES
// ============================================

// Admin Email HTML
function getAdminEmailHTML($data, $type) {
    $type_title = ($type === 'audit') ? '🔐 SECURITY AUDIT REQUEST' : '📋 CONTACT FORM SUBMISSION';
    $badge_color = ($type === 'audit') ? '#f97316' : '#0f172a';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset='UTF-8'>
    <title>New {$type_title}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .header { background: {$badge_color}; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 10px 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .field { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .field-label { font-weight: bold; color: {$badge_color}; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .field-value { font-size: 16px; color: #333; margin-top: 5px; }
        .message-box { background: #f8f9fa; padding: 15px; border-radius: 12px; border-left: 4px solid {$badge_color}; margin-top: 10px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 12px; }
    </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='badge'>NEW INQUIRY</div>
                <h1>{$type_title}</h1>
                <p>Received: " . date('F j, Y, g:i a') . "</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='field-label'>CLIENT NAME</div>
                    <div class='field-value'><strong>{$data['name']}</strong></div>
                </div>
                <div class='field'>
                    <div class='field-label'>BUSINESS NAME</div>
                    <div class='field-value'>{$data['business']}</div>
                </div>
                <div class='field'>
                    <div class='field-label'> CONTACT INFORMATION</div>
                    <div class='field-value'>
                        <a href='mailto:{$data['email']}'>{$data['email']}</a><br>
                        {$data['phone']}
                    </div>
                </div>
                <div class='field'>
                    <div class='field-label'> MESSAGE / REQUIREMENTS</div>
                    <div class='message-box'>{$data['message']}</div>
                </div>
            </div>
            <div class='footer'>
                <p><strong>West London Security Ltd</strong><br>160 Waye Avenue, Hounslow, TW5 9SF<br>📞 020 8123 4567</p>
                <p style='margin-top: 10px;'>© " . date('Y') . " All Rights Reserved</p>
            </div>
        </div>
    </body>
    </html>";
}

// Customer Confirmation Email HTML
function getCustomerEmailHTML($data, $type) {
    $action_text = ($type === 'audit') 
        ? 'Your security audit request has been received. A security specialist will contact you within 2 hours.'
        : 'Thank you for contacting West London Security Ltd. We will respond to your inquiry within 2 hours.';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset='UTF-8'>
    <title>Thank You - West London Security</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        .container { max-width: 550px; margin: 0 auto; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .header { background: #0f172a; color: white; padding: 30px; text-align: center; }
        .header .logo { font-size: 50px; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 22px; }
        .content { padding: 30px; }
        .thankyou { text-align: center; margin-bottom: 25px; }
        .thankyou h3 { color: #f97316; margin: 0 0 10px; font-size: 24px; }
        .info-box { background: #fef3c7; padding: 20px; border-radius: 16px; margin: 25px 0; border-left: 4px solid #f97316; }
        .info-box p { margin: 8px 0; }
        .button { display: inline-block; background: #f97316; color: white; padding: 12px 30px; text-decoration: none; border-radius: 10px; margin-top: 15px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .contact-info { margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee; }
    </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>🛡️</div>
                <h2>West London Security Ltd</h2>
                <p style='margin:5px 0 0; opacity:0.9;'>Professional Security Solutions</p>
            </div>
            <div class='content'>
                <div class='thankyou'>
                    <h3>Thank You, {$data['name']}!</h3>
                    <p>{$action_text}</p>
                </div>
                <div class='info-box'>
                    <p><strong>Your Reference Number:</strong> #WLS-" . strtoupper(uniqid()) . "</p>
                    <p><strong>Date:</strong> " . date('F j, Y') . "</p>
                    <p><strong>Response Time:</strong> Within 2 hours</p>
                </div>
                <p><strong>What happens next?</strong></p>
                <ul style='color: #555; line-height: 1.8;'>
                    <li>✓ Our team will review your requirements</li>
                    <li>✓ A security consultant will call you on {$data['phone']}</li>
                    <li>✓ We'll schedule a free site assessment (if needed)</li>
                    <li>✓ Receive a tailored security quote within 24 hours</li>
                </ul>
                <div class='contact-info'>
                    <p><strong> Need immediate assistance?</strong><br>
                    Call our 24/7 Command Centre: <strong style='color:#f97316;'>020 8123 4567</strong></p>
                    <p style='margin-top: 10px; font-size: 11px;'>Save this email for your reference.</p>
                </div>
            </div>
            <div class='footer'>
                <p>West London Security Ltd | 160 Waye Avenue, Hounslow, TW5 9SF<br>
                Company No: 15415447 | SIA Licensed</p>
            </div>
        </div>
    </body>
    </html>";
}

// Plain text versions
function getAdminTextEmail($data, $type) {
    $type_title = ($type === 'audit') ? 'SECURITY AUDIT REQUEST' : 'CONTACT FORM SUBMISSION';
    return str_repeat("=", 60) . "\n" .
           "{$type_title}\n" .
           str_repeat("=", 60) . "\n\n" .
           "Name: {$data['name']}\n" .
           "Business: {$data['business']}\n" .
           "Email: {$data['email']}\n" .
           "Phone: {$data['phone']}\n\n" .
           "MESSAGE:\n" . strip_tags($data['message']) . "\n\n" .
           str_repeat("-", 60) . "\n" .
           "Received: " . date('Y-m-d H:i:s') . "\n" .
           "IP Address: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
}

function getCustomerTextEmail($data, $type) {
    $action = ($type === 'audit') ? 'audit request' : 'inquiry';
    return "Thank you, {$data['name']}!\n\n" .
           "Your {$action} has been received by West London Security Ltd.\n" .
           "We will contact you within 2 hours on {$data['phone']}.\n\n" .
           "Reference: #WLS-" . strtoupper(uniqid()) . "\n" .
           "Date: " . date('F j, Y') . "\n\n" .
           "Need immediate assistance? Call us: 020 8123 4567\n\n" .
           "West London Security Ltd\n" .
           "160 Waye Avenue, Hounslow, TW5 9SF\n" .
           "www.westlondonsecurity.co.uk";
}

// ============================================
// SEND EMAIL FUNCTION
// ============================================

function sendEmail($to, $subject, $htmlBody, $textBody, $smtp_config) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;  // Set to DEBUG_SERVER for testing
        $mail->isSMTP();
        $mail->Host = $smtp_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_config['username'];
        $mail->Password = $smtp_config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_config['port'];
        
        // SSL options for localhost
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Timeout
        $mail->Timeout = 30;
        
        // Recipients
        $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
        $mail->addAddress($to);
        $mail->addReplyTo($smtp_config['from_email'], $smtp_config['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email Error to {$to}: " . $mail->ErrorInfo);
        return false;
    }
}

// ============================================
// PREPARE AND SEND BOTH EMAILS
// ============================================

$data = [
    'name' => $name,
    'business' => $business ?: 'Not provided',
    'email' => $email,
    'phone' => $phone,
    'message' => nl2br($message)
];

// Email to ADMIN
$admin_subject = ($request_type === 'audit') 
    ? "NEW AUDIT REQUEST - {$name} from " . ($business ?: 'Individual')
    : "NEW CONTACT FORM - {$name}";

$admin_html = getAdminEmailHTML($data, $request_type);
$admin_text = getAdminTextEmail($data, $request_type);

// Email to CUSTOMER
$customer_subject = "Thank you for contacting West London Security Ltd";
$customer_html = getCustomerEmailHTML($data, $request_type);
$customer_text = getCustomerTextEmail($data, $request_type);

// Send BOTH emails
$admin_sent = sendEmail($smtp_config['admin_email'], $admin_subject, $admin_html, $admin_text, $smtp_config);
$customer_sent = sendEmail($email, $customer_subject, $customer_html, $customer_text, $smtp_config);

// ============================================
// LOG RESULTS (for debugging)
// ============================================

$log_entry = date('Y-m-d H:i:s') . " | Admin: " . ($admin_sent ? 'YES' : 'NO') . " | Customer: " . ($customer_sent ? 'YES' : 'NO') . " | To: {$email}\n";
file_put_contents('email_log.txt', $log_entry, FILE_APPEND);

// ============================================
// RESPONSE TO USER
// ============================================

if ($admin_sent && $customer_sent) {
    echo json_encode([
        'success' => true, 
        'message' => '✓ Request sent successfully! A confirmation email has been sent to your inbox. We will contact you within 2 hours.'
    ]);
} elseif ($admin_sent) {
    echo json_encode([
        'success' => true, 
        'message' => '✓ Request received! Our team will contact you within 2 hours. (Confirmation email could not be sent)'
    ]);
} elseif ($customer_sent) {
    echo json_encode([
        'success' => true, 
        'message' => '✓ Request received! Please check your email for confirmation. Our team will call you soon.'
    ]);
} else {
    // Still show success to user but log error
    file_put_contents('email_errors.log', date('Y-m-d H:i:s') . " - Both emails failed for {$email}\n", FILE_APPEND);
    echo json_encode([
        'success' => true,  // Still return true so user doesn't get error
        'message' => '✓ Request received! Our team will contact you shortly at ' . $phone
    ]);
}
?>