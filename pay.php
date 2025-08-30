<?php
// pay.php
// This file is the Khalti payment gateway interface.
// It receives the amount and product details from rent.php.

session_start();

$error_message = "";
$khalti_public_key = "test_public_key"; // here is the live public key 

// Get dynamic values from the URL
$amount = isset($_GET['amount']) ? $_GET['amount'] : 0;
$uniqueProductId = isset($_GET['product_id']) ? $_GET['product_id'] : "ch-prod-" . uniqid();
$uniqueProductName = isset($_GET['product_name']) ? $_GET['product_name'] : "ConstructionHub Rental";

// Khalti redirect URL
$successRedirect = "payment_success.php"; // The page Khalti redirects to on success

// If the user is returning from a successful Khalti payment
if (isset($_GET['token']) && isset($_GET['amount']) && isset($_GET['idx'])) {
    $token = $_GET['token'];
    $paymentAmount = $_GET['amount'];

    // Verify the payment via Khalti API
    $args = http_build_query([
        'token' => $token,
        'amount'  => $paymentAmount,
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://khalti.com/api/v2/payment/verify/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Set Khalti API headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Key ' . $khalti_public_key]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response);
    
    if ($status_code == 200) {
        // Payment is verified
        header("Location: " . $successRedirect);
        exit;
    } else {
        $error_message = "Payment verification failed. Please try again.";
        if (isset($response_data->detail)) {
            $error_message .= " Error: " . htmlspecialchars($response_data->detail);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Khalti Payment</title>
    <style>
        .khalti-container {
            width: 350px;
            margin: 50px auto;
            padding: 20px;
            border: 2px solid #5C2D91;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .khalti-logo {
            width: 150px;
            margin-bottom: 20px;
        }
        .khalti-btn {
            background-color: #5C2D91;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }
        .khalti-btn:hover {
            background-color: #4a2471;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="khalti-container">
        <img src="images/khalti.PNG" alt="Khalti Logo" class="khalti-logo">
        <h2>Pay with Khalti</h2>
        <p>You are about to pay: <strong>Rs. <?= number_format($amount, 2) ?></strong></p>
        <p>For: <strong><?= htmlspecialchars($uniqueProductName) ?></strong></p>
        
        <form action="https://khalti.com/api/v2/payment/initiate/" method="POST">
            <input type="hidden" name="public_key" value="<?= $khalti_public_key ?>">
            <input type="hidden" name="product_identity" value="<?= htmlspecialchars($uniqueProductId) ?>">
            <input type="hidden" name="product_name" value="<?= htmlspecialchars($uniqueProductName) ?>">
            <input type="hidden" name="amount" value="<?= $amount * 100 ?>"> <!-- Khalti amount is in paisa -->
            <input type="hidden" name="return_url" value="http://localhost/constructionhub/pay.php"> <!-- Change to your live URL -->
            
            <button type="submit" class="khalti-btn">Pay with Khalti</button>
        </form>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
