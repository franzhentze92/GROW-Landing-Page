<?php
// Contact Form Handler for NTS G.R.O.W
// This file handles form submissions and sends emails

// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$companyname = $_POST['companyname'] ?? '';
$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';

// Validate required fields
if (empty($fullname) || empty($email) || empty($companyname) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

// Set recipient email
$to = 'info@ntsgrow.com';

// Set email subject
$subject = 'New Contact Form Submission - NTS G.R.O.W';

// Build email content
$emailContent = "New contact form submission from NTS G.R.O.W website:\n\n";
$emailContent .= "Full Name: " . htmlspecialchars($fullname) . "\n";
$emailContent .= "Email: " . htmlspecialchars($email) . "\n";
$emailContent .= "Company Name: " . htmlspecialchars($companyname) . "\n";
$emailContent .= "Phone: " . htmlspecialchars($phone) . "\n";
$emailContent .= "Message:\n" . htmlspecialchars($message) . "\n\n";
$emailContent .= "Submitted on: " . date('Y-m-d H:i:s') . "\n";

// Set email headers
$headers = "From: noreply@ntsgrow.com\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
$mailSent = mail($to, $subject, $emailContent, $headers);

if ($mailSent) {
    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again.']);
}
?>
