<?php
session_start();

// ADD THIS LINE:
require_once __DIR__ . '/config/database.php';

// KEEP YOUR EXISTING PHPMailer includes
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ENABLE ERROR DISPLAY FOR DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// Validate fields
$studentId   = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
$category    = isset($_POST['category']) ? trim($_POST['category']) : 'general';
$message     = isset($_POST['complaint']) ? trim($_POST['complaint']) : '';
$captchaIn   = isset($_POST['captcha_input']) ? trim($_POST['captcha_input']) : '';

$errors = [];
if ($studentId === '' || !preg_match('/^20\d{7,9}$/', $studentId)) {
    $errors[] = 'Invalid Student ID format.';
}
if ($category === '' || !in_array($category, ['facilities','academics','student_services'], true)) {
    $errors[] = 'Please select a valid category.';
}
if ($message === '' || strlen($message) < 10) {
    $errors[] = 'Please provide more details (at least 10 characters).';
}
if ($captchaIn === '' || !isset($_SESSION['captcha_answer']) || intval($captchaIn) !== intval($_SESSION['captcha_answer'])) {
    $errors[] = 'Captcha answer is incorrect.';
}

// Clear captcha regardless
unset($_SESSION['captcha_answer']);

if (!empty($errors)) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Submission Error</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                background: #f4f6f9; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                min-height: 100vh; 
                margin: 0;
                background-image: linear-gradient(rgba(0,0,0,0.15), rgba(0,0,0,0.15)), url(\'assets/campus.jpg\'); 
                background-size: cover; 
                background-position: center; 
            }
            .error-container { 
                background: #fff; 
                padding: 30px; 
                border-radius: 15px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
                border-left: 6px solid #dc3545;
                max-width: 500px;
                text-align: center;
            }
            .error-icon { 
                font-size: 48px; 
                color: #dc3545; 
                margin-bottom: 20px;
            }
            .btn { 
                background: #0a8f3d; 
                color: #fff; 
                border: none; 
                padding: 12px 24px; 
                border-radius: 8px; 
                cursor: pointer; 
                font-weight: bold; 
                text-decoration: none;
                display: inline-block;
                margin-top: 15px;
            }
            .btn:hover { 
                background: #087835; 
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h2 style="color: #dc3545; margin-bottom: 20px;">Submission Failed</h2>
            <div style="text-align: left; background: #ffeaea; padding: 15px; border-radius: 8px; margin: 15px 0;">'
                . implode('<br>', array_map('htmlspecialchars', $errors)) . 
            '</div>
            <a href="index.php" class="btn">Go Back to Form</a>
        </div>
    </body>
    </html>';
    exit;
}

// Generate tracking code (no PII)
$trackingCode = strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
$nowIso = date('c');

// Save to database
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO complaints (tracking_code, category, message, status) 
              VALUES (:tracking_code, :category, :message, 'submitted')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":tracking_code", $trackingCode);
    $stmt->bindParam(":category", $category);
    $stmt->bindParam(":message", $message);
    $stmt->execute();
    
} catch(PDOException $exception) {
    error_log("Database error: " . $exception->getMessage());
}

// ==================== EMAIL CONFIGURATION ====================
// Map recipient email based on category
switch ($category) {
    case 'facilities':
        $recipient = 'alfonsoronwell06@gmail.com';
        break;
    case 'academics':
        $recipient = 'academics.office@cvsu.edu.ph';
        break;
    case 'student_services':
        $recipient = 'studentservices@cvsu.edu.ph';
        break;
    default:
        $recipient = 'admin@cvsu.edu.ph';
        break;
}

// Sender Gmail Configuration
$senderEmail = 'sendercomplaint7@gmail.com';
$senderPassword = 'sgfrwxixaqebhvrn';

