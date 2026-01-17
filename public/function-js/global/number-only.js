// Untuk mencegah input karakter non-numerik pada input field
function filterNonNumeric(inputElement) {
    inputElement.value = inputElement.value.replace(/\D/g, ''); // Hapus semua karakter non-angka
}

// Untuk mencegah angka nol di depan pada input field
// Fungsi ini akan menghapus angka nol di depan jika ada
function preventLeadingZero(input) {
    // Hapus semua karakter non-digit (biar hanya angka)
    input.value = input.value.replace(/\D/g, '');

    // Jika karakter pertama adalah "0", hapus
    if (input.value.startsWith('0')) {
        input.value = input.value.replace(/^0+/, '');
    }
}

function formatRibuan(el) {
    // Hapus semua karakter selain angka
    let value = el.value.replace(/\D/g, '');
    
    // Tambahkan pemisah ribuan (.)
    el.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}