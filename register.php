
<?php
session_start();
require 'config/koneksi.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($nama == "" || $username == "" || $password == "") {
        $error = "Semua kolom wajib diisi.";
    } elseif (strlen($username) < 4) {
        $error = "Username minimal 4 karakter.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM users WHERE username = ?"
        );
        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Username sudah digunakan.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $query = mysqli_prepare(
                $koneksi,
                "INSERT INTO users (nama_lengkap, username, password)
                 VALUES (?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $query, "sss", $nama, $username, $hash
            );

            if (mysqli_stmt_execute($query)) {
                mysqli_stmt_close($query);
                mysqli_stmt_close($cek);
                header("Location: login.php");
                exit;
            }

            $error = "Registrasi gagal.";
            mysqli_stmt_close($query);
        }

        mysqli_stmt_close($cek);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hi Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
body {
    background: #F5F1EA;
    color: #26364A;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
}

.register-box {
    max-width: 420px;
    margin: 60px auto;
    padding: 36px;
    background: #FFFFFF;
    border: 1px solid #E8E1D6;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(38, 54, 74, 0.05);
}

.register-box h3 {
    font-size: 27px;
    font-weight: 800;
    letter-spacing: -1.2px;
}

.register-box p {
    font-size: 13px;
}

.form-label {
    font-weight: 600;
    font-size: 13px;
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
    background: #26364A;
    color: #FFFFFF;
    padding: 12px;
    border: none;
    border-radius: 9px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
}

.btn-library:hover {
    background: #34485F;
    color: #FFFFFF;
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

<div class="register-box">
    <h3 class="text-center mb-2">Hi Library</h3>
    <p class="text-center text-secondary mb-4">Buat akun baru</p>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control"
                   minlength="4" maxlength="20" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control"
                   minlength="6" required>
        </div>

        <button type="submit" class="btn btn-library w-100">
            Daftar
        </button>
    </form>

    <p class="text-center mt-3 mb-0">
        Sudah punya akun? <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>
