
<?php
require 'config/koneksi.php';
require 'auth.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id']) || (int) $_GET['id'] < 1) {
    header("Location: buku.php");
    exit;
}

$id = (int) $_GET['id'];

$query = mysqli_prepare($koneksi, "SELECT * FROM buku WHERE id = ?");
mysqli_stmt_bind_param($query, "i", $id);
mysqli_stmt_execute($query);
$hasil = mysqli_stmt_get_result($query);
$buku = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($query);

if (!$buku) {
    header("Location: buku.php");
    exit;
}

$error = "";

$isbn = $buku['isbn'] ?? '';
$judul = $buku['judul'] ?? '';
$penulis = $buku['penulis'] ?? '';
$penerbit = $buku['penerbit'] ?? '';
$tahun = $buku['tahun_terbit'] ?? '';
$kategori = $buku['kategori'] ?? '';
$stok = (string) ($buku['stok'] ?? 0);
$foto_lama = $buku['foto'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $isbn = trim($_POST['isbn'] ?? '');
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $penerbit = trim($_POST['penerbit'] ?? '');
    $tahun = ($_POST['tahun'] ?? '') !== '' ? $_POST['tahun'] : null;
    $kategori = trim($_POST['kategori'] ?? '');
    $stok = $_POST['stok'] ?? '0';

    $foto_baru = null;
    $lokasi_foto_baru = null;

    if ($judul == "") {
        $error = "Judul buku wajib diisi.";
    } elseif (!ctype_digit((string) $stok) || (int) $stok < 0) {
        $error = "Stok harus berupa angka nol atau lebih.";
    } elseif ($tahun !== null &&
        (!ctype_digit((string) $tahun) ||
        (int) $tahun < 1901 || (int) $tahun > 2155)) {
        $error = "Tahun terbit tidak valid.";
    }

    // Proses foto baru jika dipilih
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
            } elseif (!is_uploaded_file($_FILES['foto']['tmp_name'])) {
                $error = "File foto tidak valid.";
            } else {
                $nama_foto = bin2hex(random_bytes(16))
                           . '.' . $format[$tipe];

                $folder = __DIR__ . '/uploads/';

                if (!is_dir($folder) || !is_writable($folder)) {
                    $error = "Folder uploads belum tersedia atau tidak bisa ditulisi.";
                } elseif (move_uploaded_file(
                    $_FILES['foto']['tmp_name'],
                    $folder . $nama_foto
                )) {
                    $foto_baru = $nama_foto;
                    $lokasi_foto_baru = $folder . $nama_foto;
                } else {
                    $error = "Foto gagal disimpan.";
                }
            }
        }
    }

    if ($error == "") {
        // Jika tidak memilih foto baru, gunakan foto lama
        $foto_final = $foto_baru ?? $foto_lama;

        $query = mysqli_prepare(
            $koneksi,
            "UPDATE buku
             SET isbn = ?, judul = ?, penulis = ?, penerbit = ?,
                 tahun_terbit = ?, kategori = ?, stok = ?, foto = ?
             WHERE id = ?"
        );

        $stok_angka = (int) $stok;

        mysqli_stmt_bind_param(
            $query,
            "ssssssisi",
            $isbn,
            $judul,
            $penulis,
            $penerbit,
            $tahun,
            $kategori,
            $stok_angka,
            $foto_final,
            $id
        );

        if (mysqli_stmt_execute($query)) {
            mysqli_stmt_close($query);

            // Hapus foto lama hanya setelah data berhasil diperbarui
            if ($foto_baru !== null && $foto_lama !== '') {
                $nama_lama = basename($foto_lama);
                $path_lama = __DIR__ . '/uploads/' . $nama_lama;

                if (is_file($path_lama)) {
                    unlink($path_lama);
                }
            }

            header("Location: buku.php");
            exit;
        } else {
            if ($lokasi_foto_baru !== null &&
                is_file($lokasi_foto_baru)) {
                unlink($lokasi_foto_baru);
            }

            $error = "Data buku gagal diperbarui.";
            mysqli_stmt_close($query);
        }
    }

    // Pertahankan input saat ada kesalahan
    $buku = array_merge($buku, [
        'isbn' => $isbn,
        'judul' => $judul,
        'penulis' => $penulis,
        'penerbit' => $penerbit,
        'tahun_terbit' => $tahun,
        'kategori' => $kategori,
        'stok' => $stok,
        'foto' => $foto_baru ?? $foto_lama
    ]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Hi Library</title>

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
    <h1>Edit Buku</h1>
    <p class="subtitle mb-4">Perbarui informasi buku yang dipilih.</p>

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
                       value="<?= htmlspecialchars($buku['isbn'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Judul Buku *</label>
                <input type="text" name="judul" class="form-control"
                       value="<?= htmlspecialchars($buku['judul']) ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Penulis</label>
                    <input type="text" name="penulis" class="form-control"
                           value="<?= htmlspecialchars($buku['penulis'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control"
                           value="<?= htmlspecialchars($buku['penerbit'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Terbit</label>
                    <input type="number" name="tahun" class="form-control"
                           min="1901" max="2155"
                           value="<?= htmlspecialchars((string) ($buku['tahun_terbit'] ?? '')) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-control"
                           value="<?= htmlspecialchars($buku['kategori'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Stok Buku</label>
                <input type="number" name="stok" class="form-control"
                       min="0" value="<?= (int) $buku['stok'] ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Sampul Buku</label>

                <?php
                $foto_tampil = basename($buku['foto'] ?? '');
                $path_tampil = __DIR__ . '/uploads/' . $foto_tampil;
                ?>

                <?php if ($foto_tampil !== '' && is_file($path_tampil)): ?>
                    <div class="mb-3">
                        <img
                            src="uploads/<?= rawurlencode($foto_tampil) ?>"
                            alt="Sampul <?= htmlspecialchars($buku['judul']) ?>"
                            style="width:90px; height:125px; object-fit:cover; border-radius:8px;"
                        >
                        <div class="text-muted mt-2" style="font-size:12px;">
                            Sampul saat ini
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="font-size:12px;">
                        Belum ada foto sampul.
                    </p>
                <?php endif; ?>

                <input type="file" name="foto" class="form-control"
                       accept=".jpg,.jpeg,.png,.webp">

                <small class="text-muted">
                    Pilih foto baru jika ingin mengganti sampul.
                    JPG, PNG, atau WebP, maksimal 2 MB.
                    Kosongkan jika ingin mempertahankan foto lama.
                </small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-library">
                    Simpan Perubahan
                </button>
                <a href="buku.php" class="btn btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
