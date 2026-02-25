<style>
    table.dataTable {
        width: 100% !important;
        table-layout: auto;
    }

    table.dataTable th,
    table.dataTable td {
        white-space: nowrap; /* teks biar tidak patah */
    }
</style>
<div class="modal fade" id="lokasiProduk" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Data Lokasi Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tableLokasi">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">No</th>
                                <th class="text-center" style="width:120px;">Nama Lokasi</th>
                                <th class="text-center" style="width:300px;">Lantai</th>
                                <th class="text-center" style="width:350px;">Area</th>
                                <th class="text-center" style="width:350px;">No. Rak</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php require_once __DIR__ . "/../query/lokasi-produk.php"; ?>
                            <?php $no = 1;  foreach ($lokasi_produk as $lokasi) : ?>
                            <?php
                                $id_lokasi      = $lokasi->id_lokasi;
                                $nama_lokasi    = $lokasi->nama_lokasi;
                                $lantai         = $lokasi->lantai;     // Nama file terenkripsi (tanpa .enc)
                                $area           = $lokasi->area;
                                $no_rak         = $lokasi->no_rak;  // Nama folder
                            ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++ ?></td>
                                    <td class="align-middle text-center"><?= $nama_lokasi ?></td>
                                    <td class="align-middle"><?= $lantai ?></td>
                                    <td class="align-middle"><?= $area ?></td>
                                    <td class="align-middle"><?= $no_rak ?></td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-primary btn-sm selectLokasiProduk"
                                        data-id-lokasi="<?= $id_lokasi; ?>" 
                                        data-nama-lokasi="<?= $nama_lokasi; ?>" 
                                        data-lantai="<?= $lantai; ?>" 
                                        data-area="<?= $area; ?>" 
                                        data-no-rak="<?= $no_rak; ?>" 
                                        data-bs-dismiss="modal">
                                        Pilih
                                    </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- jQuery -->
<script>
    // select Produk Mater
    $(document).on("click", ".selectLokasiProduk", function () {
        $("#idLokasi").val($(this).data("id-lokasi"));
        $("#namaLokasi").val($(this).data("nama-lokasi"));
        $("#lantai").val($(this).data("lantai"));
        $("#area").val($(this).data("area"));
        $("#noRak").val($(this).data("no-rak"));
        $("#lokasiProduk").modal("hide");
    });
</script>

