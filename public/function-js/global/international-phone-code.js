var input = document.querySelector("#no_hp");
var isoCode = document.querySelector("#kode_negara").value;
const iti = window.intlTelInput(input, {
    initialCountry: isoCode.toLowerCase(), // intl-tel-input pakai lowercase agar aman gunakan function toLowerCase()
    separateDialCode: true,
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/js/utils.js",
});

// Cegah angka 0 di awal
input.addEventListener("keydown", function(e) {
    const value = input.value;
    if (value.length === 0 && e.key === "0") {
        e.preventDefault();
    }
});

// Cegah paste angka dengan 0 di awal
input.addEventListener("paste", function(e) {
    const paste = (e.clipboardData || window.clipboardData).getData("text");
    if (paste.startsWith("0")) {
        e.preventDefault();
    }
});

// Saat form disubmit
document.querySelector("#saveForm").addEventListener("submit", function(e) {
    const dialCode = iti.getSelectedCountryData().dialCode; // 62
    const isoCode = iti.getSelectedCountryData().iso2.toUpperCase(); // ID
    const nomorNasional = input.value.replace(/\D/g, ''); // 812xxxx

    // Set ke hidden input
    document.querySelector("#dial_code").value = dialCode;
    document.querySelector("#kode_negara").value = isoCode;
    document.querySelector("#nomor_nasional").value = nomorNasional;
});