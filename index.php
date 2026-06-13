<?php
require_once 'includes/db.php';
$activePage = 'home';
$pageTitle = 'Berliana Attire - Sewa Kebaya Premium & Eksklusif';

$kebayaList = [];
$dbInstalled = false;

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `kebaya` ORDER BY `id` DESC");
        $kebayaList = $stmt->fetchAll();
        $dbInstalled = true;
    } catch (\PDOException $e) {
        $dbInstalled = false;
    }
}

// Fallback Mock Data if DB is not installed yet
if (!$dbInstalled || empty($kebayaList)) {
    $kebayaList = [
        [
            'id' => 1,
            'nama' => 'Kebaya Brokat Modern Rose Gold',
            'deskripsi' => 'Kebaya brokat modern dengan sentuhan warna rose gold mewah. Sangat cocok untuk wisuda, kondangan, maupun lamaran. Dilengkapi dengan furing premium yang nyaman di kulit dan detail payet mutiara di bagian dada.',
            'harga_sewa' => 150000.00,
            'stok' => 3,
            'kategori' => 'Modern',
            'gambar' => 'kebaya1.png'
        ],
        [
            'id' => 2,
            'nama' => 'Kebaya Beludru Klasik Hitam Gold',
            'deskripsi' => 'Kebaya kutubaru bahan beludru premium hitam pekat bermotif bordir emas khas Jawa. Memberikan kesan agung, anggun, dan tradisional namun tetap elegan. Cocok untuk acara pernikahan adat maupun formal.',
            'harga_sewa' => 200000.00,
            'stok' => 2,
            'kategori' => 'Tradisional',
            'gambar' => 'kebaya2.png'
        ],
        [
            'id' => 3,
            'nama' => 'Kebaya Encim Kartini Putih Gading',
            'deskripsi' => 'Kebaya model Encim Kartini dengan warna putih gading (broken white). Bordiran bunga halus di sepanjang kerah dan lengan bawah. Bahan katun premium yang sejuk dan menyerap keringat. Ideal untuk acara nasional, tunangan, atau upacara.',
            'harga_sewa' => 125000.00,
            'stok' => 4,
            'kategori' => 'Klasik',
            'gambar' => 'kebaya3.png'
        ],
        [
            'id' => 4,
            'nama' => 'Kebaya Brokat Premium Burgundy',
            'deskripsi' => 'Kebaya dengan warna burgundy pekat dan potongan ekor panjang menawan. Detail brokat Prancis yang rapat dihiasi dengan payet Swarovski berkilau. Sempurna untuk penampilan yang bold dan berkelas di acara malam hari.',
            'harga_sewa' => 175000.00,
            'stok' => 2,
            'kategori' => 'Modern',
            'gambar' => 'kebaya4.png'
        ]
    ];
}

include 'includes/header.php';
?>

<!-- Database Status Alert (Only for developers) -->
<?php if (!$dbInstalled): ?>
    <div style="background-color: #FFF3CD; border-bottom: 1px solid #FFEBAA; color: #856404; padding: 12px 0; text-align: center; font-size: 0.9rem; font-weight: 500; position: fixed; top: 80px; width: 100%; z-index: 999;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i>
        Database belum dikonfigurasi. Hubungkan MySQL dan jalankan <a href="install.php" style="text-decoration: underline; font-weight: 700; color: #533f03;">Setup Database</a> agar fitur pemesanan dapat menyimpan data peminjam.
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section id="beranda" class="hero">
    <div class="container">
        <div class="hero-content">
            <span class="hero-subtitle">Eksklusif & Anggun</span>
            <h1 class="hero-title">Temukan Kebaya Impian Anda</h1>
            <p class="hero-description">Sewa kebaya premium dengan jahitan butik berkualitas tinggi untuk momen-momen berharga Anda. Tampil percaya diri, menawan, dan mempesona.</p>
            <div class="hero-buttons">
                <a href="#katalog" class="btn btn-accent" id="exploreCatalogBtn">Lihat Koleksi</a>
                <a href="booking.php" class="btn btn-outline" style="border-color: white; color: white;" id="rentNowHeroBtn">Sewa Sekarang</a>
            </div>
        </div>
    </div>
</section>

