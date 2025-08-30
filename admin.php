<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

include("conn.php");
error_reporting(1);

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = intval($_POST['order_id']);
    if (isset($_POST['confirm_order'])) {
        $status = 'confirmed';
    } elseif (isset($_POST['pending_order'])) {
        $status = 'pending';
    } elseif (isset($_POST['cancel_order'])) {
        $status = 'cancelled';
    }
    if (isset($status)) {
        $update = $conn->prepare("UPDATE rent_orders SET status=? WHERE id=?");
        $update->bind_param("si", $status, $orderId);
        $update->execute();
        $update->close();
        // Refresh to show updated status
        header("Location: admin.php?tab=rent_orders");
        exit();
    }
}

// Fetch rent orders with customer info
$rentOrdersQuery = "SELECT r.*, u.userfullname, u.userpnumber, u.useremail
                    FROM rent_orders r
                    JOIN usercredentials u ON r.user_id = u.id
                    ORDER BY r.id DESC";
$rentOrdersResult = mysqli_query($conn, $rentOrdersQuery);

// Gross Sales (sum of total_price)
$salesResult = mysqli_query($conn, "SELECT SUM(total_price) AS gross_sales FROM rent_orders");
$grossSales = mysqli_fetch_assoc($salesResult)['gross_sales'] ?? 0;

// Fetch total number of users
$usersResult = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM usercredentials");
$totalUsers = mysqli_fetch_assoc($usersResult)['total_users'] ?? 0;

// Fetch total number of items
$itemsResult = mysqli_query($conn, "SELECT COUNT(*) AS total_items FROM machinery");
$totalItems = mysqli_fetch_assoc($itemsResult)['total_items'] ?? 0;

// Fetch most rented item (example)
$mostRentedQuery = "SELECT m.name, COUNT(r.id) AS rental_count
                    FROM rent_orders r
                    JOIN machinery m ON r.item_id = m.id
                    GROUP BY m.id
                    ORDER BY rental_count DESC
                    LIMIT 1";
$mostRentedResult = mysqli_query($conn, $mostRentedQuery);
$mostRentedItem = mysqli_fetch_assoc($mostRentedResult);

// Fetch total sales per month for the last year
$salesQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS order_month, SUM(total_price) AS monthly_sales
               FROM rent_orders
               WHERE created_at >= NOW() - INTERVAL 12 MONTH
               GROUP BY order_month
               ORDER BY order_month ASC";
$salesResult = mysqli_query($conn, $salesQuery);

$salesLabels = [];
$salesData = [];
while ($row = mysqli_fetch_assoc($salesResult)) {
    $salesLabels[] = $row['order_month'];
    $salesData[] = $row['monthly_sales'];
}

// Fetch reviews with user info
$reviewsQuery = "SELECT r.*, u.userfullname
                 FROM reviews r
                 JOIN usercredentials u ON r.user_id = u.id
                 ORDER BY r.created_at DESC";
$reviewsResult = mysqli_query($conn, $reviewsQuery);

// Fetch payments with user info and order details
$paymentsQuery = "SELECT p.*, u.userfullname, r.id AS order_id, r.total_price
                  FROM payments p
                  JOIN rent_orders r ON p.order_id = r.id
                  JOIN usercredentials u ON r.user_id = u.id
                  ORDER BY p.payment_date DESC";
$paymentsResult = mysqli_query($conn, $paymentsQuery);


// Fetch machinery data for chart
$machineryQuery = "SELECT m.name, COUNT(r.id) as total_rented
                   FROM machinery m
                   LEFT JOIN rent_orders r ON m.id = r.item_id
                   GROUP BY m.id";
$machineryResult = mysqli_query($conn, $machineryQuery);

