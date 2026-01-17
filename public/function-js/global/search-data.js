const input = document.getElementById('cari-data');
const clearBtn = document.getElementById('btn-clear');
const searchBtn = document.getElementById('btn-search');

// Ambil langsung dari input
const searchValue = input.value.trim();

// Tekan Enter → cari
input.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const keyword = this.value.trim();
        const params = new URLSearchParams(window.location.search);
        params.set('search', keyword);
        params.set('page', 1);
        window.location.href = window.location.pathname + '?' + params.toString();
    }
});

// Input berubah → show/hide tombol clear
input.addEventListener('input', function () {
    clearBtn.style.display = this.value.trim() !== '' ? 'flex' : 'none';
});

// Klik tombol search
searchBtn.addEventListener('click', function () {
    const keyword = input.value.trim();
    const params = new URLSearchParams(window.location.search);
    params.set('search', keyword);
    params.set('page', 1);
    window.location.href = window.location.pathname + '?' + params.toString();
});

// Kalau ada nilai awal → tombol clear tampil
if (searchValue !== '') {
    clearBtn.style.display = 'flex';
    clearBtn.addEventListener('click', function () {
        const params = new URLSearchParams(window.location.search);
        params.delete('search');   // hapus hanya "search"
        params.delete('page');     // reset halaman
        const query = params.toString();
        window.location.href = window.location.pathname + (query ? '?' + query : '');
    });
} else {
    clearBtn.style.display = 'none';
}