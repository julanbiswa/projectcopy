<?php
// rent.php

session_start();
include("conn.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_ids']) && isset($_POST['days'])) {
    $item_ids = $_POST['item_ids'];
    $days = intval($_POST['days']);
    
    $total_price = 0;
    $item_names = [];
    
    // Sanitize item IDs and fetch prices
    $ids = implode(",", array_map('intval', $item_ids));
    $query = "SELECT name, price_per_day FROM machinery WHERE id IN ($ids)";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $total_price += $row['price_per_day'];
            $item_names[] = $row['name'];
        }
    } else {
        // Handle case where no items are found
        echo "<script>alert('No items found in cart. Please go back.'); window.location.href = 'cart.php';</script>";
        exit;
    }
    
    // Calculate total price based on rental duration
    $final_price = $total_price * $days;
    
    // Create a unique product name for the Khalti transaction
    $product_name = "ConstructionHub Rentals";
    if (count($item_names) == 1) {
        $product_name = $item_names[0];
    }
    
    // Store data in session to be used on payment success page
    $_SESSION['payment_details'] = [
        'item_ids' => $item_ids,
        'days' => $days,
        'total_price' => $final_price,
        'item_names' => $item_names
    ];
    
    // Redirect to the pay.php page with transaction details
    header("Location: pay.php?amount=" . $final_price . "&product_id=" . uniqid() . "&product_name=" . urlencode($product_name));
    exit;

} else {
    // If accessed directly, redirect to cart page
    header("Location: cart.php");
    exit;
}
?>
