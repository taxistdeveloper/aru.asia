<?php

/**
 * АДМИН-ПАНЕЛЬ - УПРАВЛЕНИЕ ОБРАТНОЙ СВЯЗЬЮ
 */

ob_start();
?>

<style>
@media (max-width: 767.98px) {
    .admin-feedback-table-wrapper {
        display: none;
    }
}
@media (min-width: 768px) {
    .admin-feedback-mobile-list {
        display: none;
    }
}
</style>

<div class="mt-4">
    <h2 class="mb-4">
        <i class="bi bi-chat-dots"></i> Обратная связь от пользователей
    </h2>

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

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Всего</h5>
                    <h3 class="text-primary"><?= $stats['total'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Новые</h5>
                    <h3 class="text-warning"><?= $stats['new'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">В работе</h5>
                    <h3 class="text-info"><?= $stats['in_progress'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Решено</h5>
                    <h3 class="text-success"><?= $stats['resolved'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>admin/feedback" class="btn btn-sm <?= $currentStatus === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
            Все
        </a>
        <a href="<?= BASE_URL ?>admin/feedback?status=new" class="btn btn-sm <?= $currentStatus === 'new' ? 'btn-warning' : 'btn-outline-warning' ?>">
            Новые (<?= $stats['new'] ?>)
        </a>
        <a href="<?= BASE_URL ?>admin/feedback?status=in_progress" class="btn btn-sm <?= $currentStatus === 'in_progress' ? 'btn-info' : 'btn-outline-info' ?>">
            В работе
        </a>
        <a href="<?= BASE_URL ?>admin/feedback?status=resolved" class="btn btn-sm <?= $currentStatus === 'resolved' ? 'btn-success' : 'btn-outline-success' ?>">
            Решено
        </a>
        <a href="<?= BASE_URL ?>admin/feedback?status=closed" class="btn btn-sm <?= $currentStatus === 'closed' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
            Закрыто
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($feedback)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">Заявок не найдено</p>
                </div>
            <?php else: ?>
                <!-- Десктоп: таблица -->
                <div class="table-responsive admin-feedback-table-wrapper">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Тип</th>
                                <th>Тема</th>
                                <th>Пользователь</th>
                                <th>Email</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedback as $item): ?>
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'bug' => ['label' => 'Ошибка', 'class' => 'danger'],
                                            'suggestion' => ['label' => 'Пожелание', 'class' => 'info'],
                                            'feature' => ['label' => 'Функция', 'class' => 'primary'],
                                            'other' => ['label' => 'Другое', 'class' => 'secondary']
                                        ];
                                        $typeInfo = $typeLabels[$item['type']] ?? $typeLabels['other'];
                                        ?>
                                        <span class="badge bg-<?= $typeInfo['class'] ?>"><?= $typeInfo['label'] ?></span>
                                    </td>
                                    <td>
                                        <strong><?= Helper::escape($item['subject']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= Helper::escape(mb_substr($item['message'], 0, 100)) ?><?= mb_strlen($item['message']) > 100 ? '...' : '' ?></small>
                                    </td>
                                    <td>
                                        <?php if ($item['user_id']): ?>
                                            <?= Helper::escape($item['user_name'] ?? 'Пользователь #' . $item['user_id']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Гость</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= Helper::escape($item['email'] ?? $item['user_email'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $statusLabels = [
                                            'new' => ['label' => 'Новая', 'class' => 'warning'],
                                            'in_progress' => ['label' => 'В работе', 'class' => 'info'],
                                            'resolved' => ['label' => 'Решено', 'class' => 'success'],
                                            'closed' => ['label' => 'Закрыто', 'class' => 'secondary']
                                        ];
                                        $statusInfo = $statusLabels[$item['status']] ?? $statusLabels['new'];
                                        ?>
                                        <span class="badge bg-<?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($item['created_at'])) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal<?= $item['id'] ?>">
                                                <i class="bi bi-eye"></i> Просмотр
                                            </button>
                                            <form method="POST" action="<?= BASE_URL ?>admin/feedback/delete" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту заявку?');">
                                                <input type="hidden" name="feedback_id" value="<?= $item['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Удалить заявку">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Модальное окно для просмотра заявки -->
                                <div class="modal fade" id="feedbackModal<?= $item['id'] ?>" tabindex="-1" aria-labelledby="feedbackModalLabel<?= $item['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="feedbackModalLabel<?= $item['id'] ?>">
                                                    Заявка #<?= $item['id'] ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Тип:</strong>
                                                    <span class="badge bg-<?= $typeInfo['class'] ?>"><?= $typeInfo['label'] ?></span>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Тема:</strong>
                                                    <p><?= Helper::escape($item['subject']) ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Сообщение:</strong>
                                                    <p class="border p-3 rounded"><?= nl2br(Helper::escape($item['message'])) ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Пользователь:</strong>
                                                    <p>
                                                        <?php if ($item['user_id']): ?>
                                                            <?= Helper::escape($item['user_name'] ?? 'Пользователь #' . $item['user_id']) ?>
                                                            (ID: <?= $item['user_id'] ?>)
                                                        <?php else: ?>
                                                            <span class="text-muted">Гость</span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Email:</strong>
                                                    <p><?= Helper::escape($item['email'] ?? $item['user_email'] ?? 'Не указан') ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Дата создания:</strong>
                                                    <p><?= date('d.m.Y H:i:s', strtotime($item['created_at'])) ?></p>
                                                </div>
                                                <?php if ($item['admin_notes']): ?>
                                                    <div class="mb-3">
                                                        <strong>Заметки администратора:</strong>
                                                        <p class="border p-3 rounded bg-light"><?= nl2br(Helper::escape($item['admin_notes'])) ?></p>
                                                    </div>
                                                <?php endif; ?>

                                                <hr>

                                                <form method="POST" action="<?= BASE_URL ?>admin/feedback/update-status">
                                                    <input type="hidden" name="feedback_id" value="<?= $item['id'] ?>">
                                                    <div class="mb-3">
                                                        <label for="status<?= $item['id'] ?>" class="form-label">Статус</label>
                                                        <select name="status" id="status<?= $item['id'] ?>" class="form-select" required>
                                                            <option value="new" <?= $item['status'] === 'new' ? 'selected' : '' ?>>Новая</option>
                                                            <option value="in_progress" <?= $item['status'] === 'in_progress' ? 'selected' : '' ?>>В работе</option>
                                                            <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>Решено</option>
                                                            <option value="closed" <?= $item['status'] === 'closed' ? 'selected' : '' ?>>Закрыто</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="admin_notes<?= $item['id'] ?>" class="form-label">Заметки администратора</label>
                                                        <textarea name="admin_notes" id="admin_notes<?= $item['id'] ?>" class="form-control" rows="3" placeholder="Введите заметки..."><?= Helper::escape($item['admin_notes'] ?? '') ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="admin_reply<?= $item['id'] ?>" class="form-label">
                                                            <i class="bi bi-envelope"></i> Ответ пользователю (отправится на email)
                                                        </label>
                                                        <textarea name="admin_reply" id="admin_reply<?= $item['id'] ?>" class="form-control" rows="4" placeholder="Введите ответ пользователю. Если заполнено, ответ будет отправлен на email: <?= Helper::escape($item['email'] ?? $item['user_email'] ?? 'Не указан') ?>"></textarea>
                                                        <small class="text-muted">Если заполнено, ответ будет отправлен на email пользователя</small>
                                                    </div>
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Мобильная версия в виде карточек -->
                <div class="admin-feedback-mobile-list">
                    <?php foreach ($feedback as $item): ?>
                        <?php
                        $typeLabels = [
                            'bug' => ['label' => 'Ошибка', 'class' => 'danger'],
                            'suggestion' => ['label' => 'Пожелание', 'class' => 'info'],
                            'feature' => ['label' => 'Функция', 'class' => 'primary'],
                            'other' => ['label' => 'Другое', 'class' => 'secondary']
                        ];
                        $typeInfo = $typeLabels[$item['type']] ?? $typeLabels['other'];

                        $statusLabels = [
                            'new' => ['label' => 'Новая', 'class' => 'warning'],
                            'in_progress' => ['label' => 'В работе', 'class' => 'info'],
                            'resolved' => ['label' => 'Решено', 'class' => 'success'],
                            'closed' => ['label' => 'Закрыто', 'class' => 'secondary']
                        ];
                        $statusInfo = $statusLabels[$item['status']] ?? $statusLabels['new'];
                        ?>
                        <div class="card mb-2">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge bg-<?= $typeInfo['class'] ?> me-1"><?= $typeInfo['label'] ?></span>
                                            <span class="badge bg-<?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
                                        </div>
                                        <strong>#<?= $item['id'] ?> • <?= Helper::escape($item['subject']) ?></strong>
                                        <div class="small text-muted mt-1">
                                            <?= Helper::escape(mb_substr($item['message'], 0, 120)) ?><?= mb_strlen($item['message']) > 120 ? '...' : '' ?>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <?php if ($item['user_id']): ?>
                                                <?= Helper::escape($item['user_name'] ?? 'Пользователь #' . $item['user_id']) ?>
                                            <?php else: ?>
                                                Гость
                                            <?php endif; ?>
                                            • <?= Helper::escape($item['email'] ?? $item['user_email'] ?? '-') ?>
                                            <br>
                                            <?= date('d.m.Y H:i', strtotime($item['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="ms-2 d-flex flex-column gap-1">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#feedbackModal<?= $item['id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>admin/feedback/delete" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту заявку?');">
                                            <input type="hidden" name="feedback_id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Обратная связь - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>





