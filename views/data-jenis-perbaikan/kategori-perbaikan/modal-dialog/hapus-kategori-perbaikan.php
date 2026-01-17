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
        <div class="modal-content" id="formHapus">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnHapus', function() {
        var id = $(this).data("id");

        $.ajax({
            url: "ajax/data-jenis-perbaikan/kategori-perbaikan/form-hapus-kategori-perbaikan.php",
            type: "POST",
            data: { id: id },
            success: function (response) {
                $("#formHapus").html(response);
            },
            error: function () {
                $("#formHapus").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>