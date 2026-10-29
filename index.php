
<?php
require 'config/koneksi.php';
require 'auth.php';

$total_buku = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku")
)['total'];

$total_kategori = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(DISTINCT kategori) AS total
    FROM buku WHERE kategori IS NOT NULL AND kategori != ''")
)['total'];

$total_stok = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COALESCE(SUM(stok), 0) AS total FROM buku")
)['total'];

$buku = mysqli_query(
    $koneksi,
    "SELECT * FROM buku ORDER BY id DESC LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hi Library</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #F5F1EA;
            color: #26364A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #E8E1D6;
            padding: 16px 0;
        }

        .navbar-brand {
            color: #26364A;
            font-weight: 800;
            letter-spacing: -0.8px;
        }

        .nav-link {
            color: #68717D;
            font-weight: 600;
        }

        .nav-link.active,
        .nav-link:hover {
            color: #C77B5A;
        }

        .page {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .welcome {
            margin-bottom: 28px;
        }

        .welcome h1 {
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .welcome p {
            color: #7B8188;
        }

        .stat-card {
            background: white;
            border: 1px solid #E8E1D6;
            border-radius: 12px;
            padding: 22px;
            height: 100%;
        }

        .stat-card p {
            color: #7B8188;
            margin-bottom: 8px;
        }

        .stat-card h2 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
        }

        .section-card {
            margin-top: 25px;
            padding: 24px;
            background: white;
            border: 1px solid #E8E1D6;
            border-radius: 12px;
        }

        .section-title {
            font-size: 17px;
            font-weight: 700;
        }

        .btn-library {
            background: #26364A;
            color: white;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-library:hover {
            background: #34485F;
            color: white;
        }

        .table {
            vertical-align: middle;
        }

        .table th {
            color: #7B8188;
            font-size: 12px;
            font-weight: 600;
        }

        .table td {
            font-size: 13px;
        }

        .book-title {
            font-weight: 700;
        }

        .logout-link {
            color: #C77B5A;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">Hi Library.</a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <a class="nav-link active" href="index.php">Dashboard</a>
                <a class="nav-link" href="buku.php">Data Buku</a>
                <span class="nav-link">
                    Halo, <?= htmlspecialchars($_SESSION['nama']) ?>
                </span>
                <a class="nav-link logout-link" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<main class="page">

    <div class="welcome">
        <h1>Dashboard</h1>
        <p>Selamat datang di Hi Library. Kelola koleksi bukumu di sini.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="stat-card">
                <p>Total Buku</p>
                <h2><?= $total_buku ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <p>Kategori Buku</p>
                <h2><?= $total_kategori ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <p>Total Stok</p>
                <h2><?= $total_stok ?></h2>
            </div>
        </div>
    </div>

    <section class="section-card">
        <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
            <h2 class="section-title mb-0">Buku Terbaru</h2>
            <a href="buku.php" class="btn btn-library">Lihat Semua</a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($buku) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($buku)): ?>
                            <tr>
                                <td class="book-title">
                                    <?= htmlspecialchars($row['judul']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['penulis'] ?? '') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['kategori'] ?? '') ?>
                                </td>
                                <td><?= (int) $row['stok'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                Belum ada data buku. Yuk, tambahkan buku pertamamu!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
