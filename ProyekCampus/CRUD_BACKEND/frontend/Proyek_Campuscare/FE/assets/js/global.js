/**
 * ================================
 * CAMPUS CARE - JAVASCRIPT
 * Politeknik Negeri Indramayu
 * ================================
 */

// ========== DATA STORAGE ==========
// In-memory storage (akan diganti dengan database)
let currentUser = null;
let laporan = [];
let konseling = [];
let fasilitas = [];
let eventDaftar = [];
let riwayat = [];

// ========== DOM ELEMENTS ==========
const loginPage = document.getElementById('loginPage');
const registerPage = document.getElementById('registerPage');
const dashboardPage = document.getElementById('dashboardPage');

// ========== EVENT LISTENERS ==========

/**
 * Login Form Handler
 */
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const nim = document.getElementById('loginNim').value;
    const password = document.getElementById('loginPassword').value;

    // Demo authentication (nanti diganti dengan PHP + MySQL)
    if (nim && password) {
        // Simulasi user data
        currentUser = {
            nama: 'Khoirul Umam',
            nim: '2405029',
            email: 'umamkhoerul163@gmail.com',
            prodi: 'D4 Rekayasa Perangkat Lunak',
            phone: '081234567890',
            alamat: 'Indramayu, Jawa Barat'
        };

        showDashboard();
    } else {
        showAlert('NIM atau Password salah!', 'danger');
    }
});

/**
 * Register Form Handler
 */
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const nama = document.getElementById('regNama').value;
    const nim = document.getElementById('regNim').value;
    const email = document.getElementById('regEmail').value;
    const password = document.getElementById('regPassword').value;
    const prodi = document.getElementById('regProdi').value;

    // Validasi password
    if (password.length < 6) {
        showAlert('Password minimal 6 karakter!', 'warning');
        return;
    }

    // Simpan user data (sementara)
    currentUser = { 
        nama, 
        nim, 
        email, 
        prodi, 
        phone: '', 
        alamat: '' 
    };
    
    showAlert('Registrasi berhasil! Silakan login.', 'success');
    showLogin();
    
    // Reset form
    this.reset();
});

/**
 * Profile Form Handler
 */
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentUser) return;

    currentUser.nama = document.getElementById('profileInputNama').value;
    currentUser.email = document.getElementById('profileInputEmail').value;
    currentUser.prodi = document.getElementById('profileInputProdi').value;
    currentUser.phone = document.getElementById('profileInputPhone').value;
    currentUser.alamat = document.getElementById('profileInputAlamat').value;
    
    updateUserInfo();
    showAlert('✅ Profil berhasil diperbarui!', 'success');
});

/**
 * Laporan Form Handler
 */
document.getElementById('formLaporan').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const kategori = document.getElementById('laporanKategori').value;
    const judul = document.getElementById('laporanJudul').value;
    const deskripsi = document.getElementById('laporanDeskripsi').value;
    const lokasi = document.getElementById('laporanLokasi').value;
    const anonim = document.getElementById('laporanAnonim').checked;
    
    const newLaporan = {
        id: Date.now(),
        tanggal: new Date().toLocaleDateString('id-ID'),
        kategori,
        judul,
        deskripsi,
        lokasi,
        anonim,
        status: 'Menunggu'
    };
    
    laporan.push(newLaporan);
    
    // Add to riwayat
    addToRiwayat('Laporan', judul, 'Menunggu');
    
    updateLaporanTable();
    updateStats();
    
    closeModal('modalLaporan');
    this.reset();
    
    showAlert('✅ Laporan berhasil dikirim! Tim kami akan segera menindaklanjuti.', 'success');
});

/**
 * Konseling Form Handler
 */
document.getElementById('formKonseling').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const dosen = document.getElementById('konselingDosen').value;
    const tanggal = document.getElementById('konselingTanggal').value;
    const waktu = document.getElementById('konselingWaktu').value;
    const topik = document.getElementById('konselingTopik').value;
    
    const newKonseling = {
        id: Date.now(),
        tanggal: new Date(tanggal).toLocaleDateString('id-ID'),
        waktu,
        dosen,
        topik,
        status: 'Menunggu Konfirmasi'
    };
    
    konseling.push(newKonseling);
    
    // Add to riwayat
    addToRiwayat('Konseling', `Konseling dengan ${dosen}`, 'Menunggu Konfirmasi');
    
    updateKonselingTable();
    updateStats();
    
    closeModal('modalKonseling');
    this.reset();
    
    showAlert('✅ Booking konseling berhasil! Menunggu konfirmasi dari dosen.', 'success');
});

/**
 * Fasilitas Form Handler
 */
