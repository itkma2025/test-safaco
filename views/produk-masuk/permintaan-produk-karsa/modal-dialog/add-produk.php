<div class="modal fade" id="produk" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header"> 
                <h5 class="modal-title" id="staticBackdropLabel">Data Produk Master</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs nav-tabs-solid nav-justified mb-3">
                    <li class="nav-item"><a class="nav-link active" href="#reg" data-bs-toggle="tab">Produk Reguler</a></li>
                    <li class="nav-item"><a class="nav-link" href="#ecat" data-bs-toggle="tab">Produk Ecat</a></li>
                    <li class="nav-item"><a class="nav-link" href="#set-marwa" data-bs-toggle="tab">Produk Set Marwa</a></li>
                    <li class="nav-item"><a class="nav-link" href="#set-ecat" data-bs-toggle="tab">Produk Set Ecat</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane show active" id="reg">
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="tableProdReguler" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:30px;">No</th>
                                        <th class="text-center" style="width:120px;">Gambar Produk</th>
                                        <th class="text-center" style="width:300px;">Nama Produk</th>
                                        <th class="text-center" style="width:350px;">Nama Kategori</th>
                                        <th class="text-center" style="width:350px;">NIE</th>
                                        <th class="text-center" style="width:100px;">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane show" id="ecat">
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="tableProdEcat" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:30px;">No</th>
                                        <th class="text-center" style="width:120px;">Gambar Produk</th>
                                        <th class="text-center" style="width:300px;">Nama Produk</th>
                                        <th class="text-center" style="width:350px;">Nama Kategori</th>
                                        <th class="text-center" style="width:350px;">NIE</th>
                                        <th class="text-center" style="width:100px;">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="set-marwa">
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="tableProdSetMarwa" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:30px;">No</th>
                                        <th class="text-center" style="width:120px;">Gambar Produk</th>
                                        <th class="text-center" style="width:300px;">Nama Produk</th>
                                        <th class="text-center" style="width:350px;">Nama Kategori</th>
                                        <th class="text-center" style="width:350px;">NIE</th>
                                        <th class="text-center" style="width:100px;">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="set-ecat">
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="tableProdSetEcat" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:30px;">No</th>
                                        <th class="text-center" style="width:120px;">Gambar Produk</th>
                                        <th class="text-center" style="width:300px;">Nama Produk</th>
                                        <th class="text-center" style="width:350px;">Nama Kategori</th>
                                        <th class="text-center" style="width:350px;">NIE</th>
                                        <th class="text-center" style="width:100px;">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div> 
<script>
  const myModal = document.getElementById('produk');

  myModal.addEventListener('hidden.bs.modal', function () {
    tampilkanProduk();
    toggleBatalButton();
  });
</script>

<script>
    $(document).ready(function () {
        new DataTable('#tableProdReguler', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 10,
            ajax: {
                url: 'produk-masuk.php?action=produk-reguler',
                type: 'POST',
            },
            drawCallback: function () {
                cekProdukTerpilih();
            }
        });
    });

    $(document).ready(function () {
        new DataTable('#tableProdEcat', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 10,
            ajax: {
                url: 'produk-masuk.php?action=produk-ecat',
                type: 'POST',
            },
            drawCallback: function () {
                cekProdukTerpilih();
            }
        });
    });

    $(document).ready(function () {
        new DataTable('#tableProdSetMarwa', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 10,
            ajax: {
                url: 'produk-masuk.php?action=produk-set-marwa',
                type: 'POST',
            },
            drawCallback: function () {
                cekProdukTerpilih();
            }
        });
    });

    $(document).ready(function () {
        new DataTable('#tableProdSetEcat', {
            processing: true,
            serverSide: true,
            lengthChange: false,
            pageLength: 10,
            ajax: {
                url: 'produk-masuk.php?action=produk-set-ecat',
                type: 'POST',
            },
            drawCallback: function () {
                cekProdukTerpilih();
            }
        });
    });
</script>