<!-- Keunggulan Section (Tentang Kami) -->
<section id="tentang" class="section-padding">
    <div class="container">
        <div class="text-center">
            <span class="section-subtitle">Mengapa Memilih Kami</span>
            <h2 class="section-title">Keunggulan Berliana Attire</h2>
        </div>
        
        <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-gem"></i></div>
                <h3>Kualitas Butik Premium</h3>
                <p>Setiap kebaya kami dirancang dan dijahit dengan detail payet eksklusif serta pilihan bahan kain berkualitas tinggi yang nyaman dipakai.</p>
            </div>
            <!-- Feature 2 -->
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-sparkles"></i></div>
                <h3>Steril & Higienis</h3>
                <p>Kami menjamin seluruh kebaya dicuci secara profesional (laundry dry cleaning) sebelum dan sesudah disewa, bersih wangi siap pakai.</p>
            </div>
            <!-- Feature 3 -->
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-arrows-spin"></i></div>
                <h3>Fitting & Custom Alternation</h3>
                <p>Dapatkan fitting gratis di butik kami, dan penyesuaian ukuran minor agar kebaya jatuh pas dan menawan di tubuh Anda.</p>
            </div>
        </div>
    </div>
</section>

<!-- Catalog Section -->
<section id="katalog" class="section-padding" style="background-color: #FAF8F5;">
    <div class="container">
        <div class="text-center">
            <span class="section-subtitle">Koleksi Busana Kami</span>
            <h2 class="section-title">Katalog Kebaya</h2>
            <p style="color: var(--text-muted); margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">Temukan beragam kategori kebaya untuk wisuda, pernikahan, lamaran, dan acara formal lainnya.</p>
        </div>
        
        <!-- Filter Tabs -->
        <div class="catalog-filter">
            <button class="filter-btn active" data-filter="all" id="filterAll">Semua</button>
            <button class="filter-btn" data-filter="Modern" id="filterModern">Modern</button>
            <button class="filter-btn" data-filter="Tradisional" id="filterTradisional">Tradisional</button>
            <button class="filter-btn" data-filter="Klasik" id="filterKlasik">Klasik</button>
        </div>
        
        <!-- Grid Catalog -->
        <div class="catalog-grid">
            <?php foreach ($kebayaList as $kebaya): ?>
                <div class="catalog-card" data-category="<?php echo htmlspecialchars($kebaya['kategori']); ?>" id="kebaya-card-<?php echo $kebaya['id']; ?>">
                    <div class="catalog-img-container">
                        <span class="category-tag"><?php echo htmlspecialchars($kebaya['kategori']); ?></span>
                        <?php 
                        $imgPath = 'uploads/' . $kebaya['gambar'];
                        if (!file_exists($imgPath) || empty($kebaya['gambar'])) {
                            $imgPath = 'uploads/kebaya1.png'; // Fallback
                        }
                        ?>
                        <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($kebaya['nama']); ?>" loading="lazy">
                    </div>
                    <div class="catalog-info">
                        <h3 class="catalog-title"><?php echo htmlspecialchars($kebaya['nama']); ?></h3>
                        <p class="catalog-desc"><?php echo htmlspecialchars($kebaya['deskripsi']); ?></p>
                        <div class="catalog-footer">
                            <div class="catalog-price">
                                <span class="price-lbl">Harga Sewa</span>
                                <span class="price-val">Rp <?php echo number_format($kebaya['harga_sewa'], 0, ',', '.'); ?> <small style="font-size: 0.75rem; font-weight: normal; color: var(--text-muted);">/ 3 Hari</small></span>
                            </div>
                            <a href="booking.php?kebaya_id=<?php echo $kebaya['id']; ?>" class="btn btn-rent" id="rentBtn-<?php echo $kebaya['id']; ?>">Sewa</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Syarat Sewa Section -->
