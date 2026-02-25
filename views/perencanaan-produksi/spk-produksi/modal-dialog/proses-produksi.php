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
<div class="modal fade" id="modalProses" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="formProsesProduksi">
        <!-- Contetnt -->
        </div>
    </div>
</div>
<!-- End modal dialog -->
<script>
  $(document).ready(function () {
    $(document).on('click', '.btnProses', function() {
        var id_spk          = $(this).attr("data-id-spk");
        var no_spk          = $(this).attr("data-no-spk");
        var referer         = $(this).attr("data-referer");

        $.ajax({
            url: "ajax/perencanaan-produksi/spk-produksi/form-proses-produksi.php",
            type: "POST",
            data: 
            { 
                id_spk: id_spk,
                no_spk: no_spk,
                referer: referer
            },
            success: function (response) {
                $("#formProsesProduksi").html(response);
            },
            error: function () {
                $("#formProsesProduksi").html('<p class="text-danger">Gagal mengambil data.</p>');
            }
        });
    });
  });
</script>
