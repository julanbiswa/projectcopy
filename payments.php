<?php
include("conn.php");
session_start();

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('You must be logged in to make a payment.'); window.location.href='index.php';</script>";
        exit();
    }

    $orderId = $_POST['order_id'];
    $amount = $_POST['amount'];
    $paymentMethod = $_POST['payment_method'];

    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, payment_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ids", $orderId, $amount, $paymentMethod);

    if ($stmt->execute()) {
        // Update the order status to confirmed
        $updateOrder = $conn->prepare("UPDATE rent_orders SET status='confirmed' WHERE id=?");
        $updateOrder->bind_param("i", $orderId);
        $updateOrder->execute();
        $updateOrder->close();
        
        echo "<script>alert('Payment successful! Your order has been confirmed.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error processing payment. Please try again.'); window.location.href='index.php';</script>";
    }
    $stmt->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Payment</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .payment-form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .payment-form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .payment-form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .payment-form-container select,
        .payment-form-container input[type="text"],
        .payment-form-container input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .payment-form-container .btn {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: var(--AccentColor);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .payment-form-container .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="payment-form-container">
        <h1>Make a Payment</h1>
        <form action="payments.php" method="POST">
            <label for="order_id">Select Order to Pay For:</label>
            <select name="order_id" id="order_id" required>
                <?php
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    $pendingOrdersQuery = "SELECT id, item_id, total_price FROM rent_orders WHERE user_id = ? AND status = 'pending'";
                    $stmt = $conn->prepare($pendingOrdersQuery);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $pendingOrdersResult = $stmt->get_result();

                    if ($pendingOrdersResult->num_rows > 0) {
                        while ($row = $pendingOrdersResult->fetch_assoc()) {
                            echo "<option value='{$row['id']}' data-amount='{$row['total_price']}'>Order #{$row['id']} - Amount: $" . number_format($row['total_price'], 2) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No pending orders to pay for</option>";
                    }
                    $stmt->close();
                }
                ?>
            </select>

            <label for="amount">Amount Due:</label>
            <input type="text" name="amount" id="amount" readonly required>

            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="Credit Card">Credit Card</option>
                <option value="PayPal">PayPal</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <button type="submit" name="submit_payment" class="btn">Submit Payment</button>
        </form>
    </div>

    <script>
        document.getElementById('order_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const amount = selectedOption.getAttribute('data-amount');
            document.getElementById('amount').value = amount;
        });

        // Set initial amount on page load
        document.addEventListener('DOMContentLoaded', function() {
            const orderSelect = document.getElementById('order_id');
            if (orderSelect.options.length > 0 && orderSelect.value) {
                const initialOption = orderSelect.options[orderSelect.selectedIndex];
                const initialAmount = initialOption.getAttribute('data-amount');
                document.getElementById('amount').value = initialAmount;
            }
        });
    </script>
</body>

</html>
