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
        <div class="modal-content" id="formEditGrade">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End odal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnEditGrade', function() {
        var id = $(this).data("id");

        $.ajax({
            url: "ajax/grade/form-edit-grade.php",
            type: "POST",
            data: { id: id },
            success: function (response) {
                $("#formEditGrade").html(response);
            },
            error: function () {
                $("#formEditGrade").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
