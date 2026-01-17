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
<div class="modal fade" id="addData" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="formAddGrade">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnAddGrade', function() {
        var id = $(this).data("id");

        $.ajax({
            url: "ajax/grade/form-tambah-grade.php",
            type: "POST",
            data: { id: id },
            success: function (response) {
                $("#formAddGrade").html(response);
            },
            error: function () {
                $("#formAddGrade").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
