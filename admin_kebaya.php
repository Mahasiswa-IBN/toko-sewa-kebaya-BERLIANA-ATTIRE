<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$adminTitle = 'Kelola Katalog Kebaya';
$dbConnected = $pdo ? true : false;

$errors = [];
$successMsg = '';

// Edit Kebaya pre-fill logic
$editMode = false;
$editData = [
    'id' => '',
    'nama' => '',
    'deskripsi' => '',
    'harga_sewa' => '',
    'stok' => 1,
    'kategori' => 'Modern',
    'gambar' => ''
];

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $editId = intval($_GET['id']);
    if ($dbConnected) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `kebaya` WHERE `id` = ?");
            $stmt->execute([$editId]);
            $res = $stmt->fetch();
            if ($res) {
                $editMode = true;
                $editData = $res;
            }
        } catch (\PDOException $e) {
            $errors[] = "Gagal mengambil data produk: " . $e->getMessage();
        }
    } else {
        $errors[] = "Database tidak terhubung. Tidak dapat memuat form edit.";
    }
}

// Handle POST: Add / Edit Kebaya
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_kebaya'])) {
    if (!$dbConnected) {
        $errors[] = "Aksi ditolak: Database tidak terhubung.";
    } else {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nama = trim($_POST['nama']);
        $deskripsi = trim($_POST['deskripsi']);
        $harga_sewa = floatval($_POST['harga_sewa']);
        $stok = intval($_POST['stok']);
        $kategori = trim($_POST['kategori']);
        
        // Handle Image Upload
        $gambar = $_POST['existing_gambar'] ?? '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['gambar']['tmp_name'];
            $fileName = $_FILES['gambar']['name'];
            $fileSize = $_FILES['gambar']['size'];
            $fileType = $_FILES['gambar']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                // Secure upload path
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = 'uploads/';
                
                if (!file_exists($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                
                $dest_path = $uploadFileDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Delete old image if updating
                    if ($id > 0 && !empty($gambar) && file_exists($uploadFileDir . $gambar)) {
                        @unlink($uploadFileDir . $gambar);
                    }
                    $gambar = $newFileName;
                } else {
                    $errors[] = 'Gagal memindahkan file gambar ke direktori upload.';
                }
            } else {
                $errors[] = 'Format gambar tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP.';
            }
        }

        if (empty($nama) || $harga_sewa <= 0 || $stok < 0) {
            $errors[] = 'Harap isi seluruh field formulir dengan benar.';
        }

        if (empty($errors)) {
            try {
                if ($id > 0) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE `kebaya` SET `nama` = ?, `deskripsi` = ?, `harga_sewa` = ?, `stok` = ?, `kategori` = ?, `gambar` = ? WHERE `id` = ?");
                    $stmt->execute([$nama, $deskripsi, $harga_sewa, $stok, $kategori, $gambar, $id]);
                    $_SESSION['flash_message'] = "Katalog kebaya '$nama' berhasil diperbarui.";
                    $_SESSION['flash_type'] = "success";
                } else {
                    // Insert
                    $stmt = $pdo->prepare("INSERT INTO `kebaya` (`nama`, `deskripsi`, `harga_sewa`, `stok`, `kategori`, `gambar`) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nama, $deskripsi, $harga_sewa, $stok, $kategori, $gambar]);
                    $_SESSION['flash_message'] = "Kebaya baru '$nama' berhasil ditambahkan ke katalog.";
                    $_SESSION['flash_type'] = "success";
                }
                header("Location: admin_kebaya.php");
                exit;
            } catch (\PDOException $e) {
                $errors[] = "Kesalahan Database: " . $e->getMessage();
            }
        }
    }
}

// Handle Action: Delete Kebaya
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $deleteId = intval($_GET['id']);
    if ($dbConnected) {
        try {
            // Delete associated image file first
            $stmt = $pdo->prepare("SELECT `gambar` FROM `kebaya` WHERE `id` = ?");
            $stmt->execute([$deleteId]);
            $kebayaImg = $stmt->fetchColumn();
            
            if (!empty($kebayaImg) && file_exists('uploads/' . $kebayaImg)) {
                @unlink('uploads/' . $kebayaImg);
            }

            $stmt = $pdo->prepare("DELETE FROM `kebaya` WHERE `id` = ?");
            $stmt->execute([$deleteId]);
            
            $_SESSION['flash_message'] = "Produk berhasil dihapus dari katalog.";
            $_SESSION['flash_type'] = "success";
        } catch (\PDOException $e) {
            $_SESSION['flash_message'] = "Gagal menghapus data: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }
    } else {
        $_SESSION['flash_message'] = "Fitur dinonaktifkan: Database tidak terhubung.";
        $_SESSION['flash_type'] = "warning";
    }
    header("Location: admin_kebaya.php");
    exit;
}

