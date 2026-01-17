// Variabel global untuk menyimpan file terkompresi jika ada
var compressedFile = null;

// Fungsi untuk menangani perubahan input file
function previewImage(event) {
    var file = event.target.files[0]; // Mengambil file yang dipilih
    if (file) {
        // Validasi File
        const validImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validImageTypes.includes(file.type)) {
            alert('Hanya file gambar yang diperbolehkan (JPEG, PNG, WebP).');
            event.target.value = ''; // Reset input jika format tidak valid
            return false;
        }
        
        // Tampilkan tombol Reset
        document.getElementById('resetButton').classList.remove('hidden');
        document.getElementById('resetButton').classList.add('inline-flex');

        var progressBar = document.getElementById('uploadProgress');
        var progressText = document.getElementById('uploadProgressText');
        var previewImg = document.getElementById('previewImg');
        var fileSizeText = document.getElementById('fileSizeText');
        var imageSizeWarning = document.getElementById('imageSizeWarning');
        var compressedFileSizeText = document.getElementById('compressedFileSizeText');
        var progressContainer = document.querySelector('#uploadProgress').parentElement;

        // Reset UI
        progressBar.style.width = '0%';
        progressText.innerText = '0%';
        progressText.style.display = 'none'; 
        previewImg.style.display = 'none'; 
        imageSizeWarning.style.display = 'none'; 
        fileSizeText.innerText = '';
        compressedFileSizeText.style.display = 'none'; 
        progressContainer.style.display = 'block';

        // Menampilkan ukuran file dalam MB
        var fileSizeInMB = (file.size / (1024 * 1024)).toFixed(2);
        fileSizeText.innerText = 'Ukuran file: ' + fileSizeInMB + ' MB';

        // Peringatan jika ukuran file lebih dari 2MB
        if (file.size > 2 * 1024 * 1024) {
            imageSizeWarning.style.display = 'block';
        }

        // Menyimpan file yang sedang diunggah secara global
        fileBeingUploaded = file;

        let progress = 0;
        function updateProgress() {
            if (progress < 100) {
                progress += 1; 
                progressBar.style.width = progress + '%';
                progressText.innerText = Math.round(progress) + '%';

                progressText.style.display = 'block';
                requestAnimationFrame(updateProgress);
            } else {
                progressContainer.style.display = 'none';
                progressText.style.display = 'none';

                var reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(file);

                if (file.size > 2 * 1024 * 1024) {
                    compressImage(file);
                } else {
                    compressedFileSizeText.style.display = 'none';
                    compressImage(file);
                }
            }
        }

        updateProgress();
    }
}

// Fungsi untuk menangani peristiwa dragover (seret gambar ke area)
function handleDragOver(event) {
    event.preventDefault();  // Mencegah perilaku default agar drop dapat terjadi
    event.stopPropagation();
    // Menambahkan sedikit styling untuk memberikan indikasi bahwa area bisa dijatuhkan
    document.getElementById('uploadArea').style.border = '2px solid #007bff';
}

// Fungsi untuk menangani peristiwa drop (jatuhkan gambar ke area)
function handleDrop(event) {
    event.preventDefault();  // Mencegah perilaku default
    event.stopPropagation();
    
    var files = event.dataTransfer.files; // Mendapatkan file yang dijatuhkan

    if (files.length > 0) {
        var file = files[0];  // Mengambil file pertama yang dijatuhkan

        // Validasi file gambar
        const validImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validImageTypes.includes(file.type)) {
            alert('Hanya file gambar yang diperbolehkan (JPEG, PNG, WebP).');
            return false;
        }

        // Mengupdate input file dengan file yang dijatuhkan
        document.getElementById('fileInput').files = files;
        previewImage({ target: { files: files } });  // Memanggil fungsi previewImage
    }
}

// Fungsi untuk mereset gaya setelah selesai dragging
function resetDragStyle() {
    document.getElementById('uploadArea').style.border = '2px dashed #ccc';
}


