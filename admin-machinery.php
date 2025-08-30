<?php
include("conn.php");

$edit_data = null;

if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM machinery WHERE id = $edit_id");
    if (mysqli_num_rows($result) > 0) {
        $edit_data = mysqli_fetch_assoc($result);
        echo "<script>window.addEventListener('DOMContentLoaded', () => showAddMachineryModal());</script>";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_machinery'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $price = $_POST['price'];
    $min_days = $_POST['min_days'];
    $deposit = $_POST['deposit'];
    $availability = $_POST['availability'];
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $condition = $_POST['condition'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Handle image
    $target_dir = "uploads/images/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);
    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $query = "INSERT INTO machinery (name, model, price_per_day, min_rent_days, deposit_amount, availability, location, `condition`, description, image_path)
                  VALUES ('$name', '$model', '$price', '$min_days', '$deposit', '$availability', '$location', '$condition', '$description', '$target_file')";
        mysqli_query($conn, $query);

        // ✅ Success alert
        echo "<script>alert('Machinery details added successfully.'); window.location.href='admin.php?tab=machinery';</script>";
        exit();
    }
}



// Delete machinery
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM machinery WHERE id = $id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();

    
}

// Fetch all machinery
$machineries = mysqli_query($conn, "SELECT * FROM machinery ORDER BY id DESC");
?>

<div id="machinery-modal" class="machinery-modal hidden">
  <div class="login-card glass">
    <span class="close-btn" onclick="closeMachineryModal()">&times;</span>
    <h2 style="text-align: center">Add Machinery</h2>

    <form id="machineryForm" action="admin.php?tab=machinery" method="POST" enctype="multipart/form-data">
      <div class="input-group">
        <i class="fas fa-tools"></i>
        <input type="text" name="name" placeholder="Machinery Name" required />
      </div>

      <div class="input-group">
        <i class="fas fa-cogs"></i>
        <input type="text" name="model" placeholder="Model Number" required />
      </div>

      <div class="input-group">
        <i class="fas fa-rupee-sign"></i>
        <input type="number" step="0.01" name="price" placeholder="Price per Day (₹)" required />
      </div>

      <div class="input-group">
        <i class="fas fa-calendar-alt"></i>
        <input type="number" name="min_days" placeholder="Minimum Rent Days" value="1" required />
      </div>

      <div class="input-group">
        <i class="fas fa-wallet"></i>
        <input type="number" step="0.01" name="deposit" placeholder="Deposit Amount (₹)" required />
      </div>

      <div class="input-group">
        <i class="fas fa-map-marker-alt"></i>
        <input type="text" name="location" placeholder="Location" required />
      </div>

      <div class="input-group">
        <select name="availability" required>
          <option value="Available">Available</option>
          <option value="Not Available">Not Available</option>
        </select>
      </div>

      <div class="input-group">
        <select name="condition" required>
          <option value="New">New</option>
          <option value="Good">Good</option>
          <option value="Used">Used</option>
          <option value="Under Maintenance">Under Maintenance</option>
        </select>
      </div>

      <div class="input-group">
        <textarea name="description" placeholder="Description" required></textarea>
      </div>
      <div class="input-group">
        <input type="file" name="image" accept="image/*" required />
      </div>

      <button type="submit" name="add_machinery">Upload Details</button>
    </form>
  </div>
</div>


<!-- EXISTING LIST -->
<div class="machinery-list">
<?php while($row = mysqli_fetch_assoc($machineries)): ?>
  <div class="machinery-item">
    <img src="<?= $row['image_path'] ?>" alt="<?= htmlspecialchars($row['name']) ?>" style="max-width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">
    <h4><?= htmlspecialchars($row['name']) ?></h4>
    <p><strong>Model:</strong> <?= htmlspecialchars($row['model']) ?></p>
    <p><strong>Rental Price:</strong> ₹<?= $row['price_per_day'] ?>/day</p>
    <p><strong>Status:</strong> <?= $row['availability'] ?> | <?= $row['condition'] ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
    <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
    <div style="margin-top: 10px;">
      <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this machinery?')" class="cart-btn" style="background-color: red; color:white; border-radius: 5px; text-decoration: none; padding: 2px;">Delete</a>
      <a href="?edit=<?= $row['id'] ?>" class="cart-btn" style="color: white; background-color: green; border-radius: 5px; text-decoration: none; padding: 2px;">Update</a> <!-- Optional: open modal with JS -->
    </div>
  </div>
<?php endwhile; ?>
</div>