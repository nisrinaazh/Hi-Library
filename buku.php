<?php
require 'config/koneksi.php';
require 'auth.php';

$cari = trim($_GET['cari'] ?? '');

if ($cari != '') {
    $query = mysqli_prepare(
        $koneksi,
        "SELECT * FROM buku
         WHERE judul LIKE ? OR penulis LIKE ? OR kategori LIKE ?
         ORDER BY id DESC"
    );

    $keyword = "%$cari%";
    mysqli_stmt_bind_param(
        $query, "sss", $keyword, $keyword, $keyword
    );
    mysqli_stmt_execute($query);
    $buku = mysqli_stmt_get_result($query);
} else {
    $buku = mysqli_query(
        $koneksi,
        "SELECT * FROM buku ORDER BY id DESC"
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - Hi Library</title>

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

        .navbar {
            background: white;
            border-bottom: 1px solid #E8E1D6;
            padding: 16px 0;
        }

        .navbar-brand {
            color: #26364A;
            font-weight: 800;
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

        h1 {
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .subtitle {
            color: #7B8188;
        }

        .book-panel {
            background: white;
            padding: 24px;
            border: 1px solid #E8E1D6;
            border-radius: 12px;
        }

        .form-control {
            padding: 10px 12px;
            border-color: #E5DED4;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
        }

        .form-control:focus {
            border-color: #C77B5A;
            box-shadow: 0 0 0 3px rgba(199, 123, 90, 0.12);
        }

        .btn-library {
            background: #26364A;
            color: white;
            border: none;
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
        }

        .table td {
            font-size: 13px;
        }

        .logout-link {
            color: #C77B5A !important;
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
                <a class="nav-link" href="index.php">Dashboard</a>
                <a class="nav-link active" href="buku.php">Data Buku</a>
                <span class="nav-link">
                    Halo, <?= htmlspecialchars($_SESSION['nama']) ?>
                </span>
                <a class="nav-link logout-link" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<main class="page">
    <h1>Data Buku</h1>
    <p class="subtitle mb-4">
        Kelola koleksi buku yang tersedia di Hi Library.
    </p>

    <div class="book-panel">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="cari" class="form-control"
                       placeholder="Cari judul, penulis, kategori..."
                       value="<?= htmlspecialchars($cari) ?>">
                <button class="btn btn-library">Cari</button>
            </form>

            <a href="tambah.php" class="btn btn-library">
                + Tambah Buku
            </a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                    <th>No.</th>
                        <th>Sampul</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Tahun</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (mysqli_num_rows($buku) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($buku)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
    <?php if (!empty($row['foto']) &&
              is_file(__DIR__ . '/uploads/' . basename($row['foto']))): ?>
        <img
            src="uploads/<?= rawurlencode(basename($row['foto'])) ?>"
            alt="Sampul <?= htmlspecialchars($row['judul']) ?>"
            width="55"
            height="75"
            style="object-fit:cover; border-radius:6px;"
        >
    <?php else: ?>
        <div style="width:55px; height:75px;
                    background:#F5F1EA; border-radius:6px;
                    display:flex; align-items:center;
                    justify-content:center; color:#9A9389;
                    font-size:10px; text-align:center;">
            No Foto
        </div>
    <?php endif; ?>
</td>
                                <td>
                                    <strong><?= htmlspecialchars($row['judul']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($row['penulis'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['kategori'] ?? '') ?></td>
                                <td><?= htmlspecialchars((string) ($row['tahun_terbit'] ?? '')) ?></td>
                                <td><?= (int) $row['stok'] ?></td>
                                <td class="text-nowrap">
                                    <a href="edit.php?id=<?= (int) $row['id'] ?>"
                                       class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="hapus.php" method="POST"
          style="display:inline;"
          onsubmit="return confirm('Yakin ingin menghapus buku ini?');">

        <input type="hidden" name="id"
        value="<?= (int) $row['id']; ?>">

        <button type="submit"
                style="background:#C77B5A; color:white;
                       border:none; padding:7px 12px;
                       border-radius:6px; cursor:pointer;">
            Hapus
        </button>
    </form>
</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                Data buku belum tersedia.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
