<div class="modal fade" id="persetujuanPJTSafaco" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title" id="staticBackdropLabel">Konfirmasi Ubah Status Pengajuan (PJT Safaco)</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Start Form -->
                <input type="hidden" class="form-control" id="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Status Pengajuan</label>
                    </div>
                    <div class="col-md-9">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_pjt_safaco" id="inlineRadio1" value="Diterima">
                            <label class="form-check-label" for="inlineRadio1">Diterima</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_pjt_safaco" id="inlineRadio2" value="Ditolak">
                            <label class="form-check-label" for="inlineRadio2">Ditolak</label>
                        </div>
                    </div>
                    <span class="text-danger mt-2 d-none" id="errorStatusPjtSafaco">
                        Status Pengajuan Belum Dipilih!.
                    </span>
                </div>
                <div class="row mb-3" id="divAlasanPenolakanPjtSafaco" style="display: none;">
                    <div class="col-md-3">
                        <label class="form-label">Alasan Penolakan</label>
                    </div>
                    <div class="col-md-9">
                        <textarea class="form-control" id="alasanPenolakanPjtSafaco" rows="3" placeholder="Masukkan alasan penolakan (jika ditolak)"></textarea>
                    </div>
                    <span class="text-danger mt-2 d-none" id="errorAlasanPenolakanPjtSafaco">
                        Alasan Penolakan Belum Diisi!.
                    </span>
                </div>
                <!-- Honeypot Field: Tersembunyi dari Pengguna -->
                <div style="display:none;">
                    <label>Honeypot (Jangan Diisi):</label>
                    <input type="text" id="honeypot" name="honeypot">
                </div>
                <!-- End Form Input -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-1" data-bs-dismiss="modal" id="btnClosePjtSafaco">Close</button>
                <button type="button" class="btn btn-info me-1" id='update-pjt-safaco'>Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Menampilkan atau menyembunyikan textarea alasan penolakan berdasarkan pilihan radio button
        $('input[name="status_pjt_safaco"]').change(function() {
            if ($(this).val() === 'Ditolak') {
                $('#divAlasanPenolakanPjtSafaco').show();
            } else {
                $('#divAlasanPenolakanPjtSafaco').hide();
                $('#alasanPenolakanPjtSafaco').val('');
            }
        });

        // Reset form saat modal ditutup
        $('#btnClosePjtSafaco').click(function() {
            $('input[name="status_pjt_safaco"]').prop('checked', false);
            $('#divAlasanPenolakanPjtSafaco').hide();
            $('#alasanPenolakanPjtSafaco').val('');
        });

        // Event handler untuk tombol update status PJT Safaco
        $("#update-pjt-safaco").click(function() {
            var id_permintaan_barang = '<?= $id_permintaan_barang ?>';
            var status = $('input[name="status_pjt_safaco"]:checked').val();
            var alasan = $('#alasanPenolakanPjtSafaco').val();

            var btn = $(this); // simpan tombol yang diklik
            btn.prop("disabled", true);
            btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Loading...');

            // console.log("id_permintaan_barang:", id_permintaan_barang);

            if (!status) {
                $('#errorStatusPjtSafaco').removeClass('d-none');
                btn.prop("disabled", false);
                btn.html('Update Status');
                return;
            } else {
                $('#errorStatusPjtSafaco').addClass('d-none');
            }

            if (status === 'Ditolak' && !alasan.trim()) {
                $('#errorAlasanPenolakanPjtSafaco').removeClass('d-none');
                btn.prop("disabled", false);
                btn.html('Update Status');
                return;
            } else {
                $('#errorAlasanPenolakanPjtSafaco').addClass('d-none');
            }

            // Kalau lolos semua
            $.ajax({
                url: "produk-masuk.php?action=update-status-pjt-safaco",
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