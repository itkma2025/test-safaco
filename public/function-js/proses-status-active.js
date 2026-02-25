$(document).ready(function () {
    $(document).on('click', '.updateStatus', function() {
        var id          = $(this).data("id");
        var status      = $(this).data("status");
        var action      = $(this).data("action");
        var routename   = $(this).data("routename");

        console.log(id);
        console.log(status);
        console.log(routename);

        $.ajax({
            url: "routes/" + routename + ".php", 
            type: "POST",
            data: 
                { 
                    id: id,
                    status: status,
                    action: action
                },
            success: function (response) {
                console.log("Server response:", response);
                // Jika response JSON string, parse dulu
                let res = typeof response === 'string' ? JSON.parse(response) : response;

                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: true,
                        allowOutsideClick: false
                    }).then(() => {
                        location.reload(); // reload halaman
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: res.message || 'Terjadi kesalahan.',
                    }).then(() => {
                        location.reload(); // reload halaman
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal update status.',
                }).then(() => {
                    location.reload(); // reload halaman
                });
            }
        });
    });
});