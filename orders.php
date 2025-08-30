<?php
session_start();
include("conn.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT r.*, m.name FROM rent_orders r JOIN machinery m ON r.item_id = m.id WHERE r.user_id = $user_id ORDER BY r.rented_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Rentals</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <a href="index.php" style="display:inline-block; margin:15px 0; padding:8px 16px; background:#006A71; color:#fff; border-radius:5px; text-decoration:none;">
        &larr; Back to Home
    </a>
    <h2>Your Rental History</h2>
    <ul>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <li><?= htmlspecialchars($row['name']) ?> - <?= $row['days'] ?> day(s) - <?= $row['rented_at'] ?></li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
