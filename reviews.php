<?php
include("conn.php");
session_start();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('You must be logged in to submit a review.'); window.location.href='index.php';</script>";
        exit();
    }

    $userId = $_SESSION['user_id'];
    $machineryId = $_POST['machinery_id'];
    $rating = $_POST['rating'];
    $reviewText = $_POST['review_text'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, machinery_id, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $userId, $machineryId, $rating, $reviewText);
    
    if ($stmt->execute()) {
        echo "<script>alert('Review submitted successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error submitting review. Please try again.'); window.location.href='index.php';</script>";
    }
    $stmt->close();
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Submit a Review</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .review-form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .review-form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .review-form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .review-form-container select,
        .review-form-container textarea,
        .review-form-container input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .review-form-container textarea {
            height: 150px;
        }

        .review-form-container .btn {
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

        .review-form-container .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="review-form-container">
        <h1>Submit a Review</h1>
        <form action="reviews.php" method="POST">
            <label for="machinery_id">Select Machinery:</label>
            <select name="machinery_id" id="machinery_id" required>
                <?php
                $machineryQuery = "SELECT id, name FROM machinery ORDER BY name ASC";
                $machineryResult = mysqli_query($conn, $machineryQuery);
                if (mysqli_num_rows($machineryResult) > 0) {
                    while ($row = mysqli_fetch_assoc($machineryResult)) {
                        echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                } else {
                    echo "<option value=''>No machinery available</option>";
                }
                ?>
            </select>

            <label for="rating">Rating (1-5):</label>
            <input type="number" name="rating" id="rating" min="1" max="5" required>

            <label for="review_text">Your Review:</label>
            <textarea name="review_text" id="review_text" rows="5" placeholder="Write your review here..." required></textarea>

            <button type="submit" name="submit_review" class="btn">Submit Review</button>
        </form>
    </div>
</body>

</html>
