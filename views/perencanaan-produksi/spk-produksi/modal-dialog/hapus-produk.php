<?php  
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['id_user'])) {
        header("location: 404.php");
        exit;
    }
?>
<!-- Modal dialog -->
<div class="modal fade" id="hapusData" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="formHapusProduk">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnHapus', function() {
        var id_details  = $(this).attr("data-id-details");
        var id_spk      = $(this).attr("data-id-spk");
        var nama_produk = $(this).attr("data-nama-produk");
        var referer         = $(this).attr("data-referer");

        $.ajax({
            url: "ajax/perencanaan-produksi/spk-produksi/form-hapus-produk.php",
            type: "POST",
            data: 
            { 
                id_details: id_details,
                id_spk: id_spk,
                nama_produk: nama_produk,
                referer: referer
            },
            success: function (response) {
                $("#formHapusProduk").html(response);
            },
            error: function () {
                $("#formHapusProduk").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
