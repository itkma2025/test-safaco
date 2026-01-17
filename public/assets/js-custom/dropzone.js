const dropZone      = document.getElementById("dropZone");
const fileInput     = document.getElementById("fileInput");
const previewZone   = document.getElementById("previewZone");
const fileInfo      = document.getElementById("fileInfo");
const dataFileName  = document.getElementById("dataFileName");

// Klik area drop-zone untuk buka file dialog
dropZone.addEventListener("click", () => fileInput.click());

// Drag over
dropZone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropZone.classList.add("dragover");
});

// Drag leave
dropZone.addEventListener("dragleave", () => {
    dropZone.classList.remove("dragover");
});

// Drop file
dropZone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropZone.classList.remove("dragover");
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFileName();
    }
});

// File input change
fileInput.addEventListener("change", showFileName);

function showFileName() {
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        const fileName = file.name;

        const reader = new FileReader();
        reader.onload = (e) => {
        const fileUrl = e.target.result;

        // Sembunyikan dropZone, tampilkan previewZone
        dropZone.style.display = "none";
        previewZone.style.display = "block";

        // Isi previewZone dengan thumbnail + nama + tombol reset
        fileInfo.innerHTML = `
            <img src="${fileUrl}" alt="preview">
            <a href="${fileUrl}" data-fancybox="preview" class="file-name">${fileName}</a>
            <span class="remove-file">&times;</span>
        `;

        // Reset handler
        document.querySelector(".remove-file").addEventListener("click", (ev) => {
            ev.stopPropagation();
            resetFileInput();
        });
        };
        reader.readAsDataURL(file);
    }
}

function resetFileInput() {
    fileInput.value             = "";
    fileInfo.innerHTML          = "";
    dataFileName.value          = "";
    dataFileName.removeAttribute("value");
    previewZone.style.display   = "none";
    dropZone.style.display      = "block"; // tampilkan lagi dropZone
}