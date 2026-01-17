$(document).ready(function () {
  const table = new DataTable("#tableNoExportNew", {
    lengthChange: false,
    autoWidth: false,
    paging: true,
    dom: '<"top">rt<"bottom"lp><"clear">',
  });
  

  // Function to update total data information
  function updateTotalData() {
    const info = table.page.info();
    $("#totalData").html(
      `Showing ${info.start + 1} to ${info.end} of ${
        info.recordsDisplay
      } entries (filtered from ${info.recordsTotal} total entries)`
    );
  }

  // Call updateTotalData initially
  updateTotalData();

  // Search functionality
  $("#cari-data").on("input", function () {
    table.search($(this).val()).draw();
    if ($(this).val().length > 0) {
      $("#resetButton").show();
    } else {
      $("#resetButton").hide();
    }
  });

  $("#resetButton").on("click", function () {
    $("#cari-data").val(""); // Clear input text
    $("#cari-data").focus(); // Optional: focus back on the input
    $(this).hide(); // Hide reset button after clicking
    table.search("").draw(); // Clear the search
  });

  table.on("draw.dt", function () {
    // Mengatur ulang nomor urut setelah menggambar ulang tabel
    table
      .column(0, { search: "applied", order: "applied" })
      .nodes()
      .each(function (cell, i) {
        cell.innerHTML = i + 1;
      });

    // Update total data information
    updateTotalData();

    // Update pagination after draw
    updatePagination();
  });

  // Custom pagination logic
  const paginate = (page) => {
    const info = table.page.info();
    if (page < 0 || page >= info.pages) return;
    table.page(page).draw("page");
  };

  const updatePagination = () => {
    const info = table.page.info();
    const maxPagesToShow = 5; // Number of pages to show at a time
    let startPage = Math.floor(info.page / maxPagesToShow) * maxPagesToShow;
    let endPage = Math.min(startPage + maxPagesToShow, info.pages);

    // Ensure that the next pages are displayed correctly
    if (info.page >= endPage - 1) {
      startPage = Math.max(endPage - maxPagesToShow, 0);
      endPage = Math.min(startPage + maxPagesToShow, info.pages);
    }

    $("#customPagination").html(`
      <li class="page-item ${info.page === 0 ? "disabled" : ""}">
        <a class="page-link" href="#" data-page="prev">&laquo;</a>
      </li>
      ${Array.from(
        { length: endPage - startPage },
        (v, i) => `
        <li class="page-item ${info.page === startPage + i ? "active" : ""}">
          <a class="page-link" href="#" data-page="${startPage + i}">${
          startPage + i + 1
        }</a>
        </li>
      `
      ).join("")}
      <li class="page-item ${info.page === info.pages - 1 ? "disabled" : ""}">
        <a class="page-link" href="#" data-page="next">&raquo;</a>
      </li>
    `);
  };

  $("#customPagination").on("click", "a", function (e) {
    e.preventDefault();
    const page = $(this).data("page");
    const info = table.page.info();
    if (page === "prev") paginate(info.page - 1);
    else if (page === "next") paginate(info.page + 1);
    else paginate(page);
  });

  updatePagination(); // Initial call to setup pagination
});

// $(document).ready(function () {
//   const tableLokasi = new DataTable("#tableLokasi", {
//     lengthChange: false,
//     autoWidth: false,
//     paging: true,
//     dom: '<"top">rt<"bottom"lp><"clear">',
//   });

//   // Function to update total data information
//   function updateTotalDataLokasi() {
//     const info = tableLokasi.page.info();
//     $("#totalDataLokasi").html(
//       `Showing ${info.start + 1} to ${info.end} of ${info.recordsDisplay} entries (filtered from ${info.recordsTotal} total entries)`
//     );
//   }

//   updateTotalDataLokasi();

//   // Search lokasi
//   $("#cari-lokasi").on("input", function () {
//     tableLokasi.search($(this).val()).draw();
//     if ($(this).val().length > 0) {
//       $("#resetButtonLokasi").show();
//     } else {
//       $("#resetButtonLokasi").hide();
//     }
//   });

//   $("#resetButtonLokasi").on("click", function () {
//     $("#cari-lokasi").val("");
//     $("#cari-lokasi").focus();
//     $(this).hide();
//     tableLokasi.search("").draw();
//   });

//   // Redraw
//   tableLokasi.on("draw.dt", function () {
//     tableLokasi
//       .column(0, { search: "applied", order: "applied" })
//       .nodes()
//       .each(function (cell, i) {
//         cell.innerHTML = i + 1;
//       });

//     updateTotalDataLokasi();
//     updatePaginationLokasi();
//   });

//   // Pagination lokasi
//   const paginateLokasi = (page) => {
//     const info = tableLokasi.page.info();
//     if (page < 0 || page >= info.pages) return;
//     tableLokasi.page(page).draw("page");
//   };

//   const updatePaginationLokasi = () => {
//     const info = tableLokasi.page.info();
//     const maxPagesToShow = 5;
//     let startPage = Math.floor(info.page / maxPagesToShow) * maxPagesToShow;
//     let endPage = Math.min(startPage + maxPagesToShow, info.pages);

//     if (info.page >= endPage - 1) {
//       startPage = Math.max(endPage - maxPagesToShow, 0);
//       endPage = Math.min(startPage + maxPagesToShow, info.pages);
//     }

//     $("#customPaginationLokasi").html(`
//       <li class="page-item ${info.page === 0 ? "disabled" : ""}">
//         <a class="page-link" href="#" data-page="prev">&laquo;</a>
//       </li>
//       ${Array.from({ length: endPage - startPage }, (v, i) => `
//         <li class="page-item ${info.page === startPage + i ? "active" : ""}">
//           <a class="page-link" href="#" data-page="${startPage + i}">${startPage + i + 1}</a>
//         </li>
//       `).join("")}
//       <li class="page-item ${info.page === info.pages - 1 ? "disabled" : ""}">
//         <a class="page-link" href="#" data-page="next">&raquo;</a>
//       </li>
//     `);
//   };

//   $("#customPaginationLokasi").on("click", "a", function (e) {
//     e.preventDefault();
//     const page = $(this).data("page");
//     const info = tableLokasi.page.info();
//     if (page === "prev") paginateLokasi(info.page - 1);
//     else if (page === "next") paginateLokasi(info.page + 1);
//     else paginateLokasi(page);
//   });

//   updatePaginationLokasi();
// });


