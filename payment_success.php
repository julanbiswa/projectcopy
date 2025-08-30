<?php
// payment_success.php
// This page displays a success message after a successful Khalti payment.
// It also records the order details in the database.

session_start();
include("conn.php");

// Redirect if not logged in or no payment details in session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['payment_details'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_details = $_SESSION['payment_details'];
$item_ids = $payment_details['item_ids'];
$days = $payment_details['days'];
$total_price = $payment_details['total_price'];
$item_names = $payment_details['item_names'];

// Generate a unique order ID
$order_id = uniqid('order_');

// Insert order details into the database
foreach ($item_ids as $item_id) {
    $item_id = intval($item_id);
    
    $stmt = $conn->prepare("INSERT INTO rent_orders (user_id, item_id, days, rented_at, order_id, total_price) VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param("iiisd", $user_id, $item_id, $days, $order_id, $total_price);
    $stmt->execute();
    $stmt->close();
}

// Clear the cart and payment details from the session
unset($_SESSION['cart']);
unset($_SESSION['payment_details']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2efe7;
            text-align: center;
            padding: 50px;
        }
        .success-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .success-icon {
            color: #4CAF50;
            font-size: 50px;
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
        }
        p {
            color: #666;
            line-height: 1.6;
        }
        .details-box {
            background-color: #f9f9f9;
            border-left: 4px solid #006A71;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
        }
        .details-box p {
            margin: 5px 0;
            color: #333;
        }
        .details-box p strong {
            color: #006A71;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #FFA500;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">&#10004;</div>
        <h2>Payment Successful!</h2>
        <p>Your payment for the following equipment rental has been successfully processed.</p>
        
        <div class="details-box">
            <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
            <p><strong>Total Amount:</strong> Rs. <?= number_format($total_price, 2) ?></p>
            <p><strong>Rental Duration:</strong> <?= htmlspecialchars($days) ?> day(s)</p>
            <p><strong>Equipment Rented:</strong> <?= htmlspecialchars(implode(", ", $item_names)) ?></p>
        </div>

        <a href="index.php" class="back-btn">Back to Home</a>
    </div>
</body>
</html>
