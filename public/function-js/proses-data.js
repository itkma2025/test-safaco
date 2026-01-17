
$(document).ready(function () {
    // ================================
    // SIMPAN STATE AWAL TOMBOL (GLOBAL)
    // ================================
    const originalBtnText = $('#btnSimpanText').text();
    const originalBtnIconClass = $('#btnSimpanIcon').attr('class');

    // ================================
    // HANDLE SUBMIT FORM
    // ================================
    $('#saveForm').on('submit', function (event) {
        event.preventDefault();

        // Cegah double submit
        if ($(this).data('submitted') === true) {
            return;
        }
        $(this).data('submitted', true);

        // Disable semua tombol dalam #formButtons
        $('#formButtons button').prop('disabled', true);

        // Ubah tampilan tombol → loading
        $('#btnSimpanText').text('Dalam Proses...');
        $('#btnSimpanIcon')
            .removeClass()
            .addClass('fe fe-loader animate-spin');

        // Delay 0,5 detik (opsional UX)
        setTimeout(() => {

            const formData = new FormData(document.getElementById('saveForm'));
            const route = formData.get('routes');
            const targetUrl = 'routes/' + route + '.php';

            $.ajax({
                url: targetUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhrFields: { withCredentials: true },

                success: function (response) {
                    resetSimpanButton();

                    let res;
                    try {
                        res = JSON.parse(response);
                    } catch (e) {
                        showError('Terjadi kesalahan sistem.');
                        enableForm();
                        return;
                    }

                    if (res.status === 'success') {
                        Swal.fire({
                            title: 'Success',
                            text: res.message,
                            icon: 'success',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.replace(res.redirect_url);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: res.message,
                            icon: 'error',
                            allowOutsideClick: false
                        });
                        enableForm();
                    }
                },

                error: function () {
                    resetSimpanButton();
                    showError('Terjadi kesalahan. Silakan coba lagi.');
                    enableForm();
                }
            });

        }, 500);
    });

    // ================================
    // HELPER FUNCTIONS
    // ================================
    function resetSimpanButton() {
        $('#btnSimpanText').text(originalBtnText);
        $('#btnSimpanIcon').attr('class', originalBtnIconClass);
    }

    function enableForm() {
        $('#formButtons button').prop('disabled', false);
        $('#saveForm').data('submitted', false);
    }

    function showError(message) {
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            allowOutsideClick: false
        });
    }

});
