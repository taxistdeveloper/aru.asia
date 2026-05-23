<?php

/**
 * ПАНЕЛЬ МЕНЕДЖЕРА - МОДЕРАЦИЯ МЕРОПРИЯТИЙ
 */

ob_start();
?>

<div class="mt-4">
    <h2 class="mb-4">Модерация мероприятий</h2>

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

    <?php if (empty($pendingEvents)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Нет мероприятий ожидающих модерации
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($pendingEvents as $event): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark" style="cursor: pointer;"
                            data-bs-toggle="collapse"
                            data-bs-target="#eventCollapse<?= $event['id'] ?>"
                            aria-expanded="false"
                            aria-controls="eventCollapse<?= $event['id'] ?>">
                            <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                <span><?= Helper::escape($event['title']) ?></span>
                                <i class="bi bi-chevron-down" id="icon<?= $event['id'] ?>"></i>
                            </h5>
                        </div>
                        <div class="collapse" id="eventCollapse<?= $event['id'] ?>">
                            <div class="card-body">
                                <?php if (!empty($event['photo'])): ?>
                                    <div class="mb-3 text-center">
                                        <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . htmlspecialchars($event['photo'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= Helper::escape($event['title']) ?>"
                                            class="img-fluid rounded"
                                            style="max-height: 200px; object-fit: cover;">
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3 text-center text-muted py-3 bg-light rounded">
                                        <i class="bi bi-image" style="font-size: 2rem;"></i>
                                        <div class="small">Фото не загружено</div>
                                    </div>
                                <?php endif; ?>
                                <p class="card-text">
                                    <strong>Описание:</strong><br>
                                    <?= Helper::escape($event['description']) ?>
                                </p>

                                <div class="mb-2">
                                    <strong><i class="bi bi-calendar"></i> Дата проведения:</strong><br>
                                    <?= date('d.m.Y H:i', strtotime($event['event_date'])) ?>
                                </div>

                                <div class="mb-2">
                                    <strong><i class="bi bi-geo-alt"></i> Место:</strong><br>
                                    <?= Helper::escape($event['location']) ?>
                                </div>

                                <div class="mb-2">
                                    <strong><i class="bi bi-currency-exchange"></i> Цена:</strong><br>
                                    <?= number_format($event['price'], 0) ?> ₸
                                </div>

                                <div class="mb-2">
                                    <strong><i class="bi bi-person"></i> Автор:</strong><br>
                                    <?= Helper::escape($event['user_email']) ?>
                                </div>

                                <div class="mb-2">
                                    <strong><i class="bi bi-clock"></i> Создано:</strong><br>
                                    <?= date('d.m.Y H:i', strtotime($event['created_at'])) ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <form method="POST" action="<?= BASE_URL ?>manager/events/approve" class="d-inline w-100 mb-2">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-check-circle"></i> Одобрить
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-danger w-100 mb-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rejectModal<?= $event['id'] ?>">
                                    <i class="bi bi-x-circle"></i> Отклонить
                                </button>
                                <button type="button" class="btn btn-danger w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal<?= $event['id'] ?>">
                                    <i class="bi bi-trash"></i> Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal для отклонения -->
                <div class="modal fade" id="rejectModal<?= $event['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Отклонить мероприятие</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>manager/events/reject">
                                <div class="modal-body">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <div class="mb-3">
                                        <label for="rejection_reason<?= $event['id'] ?>" class="form-label">
                                            Причина отклонения *
                                        </label>
                                        <textarea class="form-control"
                                            id="rejection_reason<?= $event['id'] ?>"
                                            name="rejection_reason"
                                            rows="4"
                                            required
                                            placeholder="Укажите причину отклонения и что нужно исправить..."></textarea>
                                        <small class="form-text text-muted">
                                            Укажите конкретные ошибки или что нужно исправить в мероприятии
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-danger">Отклонить</button>
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
                                    <p>Вы уверены, что хотите удалить это мероприятие?</p>
                                    <div class="alert alert-warning mb-0">
                                        <strong><?= Helper::escape($event['title']) ?></strong><br>
                                        <small>Автор: <?= Helper::escape($event['user_email']) ?></small>
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
$title = 'Модерация мероприятий - Панель менеджера';
include __DIR__ . '/../admin_layout.php';
?>