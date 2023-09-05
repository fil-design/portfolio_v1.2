<?php
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

        // Convert the invoice data to JSON
        $invoiceJson = json_encode($invoiceData);

        // Generate the JavaScript code to download the invoice PDF
        $jsCode = <<<EOD
var https = require("https");
var fs = require("fs");

function generateInvoice(invoice, filename, success, error) {
    var postData = JSON.stringify(invoice);
    var options = {
        hostname  : "invoice-generator.com",
        port      : 443,
        path      : "/",
        method    : "POST",
        headers   : {
            "Content-Type": "application/json",
            "Content-Length": Buffer.byteLength(postData)
        }
    };

    var file = fs.createWriteStream(filename);

    var req = https.request(options, function(res) {
        res.on('data', function(chunk) {
            file.write(chunk);
        })
        .on('end', function() {
            file.end();

            if (typeof success === 'function') {
                success();
            }
        });
    });
    req.write(postData);
    req.end();

    if (typeof error === 'function') {
        req.on('error', error);
    }
}

var invoice = $invoiceJson;

generateInvoice(invoice, 'invoice.pdf', function() {
    console.log("Saved invoice to invoice.pdf");
}, function(error) {
    console.error(error);
});
EOD;

        // Output the JavaScript code
        echo $jsCode;

        // Display a confirmation message
        echo '<h1>Thank you for your submission, ' . htmlspecialchars($info['name']) . '!</h1>';
    } else {
        echo 'Name and email not found in query parameters.' . PHP_EOL;
    }
} else {
    echo 'No query parameters found in the URL.' . PHP_EOL;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation Page</title>
</head>
<body>
    <h1>THANK YOU FOR THE ORDER</h1>
</body>
</html>
