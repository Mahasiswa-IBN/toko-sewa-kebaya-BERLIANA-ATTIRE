<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$adminTitle = 'Dashboard Peminjaman';

// Handle Action: Update Status
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = trim($_GET['status']);
    
    if (in_array($status, ['Pending', 'Dipinjam', 'Dikembalikan', 'Terlambat', 'Dibatalkan'])) {
        if ($pdo) {
            try {
                // If moving to Returned, we might want to increase kebaya stock (optional logic)
                // If moving to Dipinjam, we might want to check/decrease stock
                $stmt = $pdo->prepare("UPDATE `peminjaman` SET `status` = ? WHERE `id` = ?");
                $stmt->execute([$status, $id]);
                $_SESSION['flash_message'] = "Status peminjaman berhasil diperbarui menjadi $status.";
                $_SESSION['flash_type'] = "success";
            } catch (\PDOException $e) {
                $_SESSION['flash_message'] = "Gagal memperbarui status: " . $e->getMessage();
                $_SESSION['flash_type'] = "danger";
            }
        } else {
            $_SESSION['flash_message'] = "Fitur dinonaktifkan: Database belum terhubung.";
            $_SESSION['flash_type'] = "warning";
        }
    }
    header("Location: admin_dashboard.php");
    exit;
}

// Handle Action: Delete Booking
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `peminjaman` WHERE `id` = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = "Data peminjaman berhasil dihapus.";
            $_SESSION['flash_type'] = "success";
        } catch (\PDOException $e) {
            $_SESSION['flash_message'] = "Gagal menghapus data: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }
    } else {
        $_SESSION['flash_message'] = "Fitur dinonaktifkan: Database belum terhubung.";
        $_SESSION['flash_type'] = "warning";
    }
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch Search and Filter query parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

$bookings = [];
$stats = [
    'total' => 0,
    'pending' => 0,
    'active' => 0,
    'revenue' => 0
];
$dbConnected = false;

if ($pdo) {
    try {
        $dbConnected = true;
        
        // 1. Calculate Dashboard Statistics
        // Total Bookings
        $stats['total'] = $pdo->query("SELECT COUNT(*) FROM `peminjaman`")->fetchColumn();
        // Pending
        $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM `peminjaman` WHERE `status` = 'Pending'")->fetchColumn();
        // Active (Dipinjam)
        $stats['active'] = $pdo->query("SELECT COUNT(*) FROM `peminjaman` WHERE `status` = 'Dipinjam'")->fetchColumn();
        // Total Revenue (Non-cancelled)
        $stats['revenue'] = $pdo->query("SELECT SUM(total_harga) FROM `peminjaman` WHERE `status` != 'Dibatalkan'")->fetchColumn() ?: 0;

        // 2. Fetch bookings list with search/filter
        $sql = "SELECT p.*, k.nama as nama_kebaya 
                FROM `peminjaman` p 
                JOIN `kebaya` k ON p.kebaya_id = k.id 
                WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.nama_peminjam LIKE ? OR p.nik LIKE ? OR p.whatsapp LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($statusFilter)) {
            $sql .= " AND p.status = ?";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();

    } catch (\PDOException $e) {
        $dbConnected = false;
    }
}

