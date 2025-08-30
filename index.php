<?php
include("conn.php");

session_start();

// Add to cart logic
if (isset($_GET['action']) && $_GET['action'] == 'add_to_cart' && isset($_GET['item_id'])) {
    // Require login
    if (!isset($_SESSION['user_id'])) {
        echo "<script>
    window.onload = function() {
        if (confirm('You must login to make a rent. Login now?')) {
            loginShow();
        } else {
            window.location.href = 'index.php';
        }
    };
</script>";

        exit();
    }

    $item_id = $_GET['item_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (!in_array($item_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $item_id;
        echo "<script>alert('Item added to cart!'); window.location.href='index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Item is already in the cart!'); window.location.href='index.php';</script>";
        exit();
    }
}

// Make a rent logic
if (isset($_GET['action']) && $_GET['action'] == 'rent_now' && isset($_GET['item_id'])) {
    // Require login
    if (!isset($_SESSION['user_id'])) {
        echo "<script>
    window.onload = function() {
        if (confirm('You must login to make a rent. Login now?')) {
            loginShow();
        } else {
            window.location.href = 'index.php';
        }
    };
</script>";

        exit();
    }

    $item_id = $_GET['item_id'];
    $_SESSION['rent_now'] = $item_id;
    echo "<script>window.location.href = 'rent.php?item_id=$item_id';</script>";
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $full_name = $_POST['Uname'];
    $email = $_POST['Uemail'];
    $number = $_POST['Unumber'];
    $password = $_POST['Upass'];
    $confirm_password = $_POST['Uconfpass'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepared statement to avoid SQL injection
        $stmt = $conn->prepare("INSERT INTO usercredentials (userfullname, useremail, userpnumber, userpassword) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $number, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('Registration Successful!'); window.location.href = 'index.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error inserting data.');</script>";
        }

        $stmt->close();
    }
}

//for the login functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['login_email'];
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT id, userfullname, userpassword FROM usercredentials WHERE useremail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $fullname, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $fullname;
            echo "<script>window.location.href = 'index.php';</script>";
            exit();
        } else {
            echo "<script>alert('Incorrect password.');</script>";
        }
    } else {
        echo "<script>alert('User not found.');</script>";
    }

    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ConstructHub</title>
    <link rel="stylesheet" href="styles.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <!-- Equipment Detail Floating Div -->
<div id="equipment-detail" class="hidden detail-modal">
  <div class="detail-content">
    <span id="close-detail" class="close-btn">&times;</span>
    <img id="detail-image" src="" alt="Equipment Image" />
    <h2 id="detail-name"></h2>
    <p id="detail-availability"></p>
    <p id="detail-description"></p>
    <button id="detail-rent" class="cart-btn">Make a Rent</button>
    <button id="detail-cart" class="cart-btn"><i class="fas fa-cart-plus"></i> Add to Cart</button>
  </div>
</div>

<style>
.detail-modal {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex; justify-content: center; align-items: center;
  z-index: 9999;
}
.detail-content {
  background: #fff; padding: 20px; border-radius: 10px;
  width: 90%; max-width: 600px;
  text-align: center; position: relative;
}
.detail-content img {
  max-width: 100%; border-radius: 8px;
}
.close-btn {
  position: absolute; top: 10px; right: 15px;
  font-size: 24px; cursor: pointer; color: #333;
}
.hidden { display: none; }
</style>


    <header>
        <a href="index.php" class="brand">🏗️ ConstructionHub</a>
        <nav>
            <ul>
                <li style="text-decoration: underline; cursor: pointer;">Home</li>
                <li><a class="navList" href="Vlog.php">Vlog</a></li>
                <li style="color: var(--AccentColor)"><a class="navList" href="contact.php">Contact</a></li>
                <li><a class="navList" href="aboutus.php">About Us</a></li>
            </ul>
        </nav>

        <div class="login-section">
            <?php if (isset($_SESSION['user_name'])): ?>
                <div class="Login">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?> | <a class="logoutbtn"
                        href="logout.php">Logout</a></div>
            <?php else: ?>
                <div class="Login"><a href="#" onclick="registerShow()">Become Renter</a></div>
                <div class="Login"><a href="#" onclick="loginShow()">Login</a></div>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>

            <div class="cart" style="display:inline-block; margin-left:10px;">
                <a href="cart.php" style="color: white;">
                    <i class="fa-solid fa-cart-shopping fa-lg" style="color: #ffffff;"></i>
                    <span class="cartlength">
                        <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                    </span>
                </a>
            </div>


        <?php endif; ?>

        </div>



        <button id="show-hide" onclick="ShowHide()">
            <i class="fa-solid fa-bars"></i>
        </button>

    </header>

    <nav id="nav-bar">
        <ul>
            <li style="text-decoration: underline">Home</li>
            <li><a href="Vlog.php">Vlog</a></li>
            <li style="color: var(--AccentColor)"><a href="contact.php">Contact</a></li>
            <li><a href="aboutus.php">About Us</a></li>
        </ul>
    </nav>

    <div class="search-bar-container">
        <div class="textarea">
            <h3>Rent the equipment you need, when you need!</h3>
        </div>
        <div class="textarea1">
            <h6>Smartly search the equipment and make a rent here.</h6>
        </div>

        <form method="GET" class="searchbar-container">
            <input type="search" name="search" placeholder="Search for equipment..."
                value="<?= htmlspecialchars($searchQuery ?? '') ?>" />
            <button id="search-btn"><i class="fas fa-search"></i></button>
        </form>

    </div>

    <?php
    $searchQuery = "";
    if (isset($_GET['search'])) {
        $searchQuery = mysqli_real_escape_string($conn, $_GET['search']);
        $sql = "SELECT * FROM machinery WHERE name LIKE '%$searchQuery%' OR model LIKE '%$searchQuery%' ORDER BY id DESC";
    } else {
        $sql = "SELECT * FROM machinery ORDER BY id DESC";
    }
    $result = mysqli_query($conn, $sql);
    ?>

    <div id="loader" style="display: none;">
  <div class="spinner"></div>
