<?php
session_start();
include("conn.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $days = intval($_POST['days']);
    $item_ids = $_POST['item_ids'];
    $order_id = uniqid('order_');

    $_SESSION['last_order'] = [];

    foreach ($item_ids as $item_id) {
        $item_id = intval($item_id);

        // Get price
        $res = mysqli_query($conn, "SELECT name, price_per_day FROM machinery WHERE id = $item_id");
        $item = mysqli_fetch_assoc($res);
        $total_price = $item['price_per_day'] * $days;

        $stmt = $conn->prepare("INSERT INTO rent_orders (user_id, item_id, days, rented_at, order_id, total_price) VALUES (?, ?, ?, NOW(), ?, ?)");
        $stmt->bind_param("iiisd", $user_id, $item_id, $days, $order_id, $total_price);
        $stmt->execute();
        $stmt->close();

        $_SESSION['last_order'][] = [
            'name' => $item['name'],
            'days' => $days,
            'price_per_day' => $item['price_per_day'],
            'total_price' => $total_price,
        ];
    }

    $_SESSION['cart'] = [];

    // Simulate email sending
    $_SESSION['sent_email'] = true;

    header("Location: order_summary.php");
    exit;
}
?>
