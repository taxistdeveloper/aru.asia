<?php
/**
 * АДМИН-ПАНЕЛЬ - ВСЕ МЕРОПРИЯТИЯ (с возможностью удаления)
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
    <h2 class="mb-4">Все мероприятия</h2>
    <p class="text-muted mb-4">Здесь отображаются все мероприятия (на модерации, одобренные, отклонённые). Любое можно удалить.</p>

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

    <?php if (empty($events)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Нет мероприятий
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header <?= $event['status'] === 'approved' ? 'bg-success' : ($event['status'] === 'pending' ? 'bg-warning' : 'bg-danger') ?> text-white"
                             style="cursor: pointer;"
                             data-bs-toggle="collapse"
                             data-bs-target="#eventCollapse<?= $event['id'] ?>"
                             aria-expanded="false"
                             aria-controls="eventCollapse<?= $event['id'] ?>">
                            <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                <span>
                                    <?= Helper::escape($event['title']) ?><br>
                                    <small>
                                        <?php
                                        $statusLabels = [
                                            'approved' => 'Одобрено',
                                            'pending' => 'Ожидает модерации',
                                            'rejected' => 'Отклонено'
                                        ];
                                        echo $statusLabels[$event['status']] ?? $event['status'];
                                        ?>
                                    </small>
                                </span>
                                <i class="bi bi-chevron-down" id="icon<?= $event['id'] ?>"></i>
                            </h5>
                        </div>
                        <div class="collapse" id="eventCollapse<?= $event['id'] ?>">
                            <div class="card-body">
                                <?php if (!empty($event['photo'])): ?>
                                    <img src="<?= BASE_URL ?>uploads/photos/<?= rawurlencode(basename($event['photo'])) ?>"
                                         class="img-fluid rounded mb-3 w-100"
                                         style="height: 180px; object-fit: cover;"
                                         alt="<?= Helper::escape($event['title']) ?>">
                                <?php endif; ?>

                                <form method="POST"
                                      action="<?= BASE_URL ?>admin/events/update-photo"
                                      enctype="multipart/form-data"
                                      class="border rounded p-2 mb-3">
                                    <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                                    <input type="hidden" name="page" value="<?= (int)$currentPage ?>">
                                    <label for="adminEventPhoto<?= (int)$event['id'] ?>" class="form-label small fw-bold">
                                        Фото мероприятия
                                    </label>
                                    <input type="file"
                                           class="form-control form-control-sm mb-2"
                                           id="adminEventPhoto<?= (int)$event['id'] ?>"
                                           name="photo"
                                           accept="image/jpeg,image/png,image/gif,image/webp"
                                           required>
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-image"></i> Установить фото
                                    </button>
                                </form>

                                <p class="card-text">
                                    <strong>Описание:</strong><br>
                                    <?= Helper::escape(mb_substr($event['description'], 0, 150)) ?><?= mb_strlen($event['description']) > 150 ? '...' : '' ?>
                                </p>
                                <div class="mb-2">
                                    <strong><i class="bi bi-calendar"></i> Дата проведения:</strong><br>
                                    <?= date('d.m.Y H:i', strtotime($event['event_date'])) ?>
                                </div>
                                <div class="mb-2">
                                    <strong><i class="bi bi-geo-alt"></i> Место:</strong><br>
                                    <?= Helper::escape($event['location']) ?>
                                </div>
                                <?php if (!empty($event['price'])): ?>
                                <div class="mb-2">
                                    <strong><i class="bi bi-currency-exchange"></i> Цена:</strong><br>
                                    <?= number_format((float)$event['price'], 0) ?> ₸
                                </div>
                                <?php endif; ?>
                                <div class="mb-2">
                                    <strong><i class="bi bi-person"></i> Автор:</strong><br>
                                    <?= !empty($event['full_name']) ? Helper::escape($event['full_name']) : Helper::escape($event['user_email'] ?? '') ?>
                                </div>
                                <div class="mb-2">
                                    <strong><i class="bi bi-clock"></i> Создано:</strong><br>
                                    <?= date('d.m.Y H:i', strtotime($event['created_at'])) ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <form method="POST" action="<?= BASE_URL ?>admin/events/delete" class="d-inline w-100" onsubmit="return confirm('Удалить это мероприятие? Действие нельзя отменить.');">
                                    <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
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
                        <a class="page-link" href="<?= BASE_URL ?>admin/events/all?page=<?= $currentPage - 1 ?>" <?= $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            <i class="bi bi-chevron-left"></i> Предыдущая
                        </a>
                    </li>
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    if ($startPage > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>admin/events/all?page=1">1</a></li>
                        <?php if ($startPage > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>admin/events/all?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>admin/events/all?page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
                    <?php endif; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>admin/events/all?page=<?= $currentPage + 1 ?>" <?= $currentPage >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            Следующая <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted mt-2 mb-4">
                <small>Показано <?= count($events) ?> из <?= $totalEvents ?> (страница <?= $currentPage ?> из <?= $totalPages ?>)</small>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="eventCollapse"]').forEach(function(collapseEl) {
        var eventId = collapseEl.id.replace('eventCollapse', '');
        var iconEl = document.getElementById('icon' + eventId);
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
$title = 'Все мероприятия - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>
