
<?php
session_start();
require 'config/koneksi.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_lengkap, username, password
         FROM users WHERE username = ?"
    );

    mysqli_stmt_bind_param($query, "s", $username);
    mysqli_stmt_execute($query);
    $hasil = mysqli_stmt_get_result($query);
    $user = mysqli_fetch_assoc($hasil);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['username'] = $user['username'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah.";
    }

    mysqli_stmt_close($query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hi Library</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            background: #F5F1EA;
            color: #26364A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
        }

        .login-box {
            max-width: 420px;
            margin: 80px auto;
            padding: 36px;
            background: #FFFFFF;
            border: 1px solid #E8E1D6;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(38, 54, 74, 0.05);
        }

        .login-box h3 {
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -1.2px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
        }

        .form-control {
            padding: 11px 13px;
            border: 1px solid #E5DED4;
            border-radius: 9px;
            font-family: inherit;
            font-size: 13px;
        }

        .form-control:focus {
            border-color: #C77B5A;
            box-shadow: 0 0 0 3px rgba(199, 123, 90, 0.12);
        }

        .btn-library {
            width: 100%;
            padding: 12px;
            background: #26364A;
            color: white;
            border: none;
            border-radius: 9px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-library:hover {
            background: #34485F;
            color: white;
        }

        a {
            color: #C77B5A;
            font-weight: 600;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="login-box">
    <h3 class="text-center mb-2">Hi Library</h3>
    <p class="text-center text-secondary mb-4">
        Selamat datang kembali! Silakan login.
    </p>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['daftar'])): ?>
        <div class="alert alert-success">
            Akun berhasil dibuat. Silakan login.
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username"
                   class="form-control" autocomplete="username" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password"
                   class="form-control" autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn btn-library">
            Login
        </button>
    </form>

    <p class="text-center mt-4 mb-0">
        Belum punya akun?
        <a href="register.php">Daftar sekarang</a>
    </p>
</div>

</body>
</html>
