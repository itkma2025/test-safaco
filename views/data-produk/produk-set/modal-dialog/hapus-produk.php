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
    $(document).on('click', '.btnHapusProduk', function() {
        var id = $(this).data("id");

        $.ajax({
            url: "ajax/produk-set/form-hapus-produk.php",
            type: "POST",
            data: { id: id },
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
