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
<div class="modal fade" id="formSet" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="formIsiSet">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnIsiSet', function() {
        var id      = $(this).data("id");
        var idset   = $(this).data("idset");
        var action  = $(this).data("action");

        $.ajax({
            url: "ajax/produk-set/form-isi-set.php",
            type: "POST",
            data: { 
                id: id,
                idset: idset,
                action: action, 
            },
            success: function (response) {
                $("#formIsiSet").html(response);
            },
            error: function () {
                $("#formIsiSet").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
