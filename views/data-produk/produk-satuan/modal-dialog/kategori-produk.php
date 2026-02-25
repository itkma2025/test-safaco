<div class="modal fade" id="katProduk" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Data Kategori Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tableKatProd">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">No</th>
                                <th class="text-center" style="width:120px;">Gambar Nie</th>
                                <th class="text-center" style="width:300px;">Kategori Produk</th>
                                <th class="text-center" style="width:350px;">Merk</th>
                                <th class="text-center" style="width:350px;">No Izin Edar</th>
                                <th class="text-center" style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php require_once __DIR__ . "/../query/kategori-produk.php"; ?>
                            <?php $no = 1;  foreach ($katProduk as $kat_prod) : ?>
                            <?php
                                $id_kategori    = $kat_prod->id_kat_produk;
                                $nama_kategori  = $kat_prod->nama_kategori;
                                $merk           = $kat_prod->nama_merk;
                                $nie            = $kat_prod->no_izin_edar;
                                $file_nie       = $kat_prod->file_nie;
                                $filename       = $kat_prod->filename;
                                $nama_folder    = $kat_prod->nama_folder;
                                $mime_type      = $kat_prod->mime_type;
                                $key            = $kat_prod->key_nie;

                                // Kondisi jenis NIE
                                $jenis_nie = "";
                                if(trim($kat_prod->jenis_nie) == 'Lokal' ){
                                    $jenis_nie = "AKD";
                                } else if (trim($kat_prod->jenis_nie) == 'Import' ) {
                                    $jenis_nie = "AKL";
                                } else {
                                    $jenis_nie = "";
                                }

                            ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no++ ?></td>
                                    <td class="align-middle text-center">
                                        <?php  
                                            if (!empty($file_nie) && !empty($nama_folder)) {
                                                $name  = rawurlencode($filename);
                                                $path  = rawurlencode($nama_folder);
                                                $mime  = rawurlencode($mime_type);

                                                $fileNie = "{$domain_sso}enkripsi_file/decrypt_nie.php?name={$name}&path={$path}&mime_type={$mime}&key={$key}";

                                                // Fancybox thumbnail + link
                                            ?>
                                                <a href="javascript:;" data-fancybox data-type="iframe" data-src="<?php echo $fileNie; ?>" data-width="1600" data-height="1200" style="color:blue;">
                                                        📄 Lihat File NIE
                                                    </a>

                                            <?php
                                            } else {
                                                echo '<span class="text-gray-400 italic">Tidak ada foto</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="align-middle"><?= $nama_kategori ?></td>
                                    <td class="align-middle text-center"><?= $merk ?></td>
                                    <td class="align-middle text-center"><?= $jenis_nie . " "  .$nie  ?></td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-primary btn-sm selectKategori"
                                        data-id-katprod="<?= $id_kategori; ?>" 
                                        data-nama-katprod="<?= $nama_kategori; ?>" 
                                        data-merk="<?= $merk; ?>" 
                                        data-nie="<?= $nie; ?>" 
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
<script>
    // select Produk Mater
    $(document).on("click", ".selectKategori", function () {
        $("#idKatProd").val($(this).data("id-katprod"));
        $("#namaKatProd").val($(this).data("nama-katprod"));
        $("#namaMerk").val($(this).data("merk"));
        $("#katProduk").modal("hide");
    });
</script>