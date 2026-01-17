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
<div class="modal fade" id="modalForm" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="formMaintenance">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnForm', function() {
        var id      = $(this).data("id");
        var id_alat = $(this).data("id-alat");
        $.ajax({
            url: "ajax/perawatan-alat-mesin/form-maintenance.php",
            type: "POST",
            data: { 
                    id: id, 
                    id_alat: id_alat 
                  },
            success: function (response) {
                $("#formMaintenance").html(response);
            },
            error: function () {
                $("#formMaintenance").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
