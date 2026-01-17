 <?php if ($pagination['total'] > 0): ?>
<?php
    $start = ($pagination['page'] - 1) * $pagination['limit'] + 1;
    $end   = min($pagination['page'] * $pagination['limit'], $pagination['total']);
?>
<div class="text-muted medium mt-2">
    Showing <?= $start ?> to <?= $end ?> of <?= $pagination['total'] ?> entries
</div>
<?php endif; ?>

<!-- Pagination -->
<div class="mt-1">
<?php if ($pagination['pages'] > 1): ?>
    <?php
        $visible_pages = 5;
        $current_page  = $pagination['page'];
        $total_pages   = $pagination['pages'];

        $block = floor(($current_page - 1) / $visible_pages);
        $start_page = $block * $visible_pages + 1;
        $end_page   = min($start_page + $visible_pages - 1, $total_pages);

        // Get patameter
        $action = $_GET['action'] ?? null;

        $base_url = '';

        if ($action !== null && $action !== '') {
            $base_url = '?action=' . htmlspecialchars($action);
        } else {
            $base_url = '?';
        }

        if (!empty($search)) {
            $base_url .= ($base_url === '?' ? '' : '&') . 'search=' . urlencode($search);
        }

    ?>
    <nav>
        <ul class="pagination justify-content-end">
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base_url ?>&page=<?= max(1, $current_page - 1) ?>">&laquo;</a>
            </li>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $base_url ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base_url ?>&page=<?= min($total_pages, $current_page + 1) ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>