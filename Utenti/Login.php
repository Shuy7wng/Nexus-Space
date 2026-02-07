<?php
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $password = $_POST['password'];

    // Connessione al DB
    $conn = new mysqli('localhost', 'root', '', 'nexus');
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE nickname = ?");
    $stmt->bind_param("s", $nickname);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['nickname'] = $nickname;
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Password errata!";
        }
    } else {
        $message = "Nickname non trovato!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Login Nexus</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
header {
    position: absolute;
    top: 10px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    font-size: 16px;
}
.logo{
    width: 120px; 
    height: auto; 
}
.Login {
    width: 30px;  /* dimensione desiderata */
    height: auto; /* mantiene le proporzioni */
}

nav {
    padding: 1rem;
    margin: auto 20px 0px auto;
}

nav ul {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 20px;
}
nav li {
    font-size: 1.4rem;
}
nav a {
    color: black;
    text-decoration: none;
    font-weight: 500;
}

nav a:hover {
    text-decoration: underline;
}
.login-box {
    border: 1px solid #000;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    width: 300px;
}
.login-box input[type="text"], .login-box input[type="password"] {
    width: 90%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 20px;
    border: 1px solid #000;
}
.login-box button {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
}
</style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="login-box">
    <h2>LOGIN</h2>
    <div><img src="login-icon.png" alt="Login" class="Login"></div>
    <form method="POST">
        <input type="text" name="nickname" placeholder="Nickname" required><br>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">→</button>
    </form>
    <div class="message"><?= $message ?></div>
</div>
</body>

<?php include 'footer.php'; ?>
</html>