// Fungsi untuk mengompres gambar dengan progress yang halus
function compressImage(file) {
    // Cek apakah ukuran file lebih dari 2MB sebelum melanjutkan kompresi
    if (file.size <= 2 * 1024 * 1024) {
        // Jika ukuran file lebih kecil atau sama dengan 2MB, tidak perlu kompresi
        console.log('File lebih kecil dari 2MB, tidak perlu kompresi.');
        return; // Keluar dari fungsi tanpa melakukan kompresi
    }

    var reader = new FileReader();
    reader.onload = function (e) {
        var img = new Image();
        img.src = e.target.result;
        img.onload = function () {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            var maxWidth = 800; // Lebar maksimum setelah kompresi
            var maxHeight = 800; // Tinggi maksimum setelah kompresi
            var width = img.width;
            var height = img.height;

            // Mengubah ukuran gambar sesuai rasio aspek agar tidak melebihi maxWidth atau maxHeight
            if (width > height) {
                if (width > maxWidth) {
                    height *= maxWidth / width;
                    width = maxWidth;
                }
            } else {
                if (height > maxHeight) {
                    width *= maxHeight / height;
                    height = maxHeight;
                }
            }

            // Menggambar gambar yang telah diubah ukurannya pada canvas
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);

            // Mengompres gambar setelah diubah ukurannya
            canvas.toBlob(function(blob) {
                // Membuat file baru dengan gambar yang telah dikompresi
                compressedFile = new File([blob], file.name, { type: 'image/jpeg' });

                // Menghitung ukuran file terkompresi dalam MB
                var compressedFileSizeInMB = (compressedFile.size / (1024 * 1024)).toFixed(2); // Mengonversi ukuran ke MB

                // Menampilkan ukuran file setelah kompresi
                document.getElementById('compressedFileSizeText').innerText = 'Ukuran file setelah kompresi: ' + compressedFileSizeInMB + ' MB';
                // Menampilkan teks ukuran file setelah kompresi
                document.getElementById('compressedFileSizeText').style.display = 'block';

                // Anda bisa menambahkan compressedFile ke form data atau menguploadnya
                console.log('Compressed file:', compressedFile);
            }, 'image/jpeg', 0.7); // 0.7 adalah kualitas kompresi
        };
    };
    reader.readAsDataURL(file);
}

document.getElementById('resetButton').addEventListener('click', function() {
    // Sembunyikan button reset dan hapus kelas 'inline-flex'
    const resetButton = document.getElementById('resetButton');
    resetButton.classList.add('hidden');  // Menambahkan kelas 'hidden'
    resetButton.classList.remove('inline-flex');  // Menghapus kelas 'inline-flex'
    
    // Lakukan operasi lain yang diperlukan saat tombol diklik, misalnya reset preview
    resetPreview();
});

function resetPreview() {
    // Reset input file
    const fileInput = document.getElementById('fileInput');
    const npwp = document.getElementById('npwp');
    fileInput.value = ''; // Kosongkan input file
    npwp.value = ''; // Kosongkan input file

    // Reset elemen UI
    document.getElementById('file-name').innerText = '';
    document.getElementById('previewImg').style.display = 'none';
    document.getElementById('fileSizeText').innerText = '';
    document.getElementById('compressedFileSizeText').style.display = 'none';
    document.getElementById('imageSizeWarning').style.display = 'none';
    document.getElementById('uploadProgress').style.width = '0%';
    document.getElementById('uploadProgressText').style.display = 'none';

    // Reset variabel global
    compressedFile = null;

    // Sembunyikan button reset dan hapus kelas 'inline-flex'
    const resetButton = document.getElementById('resetButton');
    resetButton.classList.add('hidden');  // Menambahkan kelas 'hidden'
    resetButton.classList.remove('inline-flex');  // Menghapus kelas 'inline-flex'
    
}

