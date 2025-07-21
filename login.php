<?php
session_start();
require 'config.php'; // koneksi menggunakan PDO ($pdo)

// === LOGOUT ===
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// === Variabel Pesan ===
$login_error = '';
$register_error = '';
$register_success = '';

// === LOGIN PAKAI NPM ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $npm = $_POST['npm'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE npm = ?");
        $stmt->execute([$npm]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_npm'] = $user['npm'];
            header("Location: index.php");
            exit;
        } else {
            $login_error = "NPM atau password salah!";
        }
    } catch (PDOException $e) {
        $login_error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// === REGISTRASI ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $npm = $_POST['npm'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Cek apakah NPM atau email sudah digunakan
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE npm = ? OR email = ?");
        $check->execute([$npm, $email]);
        $exists = $check->fetchColumn();

        if ($exists > 0) {
            $register_error = "NPM atau Email sudah terdaftar!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, npm, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $npm, $email, $password]);
            $register_success = "Registrasi berhasil! Silakan login.";
        }
    } catch (PDOException $e) {
        $register_error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://kit.fontawesome.com/2efc16a506.js" crossorigin="anonymous"></script>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap");
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Montserrat", sans-serif;
        }
        
        body {
            background-color: #004754;
            background: linear-gradient(to right, #004754, #bebd00);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100vh;
        }
        
        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(60, 48, 48, 0.35);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }
        
        .container p {
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.3px;
            margin: 20px 0;
        }
        
        .fa-brands {
            color: #004754;
        }
        
        .container span {
            font-size: 12px;
        }
        
        .container a {
            color: #333333;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
        }
        
        .container button {
            background-color: #bebd00;
            color: #ffffff;
            font-size: 12px;
            padding: 10px 45px;
            border: 1px solid transparent;
            border-radius: 0px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
        }
        
        .container button.hidden {
            background-color: transparent;
            border-color: #ffffff;
        }
        
        .container form {
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }
        
        .container input {
            background-color: #cccccc;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 13px;
            border-radius: 0px;
            width: 100%;
            outline: none;
        }
        
        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }
        
        .sign-in {
            left: 0;
            width: 50%;
            z-index: 2;
        }
        
        .container.active .sign-in {
            transform: translateX(100%);
        }
        
        .sign-up {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }
        
        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: move 0.6s;
        }
        
        @keyframes move {
            0%,
            49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%,
            100% {
                opacity: 1;
                z-index: 5;
            }
        }
        
        .social-icons {
            margin: 20px 0;
        }
        
        .social-icons a {
            border: 1px solid #004754;
            border-radius: 25%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 3px;
            width: 40px;
            height: 40px;
        }
        
        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }
        
        .container.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }
        
        .toggle {
            background-color: #004754;
            height: 100%;
            background: linear-gradient(to right, #004754, #004754);
            color: #ffffff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }
        
        .container.active .toggle {
            transform: translateX(50%);
        }
        
        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }
        
        .toggle-left {
            transform: translateX(-200%);
        }
        
        .container.active .toggle-left {
            transform: translateX(0);
        }
        
        .toggle-right {
            right: 0;
            transform: translateX(0);
        }
        
        .container.active .toggle-right {
            transform: translateX(200%);
        }

        /* Style untuk pesan error/success */
        .error-message {
            color: #e74c3c;
            background-color: #fdecea;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            width: 100%;
            text-align: center;
        }
        
        .success-message {
            color: #2ecc71;
            background-color: #e8f8f0;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            width: 100%;
            text-align: center;
        }
    </style>
</head>

<body>
   <div class="container" id="container">
    <!-- REGISTER FORM -->
    <div class="form-container sign-up">
        <form method="POST" action="">
            <h1>Register With</h1>
            <div class="social-icons">
                <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
            <hr>
            <h1>OR</h1>
            <hr>
            <span>Fill Out The Following Info For Registration</span>

            <?php if (!empty($register_error)) : ?>
                <div class="error-message"><?= $register_error ?></div>
            <?php endif; ?>

            <?php if (!empty($register_success)) : ?>
                <div class="success-message"><?= $register_success ?></div>
            <?php endif; ?>

            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="npm" placeholder="NPM" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Sign Up</button>
        </form>
    </div>

    <!-- LOGIN FORM -->
    <div class="form-container sign-in">
        <form method="POST" action="">
            <h1>Login With</h1>
            <div class="social-icons">
                <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
            <hr>
            <h1>OR</h1>
            <hr>
            <span>Login With Your NPM & Password</span>

            <?php if (!empty($login_error)) : ?>
                <div class="error-message"><?= $login_error ?></div>
            <?php endif; ?>

            <input type="text" name="npm" placeholder="NPM" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>

    <!-- TOGGLE PANEL -->
    <div class="toggle-container">
        <div class="toggle">
            <div class="toggle-panel toggle-left">
                <h1>Welcome Back!</h1>
                <p>Provide your personal details to use all features</p>
                <button class="hidden" id="login">Sign In</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Hello</h1>
                <p>Register to use all features in our site</p>
                <button class="hidden" id="register">Sign Up</button>
            </div>
        </div>
    </div>
</div>

    <script>
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');

        registerBtn.addEventListener('click', () => {
            container.classList.add("active");
        });
        
        loginBtn.addEventListener('click', () => {
            container.classList.remove("active");
        });

        // Auto switch to login form if there's register success message
        <?php if(!empty($register_success)): ?>
            container.classList.remove("active");
        <?php endif; ?>
    </script>
</body>
</html>