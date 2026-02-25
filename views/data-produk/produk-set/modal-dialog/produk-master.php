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
                    <table class="table table-striped table-bordered" id="tableProdMaster">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">No</th>
                                <th class="text-center" style="width:120px;">Gambar Produk</th>
                                <th class="text-center" style="width:300px;">Nama Produk</th>
                                <th class="text-center" style="width:350px;">Deskripsi</th>
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
        new DataTable('#tableProdMaster', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 6,
            ajax: {
                url: 'data-produk.php?action=produk-master-set',
                type: 'POST'
            },
            columnDefs: [
                { targets: [0,1,4], orderable: false }
            ]
        });
    });
</script>

<script>
   // select Produk Master
    $(document).on("click", ".selectProdukMaster", function () {

        // ========================
        // 1. Data produk
        // ========================
        const idProduk   = $(this).data("id-produk-master");
        const namaProduk = $(this).data("nama-produk-master");
        const deskripsi  = $(this).data("deskripsi-produk-master");

        $("#idProdukMaster").val(idProduk);
        $("#namaProdukMaster, #namaProduk").val(namaProduk);

        // ========================
        // 2. Deskripsi
        // ========================
        if (window.editorInstance) {
            window.editorInstance.setData(deskripsi);

            const text = deskripsi.replace(/<[^>]*>/g, "");
            $("#charCount").text(`${Math.min(text.length, 2000)} / 2000`);
        } else {
            $("#deskripsi").val(deskripsi);
        }

        // ========================
        // 3. Preview gambar (SIMPLE) di gunakan jika ingin menampilkan preview gambar produk master di form produk
        // ========================
        const imgSrc = $(this).data("img-src");
        $("#previewProdukMasterLink").attr("href", imgSrc);

        if (imgSrc) {
            $("#previewProdukMaster")
                .attr("src", imgSrc)
                .removeClass("d-none");
        }

        // Tutup modal
        $("#produk").modal("hide");
    });
</script>