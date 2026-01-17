<!-- Deteksi dulu apakah browser mendukung passive -->
<script>
    (function() {
        const nonPassiveEvents = ['touchstart', 'touchmove', 'wheel', 'mousewheel'];

        const supportsPassive = (() => {
            let supported = false;
            try {
                const opts = Object.defineProperty({}, 'passive', {
                    get() { supported = true; }
                });
                window.addEventListener('testPassive', null, opts);
            } catch (e) {}
            return supported;
        })();

        if (!supportsPassive) return;

        const origAdd = EventTarget.prototype.addEventListener;
        EventTarget.prototype.addEventListener = function(type, listener, options) {
            if (nonPassiveEvents.includes(type)) {
                // Paksa jadi non-passive supaya preventDefault() tetap bisa
                options = { passive: false };
            }
            return origAdd.call(this, type, listener, options);
        };
    })();
</script>

<!-- Tambahan script dari view untuk push script -->
<?php render_scripts() ?>

<!-- jQuery -->
<script src="<?= asset('js/jquery-3.7.1.min.js') ?>"></script>

<!-- Bootstrap Core JS -->
<script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>

<!-- Feather Icon JS -->
<script src="<?= asset('js/feather.min.js') ?>"></script>

<!-- Slimscroll JS -->
<script src="<?= asset('js/jquery.slimscroll.min.js') ?>"></script>

<!-- Chart JS -->
<script src="<?= asset('plugins/apexchart/apexcharts.min.js') ?>"></script>
<script src="<?= asset('plugins/apexchart/chart-data.js') ?>"></script>

<!-- Chart JS -->
<script src="<?= asset('plugins/chartjs/chart.min.js') ?>"></script>
<script src="<?= asset('plugins/chartjs/chart-data.js') ?>"></script>

<!-- Datetimepicker JS -->
<script src="<?= asset('js/moment.js') ?>"></script>
<script src="<?= asset('js/bootstrap-datetimepicker.min.js') ?>"></script>

<!-- Daterangepikcer JS -->
<script src="<?= asset('plugins/daterangepicker/daterangepicker.js') ?>"></script>

<!-- Summernote JS -->
<script src="<?= asset('plugins/summernote/summernote-lite.min.js') ?>"></script>

<!-- Bootstrap Tagsinput JS -->
<script src="<?= asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') ?>"></script>

<!-- Select2 JS -->
<script src="<?= asset('plugins/select2/js/select2.min.js') ?>"></script>

<!-- Color Picker JS -->
<script src="<?= asset('plugins/@simonwep/pickr/pickr.es5.min.js') ?>"></script>

<!-- Custom JS -->
<script src="<?= asset('js/todo.js') ?>"></script>
<script src="<?= asset('js/theme-colorpicker.js') ?>"></script>     
<script src="<?= asset('js/script.js') ?>"></script>


