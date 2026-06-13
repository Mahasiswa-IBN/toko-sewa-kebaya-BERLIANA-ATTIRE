/* ==========================================================================
   BERLIANA ATTIRE - JAVASCRIPT (PREMIUM INTERACTION)
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Scrolled Header Navigation Effect
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // 2. Catalog Filter System (on index.php)
    const filterButtons = document.querySelectorAll('.filter-btn');
    const catalogCards = document.querySelectorAll('.catalog-card');

    if (filterButtons.length > 0 && catalogCards.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');

                catalogCards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'flex';
                        card.style.animation = 'fadeInUp 0.6s ease forwards';
                    } else {
                        const cardCategory = card.getAttribute('data-category').toLowerCase();
                        if (cardCategory === filterValue.toLowerCase()) {
                            card.style.display = 'flex';
                            card.style.animation = 'fadeInUp 0.6s ease forwards';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        });
    }

    // 3. Real-time Rental Calculator (on booking.php)
    const bookingForm = document.getElementById('bookingForm');
    const kebayaSelect = document.getElementById('kebaya_id');
    const durationInput = document.getElementById('durasi');
    const dateSewaInput = document.getElementById('tanggal_sewa');

    // Summary Elements
    const summaryKebayaName = document.getElementById('summary-kebaya-name');
    const summaryKebayaPrice = document.getElementById('summary-kebaya-price');
    const summaryDuration = document.getElementById('summary-duration');
    const summaryReturnDate = document.getElementById('summary-return-date');
    const summaryTotalPrice = document.getElementById('summary-total-price');
    const inputTotalPrice = document.getElementById('total_harga');
    const inputTanggalKembali = document.getElementById('tanggal_kembali');

    if (bookingForm && kebayaSelect && durationInput && dateSewaInput) {
        
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        function calculateRental() {
            // Get selected option details
            const selectedOption = kebayaSelect.options[kebayaSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                // Reset summary if no kebaya selected
                if (summaryKebayaName) summaryKebayaName.textContent = '-';
                if (summaryKebayaPrice) summaryKebayaPrice.textContent = 'Rp 0';
                if (summaryDuration) summaryDuration.textContent = '0 Hari';
                if (summaryReturnDate) summaryReturnDate.textContent = '-';
                if (summaryTotalPrice) summaryTotalPrice.textContent = 'Rp 0';
                if (inputTotalPrice) inputTotalPrice.value = 0;
                return;
            }

            const kebayaName = selectedOption.getAttribute('data-nama');
            const pricePerPeriod = parseFloat(selectedOption.getAttribute('data-harga')); // Price per 3 days
            const pricePerDay = pricePerPeriod / 3; // Daily price

            const duration = parseInt(durationInput.value) || 3;
            const dateSewaVal = dateSewaInput.value;

            // Total price calculation
            const totalPrice = pricePerDay * duration;

            // Update Summary Text
            if (summaryKebayaName) summaryKebayaName.textContent = kebayaName;
            if (summaryKebayaPrice) summaryKebayaPrice.textContent = `${formatRupiah(pricePerPeriod)} / 3 Hari`;
            if (summaryDuration) summaryDuration.textContent = `${duration} Hari`;
            if (summaryTotalPrice) summaryTotalPrice.textContent = formatRupiah(totalPrice);
            if (inputTotalPrice) inputTotalPrice.value = Math.round(totalPrice);

            // Calculate return date
            if (dateSewaVal) {
                const startDate = new Date(dateSewaVal);
                const returnDate = new Date(startDate);
                returnDate.setDate(startDate.getDate() + duration);

                // Format date as YYYY-MM-DD
                const yyyy = returnDate.getFullYear();
                let mm = returnDate.getMonth() + 1;
                let dd = returnDate.getDate();

                if (dd < 10) dd = '0' + dd;
                if (mm < 10) mm = '0' + mm;

                const formattedReturnDate = `${yyyy}-${mm}-${dd}`;
                if (inputTanggalKembali) inputTanggalKembali.value = formattedReturnDate;

                // Format return date for summary (Indonesian readable format)
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const readableReturnDate = returnDate.toLocaleDateString('id-ID', options);
                if (summaryReturnDate) summaryReturnDate.textContent = readableReturnDate;
            } else {
                if (summaryReturnDate) summaryReturnDate.textContent = 'Pilih Tanggal Sewa';
            }
        }

        // Add event listeners to trigger calculation on input change
        kebayaSelect.addEventListener('change', calculateRental);
        durationInput.addEventListener('input', calculateRental);
        dateSewaInput.addEventListener('change', calculateRental);

        // Initial Run
        calculateRental();
    }

    // 4. Mobile Menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('nav ul');
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            if (navMenu.style.display === 'flex') {
                navMenu.style.display = 'none';
            } else {
                navMenu.style.display = 'flex';
                navMenu.style.flexDirection = 'column';
                navMenu.style.position = 'absolute';
                navMenu.style.top = '70px';
                navMenu.style.left = '0';
                navMenu.style.width = '100%';
                navMenu.style.backgroundColor = 'rgba(255, 253, 249, 0.98)';
                navMenu.style.padding = '20px';
                navMenu.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            }
        });
    }

});