$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $senderEmail;
    $mail->Password   = $senderPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Bypass SSL verification for local testing
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // Email setup
    $mail->setFrom($senderEmail, 'CvSU Complaint System');
    $mail->addAddress($recipient);
    $mail->addReplyTo($senderEmail, 'No Reply');

    $mail->isHTML(true);
    $mail->Subject = 'New Complaint - ' . ucfirst($category) . ' [' . $trackingCode . ']';
    
    $body  = '<h3>New Anonymous Complaint Received</h3>';
    $body .= '<p><strong>Tracking Code:</strong> ' . htmlspecialchars($trackingCode) . '</p>';
    $body .= '<p><strong>Category:</strong> ' . htmlspecialchars(ucfirst($category)) . '</p>';
    $body .= '<p><strong>Message:</strong></p>';
    $body .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #0a8f3d;">';
    $body .= nl2br(htmlspecialchars($message));
    $body .= '</div>';
    $body .= '<br><p><em>Please login to the admin dashboard to update the status.</em></p>';
    
    $mail->Body = $body;

    // Send email
    $mail->send();
    $emailStatus = "✅ Email notification sent to " . htmlspecialchars($recipient);
    
} catch (Exception $e) {
    // Continue even if email fails; complaint is stored with tracking code
    $emailStatus = "⚠️ Email notification failed but complaint was saved.";
}
// ==================== END EMAIL CONFIGURATION ====================

// SUCCESS PAGE WITH BEAUTIFUL DESIGN
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Submitted Successfully</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f4f6f9; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0;
            background-image: linear-gradient(rgba(0,0,0,0.15), rgba(0,0,0,0.15)), url(\'assets/campus.jpg\'); 
            background-size: cover; 
            background-position: center; 
        }
        .success-container { 
            background: #fff; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.2); 
            border-top: 6px solid #0a8f3d;
            max-width: 500px;
            text-align: center;
            animation: fadeIn 0.6s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .success-icon { 
            font-size: 64px; 
            color: #0a8f3d; 
            margin-bottom: 20px;
            animation: bounce 0.6s ease-in;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }
        .tracking-code { 
            background: linear-gradient(135deg, #0a8f3d, #087835); 
            color: white; 
            padding: 15px; 
            border-radius: 10px; 
            font-size: 1.4em; 
            font-weight: bold; 
            letter-spacing: 2px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(10, 143, 61, 0.3);
        }
        .btn { 
            background: #0a8f3d; 
            color: #fff; 
            border: none; 
            padding: 12px 30px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(10, 143, 61, 0.3);
        }
        .btn:hover { 
            background: #087835; 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(10, 143, 61, 0.4);
        }
        .btn-secondary { 
            background: #6c757d; 
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        .btn-secondary:hover { 
            background: #5a6268; 
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }
        .status-info {
            background: #e8f5ee;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #0a8f3d;
        }
        .steps {
            text-align: left;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .steps li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✅</div>
        <h2 style="color: #0a8f3d; margin-bottom: 10px;">Complaint Submitted Successfully!</h2>
        <p style="color: #666; margin-bottom: 20px;">Your complaint has been received and will be processed shortly.</p>
        
        <div class="status-info">
            ' . $emailStatus . '
        </div>
        
        <div class="tracking-code">' . htmlspecialchars($trackingCode) . '</div>
        
        <p><strong>Save this tracking code to check your complaint status.</strong></p>
        
        <div class="steps">
            <h4 style="margin-top: 0; color: #0a8f3d;">Next Steps:</h4>
            <ul>
                <li>Use the tracking code above to check status</li>
                <li>The concerned office will review your complaint</li>
                <li>Check back for updates and office responses</li>
            </ul>
        </div>
        
        <div>
            <a href="http://' . $_SERVER['HTTP_HOST'] . '/complaint_systemcopy/track.php?code=' . urlencode($trackingCode) . '" class="btn">
                📱 Track Your Complaint
            </a>
            <a href="index.php" class="btn btn-secondary">
                📝 Submit Another Complaint
            </a>
        </div>
        
        <p style="margin-top: 20px; font-size: 0.9em; color: #888;">
            <em>Your identity remains anonymous throughout the process.</em>
        </p>
    </div>
</body>
</html>';
?>