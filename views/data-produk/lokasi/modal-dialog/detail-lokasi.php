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
<div class="modal fade" id="detailData" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="formDetailLokasi">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End odal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnDetailLokasi', function() {
        var id = $(this).data("id");

        $.ajax({
            url: "ajax/lokasi/detail-lokasi.php",
            type: "POST",
            data: { id: id },
            success: function (response) {
                $("#formDetailLokasi").html(response);
            },
            error: function () {
                $("#formDetailLokasi").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
