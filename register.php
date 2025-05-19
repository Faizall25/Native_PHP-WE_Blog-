<?php
session_start();
include 'includes/db_connect.php';

// Cek jika sudah login, redirect ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validasi sederhana
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Semua kolom harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email tidak valid.";
    } else {
        // Cek apakah username atau email sudah ada
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "Username atau email sudah digunakan.";
        } else {
            // Hash password dan simpan user baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                // Redirect ke login setelah registrasi berhasil
                header("Location: login.php");
                exit;
            } else {
                $error = "Terjadi kesalahan saat registrasi.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blog Dinamis</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .register-card {
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

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #004aad, #007bff);
        }

        .register-card h1 {
            font-size: 2em;
            margin-bottom: 30px;
            color: #222;
            position: relative;
        }

        .register-card h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: #004aad;
        }

        .register-card .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .register-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #222;
            font-size: 1.1em;
        }

        .register-card input {
            width: 550px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }

        .register-card input:focus {
            border-color: #004aad;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 74, 173, 0.3);
        }

        .register-card .error-message {
            color: #e63946;
            font-size: 0.9em;
            margin-bottom: 20px;
            text-align: left;
        }

        .register-card button {
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

        .register-card button:hover {
            background: #003b8c;
        }

        .register-card .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9em;
            transition: background 0.3s;
        }

        .register-card .back-button:hover {
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .register-card {
                margin: 50px 20px;
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="register-card">
        <h1>Register</h1>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <button type="submit" name="submit">Daftar</button>
        </form>
        <a href="index.php" class="back-button">Kembali</a>
    </div>
</body>

</html>