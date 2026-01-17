$('#npwp').on('input', function () {
    let value = $(this).val().replace(/\D/g, '').slice(0, 15); // Hanya angka, maksimal 15 digit

    let formatted = '';
    if (value.length > 0) formatted += value.substr(0, 2);
    if (value.length > 2) formatted += '.' + value.substr(2, 3);
    if (value.length > 5) formatted += '.' + value.substr(5, 3);
    if (value.length > 8) formatted += '.' + value.substr(8, 1);
    if (value.length > 9) formatted += '-' + value.substr(9, 3);
    if (value.length > 12) formatted += '.' + value.substr(12, 3);

    $(this).val(formatted);
});

$('#npwp_cp').on('input', function () {
    let value = $(this).val().replace(/\D/g, '').slice(0, 15); // Hanya angka, maksimal 15 digit

    let formatted = '';
    if (value.length > 0) formatted += value.substr(0, 2);
    if (value.length > 2) formatted += '.' + value.substr(2, 3);
    if (value.length > 5) formatted += '.' + value.substr(5, 3);
    if (value.length > 8) formatted += '.' + value.substr(8, 1);
    if (value.length > 9) formatted += '-' + value.substr(9, 3);
    if (value.length > 12) formatted += '.' + value.substr(12, 3);

    $(this).val(formatted);
});