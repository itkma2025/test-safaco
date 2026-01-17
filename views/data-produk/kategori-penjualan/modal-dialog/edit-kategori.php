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
        <div class="modal-content" id="formEditKategori">
        <!-- Contetnt --> 
        </div>
    </div>
</div>
<!-- End odal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnEditKategori', function() {
        var id = $(this).data("id");

        $.ajax({
            url: "ajax/kategori-penjualan/form-edit-kategori.php",
            type: "POST",
            data: { id: id },
            success: function (response) {
                $("#formEditKategori").html(response);
            },
            error: function () {
                $("#formEditKategori").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