// Mock Dashboard data if DB not connected
if (!$dbConnected) {
    $stats = [
        'total' => 3,
        'pending' => 1,
        'active' => 1,
        'revenue' => 475000
    ];

    $bookings = [
        [
            'id' => 1,
            'nama_peminjam' => 'Amanda Lestari',
            'nik' => '3273012345678901',
            'whatsapp' => '082212345678',
            'alamat' => 'Jl. Kebagusan Dalam II No. 12, Jakarta Selatan',
            'tanggal_sewa' => date('Y-m-d', strtotime('-1 days')),
            'tanggal_kembali' => date('Y-m-d', strtotime('+2 days')),
            'durasi' => 3,
            'total_harga' => 150000.00,
            'nama_kebaya' => 'Kebaya Brokat Modern Rose Gold',
            'status' => 'Dipinjam',
            'catatan' => 'Request fitting lengan kanan agak longgar.'
        ],
        [
            'id' => 2,
            'nama_peminjam' => 'Rina Novianti',
            'nik' => '3273098765432109',
            'whatsapp' => '085611223344',
            'alamat' => 'Apartemen Kemang Village Tower Tiffany 18B, Jakarta Selatan',
            'tanggal_sewa' => date('Y-m-d', strtotime('+3 days')),
            'tanggal_kembali' => date('Y-m-d', strtotime('+6 days')),
            'durasi' => 3,
            'total_harga' => 200000.00,
            'nama_kebaya' => 'Kebaya Beludru Klasik Hitam Gold',
            'status' => 'Pending',
            'catatan' => 'Sewa untuk acara lamaran tanggal ' . date('d-m-Y', strtotime('+4 days'))
        ],
        [
            'id' => 3,
            'nama_peminjam' => 'Dwi Prasetyo',
            'nik' => '3273111222333444',
            'whatsapp' => '081299887766',
            'alamat' => 'Perumahan Pondok Indah Blok A4 No. 9, Jakarta Selatan',
            'tanggal_sewa' => date('Y-m-d', strtotime('-5 days')),
            'tanggal_kembali' => date('Y-m-d', strtotime('-2 days')),
            'durasi' => 3,
            'total_harga' => 125000.00,
            'nama_kebaya' => 'Kebaya Encim Kartini Putih Gading',
            'status' => 'Dikembalikan',
            'catatan' => ''
        ]
    ];

    // Filter/Search Mock Data manually
    if (!empty($search)) {
        $bookings = array_filter($bookings, function($b) use ($search) {
            return (stripos($b['nama_peminjam'], $search) !== false || 
                    stripos($b['nik'], $search) !== false || 
                    stripos($b['whatsapp'], $search) !== false);
        });
    }

    if (!empty($statusFilter)) {
        $bookings = array_filter($bookings, function($b) use ($statusFilter) {
            return $b['status'] == $statusFilter;
        });
    }
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

<!-- Stats Cards Grid -->
<div class="stats-grid">
    <!-- Stat 1 -->
    <div class="stat-card">
        <span class="stat-title">Total Peminjaman</span>
        <span class="stat-value"><?php echo number_format($stats['total']); ?></span>
        <span class="stat-desc">Transaksi terdaftar</span>
    </div>
    <!-- Stat 2 -->
    <div class="stat-card">
        <span class="stat-title">Menunggu Persetujuan</span>
        <span class="stat-value" style="color: var(--warning);"><?php echo number_format($stats['pending']); ?></span>
        <span class="stat-desc">Status pending/baru</span>
    </div>
    <!-- Stat 3 -->
    <div class="stat-card">
        <span class="stat-title">Sedang Dipinjam</span>
        <span class="stat-value" style="color: var(--info);"><?php echo number_format($stats['active']); ?></span>
        <span class="stat-desc">Aktif di luar butik</span>
    </div>
    <!-- Stat 4 -->
    <div class="stat-card">
        <span class="stat-title">Total Pendapatan</span>
        <span class="stat-value" style="color: var(--success);">Rp <?php echo number_format($stats['revenue'], 0, ',', '.'); ?></span>
        <span class="stat-desc">Dari transaksi berhasil</span>
    </div>
</div>

<!-- Search & Filters Card -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-content" style="padding: 20px 30px;">
        <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 200px;">
                <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--primary); display: block; margin-bottom: 8px;">Cari Peminjam</label>
                <input type="text" name="search" class="form-control" placeholder="Nama, NIK KTP, atau WhatsApp..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--primary); display: block; margin-bottom: 8px;">Filter Status</label>
                <select name="status_filter" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="Pending" <?php echo ($statusFilter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Dipinjam" <?php echo ($statusFilter == 'Dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                    <option value="Dikembalikan" <?php echo ($statusFilter == 'Dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                    <option value="Terlambat" <?php echo ($statusFilter == 'Terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                    <option value="Dibatalkan" <?php echo ($statusFilter == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-sm btn-info" style="padding: 12px 25px; border-radius: 8px;"><i class="fa-solid fa-magnifying-glass" style="margin-right: 8px;"></i> Cari</button>
                <a href="admin_dashboard.php" class="btn-sm" style="padding: 12px 20px; border-radius: 8px; border: 1px solid #ddd; background-color: #fff; text-align: center;">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        <h2>Daftar Data Peminjam Kebaya</h2>
        <span style="font-size: 0.85rem; color: var(--text-light);">Menampilkan <?php echo count($bookings); ?> data</span>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Peminjam & NIK</th>
                    <th>WhatsApp & Alamat</th>
                    <th>Kebaya & Durasi</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-light);">
                            <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #ddd; display: block; margin-bottom: 10px;"></i>
                            Tidak ada data peminjaman ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($b['nama_peminjam']); ?></strong>
                                <span style="display: block; font-size: 0.75rem; color: var(--text-light); margin-top: 3px;">NIK: <?php echo htmlspecialchars($b['nik']); ?></span>
                            </td>
                            <td>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $b['whatsapp']); ?>" target="_blank" style="color: #25D366; font-weight: 600;">
                                    <i class="fa-brands fa-whatsapp"></i> <?php echo htmlspecialchars($b['whatsapp']); ?>
                                </a>
                                <span style="display: block; font-size: 0.75rem; color: var(--text-light); margin-top: 3px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($b['alamat']); ?>">
                                    <?php echo htmlspecialchars($b['alamat']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($b['nama_kebaya']); ?></strong>
                                <span style="display: block; font-size: 0.75rem; color: var(--text-light); margin-top: 3px;">
                                    <?php echo date('d M Y', strtotime($b['tanggal_sewa'])); ?> s/d <?php echo date('d M Y', strtotime($b['tanggal_kembali'])); ?> (<?php echo $b['durasi']; ?> Hari)
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--primary);">Rp <?php echo number_format($b['total_harga'], 0, ',', '.'); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($b['status']); ?>">
                                    <?php echo htmlspecialchars($b['status']); ?>
                                </span>
                            </td>
                            <td style="max-width: 150px; font-size: 0.8rem; color: var(--text-light); word-wrap: break-word;">
                                <?php echo htmlspecialchars($b['catatan'] ?: '-'); ?>
                            </td>
                            <td>
                                <div class="actions-btn" style="justify-content: center;">
                                    <?php if ($b['status'] == 'Pending'): ?>
                                        <a href="admin_dashboard.php?action=update_status&id=<?php echo $b['id']; ?>&status=Dipinjam" class="btn-sm btn-success" title="Setujui dan Pinjamkan"><i class="fa-solid fa-check"></i> Pinjamkan</a>
                                        <a href="admin_dashboard.php?action=update_status&id=<?php echo $b['id']; ?>&status=Dibatalkan" class="btn-sm btn-danger" title="Batalkan"><i class="fa-solid fa-xmark"></i></a>
                                    <?php elseif ($b['status'] == 'Dipinjam'): ?>
                                        <a href="admin_dashboard.php?action=update_status&id=<?php echo $b['id']; ?>&status=Dikembalikan" class="btn-sm btn-success" title="Kembalikan Kebaya"><i class="fa-solid fa-rotate-left"></i> Kembali</a>
                                        <a href="admin_dashboard.php?action=update_status&id=<?php echo $b['id']; ?>&status=Terlambat" class="btn-sm btn-danger" title="Set Terlambat"><i class="fa-solid fa-clock"></i></a>
                                    <?php elseif ($b['status'] == 'Terlambat'): ?>
                                        <a href="admin_dashboard.php?action=update_status&id=<?php echo $b['id']; ?>&status=Dikembalikan" class="btn-sm btn-success" title="Kembalikan Kebaya"><i class="fa-solid fa-rotate-left"></i> Kembali</a>
                                    <?php endif; ?>
                                    
                                    <a href="admin_dashboard.php?action=delete&id=<?php echo $b['id']; ?>" class="btn-sm" style="background-color: #EEE; color: #555;" onclick="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?')" title="Hapus Data"><i class="fa-solid fa-trash-can"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</body>
</html>
