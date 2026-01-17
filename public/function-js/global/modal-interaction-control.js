$(document).ready(function () {
    // Mencegah penutupan modal ketika klik di luar modal
    $('.modal').on('click', function (event) {
        if (event.target === this) {
            event.stopPropagation(); // Menonaktifkan event klik di luar modal
        }
    });
});

function reloadPage() {
    location.reload(); // Me-reload halaman saat tombol Cancel diklik
}