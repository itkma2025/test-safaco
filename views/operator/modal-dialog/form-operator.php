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
<div class="modal fade" id="operator" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="formOperator">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnOperator', function() {
        var id  = $(this).data("id");
        $.ajax({
            url: "ajax/operator/form-operator.php",
            type: "POST",
            data: { id: id },
            success: function (response) {
                $("#formOperator").html(response);
            },
            error: function () {
                $("#formOperator").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
