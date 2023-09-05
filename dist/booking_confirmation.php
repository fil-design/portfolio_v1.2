<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require '../vendor/autoload.php';

// Function to extract name and email from query parameters
function extractInfoFromQueryParameters($queryParameters) {
    parse_str($queryParameters, $params);
    $name = isset($params['name']) ? $params['name'] : null;
    $email = isset($params['email']) ? $params['email'] : null;

    return ['name' => $name, 'email' => $email];
}

// Get the query parameters from the current URL
$currentURL = $_SERVER['REQUEST_URI'];

if (isset($_SERVER['QUERY_STRING'])) {
    $info = extractInfoFromQueryParameters($_SERVER['QUERY_STRING']);

    if ($info['name'] && $info['email']) {
        // Create a PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = 0;   // Enable verbose debug output (change to 2 for debugging)
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';  // Specify your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = '8239cdb6d51b88'; // SMTP username
            $mail->Password = '3c674995f83d25';   // SMTP password
            $mail->SMTPSecure = 'tls';   // Enable TLS encryption
            $mail->Port = 587;   // TCP port to connect to

            // Sender and recipient information
            $mail->setFrom('your@example.com', 'Your Name');
            $mail->addAddress($info['email'], $info['name']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Invoice for Your Purchase';
            // Generate your invoice data here and include it in the email body
            $invoiceData = [
                'logo' => 'http://invoiced.com/img/logo-invoice.png',
                'from' => 'Invoiced\n701 Brazos St\nAustin, TX 78748',
                'to' => $info['name'],
                'currency' => 'usd',
                'number' => 'INV-0001',
                'payment_terms' => 'Auto-Billed - Do Not Pay',
                'items' => [
                    [
                        'name' => 'Subscription to Starter',
                        'quantity' => 1,
                        'unit_cost' => 50,
                    ],
                ],
                'fields' => [
                    'tax' => '%',
                ],
                'tax' => 5,
                'notes' => 'Thanks for being an awesome customer!',
                'terms' => 'No need to submit payment. You will be auto-billed for this invoice.',
            ];
            $mail->Body = 'Here is your invoice:<br>' . json_encode($invoiceData, JSON_PRETTY_PRINT);

            // Send the email
            $mail->send();
            echo 'Invoice email sent successfully.';
        } catch (Exception $e) {
            echo 'Email could not be sent. Error: ', $mail->ErrorInfo;
        }
    } else {
        echo 'Name and email not found in query parameters.' . PHP_EOL;
    }
} else {
    echo 'No query parameters found in the URL.' . PHP_EOL;
}
?>
