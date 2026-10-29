
<?php
require 'config/koneksi.php';
require 'auth.php';

$error = "";

$isbn = "";
$judul = "";
$penulis = "";
$penerbit = "";
$tahun = "";
$kategori = "";
$stok = "0";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $isbn = trim($_POST['isbn'] ?? '');
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $penerbit = trim($_POST['penerbit'] ?? '');
    $tahun = ($_POST['tahun'] ?? '') !== '' ? $_POST['tahun'] : null;
    $kategori = trim($_POST['kategori'] ?? '');
    $stok = $_POST['stok'] ?? '0';

    $foto = null;
    $foto_simpan = null;

    // Validasi data buku
    if ($judul == "") {
        $error = "Judul buku wajib diisi.";
    } elseif (!ctype_digit((string) $stok) || (int) $stok < 0) {
        $error = "Stok harus berupa angka nol atau lebih.";
    } elseif ($tahun !== null &&
        (!ctype_digit((string) $tahun) ||
        (int) $tahun < 1901 || (int) $tahun > 2155)) {
        $error = "Tahun terbit tidak valid.";
    }

    // Periksa foto jika pengguna mengunggahnya
    if ($error == "" &&
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $error = "Foto gagal diunggah.";
        } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $error = "Ukuran foto maksimal 2 MB.";
        } else {
            $info = new finfo(FILEINFO_MIME_TYPE);
            $tipe = $info->file($_FILES['foto']['tmp_name']);

            $format = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($format[$tipe])) {
                $error = "Foto harus berformat JPG, PNG, atau WebP.";
            } else {
                $nama_foto = bin2hex(random_bytes(16))
                           . '.' . $format[$tipe];

                $folder = __DIR__ . '/uploads/';

                if (!is_dir($folder) ||
                    !is_writable($folder)) {
                    $error = "Folder uploads belum tersedia atau tidak bisa ditulisi.";
                } elseif (!is_uploaded_file($_FILES['foto']['tmp_name'])) {
                    $error = "File foto tidak valid.";
                } elseif (move_uploaded_file(
                    $_FILES['foto']['tmp_name'],
                    $folder . $nama_foto
                )) {
                    $foto = $nama_foto;
                    $foto_simpan = $folder . $nama_foto;
                } else {
                    $error = "Foto gagal disimpan.";
                }
            }
        }
    }

    // Simpan data buku ke database
    if ($error == "") {
        $query = mysqli_prepare(
            $koneksi,
            "INSERT INTO buku
            (isbn, judul, penulis, penerbit, tahun_terbit,
             kategori, stok, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stok_angka = (int) $stok;

        mysqli_stmt_bind_param(
            $query,
            "ssssssis",
            $isbn,
            $judul,
            $penulis,
            $penerbit,
            $tahun,
            $kategori,
            $stok_angka,
            $foto
        );

        if (mysqli_stmt_execute($query)) {
            mysqli_stmt_close($query);
            header("Location: buku.php");
            exit;
        } else {
            // Hapus file jika data gagal disimpan
            if ($foto_simpan !== null &&
                file_exists($foto_simpan)) {
                unlink($foto_simpan);
            }

            $error = "Buku gagal ditambahkan. Silakan coba lagi.";
            mysqli_stmt_close($query);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - Hi Library</title>

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

        .nav-link:hover {
            color: #C77B5A;
        }

        .page {
            max-width: 800px;
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

        .form-panel {
            background: white;
            border: 1px solid #E8E1D6;
            border-radius: 12px;
            padding: 28px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
        }

        .form-control {
            padding: 11px 12px;
            border: 1px solid #E5DED4;
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
            padding: 11px 18px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-library:hover {
            background: #34485F;
            color: white;
        }

        .btn-cancel {
            background: #E8E1D6;
            color: #26364A;
            border-radius: 8px;
            padding: 11px 18px;
            font-size: 13px;
            font-weight: 600;
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
            <div class="navbar-nav ms-auto gap-lg-3">
                <a class="nav-link" href="index.php">Dashboard</a>
                <a class="nav-link" href="buku.php">Data Buku</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<main class="page">
    <h1>Tambah Buku</h1>
    <p class="subtitle mb-4">Masukkan informasi buku baru.</p>

    <div class="form-panel">

        <?php if ($error != ""): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-control"
                       value="<?= htmlspecialchars($isbn) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Judul Buku *</label>
                <input type="text" name="judul" class="form-control"
                       value="<?= htmlspecialchars($judul) ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Penulis</label>
                    <input type="text" name="penulis" class="form-control"
                           value="<?= htmlspecialchars($penulis) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control"
                           value="<?= htmlspecialchars($penerbit) ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Terbit</label>
                    <input type="number" name="tahun" class="form-control"
                           min="1901" max="2155"
                           value="<?= htmlspecialchars((string) ($tahun ?? '')) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-control"
                           placeholder="Contoh: Novel, Pendidikan"
                           value="<?= htmlspecialchars($kategori) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Stok Buku</label>
                <input type="number" name="stok" class="form-control"
                       min="0" value="<?= htmlspecialchars((string) $stok) ?>"
                       required>
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Sampul Buku</label>
                <input type="file" name="foto" class="form-control"
                       accept=".jpg,.jpeg,.png,.webp">
                <small class="text-muted">
                    Format JPG, PNG, atau WebP. Maksimal 2 MB. Foto boleh dikosongkan.
                </small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-library">
                    Simpan Buku
                </button>
                <a href="buku.php" class="btn btn-cancel">
                    Batal
                </a>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
