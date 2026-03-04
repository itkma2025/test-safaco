<div class="modal fade" id="persetujuanMR" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title" id="staticBackdropLabel">Konfirmasi Ubah Status Pengajuan (MR)</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Status Form -->
                <input type="hidden" class="form-control" id="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Status Pengajuan</label>
                    </div>
                    <div class="col-md-9">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_mr" id="inlineRadio1" value="Diterima">
                            <label class="form-check-label" for="inlineRadio1">Diterima</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_mr" id="inlineRadio2" value="Ditolak">
                            <label class="form-check-label" for="inlineRadio2">Ditolak</label>
                        </div>
                    </div>
                    <span class="text-danger mt-2 d-none" id="errorStatusPjtSafaco">
                        Status Pengajuan Belum Dipilih!.
                    </span>
                </div>
                <div class="row mb-3" id="divAlasanPenolakanMr" style="display: none;">
                    <div class="col-md-3">
                        <label class="form-label">Alasan Penolakan</label>
                    </div>
                    <div class="col-md-9">
                        <textarea class="form-control" id="alasanPenolakanMr" rows="3" placeholder="Masukkan alasan penolakan (jika ditolak)"></textarea>
                    </div>
                    <span class="text-danger mt-2 d-none" id="errorAlasanPenolakanMr">
                        Alasan Penolakan Belum Diisi!.
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-1" data-bs-dismiss="modal" id="btnCloseMr">Close</button>
                <button type="button" class="btn btn-info me-1" id="update-mr">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Menampilkan atau menyembunyikan textarea alasan penolakan berdasarkan pilihan radio button
        $('input[name="status_mr"]').change(function() {
            if ($(this).val() === 'Ditolak') {
                $('#divAlasanPenolakanMr').show();
            } else {
                $('#divAlasanPenolakanMr').hide();
                $('#alasanPenolakanMr').val('');
            }
        });

        $('#btnCloseMr').click(function() {
            // Reset form saat modal ditutup
            $('input[name="status_mr"]').prop('checked', false);
            $('#divAlasanPenolakanMr').hide();
            $('#alasanPenolakanMr').val('');
        });

        // Event handler untuk tombol update status MR
        $("#update-mr").click(function() {
            var id_permintaan_barang = '<?= $id_permintaan_barang ?>';
            var status = $('input[name="status_mr"]:checked').val();
            var alasan = $('#alasanPenolakanMr').val();

            var btn = $(this); // simpan tombol yang diklik
            btn.prop("disabled", true);
            btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Loading...');


            // console.log("id_permintaan_barang:", id_permintaan_barang);

            if (!status) {
                $('#errorStatusMr').removeClass('d-none');
                btn.prop("disabled", false);
                btn.html('Update Status');
                return;
            } else {
                $('#errorStatusMr').addClass('d-none');
            }

            if (status === 'Ditolak' && !alasan.trim()) {
                $('#errorAlasanPenolakanMr').removeClass('d-none');
                btn.prop("disabled", false);
                btn.html('Update Status');
                return;
            } else {
                $('#errorAlasanPenolakanMr').addClass('d-none');
            }

            // Kalau lolos semua
            $.ajax({
                url: "produk-masuk.php?action=update-status-mr",
                type: "POST",
                dataType: "json",
                data: {
                    csrf_token: $('#csrf_token').val(),
                    id_permintaan_barang: id_permintaan_barang,
                    status: status,
                    alasan: alasan,
                    honeypot: $('#honeypot').val()
                },
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({
                            title: "Success",
                            html: `
                                <div style="font-size:15px;">
                                    Data ${response.message}</b>.<br><br>
                                </div>
                            `,
                            icon: "success"
                        }).then(() => {
                            // Reload setelah klik OK
                            location.reload();
                        });
                    } else {
                        Swal.fire("Gagal", response.message, "error");
                        // aktifkan kembali tombol
                        btn.prop("disabled", false);
                        btn.html('Update Status');
                    }
                },
                error: function (xhr) {
                    Swal.fire("Server Error", xhr.responseText, "error");
                    // aktifkan kembali tombol
                    btn.prop("disabled", false);
                    btn.html('Update Status');
                }
            });
        });
    });
</script>