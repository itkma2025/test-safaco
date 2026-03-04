// Fungsi untuk pemisah ribuan
$(document).on("input", ".inputQty", function () {
    filterNonNumeric(this);     // Hapus karakter non angka
    preventLeadingZero(this);   // Hapus nol di depan
    formatRibuan(this);         // Format ribuan pakai titik
});

// Menampilkan data dari localStorage saat halaman dimuat
$(document).ready(function () {
    tampilkanProduk();
});

function tampilkanProduk() {
    let produkList = JSON.parse(localStorage.getItem("selectedProduk")) || [];
    let tbody = $("#tbodyProduk");
    tbody.empty();

    if (produkList.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="10" class="text-center text-muted">
                    Belum ada produk dipilih
                </td>
            </tr>
        `);
        return;
    }

    produkList.forEach((item, index) => {
        tbody.append(`
            <tr>
                <input type="hidden" class="idProduk" value="${item.idProduk}">
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${item.kodeProduk}</td>
                <td>${item.namaProduk}</td>
                <td>${item.namaKategori}</td>
                <td class="text-center">${item.namaMerk}</td>
                <td class="text-center">${item.namaGrade}</td>
                <td>
                    <input type="text" class="form-control text-end form-control-sm inputQty" minlenght="1" maxlength="16" data-id="${item.idProduk}" required>
                </td>
                <td class="text-center">${item.satuan}</td>
                <td class="text-center">0</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger btnHapus" data-id="${item.idProduk}">
                        <i class="fe fe-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

// Hapus produk dari localStorage saat tombol Hapus diklik
$(document).on("click", ".btnHapus", function () {
    let id = $(this).data("id");

    let produkList = JSON.parse(localStorage.getItem("selectedProduk")) || [];
    produkList = produkList.filter(item => item.idProduk != id);
    localStorage.setItem("selectedProduk", JSON.stringify(produkList));

    tampilkanProduk();
    toggleButton();
    cekProdukTerpilih();
});

// Cek produk terpilih
function cekProdukTerpilih() {
    let produkList = JSON.parse(localStorage.getItem("selectedProduk")) || [];

    $(".selectProduk").each(function () {
        let idProduk = $(this).data("id-produk");

        let sudahAda = produkList.some(item => item.idProduk == idProduk);

        if (sudahAda) {
            $(this)
                .prop("disabled", true)
                .text("Pilih");
        } else {
            $(this)
                .prop("disabled", false)
                .text("Pilih");
        }
    });
}

// Kode untuk select produk
$(document).on("click", ".selectProduk", function () {
    const $btn = $(this);

    const idProduk      = $(this).data("id-produk");
    const idProdukKarsa = $(this).data("id-produk-karsa");
    const kodeProduk    = $(this).data("kode-produk");
    const namaProduk    = $(this).data("nama-produk");
    const namaKategori  = $(this).data("nama-kategori");
    const namaMerk      = $(this).data("nama-merk");
    const namaGrade     = $(this).data("nama-grade");
    const satuan        = $(this).data("satuan");

    $btn.prop("disabled", true).text("Pilih");

    // Ambil data lama
    let produkList = JSON.parse(localStorage.getItem("selectedProduk")) || [];

    // Cek apakah sudah ada berdasarkan id
    let sudahAda = produkList.some(item => item.idProduk == idProduk);

    if (!sudahAda) {
        produkList.push({
            idProduk: idProduk,
            idProdukKarsa: idProdukKarsa,
            kodeProduk: kodeProduk,
            namaProduk: namaProduk,
            namaKategori: namaKategori,
            namaMerk: namaMerk,
            namaGrade: namaGrade,
            satuan: satuan
        });
    }

    // Simpan kembali
    localStorage.setItem("selectedProduk", JSON.stringify(produkList));

    toggleButton();
});

// Proses simpan permintaan produk
$("#btnProses").on("click", function () {
    let btn = $(this);

    // Disable semua button dalam container
    $(".btnAdd").prop("disabled", true);
    $("#btnProses").prop("disabled", true);
    $("#batalPermintaan").prop("disabled", true);

    // Tambahkan loading di tombol proses
    btn.html(`<span class="spinner-border spinner-border-sm me-2"></span> Memproses...`);

    let csrf_token           = $("#csrf_token").val();
    let id_permintaan_barang = $("#id_permintaan_barang").val();
    let no_permintaan        = $("#no_permintaan").val();
    let tgl_permintaan       = $("#tgl_permintaan").val();
    let jenis_permintaan     = $("#jenis_permintaan").val();
    let catatan              = $("#catatan").val();
    let honeypot             = $("#honeypot").val();
    let produkList           = JSON.parse(localStorage.getItem("selectedProduk")) || [];

     if (produkList.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "Oops...",
            text: "Belum ada produk dipilih"
        });
        return;
    }

    // VALIDASI
    let requiredFields = [
        { id: "#id_permintaan_barang", message: "ID Permintaan tidak boleh kosong" },
        { id: "#no_permintaan", message: "No Permintaan tidak boleh kosong" },
        { id: "#tgl_permintaan", message: "Tanggal Permintaan wajib diisi" },
        { id: "#jenis_permintaan", message: "Jenis Permintaan wajib dipilih" },
        { id: "#catatan", message: "Catatan wajib diisi" }
    ];

    for (let field of requiredFields) {
        if (!$(field.id).val()) {
            Swal.fire("Data Tidak Lengkap", field.message, "warning");
            $(".btnAdd").prop("disabled", false);
            $("#btnProses").prop("disabled", false);
            $("#batalPermintaan").prop("disabled", false);
            btn.html(`<i class="fe fe-refresh-cw me-1"></i>Proses Permintaan`);
            return;
        }
    }
    
    // Inject qty ke dalam produkList dan Cek validasi qty
    let validQty = true;
    $(".inputQty").each(function () {

        let id  = $(this).data("id");
        let qty = $(this).val().replace(/\./g, ''); // hapus titik ribuan

        if (!qty || parseInt(qty) < 1) {
            validQty = false;
            $(this).focus();
            return false;
        }

        produkList.forEach(function (item) {
            if (item.idProduk == id) {
                item.qty = parseInt(qty);
            }
        });

    });

    if (!validQty) {
        Swal.fire("Data Tidak Lengkap", "Qty minimal 1 dan tidak boleh kosong", "warning");
        $(".btnAdd").prop("disabled", false);
        $("#btnProses").prop("disabled", false);
        $("#batalPermintaan").prop("disabled", false);
        btn.html(`<i class="fe fe-refresh-cw me-1"></i>Proses Permintaan`);
        return;
    }

    // Kalau lolos semua
    $.ajax({
        url: "produk-masuk.php?action=simpan-permintaan-produk-karsa",
        type: "POST",
        dataType: "json",
        data: {
            csrf_token: csrf_token,
            id_permintaan_barang: id_permintaan_barang,
            no_permintaan: no_permintaan,
            tgl_permintaan: tgl_permintaan,
            jenis_permintaan: jenis_permintaan,
            catatan: catatan,
            honeypot: honeypot,
            produk: JSON.stringify(produkList)
        },
        success: function (response) {
            if (response.status === "success") {
                Swal.fire({
                    title: "Success",
                    html: `
                        <div style="font-size:15px;">
                            Permintaan produk Anda telah <b>${response.message}</b> ke dalam sistem.<br><br>
                            
                            <b>Nomor Permintaan:</b> ${response.data.no_permintaan}<br>
                            <b>Total Permintaan:</b> ${response.data.jumlah_produk} Barang<br>
                        </div>
                    `,
                    icon: "success"
                }).then(() => {
                    // Hapus localStorage dulu
                    localStorage.removeItem("selectedProduk");

                    // Enabled semua button dalam container
                    $(".btnAdd").prop("disabled", false);
                    btn.html(`<i class="fe fe-refresh-cw me-1"></i>Proses Permintaan`);

                    // Reload setelah klik OK
                    location.reload();
                });
            } else {
                Swal.fire("Gagal", response.message, "error");
                $(".btnAdd").prop("disabled", false);
                $("#btnProses").prop("disabled", false);
                $("#batalPermintaan").prop("disabled", false);
                btn.html(`<i class="fe fe-refresh-cw me-1"></i>Proses Permintaan`);
            }
        },
        error: function (xhr) {
            Swal.fire("Server Error", xhr.responseText, "error");
            $(".btnAdd").prop("disabled", false);
            $("#btnProses").prop("disabled", false);
            $("#batalPermintaan").prop("disabled", false);
            btn.html(`<i class="fe fe-refresh-cw me-1"></i>Proses Permintaan`);
        }
    });
});

// Tombol Batal
$(document).ready(function () {
    toggleButton();
});

// Fungsi toogle cek produk di localStorage untuk enable/disable tombol batal
function toggleButton() {

    let produkList = JSON.parse(localStorage.getItem("selectedProduk")) || [];

    if (produkList.length > 0) {
        $("#btnProses").prop("disabled", false);
        $("#batalPermintaan").prop("disabled", false);
    } else {
        $("#btnProses").prop("disabled", true);
        $("#batalPermintaan").prop("disabled", true);
    }
}

$(document).on("click", "#batalPermintaan", function () {
    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Permintaan akan dibatalkan dan data yang sudah diisi akan hilang.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Batalkan"
    }).then((result) => {
        if (result.isConfirmed) {
            // Hapus localStorage dulu
            localStorage.removeItem("selectedProduk");

            // Tampilkan alert sukses
            Swal.fire({
                title: "Dibatalkan!",
                text: "Permintaan telah dibatalkan.",
                icon: "success"
            }).then(() => {
                // Reload setelah klik OK
                location.reload();
            });
        }

    });
});