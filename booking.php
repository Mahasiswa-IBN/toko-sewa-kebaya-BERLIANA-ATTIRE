<?php
require_once 'includes/db.php';
$activePage = 'booking';
$pageTitle = 'Formulir Pemesanan Sewa - Berliana Attire';

// Fetch Kebayas for dropdown
$kebayaList = [];
$dbInstalled = false;
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nama, harga_sewa, kategori FROM `kebaya` ORDER BY `nama` ASC");
        $kebayaList = $stmt->fetchAll();
        $dbInstalled = true;
    } catch (\PDOException $e) {
        $dbInstalled = false;
    }
}

// Fallback Mock Data for Dropdown if DB not installed
if (!$dbInstalled || empty($kebayaList)) {
    $kebayaList = [
        ['id' => 1, 'nama' => 'Kebaya Brokat Modern Rose Gold', 'harga_sewa' => 150000.00, 'kategori' => 'Modern'],
        ['id' => 2, 'nama' => 'Kebaya Beludru Klasik Hitam Gold', 'harga_sewa' => 200000.00, 'kategori' => 'Tradisional'],
        ['id' => 3, 'nama' => 'Kebaya Encim Kartini Putih Gading', 'harga_sewa' => 125000.00, 'kategori' => 'Klasik'],
        ['id' => 4, 'nama' => 'Kebaya Brokat Premium Burgundy', 'harga_sewa' => 175000.00, 'kategori' => 'Modern']
    ];
}

$selectedKebayaId = isset($_GET['kebaya_id']) ? intval($_GET['kebaya_id']) : 0;

$successBooking = false;
$waUrl = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_peminjam']);
    $nik = trim($_POST['nik']);
    $whatsapp = trim($_POST['whatsapp']);
    $alamat = trim($_POST['alamat']);
    $kebayaId = intval($_POST['kebaya_id']);
    $tanggalSewa = $_POST['tanggal_sewa'];
    $durasi = intval($_POST['durasi']);
    $tanggalKembali = $_POST['tanggal_kembali'];
    $totalHarga = floatval($_POST['total_harga']);
    $catatan = trim($_POST['catatan']);

    // Find selected kebaya name
    $kebayaNama = '';
    foreach ($kebayaList as $k) {
        if ($k['id'] == $kebayaId) {
            $kebayaNama = $k['nama'];
            break;
        }
    }

    // Save to DB if connection exists
    if ($dbInstalled && $pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO `peminjaman` (nama_peminjam, nik, whatsapp, alamat, tanggal_sewa, tanggal_kembali, durasi, total_harga, kebaya_id, status, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");
            $stmt->execute([$nama, $nik, $whatsapp, $alamat, $tanggalSewa, $tanggalKembali, $durasi, $totalHarga, $kebayaId, $catatan]);
            $successBooking = true;
        } catch (\PDOException $e) {
            // Handle error silently, direct to WhatsApp anyway
        }
    }

    // Format WhatsApp message
    $whatsappNum = '6282212345678'; // Shop WA number
    
    // Formatting tanggal sewa & kembali
    $tglSewaFormatted = date('d-m-Y', strtotime($tanggalSewa));
    $tglKembaliFormatted = date('d-m-Y', strtotime($tanggalKembali));
    
    $messageText = "*FORMULIR BOOKING KEBAYA - BERLIANA ATTIRE*\n";
    $messageText .= "---------------------------------------------------------\n";
    $messageText .= "*Data Peminjam:*\n";
    $messageText .= "Nama Lengkap: {$nama}\n";
    $messageText .= "NIK KTP: {$nik}\n";
    $messageText .= "No. WhatsApp: {$whatsapp}\n";
    $messageText .= "Alamat: {$alamat}\n\n";
    $messageText .= "*Detail Sewa:*\n";
    $messageText .= "Kebaya: {$kebayaNama}\n";
    $messageText .= "Tanggal Ambil/Sewa: {$tglSewaFormatted}\n";
    $messageText .= "Tanggal Pengembalian: {$tglKembaliFormatted}\n";
    $messageText .= "Durasi: {$durasi} Hari\n";
    $messageText .= "Total Biaya Sewa: *Rp " . number_format($totalHarga, 0, ',', '.') . "*\n";
    if (!empty($catatan)) {
        $messageText .= "Catatan: {$catatan}\n";
    }
    $messageText .= "---------------------------------------------------------\n";
    $messageText .= "Mohon konfirmasi ketersediaan stok kebaya di atas. Terima kasih.";

    $waUrl = "https://api.whatsapp.com/send?phone={$whatsappNum}&text=" . urlencode($messageText);
    
    // Redirect to WhatsApp
    header("Location: " . $waUrl);
    exit;
}

