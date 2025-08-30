<?php
session_start();
include("conn.php");

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $query = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Message sent successfully!');</script>";
    } else {
        echo "<script>alert('Failed to send message.');</script>";
    }
}

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConstructHub</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>
    <header>
                <a href="index.php" class="brand">🏗️ ConstructionHub</a>


        <nav >
            <ul>
                <li>
                    <a class="navList" href="index.php" >Home</a>
                </li>
                <li >
                    <a class="navList" href="Vlog.php" >Vlog</a>
                </li>
                <li style="text-decoration: underline; cursor: pointer;" >
                    Contact
                </li>
                <li>
                    <a class="navList" href="aboutus.php">About Us</a>
                </li>
            </ul>
        </nav>

        <div class="login-section">
<?php if (isset($_SESSION['user_name'])): ?>
    <div class="Login">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?> | <a class="logoutbtn" href="logout.php">Logout</a></div>
<?php else: ?>
    <div class="Login"><a href="#" onclick="registerShow()">Become Renter</a></div>
    <div class="Login"><a href="#" onclick="loginShow()">Login</a></div>
<?php endif; ?>

        <button id="show-hide" onclick="ShowHide()">
            <i class="fa-solid fa-bars"></i>
        </button>
    </header>

    <nav id="nav-bar" >
        <ul>
            <li>
                    <a href="index.php" >Home</a>
                </li>
                <li >
                    <a href="Vlog.php" >Vlog</a>
                </li>
                <li style="text-decoration: underline; " >
                    Contact
                </li>
                <li>
                    <a href="aboutus.php">About Us</a>
                </li>
        </ul>
    </nav>

    <div class="contact-details">
        <p><i class="fas fa-map-marker-alt"></i> 123 Industrial Road, Sector 21, Kadaghari, Kathmandu</p>
        <p><i class="fas fa-phone"></i> +91 98765 43210</p>
        <p><i class="fas fa-envelope"></i> support@constructhub.com</p>
        <p><i class="fas fa-clock"></i> Mon - Sat: 9:00 AM to 6:00 PM</p>
      </div>

    <main class="contact-main">
        <section class="contact-section">
          <h2>Contact Us</h2>
          <p>If you have any questions, feel free to drop us a message below.</p>
          <form class="contact-form" method="POST" action="contact.php">
    <input type="text" name="name" placeholder="Full Name" required />
    <input type="email" name="email" placeholder="Email Address" required />
    <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
    <button type="submit" name="submit">Send Message</button>
</form>

        </section>
      </main>

      <footer class="site-footer">
        <div class="footer-container">
          <div class="footer-column">
            <h3 class="footerText">ConstructHub</h3>
            <p class="footer-p">Rent reliable construction equipment anytime, anywhere. We help contractors and builders find what they need.</p>
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