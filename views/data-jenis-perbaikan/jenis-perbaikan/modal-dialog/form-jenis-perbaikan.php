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
        <div class="modal-content" id="formJenPerbaikan">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnForm', function() {
        var id = $(this).data("id");

        $.ajax({
            url: "ajax/data-jenis-perbaikan/jenis-perbaikan/form-jenis-perbaikan.php",
            type: "POST",
            data: { id: id },
            success: function (response) {
                $("#formJenPerbaikan").html(response);
            },
            error: function () {
                $("#formJenPerbaikan").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
