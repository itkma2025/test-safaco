<div class="modal fade" id="produk" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header"> 
                <h5 class="modal-title" id="staticBackdropLabel">Data Produk Master</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tableProdSatuan">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">No</th>
                                <th class="text-center" style="width:120px;">Gambar Produk</th>
                                <th class="text-center" style="width:300px;">Nama Produk</th>
                                <th class="text-center" style="width:350px;">Merk</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 
<script>
    $(document).ready(function () {
        new DataTable('#tableProdSatuan', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 6,
            ajax: {
                url: 'data-produk.php?action=produk-satuan-set',
                type: 'POST'
            },
            columnDefs: [
                { targets: [0,1,4], orderable: false }
            ]
        });
    });
</script>

<script>
   // select Produk Satuan
    $(document).on("click", ".selectProduk", function () {

        // ========================
        // 1. Data produk
        // ========================
        const idProduk   = $(this).data("id-produk");
        const namaProduk = $(this).data("nama-produk");
        const merk  = $(this).data("merk");

        $("#idProduk").val(idProduk);
        $("#namaProduk").val(namaProduk);

        // ========================
        // 2. Merk
        // ========================
        $("#merk").val(merk);

        // ========================
        // 3. Preview gambar (SIMPLE) di gunakan jika ingin menampilkan preview gambar produk master di form produk
        // ========================
        const imgSrc = $(this).data("img-src");
        $("#previewProdukLink").attr("href", imgSrc);

        if (imgSrc) {
            $("#previewProduk")
                .attr("src", imgSrc)
                .removeClass("d-none");
        }

        // Tutup modal
        $("#produk").modal("hide");
    });
</script>