// Fetch all kebayas
$katalog = [];
if ($dbConnected) {
    try {
        $stmt = $pdo->query("SELECT * FROM `kebaya` ORDER BY `id` DESC");
        $katalog = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $dbConnected = false;
    }
}

// Fallback Mock Data if database not connected
if (!$dbConnected) {
    $katalog = [
        [
            'id' => 1,
            'nama' => 'Kebaya Brokat Modern Rose Gold',
            'deskripsi' => 'Kebaya brokat modern dengan sentuhan warna rose gold mewah.',
            'harga_sewa' => 150000.00,
            'stok' => 3,
            'kategori' => 'Modern',
            'gambar' => 'kebaya1.png'
        ],
        [
            'id' => 2,
            'nama' => 'Kebaya Beludru Klasik Hitam Gold',
            'deskripsi' => 'Kebaya kutubaru bahan beludru premium hitam pekat bermotif bordir emas khas Jawa.',
            'harga_sewa' => 200000.00,
            'stok' => 2,
            'kategori' => 'Tradisional',
            'gambar' => 'kebaya2.png'
        ],
        [
            'id' => 3,
            'nama' => 'Kebaya Encim Kartini Putih Gading',
            'deskripsi' => 'Kebaya model Encim Kartini dengan warna putih gading (broken white).',
            'harga_sewa' => 125000.00,
            'stok' => 4,
            'kategori' => 'Klasik',
            'gambar' => 'kebaya3.png'
        ]
    ];
}

include 'includes/admin_header.php';
?>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="card" style="margin-bottom: 25px; border-left: 4px solid var(--<?php echo $_SESSION['flash_type']; ?>);">
        <div class="card-content" style="padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; background-color: #FCFBFA;">
            <span style="font-weight: 500; font-size: 0.95rem; color: #555;">
                <i class="fa-solid fa-circle-info" style="margin-right: 8px; color: var(--accent);"></i>
                <?php echo $_SESSION['flash_message']; ?>
            </span>
            <button onclick="this.parentElement.parentElement.remove();" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #999;">&times;</button>
        </div>
    </div>
    <?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    ?>
<?php endif; ?>

<!-- Error Alerts -->
<?php if (!empty($errors)): ?>
    <div class="card" style="margin-bottom: 25px; border-left: 4px solid var(--danger);">
        <div class="card-content" style="padding: 15px 25px; background-color: #FFF2F2; color: #C53030; font-size: 0.9rem;">
            <ul style="padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo $err; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<!-- Form Card: Add / Edit Kebaya -->
