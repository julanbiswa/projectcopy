<?php
session_start();
include("conn.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];

if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    if (($key = array_search($remove_id, $cart)) !== false) {
        unset($cart[$key]);
        $_SESSION['cart'] = array_values($cart); // reindex
        header("Location: cart.php");
        exit;
    }
}

$items = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(",", array_map('intval', $cart));
    $query = "SELECT * FROM machinery WHERE id IN ($ids)";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Your Cart - ConstructHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="styles.css"/>
  <style>
  :root {
    --PrimaryColor: #006A71;
    --SecondaryColor: #48A6A7;
    --AccentColor: #FFA500;
    --LightColor: #F2EFE7;
    --TextColor: #333;
    --White: #ffffff;
  }
  body {
    font-family: Arial, sans-serif;
    background-color: var(--LightColor);
    color: var(--TextColor);
    margin: 0;
    padding: 20px;
  }
  .container {
    max-width: 900px;
    margin: 0 auto;
    background-color: var(--White);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  h2 {
    color: var(--PrimaryColor);
    text-align: center;
    margin-bottom: 20px;
  }
  .cart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
  }
  .cart-card {
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    padding: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .cart-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 5px;
  }
  .cart-card h3 {
    margin: 10px 0;
    color: var(--TextColor);
    text-align: center;
  }
  .cart-card p {
    margin: 5px 0;
    text-align: center;
  }
  .remove-btn {
    display: inline-block;
    background-color: #dc3545;
    color: white;
    padding: 8px 16px;
    border-radius: 5px;
    text-decoration: none;
    margin-top: 10px;
  }
  .remove-btn:hover {
    background-color: #c82333;
  }
  .total-section {
    text-align: right;
    font-size: 1.5em;
    margin-top: 20px;
    border-top: 2px solid #ccc;
    padding-top: 10px;
  }
  .checkout-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    margin-top: 20px;
  }
  .checkout-section label, .checkout-section input, .checkout-section button {
    font-size: 1em;
  }
  .checkout-section input {
    width: 150px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  .checkout-section button {
    padding: 10px 20px;
    background-color: var(--AccentColor);
    color: var(--White);
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  .checkout-section button:hover {
    background-color: #ff8c00;
  }
  .no-items {
    text-align: center;
    color: #555;
  }
  .back-btn {
    display: inline-block;
    margin: 15px 0;
    padding: 8px 16px;
    background: var(--PrimaryColor);
    color: #fff;
    border-radius: 5px;
    text-decoration: none;
  }
  </style>
</head>
<body>
  <div class="container">
    <a href="index.php" class="back-btn">&larr; Back to Home</a>
    <h2>Your Cart</h2>

    <?php if (empty($items)): ?>
      <p class="no-items">Your cart is empty.</p>
    <?php else: ?>
      <!-- Change the form action to the new rent.php file -->
      <form action="rent.php" method="POST">
        <div class="cart-grid">
          <?php foreach ($items as $item): ?>
            <?php $total += $item['price_per_day']; ?>
            <div class="cart-card">
              <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <p>Price/day: ₹<?= number_format($item['price_per_day'], 2) ?></p>
              <p>Status: <strong><?= htmlspecialchars($item['availability']) ?></strong></p>
              <a href="cart.php?remove=<?= $item['id'] ?>" class="remove-btn">Remove</a>
              <input type="hidden" name="item_ids[]" value="<?= $item['id'] ?>">
            </div>
          <?php endforeach; ?>
        </div>

        <div class="total-section">
          Total per day: <strong>₹<?= number_format($total, 2) ?></strong>
        </div>

        <div class="checkout-section">
          <label for="days">Rental Duration (in days):</label>
          <input type="number" name="days" id="days" required min="1">
          <button type="submit">Proceed to Checkout</button>
        </div>
      </form>
    <?php endif; ?>

  </div>
</body>
</html>
