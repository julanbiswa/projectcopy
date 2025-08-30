<?php
session_start();
include("conn.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to rent equipment.'); window.location.href='index.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'], $_POST['days'], $_POST['payment_method'])) {
    $user_id = $_SESSION['user_id'];
    $item_id = intval($_POST['item_id']);
    $days = intval($_POST['days']);
    $rented_at = date('Y-m-d H:i:s');
    $status = 'pending';

    // Payment details
    $payment_method = $_POST['payment_method'];
    $card_number = $_POST['card_number'];
    $card_name = $_POST['card_name'];
    $card_expiry = $_POST['card_expiry'];
    $card_cvv = $_POST['card_cvv'];

    // Assume $item_price is fetched from DB for the selected item
    $item_query = $conn->query("SELECT price FROM machinery WHERE id = $item_id");
    $item_row = $item_query->fetch_assoc();
    $item_price = $item_row['price'];
    $total_price = $item_price * $days;

    if ($payment_method === 'eswa' || $payment_method === 'credit_card') {
        $total_price *= 0.9; // Apply 10% discount
    }

    // Simulate payment processing (always success for demo)
    $payment_success = true;

    if ($payment_success) {
        // Insert rent order into database
        $stmt = $conn->prepare("INSERT INTO rent_orders (user_id, item_id, days, rented_at, status, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissd", $user_id, $item_id, $days, $rented_at, $status, $total_price);

        if ($stmt->execute()) {
            $rent_order_id = $stmt->insert_id;

            // Save payment if online
            if ($payment_method === 'eswa' || $payment_method === 'credit_card') {
                $paid_at = date('Y-m-d H:i:s');
                $pay_stmt = $conn->prepare("INSERT INTO payments (user_id, rent_order_id, amount, payment_method, paid_at) VALUES (?, ?, ?, ?, ?)");
                $pay_stmt->bind_param("iisss", $user_id, $rent_order_id, $total_price, $payment_method, $paid_at);
                $pay_stmt->execute();
                $pay_stmt->close();
            }

            echo "<script>alert('Payment successful! Rent confirmed.'); window.location.href='orders.php';</script>";
        } else {
            echo "<script>alert('Error confirming rent.'); window.location.href='index.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Payment failed. Please try again.'); window.location.href='rent.php?item_id=$item_id';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='index.php';</script>";
}
?>