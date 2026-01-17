document.addEventListener('DOMContentLoaded', function() {
    const imgWrapper = document.getElementById('imgWrapper');
    const zoomModal = document.getElementById('zoomModal');
    const zoomImg = document.getElementById('zoomImg');
    const closeZoom = document.getElementById('closeZoom');

    // Klik div untuk zoom
    imgWrapper.style.cursor = 'pointer';
    imgWrapper.addEventListener('click', () => {
        const bg = window.getComputedStyle(imgWrapper).backgroundImage;
        const url = bg.slice(5, -2); // hapus 'url("...")'
        zoomImg.src = url;
        zoomModal.style.display = 'flex';
    });

    // Klik button x untuk close
    closeZoom.addEventListener('click', (e) => {
        e.stopPropagation(); // mencegah event bubbling
        zoomModal.style.display = 'none';
    });

    // Klik di luar gambar untuk menutup zoom
    zoomModal.addEventListener('click', () => {
        zoomModal.style.display = 'none';
    });

    // Jangan tutup saat klik gambar
    zoomImg.addEventListener('click', (e) => {
        e.stopPropagation();
    });
});