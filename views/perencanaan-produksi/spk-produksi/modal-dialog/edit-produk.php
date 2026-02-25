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
<div class="modal fade" id="editData" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="formEditProduk">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnEdit', function() {
        var id_details      = $(this).attr("data-id-details");
        var id_spk          = $(this).attr("data-id-spk");
        var kode_produk     = $(this).attr("data-kode-produk");
        var nama_produk     = $(this).attr("data-nama-produk");
        var qty_plan        = $(this).attr("data-qty-plan");
        var referer         = $(this).attr("data-referer");

        $.ajax({
            url: "ajax/perencanaan-produksi/spk-produksi/form-edit-produk.php",
            type: "POST",
            data: 
            { 
                id_details: id_details,
                id_spk: id_spk,
                kode_produk: kode_produk,
                nama_produk: nama_produk,
                qty_plan: qty_plan,
                referer: referer
            },
            success: function (response) {
                $("#formEditProduk").html(response);
            },
            error: function () {
                $("#formEditProduk").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