document.getElementById('formFasilitas').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const nama = document.getElementById('fasilitasNama').value;
    const tanggal = document.getElementById('fasilitasTanggal').value;
    const waktuMulai = document.getElementById('fasilitasWaktuMulai').value;
    const waktuSelesai = document.getElementById('fasilitasWaktuSelesai').value;
    const keperluan = document.getElementById('fasilitasKeperluan').value;
    
    // Validasi waktu
    if (waktuMulai >= waktuSelesai) {
        showAlert('⚠️ Waktu selesai harus lebih besar dari waktu mulai!', 'warning');
        return;
    }
    
    const newFasilitas = {
        id: Date.now(),
        tanggal: new Date(tanggal).toLocaleDateString('id-ID'),
        waktu: `${waktuMulai} - ${waktuSelesai}`,
        fasilitas: nama,
        keperluan,
        status: 'Menunggu Persetujuan'
    };
    
    fasilitas.push(newFasilitas);
    
    // Add to riwayat
    addToRiwayat('Booking Fasilitas', nama, 'Menunggu Persetujuan');
    
    updateFasilitasTable();
    updateStats();
    
    closeModal('modalFasilitas');
    this.reset();
    
    showAlert('✅ Booking fasilitas berhasil! Menunggu persetujuan admin.', 'success');
});

// ========== NAVIGATION FUNCTIONS ==========

/**
 * Show Dashboard
 */
function showDashboard() {
    loginPage.classList.add('hidden');
    registerPage.classList.add('hidden');
    dashboardPage.classList.add('active');

    updateUserInfo();
    updateStats();
    updateAllTables();
}

/**
 * Show Register Page
 */
function showRegister() {
    loginPage.classList.add('hidden');
    registerPage.classList.remove('hidden');
}

/**
 * Show Login Page
 */
function showLogin() {
    registerPage.classList.add('hidden');
    loginPage.classList.remove('hidden');
}

/**
 * Show Page
 * @param {string} page - Page name
 */
function showPage(page) {
    // Hide all pages
    document.querySelectorAll('.content-page').forEach(p => p.classList.add('hidden'));
    
    // Remove active from all nav links
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    
    // Show selected page
    const pageName = 'content' + page.charAt(0).toUpperCase() + page.slice(1);
    const pageElement = document.getElementById(pageName);
    
    if (pageElement) {
        pageElement.classList.remove('hidden');
    }
    
    // Set active nav link
    if (event && event.target) {
        const navLink = event.target.closest('.nav-link');
        if (navLink) {
            navLink.classList.add('active');
        }
    }
}

/**
 * Logout
 */
function logout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        // Reset data
        currentUser = null;
        laporan = [];
        konseling = [];
        fasilitas = [];
        eventDaftar = [];
        riwayat = [];
        
        dashboardPage.classList.remove('active');
        loginPage.classList.remove('hidden');
        
        // Reset forms
        document.getElementById('loginForm').reset();
        
        showAlert('✅ Logout berhasil!', 'success');
    }
}

// ========== UPDATE FUNCTIONS ==========

/**
 * Update User Info
 */
function updateUserInfo() {
    if (!currentUser) return;

    const initial = currentUser.nama.charAt(0).toUpperCase();
    
    // Sidebar
    document.getElementById('sidebarName').textContent = currentUser.nama;
    document.getElementById('sidebarNim').textContent = currentUser.nim;
    document.getElementById('sidebarAvatar').textContent = initial;
    
    // Welcome
    document.getElementById('welcomeName').textContent = currentUser.nama;

    // Profile
    document.getElementById('profileName').textContent = currentUser.nama;
    document.getElementById('profileNim').textContent = currentUser.nim;
    document.getElementById('profileAvatar').textContent = initial;
    document.getElementById('profileInputNama').value = currentUser.nama;
    document.getElementById('profileInputNim').value = currentUser.nim;
    document.getElementById('profileInputEmail').value = currentUser.email;
    document.getElementById('profileInputProdi').value = currentUser.prodi;
    document.getElementById('profileInputPhone').value = currentUser.phone;
    document.getElementById('profileInputAlamat').value = currentUser.alamat;
}

/**
 * Update Statistics
 */
function updateStats() {
    document.getElementById('totalLaporan').textContent = laporan.length;
    document.getElementById('totalKonseling').textContent = konseling.length;
    document.getElementById('totalFasilitas').textContent = fasilitas.length;
    document.getElementById('totalEvent').textContent = eventDaftar.length;
}

/**
 * Update All Tables
 */
function updateAllTables() {
    updateLaporanTable();
    updateKonselingTable();
    updateFasilitasTable();
    updateRiwayatTable();
}

/**
 * Update Laporan Table
 */
function updateLaporanTable() {
    const tbody = document.getElementById('laporanTableBody');
    
    if (laporan.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Belum ada laporan</td></tr>';
        return;
    }
    
    tbody.innerHTML = laporan.map(l => `
        <tr>
            <td>${l.tanggal}</td>
            <td>${l.kategori}</td>
            <td>${l.judul}</td>
            <td><span class="badge ${getStatusBadge(l.status)}">${l.status}</span></td>
            <td>
                <button class="btn btn-primary btn-small" onclick="viewLaporan(${l.id})">Lihat Detail</button>
            </td>
        </tr>
    `).join('');
}

/**
 * Update Konseling Table
 */
