dt(document).ready(function () {
    var table = dt('#tableWithExport').DataTable({
        lengthChange: false,
        paging: true,
        pageLength: 10,
        dom: 't',
        info: false,
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export to Excel',
                title: 'Data Customer',
                exportOptions: {
                    columns: ":not(:last-child)"
                }
            },
            {
                extend: 'pdfHtml5',
                text: 'Export to PDF',
                title: 'Data Customer',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ":not(:last-child)"
                },
                customize: function (doc) {
                    doc.pageMargins = [40, 40, 40, 40]; // margin kiri-kanan sama
                    doc.defaultStyle.fontSize = 9;
                    doc.styles.tableHeader = {
                        bold: true,
                        fontSize: 10,
                        color: 'black',
                        alignment: 'center'
                    };

                    // Hindari potong baris di akhir halaman
                    doc.content[1].layout = {
                        hLineWidth: function () { return 0.5; },
                        vLineWidth: function () { return 0.5; },
                        hLineColor: function () { return '#aaa'; },
                        vLineColor: function () { return '#aaa'; },
                        paddingLeft: function () { return 4; },
                        paddingRight: function () { return 4; },
                        paddingTop: function () { return 2; },
                        paddingBottom: function () { return 2; }
                    };

                    // Set agar baris tidak terpotong di akhir halaman
                    doc.content[1].table.dontBreakRows = true;

                    // Set agar header tetap muncul di setiap halaman
                    doc.content[1].table.headerRows = 1;
                    doc.content[1].table.keepWithHeaderRows = 1;

                    // Penyesuaian column widths agar tidak terlalu sempit/lebar
                    const columnCount = doc.content[1].table.body[0].length;
                    if (columnCount <= 6) {
                        doc.content[1].table.widths = Array(columnCount).fill('*');
                    } else {
                        doc.content[1].table.widths = Array(columnCount).fill('auto');
                    }

                    // Alignment isi sel
                    doc.content[1].table.body.forEach(function(row, rowIndex) {
                        row.forEach(function(cell, colIndex) {
                            if (typeof cell === 'object') {
                                cell.alignment = 'center';
                                cell.margin = [2, 4, 2, 4];
                            }
                        });
                    });
                }
            }
        ],
    });

    // Tombol export
    dt('#export-excel').on('click', function () {
        table.button(0).trigger();
    });
    dt('#export-pdf').on('click', function () {
        table.button(1).trigger();
    });

    // Pencarian manual
    dt('#custom-search').on('keyup', function () {
        table.search(this.value).draw();
    });

    function updatePagination() {
        var info = table.page.info();

        dt('#start').text(info.recordsDisplay > 0 ? info.start + 1 : 0);
        dt('#end').text(info.recordsDisplay > 0 ? info.end : 0);
        dt('#total').text(info.recordsDisplay);

        var pageLinks = dt('#page-links');
        pageLinks.empty();
        for (var i = 0; i < info.pages; i++) {
            var pageNumber = i + 1;
            var activeClass = (i === info.page) ? 'bg-indigo-600 text-white' : 'text-gray-900';
            pageLinks.append('<a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ' + activeClass + '" data-page="' + i + '">' + pageNumber + '</a>');
        }

        dt('#prev').toggleClass('text-gray-400', info.page === 0);
        dt('#next').toggleClass('text-gray-400', info.page === info.pages - 1);
    }

    function updateRowNumbers() {
        table
            .column(0, { search: "applied", order: "applied" })
            .nodes()
            .each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
    }

    // Pagination events
    dt(document).on('click', '#page-links a', function (e) {
        e.preventDefault();
        table.page(dt(this).data('page')).draw('page');
    });

    dt(document).on('click', '#prev', function (e) {
        e.preventDefault();
        table.page('previous').draw('page');
    });

    dt(document).on('click', '#next', function (e) {
        e.preventDefault();
        table.page('next').draw('page');
    });

    table.on('draw.dt', function () {
        updatePagination();
        updateRowNumbers();
    });

    updatePagination();
    updateRowNumbers();
});
