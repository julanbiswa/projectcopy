<?php
session_start();
include("conn.php");
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
                    <a class="navList" href="index.php">Home</a>
                </li>
                <li>
                    <a class="navList" href="Vlog.php" >Vlog</a>
                </li>
                <li>
                    <a class="navList" href="contact.php" >Contact</a>
                </li>
                <li  style="text-decoration: underline; cursor: pointer; ">
                About Us
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
                    <a href="index.php">Home</a>
                </li>
                <li>
                    <a href="Vlog.php" >Vlog</a>
                </li>
                <li>
                    <a href="contact.php" >Contact</a>
                </li>
                <li  style="text-decoration: underline; ">
                <li><a href="aboutus.php">About Us</a></li>
                </li>
        </ul>
    </nav>

    <main class="about-main">
        <section class="about-section">
          <h2>About ConstructHub</h2>
          <p>
            ConstructHub is your one-stop solution for renting quality construction equipment on demand. 
            Whether you're a professional contractor or a DIY enthusiast, our platform offers a wide range of tools and machinery with flexible rental plans.
          </p>
          <p>
            Our mission is to simplify the rental process and make top-notch gear accessible to everyone across the country. Backed by passionate engineers, logistics pros, and customer-first tech, we’re here to power your next project — big or small.
          </p>

          <p>Welcome to ConstructHub, your reliable partner for renting high-quality construction equipment anytime, anywhere. Whether you’re a seasoned contractor, project manager, or a DIY enthusiast, we provide a wide selection of well-maintained machinery and tools tailored to meet the diverse needs of any construction or renovation project.

At ConstructHub, we believe that access to dependable equipment should be simple, fast, and affordable. Our platform is designed with user convenience in mind, offering flexible rental plans, transparent pricing, and hassle-free booking — all just a few clicks away. From excavators, mixers, and loaders to drills and scaffolding, we ensure that every piece of equipment is ready for peak performance.

Our mission is to streamline the equipment rental experience across Nepal, empowering individuals and businesses to complete projects efficiently and safely. We’re backed by a passionate team of engineers, logistics experts, and customer support professionals who are dedicated to delivering exceptional service and support at every step.

ConstructHub isn’t just a rental service — it’s a complete construction partner that grows with your needs. No matter the size or scope of your project, we’re here to provide the tools and reliability to help you build with confidence.

</p>
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
            <p><i class="fas fa-phone"></i> +977 98765 43210</p>
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