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
        <div class="modal-content" id="formHapusHistory">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnHapusHistory', function() {
        var id = $(this).data("id");

        $.ajax({
            url: "ajax/perawatan-alat-mesin/form-hapus-history-maintenance.php",
            type: "POST",
            data: { id: id },
            success: function (response) {
                $("#formHapusHistory").html(response);
            },
            error: function () {
                $("#formHapusHistory").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