function updateKonselingTable() {
    const tbody = document.getElementById('konselingTableBody');
    
    if (konseling.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Belum ada jadwal konseling</td></tr>';
        return;
    }
    
    tbody.innerHTML = konseling.map(k => `
        <tr>
            <td>${k.tanggal}</td>
            <td>${k.waktu}</td>
            <td>${k.dosen}</td>
            <td>${k.topik.substring(0, 50)}${k.topik.length > 50 ? '...' : ''}</td>
            <td><span class="badge ${getStatusBadge(k.status)}">${k.status}</span></td>
        </tr>
    `).join('');
}

/**
 * Update Fasilitas Table
 */
function updateFasilitasTable() {
    const tbody = document.getElementById('fasilitasTableBody');
    
    if (fasilitas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Belum ada booking fasilitas</td></tr>';
        return;
    }
    
    tbody.innerHTML = fasilitas.map(f => `
        <tr>
            <td>${f.tanggal}</td>
            <td>${f.waktu}</td>
            <td>${f.fasilitas}</td>
            <td>${f.keperluan.substring(0, 50)}${f.keperluan.length > 50 ? '...' : ''}</td>
            <td><span class="badge ${getStatusBadge(f.status)}">${f.status}</span></td>
        </tr>
    `).join('');
}

/**
 * Update Riwayat Table
 */
function updateRiwayatTable() {
    const tbody = document.getElementById('riwayatTableBody');
    
    if (riwayat.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Belum ada riwayat aktivitas</td></tr>';
        return;
    }
    
    tbody.innerHTML = riwayat.slice().reverse().map(r => `
        <tr>
            <td>${r.tanggal}</td>
            <td><strong>${r.jenis}</strong></td>
            <td>${r.deskripsi}</td>
            <td><span class="badge ${getStatusBadge(r.status)}">${r.status}</span></td>
        </tr>
    `).join('');
}

// ========== HELPER FUNCTIONS ==========

/**
 * Get Status Badge Class
 * @param {string} status - Status text
 * @returns {string} Badge class
 */
function getStatusBadge(status) {
    const badges = {
        'Menunggu': 'badge-warning',
        'Menunggu Konfirmasi': 'badge-warning',
        'Menunggu Persetujuan': 'badge-warning',
        'Diproses': 'badge-info',
        'Disetujui': 'badge-success',
        'Selesai': 'badge-success',
        'Terdaftar': 'badge-success',
        'Ditolak': 'badge-danger'
    };
    return badges[status] || 'badge-info';
}

/**
 * Add to Riwayat
 * @param {string} jenis - Type of activity
 * @param {string} deskripsi - Description
 * @param {string} status - Status
 */
function addToRiwayat(jenis, deskripsi, status) {
    riwayat.push({
        tanggal: new Date().toLocaleDateString('id-ID'),
        jenis,
        deskripsi,
        status
    });
    
    updateRiwayatTable();
}

/**
 * View Laporan Detail
 * @param {number} id - Laporan ID
 */
function viewLaporan(id) {
    const lap = laporan.find(l => l.id === id);
    if (!lap) return;
    
    const detail = `
DETAIL LAPORAN #${lap.id}

Kategori: ${lap.kategori}
Judul: ${lap.judul}
Deskripsi: ${lap.deskripsi}
Lokasi: ${lap.lokasi || '-'}
Anonim: ${lap.anonim ? 'Ya' : 'Tidak'}
Status: ${lap.status}
Tanggal: ${lap.tanggal}
    `;
    
    alert(detail.trim());
}

/**
 * Daftar Event
 * @param {string} eventName - Event name
 */
function daftarEvent(eventName) {
    if (eventDaftar.includes(eventName)) {
        showAlert('⚠️ Anda sudah terdaftar untuk event ini!', 'warning');
        return;
    }
    
    eventDaftar.push(eventName);
    
    // Add to riwayat
    addToRiwayat('Event', `Mendaftar ${eventName}`, 'Terdaftar');
    
    updateStats();
    
    showAlert(`✅ Berhasil mendaftar ${eventName}!`, 'success');
}

/**
 * Show Alert
 * @param {string} message - Alert message
 * @param {string} type - Alert type (success, danger, warning, info)
 */
function showAlert(message, type = 'info') {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.style.position = 'fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.style.minWidth = '300px';
    alert.style.animation = 'slideInRight 0.3s ease';
    
    document.body.appendChild(alert);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        alert.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(alert);
        }, 300);
    }, 3000);
}

// ========== MODAL FUNCTIONS ==========

/**
 * Open Modal
 * @param {string} modalId - Modal ID
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Close Modal
 * @param {string} modalId - Modal ID
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Close modal when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// ========== INITIALIZATION ==========

/**
 * Initialize App
 */
function initApp() {
    // Set minimum date to today for date inputs
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = ['konselingTanggal', 'fasilitasTanggal'];
    
    dateInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.setAttribute('min', today);
        }
    });

    // Prevent default on navigation links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });

    console.log('✅ Campus Care initialized successfully!');
}

// ========== ANIMATIONS ==========

// Add slide animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ========== RUN INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', initApp);

// ========== EXPORT FUNCTIONS (for testing) ==========
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showDashboard,
        showLogin,
        showRegister,
        showPage,
        logout,
        daftarEvent,
        viewLaporan,
        openModal,
        closeModal
    };
}