include 'includes/header.php';
?>

<!-- Spacer for Fixed Navigation -->
<div style="height: 100px;"></div>

<!-- Booking Form Section -->
<section class="section-padding booking-section">
    <div class="container">
        
        <div class="text-center" style="margin-bottom: 50px;">
            <span class="section-subtitle">Reservasi Mudah & Cepat</span>
            <h2 class="section-title">Formulir Sewa Kebaya</h2>
            <p style="color: var(--text-muted); max-width: 600px; margin: 10px auto 0 auto;">Lengkapi data diri Anda di bawah ini. Setelah mengirim formulir, Anda akan langsung diarahkan ke WhatsApp kami untuk konfirmasi ketersediaan stok.</p>
        </div>

        <div class="booking-wrapper">
            <!-- Left Side: Form -->
            <div class="booking-form-side">
                <form id="bookingForm" method="POST">
                    
                    <!-- Hidden fields for database calculations -->
                    <input type="hidden" name="total_harga" id="total_harga" value="0">
                    <input type="hidden" name="tanggal_kembali" id="tanggal_kembali" value="">

                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--primary); margin-bottom: 25px; border-bottom: 1px solid var(--border-light); padding-bottom: 15px;">Data Diri Peminjam</h3>
                    
                    <div class="form-group">
                        <label for="nama_peminjam">Nama Lengkap (Sesuai KTP)</label>
                        <input type="text" name="nama_peminjam" id="nama_peminjam" class="form-control" placeholder="Contoh: Amanda Lestari" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nik">Nomor NIK KTP</label>
                            <input type="text" name="nik" id="nik" class="form-control" placeholder="16 Digit Nomor KTP" pattern="[0-9]{16}" title="NIK harus berupa 16 digit angka" required>
                        </div>
                        <div class="form-group">
                            <label for="whatsapp">Nomor WhatsApp Aktif</label>
                            <input type="tel" name="whatsapp" id="whatsapp" class="form-control" placeholder="Contoh: 082212345678" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap Rumah</label>
                        <textarea name="alamat" id="alamat" rows="3" class="form-control" placeholder="Tuliskan alamat lengkap beserta kota" required style="resize: vertical; min-height: 80px;"></textarea>
                    </div>

                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--primary); margin-top: 40px; margin-bottom: 25px; border-bottom: 1px solid var(--border-light); padding-bottom: 15px;">Detail Penyewaan</h3>
                    
                    <div class="form-group">
                        <label for="kebaya_id">Pilih Kebaya</label>
                        <select name="kebaya_id" id="kebaya_id" class="form-control" required>
                            <option value="" disabled selected>-- Pilih Kebaya --</option>
                            <?php foreach ($kebayaList as $kebaya): ?>
                                <option value="<?php echo $kebaya['id']; ?>" 
                                        data-nama="<?php echo htmlspecialchars($kebaya['nama']); ?>" 
                                        data-harga="<?php echo $kebaya['harga_sewa']; ?>"
                                        <?php echo ($kebaya['id'] == $selectedKebayaId) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kebaya['nama']); ?> [Rp <?php echo number_format($kebaya['harga_sewa'], 0, ',', '.'); ?> / 3 hari]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tanggal_sewa">Tanggal Sewa (Ambil Kebaya)</label>
                            <input type="date" name="tanggal_sewa" id="tanggal_sewa" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="durasi">Durasi Sewa (Minimal 3 Hari)</label>
                            <input type="number" name="durasi" id="durasi" class="form-control" min="3" max="30" value="3" required>
                            <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 5px; display: block;">Perpanjangan dikenakan biaya proporsional per hari.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="catatan">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" id="catatan" rows="2" class="form-control" placeholder="Tuliskan ukuran cadangan, request fitting, atau catatan lainnya" style="resize: vertical; min-height: 60px;"></textarea>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;" id="submitBookingBtn">
                            <i class="fa-brands fa-whatsapp" style="margin-right: 8px;"></i> Kirim & Konfirmasi ke WhatsApp
                        </button>
                    </div>

                </form>
            </div>

            <!-- Right Side: Booking Summary -->
            <div class="booking-info-side">
                <div>
                    <h3>Detail Estimasi</h3>
                    <p style="opacity: 0.85; font-size: 0.9rem; margin-bottom: 30px;">Berikut adalah ringkasan estimasi sewa kebaya pilihan Anda secara real-time:</p>
                    
                    <div class="price-summary" style="background: rgba(255,255,255,0.08); border: 1px dashed var(--accent); color: white; padding: 25px; border-radius: 12px;">
                        <div class="summary-row" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 12px;">
                            <span style="font-size: 0.85rem; opacity: 0.8;">Kebaya Terpilih</span>
                            <span id="summary-kebaya-name" style="font-weight: 600; text-align: right; max-width: 60%; font-family: 'Playfair Display', serif;">-</span>
                        </div>
                        <div class="summary-row" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 12px;">
                            <span style="font-size: 0.85rem; opacity: 0.8;">Tarif Dasar</span>
                            <span id="summary-kebaya-price" style="font-weight: 600;">Rp 0</span>
                        </div>
                        <div class="summary-row" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 12px;">
                            <span style="font-size: 0.85rem; opacity: 0.8;">Durasi Sewa</span>
                            <span id="summary-duration" style="font-weight: 600;">3 Hari</span>
                        </div>
                        <div class="summary-row" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 12px;">
                            <span style="font-size: 0.85rem; opacity: 0.8;">Tanggal Pengembalian</span>
                            <span id="summary-return-date" style="font-weight: 600; text-align: right; max-width: 60%; color: var(--accent);">Pilih Tanggal Sewa</span>
                        </div>
                        <div class="summary-row total" style="border-top: none; padding-top: 10px; margin-top: 15px;">
                            <span style="font-size: 1rem; color: white;">Total Biaya Sewa</span>
                            <span id="summary-total-price" style="font-size: 1.5rem; color: var(--accent); font-weight: 700;">Rp 0</span>
                        </div>
                    </div>
                </div>

                <div style="border-top: 1px solid rgba(255,255,255,0.15); padding-top: 25px; margin-top: 30px;">
                    <h4 style="font-size: 1.1rem; color: var(--accent); margin-bottom: 12px;">Penting untuk Diketahui</h4>
                    <ul style="list-style: none; font-size: 0.8rem; opacity: 0.9; line-height: 1.5;">
                        <li style="margin-bottom: 8px;"><i class="fa-solid fa-circle-check" style="color: var(--accent); margin-right: 8px;"></i> Jaminan kartu identitas (KTP/SIM asli) diserahkan saat pengambilan kebaya.</li>
                        <li style="margin-bottom: 8px;"><i class="fa-solid fa-circle-check" style="color: var(--accent); margin-right: 8px;"></i> Pengambilan & pengembalian dilakukan di butik Berliana Attire (Kemang).</li>
                        <li><i class="fa-solid fa-circle-check" style="color: var(--accent); margin-right: 8px;"></i> Pembayaran lunas atau DP 50% dilakukan setelah konfirmasi ketersediaan stok disetujui admin.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
