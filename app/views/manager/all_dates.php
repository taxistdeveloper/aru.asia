<?php

/**
 * ПАНЕЛЬ МЕНЕДЖЕРА - ПРОСМОТР ВСЕХ СВИДАНИЙ
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
    <h2 class="mb-4">Все свидания</h2>

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
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white"
                            style="cursor: pointer;"
                            data-bs-toggle="collapse"
                            data-bs-target="#dateCollapse<?= $date['id'] ?>"
                            aria-expanded="false"
                            aria-controls="dateCollapse<?= $date['id'] ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2 flex-grow-1">
                                    <?php if (!empty($date['user_photo'])): ?>
                                        <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $date['user_photo'] ?>"
                                            alt="Фото пользователя"
                                            class="rounded"
                                            style="width: 50px; height: 50px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center"
                                            style="width: 50px; height: 50px; display: none;">
                                            <i class="bi bi-person" style="font-size: 24px;"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center"
                                            style="width: 50px; height: 50px;">
                                            <i class="bi bi-person" style="font-size: 24px;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-0">
                                            <?= Helper::escape($date['title']) ?>
                                        </h5>
                                        <?php if (!empty($date['category_name'])): ?>
                                            <small><?= Helper::escape($date['category_name']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-down" id="icon<?= $date['id'] ?>"></i>
                            </div>
                        </div>
                        <div class="collapse" id="dateCollapse<?= $date['id'] ?>">
                            <div class="card-body">
                                <?php if (!empty($date['description'])): ?>
                                    <p class="card-text">
                                        <strong>Описание:</strong><br>
                                        <?= Helper::escape(mb_substr($date['description'], 0, 150)) ?><?= mb_strlen($date['description']) > 150 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>

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
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <?php if (!empty($date['user_photo'])): ?>
                                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $date['user_photo'] ?>"
                                                alt="Фото пользователя"
                                                class="rounded"
                                                style="width: 40px; height: 40px; object-fit: cover;"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white"
                                                style="width: 40px; height: 40px; display: none;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white"
                                                style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span><?= !empty($date['full_name']) ? Helper::escape($date['full_name']) : Helper::escape($date['user_email']) ?></span>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <strong><i class="bi bi-clock"></i> Создано:</strong><br>
                                    <?= date('d.m.Y H:i', strtotime($date['created_at'])) ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-grid gap-2">
                                    <button type="button"
                                        class="btn btn-info btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#extendDeadlineModal<?= $date['id'] ?>">
                                        <i class="bi bi-calendar-plus"></i> Увеличить дедлайн
                                    </button>
                                    <div class="btn-group" role="group">
                                        <button type="button"
                                            class="btn btn-warning btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#remarkModal<?= $date['id'] ?>">
                                            <i class="bi bi-exclamation-triangle"></i> Замечание
                                        </button>
                                        <button type="button"
                                            class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal<?= $date['id'] ?>">
                                            <i class="bi bi-trash"></i> Удалить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal для увеличения дедлайна -->
                <div class="modal fade" id="extendDeadlineModal<?= $date['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Увеличить дедлайн свидания</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>manager/dates/extend-deadline">
                                <div class="modal-body">
                                    <input type="hidden" name="date_id" value="<?= $date['id'] ?>">
                                    <p><strong>Свидание:</strong> <?= Helper::escape($date['title']) ?></p>
                                    <p><strong>Текущая дата:</strong> <?= date('d.m.Y H:i', strtotime($date['date_time'])) ?></p>
                                    <div class="mb-3">
                                        <label for="days<?= $date['id'] ?>" class="form-label">
                                            Увеличить на (дней) *
                                        </label>
                                        <input type="number"
                                            class="form-control"
                                            id="days<?= $date['id'] ?>"
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

                <!-- Modal для добавления замечания -->
                <div class="modal fade" id="remarkModal<?= $date['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title">
                                    <i class="bi bi-exclamation-triangle"></i> Добавить замечание
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>manager/dates/set-remark">
                                <div class="modal-body">
                                    <input type="hidden" name="date_id" value="<?= $date['id'] ?>">
                                    <p><strong>Свидание:</strong> <?= Helper::escape($date['title']) ?></p>
                                    <p><strong>Автор:</strong> <?= !empty($date['full_name']) ? Helper::escape($date['full_name']) : Helper::escape($date['user_email']) ?></p>
                                    <div class="mb-3">
                                        <label for="remark<?= $date['id'] ?>" class="form-label">
                                            Замечание <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control"
                                            id="remark<?= $date['id'] ?>"
                                            name="remark"
                                            rows="6"
                                            required
                                            placeholder="Опишите, что нужно исправить в свидании..."></textarea>
                                        <small class="form-text text-muted">
                                            Замечание будет отправлено автору свидания в виде сообщения
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Отправить замечание
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal для подтверждения удаления -->
                <div class="modal fade" id="deleteModal<?= $date['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-exclamation-triangle"></i> Подтверждение удаления
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>manager/dates/delete">
                                <div class="modal-body">
                                    <input type="hidden" name="date_id" value="<?= $date['id'] ?>">
                                    <p>Вы уверены, что хотите удалить это свидание?</p>
                                    <div class="alert alert-warning">
                                        <strong>Свидание:</strong> <?= Helper::escape($date['title']) ?><br>
                                        <strong>Автор:</strong> <?= !empty($date['full_name']) ? Helper::escape($date['full_name']) : Helper::escape($date['user_email']) ?><br>
                                        <strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($date['date_time'])) ?>
                                    </div>
                                    <p class="text-danger mb-0">
                                        <i class="bi bi-exclamation-triangle"></i> Это действие нельзя отменить!
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash"></i> Удалить свидание
                                    </button>
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
                           href="<?= BASE_URL ?>manager/dates/all?page=<?= $currentPage - 1 ?>"
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
                            <a class="page-link" href="<?= BASE_URL ?>manager/dates/all?page=1">1</a>
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
                            <a class="page-link" href="<?= BASE_URL ?>manager/dates/all?page=<?= $i ?>">
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
                            <a class="page-link" href="<?= BASE_URL ?>manager/dates/all?page=<?= $totalPages ?>">
                                <?= $totalPages ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Кнопка "Следующая" -->
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" 
                           href="<?= BASE_URL ?>manager/dates/all?page=<?= $currentPage + 1 ?>"
                           <?= $currentPage >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            Следующая <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Информация о пагинации -->
            <div class="text-center text-muted mt-2 mb-4">
                <small>
                    Показано <?= count($dates) ?> из <?= $totalDates ?> свиданий 
                    (страница <?= $currentPage ?> из <?= $totalPages ?>)
                </small>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка поворота иконки стрелки при раскрытии/сворачивании
        const collapseElements = document.querySelectorAll('[id^="dateCollapse"]');

        collapseElements.forEach(function(collapseEl) {
            const collapseId = collapseEl.id;
            const dateId = collapseId.replace('dateCollapse', '');
            const iconEl = document.getElementById('icon' + dateId);

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
$title = 'Все свидания - Панель менеджера';
include __DIR__ . '/../admin_layout.php';
?>
