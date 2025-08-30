<?php
include("conn.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        header("Location: admin_login.php?registered=1");
        exit();
    } else {
        $error = "Registration failed!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration</title>
</head>
<body>
    <h2>Admin Registration</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Username:</label><input type="text" name="username" required><br>
        <label>Password:</label><input type="password" name="password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>