</div>



    <main>
        <div class="card-container">
            <?php
            $result = mysqli_query($conn, "SELECT * FROM machinery ORDER BY id DESC LIMIT 20");

            while ($row = mysqli_fetch_assoc($result)):
                $availability_class = $row['availability'] === 'Available' ? 'available' : 'not-available';
                ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" />
                    <div class="card-content">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <div class="available-rating">
                            <p class="availability <?= $availability_class ?>"><?= $row['availability'] ?></p>
                            <div class="ratings">⭐⭐⭐⭐☆</div> <!-- You can add rating logic later -->
                        </div>
                    </div>
                    <div class="button-container">
                        <a href="index.php?action=rent_now&item_id=<?= $row['id'] ?>" class="cart-btn">Make a rent</a>
                        <a href="index.php?action=add_to_cart&item_id=<?= $row['id'] ?>" class="cart-btn"><i
                                class="fas fa-cart-plus"></i> Add to Cart</a>

                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    </main>

    <!-- Login Modal -->
    <div id="login-modal" class="login-modal hidden">
        <div class="login-card glass">
            <span class="close-btn" onclick="closeLogin()">&times;</span>
            <h2>Login to ConstructHub</h2>
            <form action="#" method="POST">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" placeholder="Email" required name="login_email" />
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" required name="login_password" />
                </div>
                <button type="submit" name="login">Login</button>
            </form>

            <p style="text-align: center">
                Don't have an account?
                <button id="register-button" onclick="registerShow()">Register</button>
            </p>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="register-modal" class="register-modal hidden">
        <div class="login-card glass">
            <span class="close-btn" onclick="closeRegister()">&times;</span>
            <h2 style="text-align: center">Register to ConstructHub</h2>
            <form action="#" method="POST">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Full Name" required name="Uname" />
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" placeholder="Email" required name="Uemail" />
                </div>
                <div class="input-group">
                    <i class="fas fa-phone"></i>
                    <input type="text" placeholder="Number" required name="Unumber" />
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" required name="Upass" />
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Confirm Password" required name="Uconfpass" />
                </div>
                <button type="submit" name="register">Register</button>
            </form>
            <p style="text-align: center">
                Already have an account?
                <button onclick="loginShow()">Login</button>
            </p>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-column">
                <h3 class="footerText">ConstructHub</h3>
                <p class="footer-p">Rent reliable construction equipment anytime, anywhere. We help contractors and
                    builders find what they need.</p>
            </div>
            <div class="footer-column">
                <h4 class="footerText">Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="Vlog.php">Vlog</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="aboutus.php">About Us</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4 class="footerText">Contact</h4>
                <p><i class="fas fa-phone"></i> +91 98765 43210</p>
                <p><i class="fas fa-envelope"></i> support@constructhub.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Kadaghari, Kathmandu, Nepal</p>
            </div>
            <div class="footer-column">
                <h4 class="footerText">Follow Us</h4>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 ConstructHub. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>

</html>