$labels = [];
$machineryPerMonth = [];
while ($row = mysqli_fetch_assoc($machineryResult)) {
    $labels[] = $row['name'];
    $machineryPerMonth[] = $row['total_rented'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="admin.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --PrimaryColor: #f0f0f0;
            --SecondaryColor: #284435;
            --AccentColor: #0d832d;
            --TextColor: #333;
            --LightText: #f9f9f9;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: var(--PrimaryColor);
            color: var(--TextColor);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--SecondaryColor);
            color: var(--LightText);
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--LightText);
        }

        .sidebar a {
            display: block;
            color: var(--LightText);
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }

        .sidebar a.active {
            background-color: var(--AccentColor);
        }

        .sidebar a:hover:not(.active) {
            background-color: #3f5847;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h1 {
            color: var(--TextColor);
        }

        .header .user-info {
            display: flex;
            align-items: center;
        }

        .user-info span {
            margin-right: 10px;
            font-weight: bold;
        }

        .user-info i {
            font-size: 24px;
        }

        .tab-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background-color: var(--AccentColor);
            color: var(--LightText);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
        }

        .stat-box p {
            font-size: 2em;
            margin: 0;
            font-weight: bold;
        }

        .content-section {
            margin-top: 30px;
        }

        .content-section h2 {
            border-bottom: 2px solid var(--AccentColor);
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: var(--TextColor);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }

        .status-confirmed {
            background-color: var(--AccentColor);
        }

        .status-pending {
            background-color: #f0ad4e;
        }

        .status-cancelled {
            background-color: #d9534f;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-content h2 {
            margin-top: 0;
        }

        .modal-content label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .modal-content input,
        .modal-content textarea,
        .modal-content select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .modal-content button {
            background-color: var(--AccentColor);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="#" onclick="showTab('dashboard')" id="dashboard-tab" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="#" onclick="showTab('rent_orders')" id="rent_orders-tab"><i class="fas fa-clipboard-list"></i> Rent Orders</a>
            <a href="#" onclick="showTab('machinery')" id="machinery-tab"><i class="fas fa-cogs"></i> Machinery</a>
            <a href="#" onclick="showTab('vlogs')" id="vlogs-tab"><i class="fas fa-video"></i> Vlogs</a>
            <a href="#" onclick="showTab('reviews')" id="reviews-tab"><i class="fas fa-star"></i> Reviews</a>
            <a href="#" onclick="showTab('payments')" id="payments-tab"><i class="fas fa-credit-card"></i> Payments</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Admin</span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>

            <div id="dashboard-content" class="tab-content">
                <div class="stats-grid">
                    <div class="stat-box">
                        <h3>Gross Sales</h3>
                        <p>₹<?= number_format($grossSales, 2) ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Total Users</h3>
                        <p><?= number_format($totalUsers) ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Total Items</h3>
                        <p><?= number_format($totalItems) ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Most Rented</h3>
                        <p><?= htmlspecialchars($mostRentedItem['name'] ?? 'N/A') ?></p>
                    </div>
                </div>

                <div class="content-section">
                    <h2>Sales Overview</h2>
                    <canvas id="salesChart"></canvas>
                </div>

                <div class="content-section">
                    <h2>Machinery Rental Insights</h2>
                    <canvas id="machineryChart"></canvas>
                </div>
            </div>

            <div id="rent_orders-content" class="tab-content hidden">
                <h2>Rent Orders</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Contact</th>
                                <th>Item ID</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($rentOrdersResult) > 0) {
                                while ($row = mysqli_fetch_assoc($rentOrdersResult)) {
                                    $statusClass = strtolower($row['status']);
                                    echo "<tr>";
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td>" . htmlspecialchars($row['userfullname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['userpnumber']) . "</td>";
                                    echo "<td>{$row['item_id']}</td>";
                                    echo "<td>₹" . number_format($row['total_price'], 2) . "</td>";
                                    echo "<td><span class='status-button status-{$statusClass}'>{$row['status']}</span></td>";
                                    echo "<td>";
                                    echo "<form method='POST' style='display:inline;'>";
                                    echo "<input type='hidden' name='order_id' value='{$row['id']}'>";
                                    echo "<button type='submit' name='confirm_order' class='status-button status-confirmed'>Confirm</button>";
                                    echo "<button type='submit' name='pending_order' class='status-button status-pending'>Pending</button>";
                                    echo "<button type='submit' name='cancel_order' class='status-button status-cancelled'>Cancel</button>";
                                    echo "</form>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No rent orders found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="machinery-content" class="tab-content hidden">
                <h2>Machinery</h2>
                <!-- Add machinery button -->
                <button onclick="showAddMachineryModal()" class="add-btn">Add New Machinery</button>
                <div id="machinery-list">
                    <!-- Machinery items will be added here dynamically -->
                </div>
            </div>

            <div id="vlogs-content" class="tab-content hidden">
                <h2>Vlogs</h2>
                <!-- Add vlog button -->
                <button onclick="showAddVlogModal()" class="add-btn">Add New Vlog</button>
                <div id="vlog-list">
                    <!-- Vlog items will be added here dynamically -->
                </div>
            </div>

            <div id="reviews-content" class="tab-content hidden">
                <h2>Reviews</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Machinery ID</th>
                                <th>Rating</th>
                                <th>Review Text</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($reviewsResult) > 0) {
                                while ($row = mysqli_fetch_assoc($reviewsResult)) {
                                    echo "<tr>";
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td>" . htmlspecialchars($row['userfullname']) . "</td>";
                                    echo "<td>{$row['machinery_id']}</td>";
                                    echo "<td>" . htmlspecialchars($row['rating']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['review_text']) . "</td>";
                                    echo "<td>{$row['created_at']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No reviews found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="payments-content" class="tab-content hidden">
                <h2>Payments</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>User</th>
                                <th>Order ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($paymentsResult) > 0) {
                                while ($row = mysqli_fetch_assoc($paymentsResult)) {
                                    echo "<tr>";
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td>" . htmlspecialchars($row['userfullname']) . "</td>";
                                    echo "<td>{$row['order_id']}</td>";
                                    echo "<td>₹" . number_format($row['amount'], 2) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                                    echo "<td>{$row['payment_date']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No payments found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    <!-- Add Machinery Modal -->
    <div id="machinery-modal" class="modal hidden">
        <div class="modal-content">
            <h2>Add New Machinery</h2>
            <span class="close-btn" onclick="closeMachineryModal()">&times;</span>
            <form action="add_machinery.php" method="POST" enctype="multipart/form-data">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="image">Image URL:</label>
                <input type="text" id="image" name="image" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>

                <label for="availability">Availability:</label>
                <select id="availability" name="availability" required>
                    <option value="Available">Available</option>
                    <option value="Rented">Rented</option>
                </select>

                <label for="price">Price (per day):</label>
                <input type="number" id="price" name="price" required>

                <label for="rating">Rating:</label>
                <input type="number" step="0.1" min="1" max="5" id="rating" name="rating" required>

                <button type="submit" name="submit_machinery">Add Machinery</button>
            </form>
        </div>
    </div>

    <!-- Add Vlog Modal -->
    <div id="vlog-modal" class="modal hidden">
        <div class="modal-content">
            <h2>Add New Vlog</h2>
            <span class="close-btn" onclick="closeVlogModal()">&times;</span>
            <form action="add_vlog.php" method="POST">
                <label for="vlog_title">Title:</label>
                <input type="text" id="vlog_title" name="vlog_title" required>

                <label for="vlog_video_url">YouTube Video URL:</label>
                <input type="text" id="vlog_video_url" name="vlog_video_url" required>

                <label for="vlog_description">Description:</label>
                <textarea id="vlog_description" name="vlog_description" required></textarea>

                <button type="submit" name="submit_vlog">Add Vlog</button>
            </form>
        </div>
    </div>
    <script>
        function showTab(tabId) {
            const tabs = ['dashboard', 'rent_orders', 'machinery', 'vlogs', 'reviews', 'payments'];
            tabs.forEach(tab => {
                document.getElementById(`${tab}-content`).classList.add('hidden');
                document.getElementById(`${tab}-tab`).classList.remove('active');
            });
            document.getElementById(`${tabId}-content`).classList.remove('hidden');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        }

        // Add Machinery Modal functions
        function showAddMachineryModal() {
            document.getElementById("machinery-modal").classList.remove("hidden");
        }

        function closeMachineryModal() {
            document.getElementById("machinery-modal").classList.add("hidden");
        }

        // Add Vlog Modal functions
        function showAddVlogModal() {
            document.getElementById("vlog-modal").classList.remove("hidden");
        }

        function closeVlogModal() {
            document.getElementById("vlog-modal").classList.add("hidden");
        }

        // Open correct tab on redirect with ?tab=machinery
        window.addEventListener("DOMContentLoaded", () => {
            const tabParam = new URLSearchParams(window.location.search).get("tab");
            if (tabParam) showTab(tabParam);
        });

        // ESC key closes modal
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape") {
                closeMachineryModal();
                closeVlogModal();
            }
        });

        const salesLabels = <?= json_encode($salesLabels) ?>;
        const salesData = <?= json_encode($salesData) ?>;
        const machineryLabels = <?= json_encode($labels) ?>;
        const machineryData = <?= json_encode($machineryPerMonth) ?>;

        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Monthly Sales',
                    data: salesData,
                    borderColor: '#0d832d',
                    backgroundColor: 'rgba(13, 131, 45, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Machinery Chart
        const machineryCtx = document.getElementById('machineryChart').getContext('2d');
        new Chart(machineryCtx, {
            type: 'bar',
            data: {
                labels: machineryLabels,
                datasets: [{
                    label: 'Machinery Rented',
                    data: machineryData,
                    backgroundColor: '#48A6A7'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>

</html>