<section id="syarat" class="section-padding terms-bg">
    <div class="container">
        <div class="terms-grid">
            <div>
                <span class="section-subtitle">Ketentuan Berliana Attire</span>
                <h2 style="font-size: 2.8rem; margin-bottom: 25px;">Syarat & Ketentuan Sewa</h2>
                <p style="margin-bottom: 30px; opacity: 0.85;">Penyewaan kebaya di Berliana Attire sangat mudah dan praktis. Silakan pelajari syarat penyewaan berikut untuk kenyamanan bersama:</p>
                <ul class="terms-list">
                    <li>Penyewa wajib menjaminkan kartu identitas asli (KTP/KIA/SIM/Paspor) yang masih berlaku.</li>
                    <li>Durasi sewa standar adalah 3 hari (hari ke-1 pengambilan, hari ke-2 pemakaian, hari ke-3 pengembalian).</li>
                    <li>Keterlambatan pengembalian akan dikenakan denda Rp 50.000,- per hari.</li>
                    <li>Penyewa bertanggung jawab penuh atas keutuhan kebaya. Kerusakan kain, robek, atau noda permanen akan dikenakan biaya perbaikan/ganti rugi.</li>
                    <li>Kebaya tidak perlu dicuci saat dikembalikan, kami yang akan mengurus laundry dry-cleaning.</li>
                </ul>
            </div>
            
            <div style="position: relative;">
                <div style="border: 2px solid var(--accent); border-radius: 20px; overflow: hidden; padding: 8px;">
                    <img src="uploads/kebaya2.png" alt="Detail Kebaya Berliana Attire" style="border-radius: 12px; width: 100%; filter: brightness(0.95);">
                </div>
                <!-- Mini floating card -->
                <div style="position: absolute; bottom: -20px; left: -20px; background: rgba(74, 14, 23, 0.9); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; border: 1px solid var(--accent); max-width: 200px; box-shadow: var(--shadow-hover);">
                    <div style="font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--accent); font-weight: bold; margin-bottom: 5px;">Butuh Bantuan?</div>
                    <div style="font-size: 0.8rem; line-height: 1.4; opacity: 0.9;">Hubungi admin kami via WhatsApp jika ingin bertanya lebih lanjut.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section-padding">
    <div class="container">
        <div class="text-center">
            <span class="section-subtitle">Testimoni Pelanggan</span>
            <h2 class="section-title">Apa Kata Mereka?</h2>
        </div>
        
        <div class="testimonials-grid">
            <!-- Testimonial 1 -->
            <div class="testimonial-card">
                <p class="testimonial-text">"Kebaya Rose Gold dari Berliana Attire pas banget di badan saya untuk wisuda kemarin. Kainnya adem, jahitannya rapi, dan payetnya kelihatan mewah sekali saat difoto. Banyak yang nanya sewa di mana!"</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">AL</div>
                    <div>
                        <div class="testimonial-name">Amanda Lestari</div>
                        <div class="testimonial-role">Wisudawati Universitas Indonesia</div>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="testimonial-card">
                <p class="testimonial-text">"Sewa di sini sangat praktis. Pelayanan adminnya ramah banget, kebaya sudah bersih wangi pas diambil dan gak perlu repot-repot dicuci pas dibalikin. Highly recommended!"</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">RN</div>
                    <div>
                        <div class="testimonial-name">Rina Novianti</div>
                        <div class="testimonial-role">Penyewa untuk Acara Lamaran</div>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 3 -->
            <div class="testimonial-card">
                <p class="testimonial-text">"Kebaya Beludru Hitam Emasnya bener-bener agung dan mewah banget waktu acara resepsi adat kemarin. Harganya sangat affordable dibanding butik-butik besar lain dengan kualitas yang sama."</p>
                <div class="testimonial-user">
                    <div class="testimonial-avatar">DP</div>
                    <div>
                        <div class="testimonial-name">Dwi Prasetyo</div>
                        <div class="testimonial-role">Pengantin Adat Jawa</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section id="kontak" class="section-padding" style="background: linear-gradient(rgba(74, 14, 23, 0.9), rgba(47, 5, 11, 0.95)), url('uploads/kebaya4.png'); background-size: cover; background-position: center; color: white; text-align: center;">
    <div class="container" style="max-width: 700px;">
        <span class="section-subtitle" style="color: var(--accent);">Reservasi Sekarang</span>
        <h2 style="font-size: 3rem; color: white; margin-bottom: 20px; font-family: 'Playfair Display', serif;">Siap Tampil Cantik & Anggun?</h2>
        <p style="font-size: 1.1rem; margin-bottom: 35px; opacity: 0.9;">Booking kebaya favorit Anda sekarang sebelum tanggal pemakaian Anda di-booking orang lain. Kami juga melayani konsultasi pemilihan warna dan model yang cocok.</p>
        
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="booking.php" class="btn btn-accent" id="bookingActionBtn"><i class="fa-solid fa-calendar-check" style="margin-right: 8px;"></i>Pesan via Website</a>
            <a href="https://wa.me/6282212345678?text=Halo%20Berliana%20Attire,%20saya%20ingin%20tanya%20mengenai%20sewa%20kebaya." target="_blank" class="btn btn-outline" style="border-color: white; color: white;" id="whatsappActionBtn"><i class="fa-brands fa-whatsapp" style="margin-right: 8px;"></i>Chat WhatsApp Admin</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
