<?php
session_start();
include 'includes/db_connect.php';

// Cek jika sudah login, redirect ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = trim($_POST['password']);

    // Query untuk mencari user
    $stmt = $conn->prepare("SELECT user_id, password FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Cek apakah user ditemukan
    if (!$user) {
        $error = "Username tidak ditemukan.";
    } else {
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah.";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blog Dinamis</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            margin: 100px auto;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #004aad, #007bff);
        }

        .login-card h1 {
            font-size: 2em;
            margin-bottom: 30px;
            color: #222;
            position: relative;
        }

        .login-card h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: #004aad;
        }

        .login-card .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .login-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #222;
            font-size: 1.1em;
        }

        .login-card input {
            width: 500px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }

        .login-card input:focus {
            border-color: #004aad;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 74, 173, 0.3);
        }

        .login-card .error-message {
            color: #e63946;
            font-size: 0.9em;
            margin-bottom: 20px;
            text-align: left;
        }

        .login-card button {
            width: 100%;
            padding: 14px;
            background: #004aad;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .login-card button:hover {
            background: #003b8c;
        }

        .login-card .register-link {
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
            text-align: left;
        }

        .login-card .register-link a {
            color: #004aad;
            text-decoration: none;
            font-weight: 500;
        }

        .login-card .register-link a:hover {
            text-decoration: underline;
        }

        .login-card .back-button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9em;
            transition: background 0.3s;
        }

        .login-card .back-button:hover {
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .login-card {
                margin: 50px 20px;
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <button type="submit" name="submit">Masuk</button>
        </form>
        <p class="register-link">Belum punya akun? <a href="register.php">Register di sini</a></p>
        <a href="index.php" class="back-button">Kembali</a>
    </div>
</body>

</html>