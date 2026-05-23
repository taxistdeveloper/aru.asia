<?php

/**
 * ПАНЕЛЬ МЕНЕДЖЕРА - ПРОСМОТР ВСЕХ МЕРОПРИЯТИЙ
 */

ob_start();
?>

<style>
    /* Стили для пагинации */
    .pagination {
        margin: 20px 0;
    }

    .pagination .page-link {
        color: #495057;
        border-color: #dee2e6;
        padding: 8px 12px;
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        cursor: auto;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .pagination .page-link:hover:not(.disabled) {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #0d6efd;
    }

    /* Мобильная версия пагинации */
    @media (max-width: 767px) {
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination .page-item {
            margin: 2px;
        }

        .pagination .page-link {
            padding: 6px 10px;
            font-size: 14px;
            min-width: 40px;
        }
    }
</style>

<div class="mt-4 mobile-page-container">
    <h2 class="mb-4">Все мероприятия</h2>

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

                                <?php if ($event['price'] > 0): ?>
                                    <div class="mb-2">
                                        <strong><i class="bi bi-currency-exchange"></i> Цена:</strong><br>
                                        <?= number_format($event['price'], 0) ?> ₸
                                    </div>
                                <?php endif; ?>

                                <div class="mb-2">
                                    <strong><i class="bi bi-person"></i> Автор:</strong><br>
                                    <?= !empty($event['full_name']) ? Helper::escape($event['full_name']) : Helper::escape($event['user_email']) ?>
                                </div>

                                <div class="mb-2">
                                    <strong><i class="bi bi-clock"></i> Создано:</strong><br>
                                    <?= date('d.m.Y H:i', strtotime($event['created_at'])) ?>
                                </div>
                            </div>
                            <div class="card-footer d-flex gap-2">
                                <button type="button"
                                    class="btn btn-info btn-sm flex-grow-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#extendDeadlineModal<?= $event['id'] ?>">
                                    <i class="bi bi-calendar-plus"></i> Увеличить дедлайн
                                </button>
                                <button type="button"
                                    class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal<?= $event['id'] ?>">
                                    <i class="bi bi-trash"></i> Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal для увеличения дедлайна -->
                <div class="modal fade" id="extendDeadlineModal<?= $event['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Увеличить дедлайн мероприятия</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>manager/events/extend-deadline">
                                <div class="modal-body">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <p><strong>Мероприятие:</strong> <?= Helper::escape($event['title']) ?></p>
                                    <p><strong>Текущая дата:</strong> <?= date('d.m.Y H:i', strtotime($event['event_date'])) ?></p>
                                    <div class="mb-3">
                                        <label for="days<?= $event['id'] ?>" class="form-label">
                                            Увеличить на (дней) *
                                        </label>
                                        <input type="number"
                                            class="form-control"
                                            id="days<?= $event['id'] ?>"
                                            name="days"
                                            min="1"
                                            max="365"
                                            value="7"
                                            required>
                                        <small class="form-text text-muted">
                                            Укажите количество дней, на которое нужно увеличить дедлайн
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-primary">Увеличить дедлайн</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal для удаления -->
                <div class="modal fade" id="deleteModal<?= $event['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-exclamation-triangle"></i> Удалить мероприятие
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>manager/events/delete">
                                <div class="modal-body">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <input type="hidden" name="from" value="all">
                                    <p>Вы уверены, что хотите удалить это мероприятие?</p>
                                    <div class="alert alert-warning mb-0">
                                        <strong><?= Helper::escape($event['title']) ?></strong><br>
                                        <small>Автор: <?= !empty($event['full_name']) ? Helper::escape($event['full_name']) : Helper::escape($event['user_email']) ?></small>
                                    </div>
                                    <p class="text-danger small mt-2 mb-0">
                                        <i class="bi bi-exclamation-triangle"></i> Действие нельзя отменить.
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Удалить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Навигация по страницам" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Кнопка "Предыдущая" -->
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="<?= BASE_URL ?>manager/events/all?page=<?= $currentPage - 1 ?>"
                            <?= $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            <i class="bi bi-chevron-left"></i> Предыдущая
                        </a>
                    </li>

                    <!-- Номера страниц -->
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    // Показываем первую страницу, если не в начале
                    if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= BASE_URL ?>manager/events/all?page=1">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Основные страницы -->
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>manager/events/all?page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Показываем последнюю страницу, если не в конце -->
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= BASE_URL ?>manager/events/all?page=<?= $totalPages ?>">
                                <?= $totalPages ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Кнопка "Следующая" -->
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="<?= BASE_URL ?>manager/events/all?page=<?= $currentPage + 1 ?>"
                            <?= $currentPage >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            Следующая <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Информация о пагинации -->
            <div class="text-center text-muted mt-2 mb-4">
                <small>
                    Показано <?= count($events) ?> из <?= $totalEvents ?> мероприятий
                    (страница <?= $currentPage ?> из <?= $totalPages ?>)
                </small>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка поворота иконки стрелки при раскрытии/сворачивании
        const collapseElements = document.querySelectorAll('[id^="eventCollapse"]');

        collapseElements.forEach(function(collapseEl) {
            const collapseId = collapseEl.id;
            const eventId = collapseId.replace('eventCollapse', '');
            const iconEl = document.getElementById('icon' + eventId);

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
$title = 'Все мероприятия - Панель менеджера';
include __DIR__ . '/../admin_layout.php';
?>