<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KejaMtaani | Secure House Hunting</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/welcome.css">
</head>

<body>

<header>
    <div class="logo">
        <img src="assets/images/logo.png" alt="KejaMtaani Logo">
        <span>kejaMtaani</span>
    </div>

    <div class="nav">
        <a href="auth/login.php" class="login">Login</a>
        <a href="auth/select_role.php" class="signup">Sign Up</a>
    </div>
</header>

<main class="container">
    <div class="left">
        <h1>Find Verified Homes Around You</h1>
        <p>
            KejaMtaani connects tenants with trusted landlords through verified listings,
            virtual tours, and accurate location mapping — all in one secure platform.
        </p>

        <div class="buttons">
            <a href="auth/select_role.php" class="btn-primary">Get Started</a>
        </div>
    </div>

    <div class="right">
        <img src="assets/images/rental_houses.jpg" class="main-image" alt="Rental Houses">
    </div>
</main>

<footer>
    <div class="footer-content">
        <p>
            KejaMtaani is a secure house-hunting platform designed to reduce
            fraud and simplify rental search by verifying landlords and properties before listing.
        </p>

        <div class="footer-links">
            <a href="#">Contact</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </div>

    <div class="copyright">
        &copy; <?php echo date("Y"); ?> KejaMtaani. All rights reserved.
    </div>
</footer>

</body>
</html>