<div class="card">
    <div class="card-header">
        <h2><?php echo $editMode ? 'Edit Kebaya: ' . htmlspecialchars($editData['nama']) : 'Tambah Kebaya Baru'; ?></h2>
        <?php if ($editMode): ?>
            <a href="admin_kebaya.php" class="btn-sm" style="border: 1px solid #ddd; background-color: #fff; text-decoration: none;">Batal Edit</a>
        <?php endif; ?>
    </div>
    <div class="card-content">
        <form method="POST" enctype="multipart/form-data" class="admin-form" style="max-width: 100%;">
            
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
            <input type="hidden" name="existing_gambar" value="<?php echo $editData['gambar']; ?>">

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <!-- Left side form inputs -->
                <div>
                    <div class="form-group">
                        <label for="nama">Nama Kebaya</label>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Contoh: Kebaya Brokat Rose Gold Premium" value="<?php echo htmlspecialchars($editData['nama']); ?>" required <?php echo !$dbConnected ? 'disabled' : ''; ?>>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Detail</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control" placeholder="Jelaskan detail bahan, warna, payet, kecocokan acara, dll..." required <?php echo !$dbConnected ? 'disabled' : ''; ?> style="resize: vertical;"><?php echo htmlspecialchars($editData['deskripsi']); ?></textarea>
                    </div>
                </div>

                <!-- Right side form inputs -->
                <div>
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select name="kategori" id="kategori" class="form-control" required <?php echo !$dbConnected ? 'disabled' : ''; ?>>
                            <option value="Modern" <?php echo ($editData['kategori'] == 'Modern') ? 'selected' : ''; ?>>Modern</option>
                            <option value="Tradisional" <?php echo ($editData['kategori'] == 'Tradisional') ? 'selected' : ''; ?>>Tradisional</option>
                            <option value="Klasik" <?php echo ($editData['kategori'] == 'Klasik') ? 'selected' : ''; ?>>Klasik</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="harga_sewa">Harga Sewa / 3 Hari</label>
                            <input type="number" name="harga_sewa" id="harga_sewa" class="form-control" placeholder="150000" value="<?php echo $editData['harga_sewa']; ?>" required <?php echo !$dbConnected ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="stok">Stok Tersedia</label>
                            <input type="number" name="stok" id="stok" class="form-control" min="0" placeholder="3" value="<?php echo $editData['stok']; ?>" required <?php echo !$dbConnected ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Foto Produk</label>
                        <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" <?php echo ($editMode || !$dbConnected) ? '' : 'required'; ?>>
                        <small style="color: var(--text-light); font-size: 0.75rem; display: block; margin-top: 5px;">Format: JPG, PNG, WEBP. Maksimal 2MB.</small>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border); padding-top: 25px; margin-top: 20px; display: flex; justify-content: flex-end; gap: 15px;">
                <?php if (!$dbConnected): ?>
                    <span style="color: var(--warning); font-size: 0.85rem; font-weight: 500; display: flex; align-items: center;"><i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i> Mode Demo (Database belum disetup). Fitur simpan dikunci.</span>
                <?php endif; ?>
                <button type="submit" name="save_kebaya" class="btn-sm btn-info" style="padding: 12px 35px; border-radius: 8px;" <?php echo !$dbConnected ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 8px;"></i> Simpan Produk
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Table Card: Katalog Kebaya -->
<div class="card">
    <div class="card-header">
        <h2>Daftar Koleksi Kebaya</h2>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Foto</th>
                    <th>Nama & Kategori</th>
                    <th>Deskripsi</th>
                    <th>Harga Sewa / 3 Hari</th>
                    <th>Stok</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($katalog as $k): ?>
                    <tr>
                        <td>
                            <?php 
                            $imgPath = 'uploads/' . $k['gambar'];
                            if (!file_exists($imgPath) || empty($k['gambar'])) {
                                $imgPath = 'uploads/kebaya1.png'; // Fallback
                            }
                            ?>
                            <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($k['nama']); ?>" class="img-preview">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($k['nama']); ?></strong>
                            <span class="badge badge-pending" style="display: inline-block; font-size: 0.7rem; padding: 3px 8px; margin-top: 5px; background-color: #EEE; color: #555;"><?php echo htmlspecialchars($k['kategori']); ?></span>
                        </td>
                        <td style="max-width: 300px; font-size: 0.85rem; color: var(--text-light); line-height: 1.5;">
                            <?php echo htmlspecialchars($k['deskripsi']); ?>
                        </td>
                        <td>
                            <strong style="color: var(--primary);">Rp <?php echo number_format($k['harga_sewa'], 0, ',', '.'); ?></strong>
                        </td>
                        <td>
                            <?php if ($k['stok'] > 0): ?>
                                <span style="font-weight: 600; color: var(--success);"><?php echo $k['stok']; ?> unit</span>
                            <?php else: ?>
                                <span style="font-weight: 600; color: var(--danger);">Habis</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions-btn" style="justify-content: center;">
                                <a href="admin_kebaya.php?action=edit&id=<?php echo $k['id']; ?>" class="btn-sm btn-info" title="Edit"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a href="admin_kebaya.php?action=delete&id=<?php echo $k['id']; ?>" class="btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini dari katalog? Semua data transaksi peminjaman produk ini juga akan terhapus.')" title="Hapus"><i class="fa-solid fa-trash-can"></i> Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</body>
</html>
