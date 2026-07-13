<?php
/**
 * АДМИН-ПАНЕЛЬ - ВСЕ СВИДАНИЯ (с возможностью удаления)
 */

ob_start();
?>

<style>
    .pagination { margin: 20px 0; }
    .pagination .page-link { color: #495057; border-color: #dee2e6; padding: 8px 12px; }
    .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .pagination .page-item.disabled .page-link { color: #6c757d; pointer-events: none; }
    .pagination .page-link:hover:not(.disabled) { background-color: #e9ecef; color: #0d6efd; }
    @media (max-width: 767px) {
        .pagination { flex-wrap: wrap; justify-content: center; }
        .pagination .page-item { margin: 2px; }
        .pagination .page-link { padding: 6px 10px; font-size: 14px; min-width: 40px; }
    }
</style>

<div class="mt-4 mobile-page-container">
    <h2 class="mb-4">Все свидания</h2>
    <p class="text-muted mb-4">Список всех активных и прошедших свиданий. Любое можно удалить.</p>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (empty($dates)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Нет свиданий
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($dates as $date): ?>
                <?php
                $isExpired = strtotime($date['date_time']) < time();
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header <?= $isExpired ? 'bg-secondary' : 'bg-primary' ?> text-white"
                             style="cursor: pointer;"
                             data-bs-toggle="collapse"
                             data-bs-target="#dateCollapse<?= $date['id'] ?>"
                             aria-expanded="false"
                             aria-controls="dateCollapse<?= $date['id'] ?>">
                            <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                <span>
                                    <?= Helper::escape($date['title']) ?><br>
                                    <small>
                                        <?php if (!empty($date['category_name'])): ?>
                                            <?= Helper::escape($date['category_name']) ?> ·
                                        <?php endif; ?>
                                        <?= $isExpired ? 'Прошло' : 'Активно' ?>
                                    </small>
                                </span>
                                <i class="bi bi-chevron-down" id="icon<?= $date['id'] ?>"></i>
                            </h5>
                        </div>
                        <div class="collapse" id="dateCollapse<?= $date['id'] ?>">
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong><i class="bi bi-calendar"></i> Дата и время:</strong><br>
                                    <?= date('d.m.Y H:i', strtotime($date['date_time'])) ?>
                                </div>
                                <div class="mb-2">
                                    <strong><i class="bi bi-geo-alt"></i> Место:</strong><br>
                                    <?= Helper::escape($date['location']) ?>
                                </div>
                                <div class="mb-2">
                                    <strong><i class="bi bi-person"></i> Автор:</strong><br>
                                    <?= !empty($date['full_name']) ? Helper::escape($date['full_name']) : Helper::escape($date['user_email'] ?? '') ?>
                                </div>
                                <div class="mb-2">
                                    <strong><i class="bi bi-clock"></i> Создано:</strong><br>
                                    <?= date('d.m.Y H:i', strtotime($date['created_at'])) ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <form method="POST" action="<?= BASE_URL ?>admin/dates/delete" class="d-inline w-100" onsubmit="return confirm('Удалить это свидание? Действие нельзя отменить.');">
                                    <input type="hidden" name="date_id" value="<?= (int)$date['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        <i class="bi bi-trash"></i> Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Навигация по страницам" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>admin/dates/all?page=<?= $currentPage - 1 ?>" <?= $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            <i class="bi bi-chevron-left"></i> Предыдущая
                        </a>
                    </li>
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    if ($startPage > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>admin/dates/all?page=1">1</a></li>
                        <?php if ($startPage > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>admin/dates/all?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>admin/dates/all?page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
                    <?php endif; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>admin/dates/all?page=<?= $currentPage + 1 ?>" <?= $currentPage >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            Следующая <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted mt-2 mb-4">
                <small>Показано <?= count($dates) ?> из <?= $totalDates ?> (страница <?= $currentPage ?> из <?= $totalPages ?>)</small>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="dateCollapse"]').forEach(function(collapseEl) {
        var dateId = collapseEl.id.replace('dateCollapse', '');
        var iconEl = document.getElementById('icon' + dateId);
        if (iconEl) {
            collapseEl.addEventListener('show.bs.collapse', function() {
                iconEl.classList.remove('bi-chevron-down');
                iconEl.classList.add('bi-chevron-up');
            });
            collapseEl.addEventListener('hide.bs.collapse', function() {
                iconEl.classList.remove('bi-chevron-up');
                iconEl.classList.add('bi-chevron-down');
            });
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$title = 'Все свидания - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>
