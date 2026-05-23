<?php
/**
 * АДМИН-ПАНЕЛЬ - ОТПРАВКА СООБЩЕНИЙ ПОЛЬЗОВАТЕЛЯМ
 */

ob_start();
?>

<div class="mt-4">
    <h2 class="mb-4">
        <i class="bi bi-send"></i> Отправить сообщение пользователю
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

    <div class="row">
        <!-- Форма отправки сообщения -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-envelope"></i> Новое сообщение</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/send-message/submit" id="sendMessageForm">
                        <div class="mb-3">
                            <label for="recipient_type" class="form-label">Тип получателя <span class="text-danger">*</span></label>
                            <select class="form-select" id="recipient_type" name="recipient_type" required>
                                <option value="single">Одному пользователю</option>
                                <option value="all">Всем пользователям</option>
                            </select>
                        </div>

                        <div class="mb-3" id="single-user-field">
                            <label for="to_user_id" class="form-label">Получатель <span class="text-danger">*</span></label>
                            <select class="form-select" id="to_user_id" name="to_user_id">
                                <option value="">Выберите пользователя</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"
                                            data-email="<?= htmlspecialchars($user['email']) ?>"
                                            data-name="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                            <?= (isset($selectedUserId) && $selectedUserId == $user['id']) ? 'selected' : '' ?>>
                                        <?= Helper::escape($user['full_name'] ?? $user['email']) ?>
                                        <?php if ($user['email'] && $user['full_name']): ?>
                                            (<?= Helper::escape($user['email']) ?>)
                                        <?php endif; ?>
                                        <?php if ($user['city']): ?>
                                            - <?= Helper::escape($user['city']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Используйте поиск ниже, чтобы найти нужного пользователя</small>
                        </div>

                        <div class="alert alert-warning" id="all-users-warning" style="display: none;">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Внимание!</strong> Сообщение будет отправлено всем пользователям системы. Это действие может занять некоторое время.
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Сообщение <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="8" required
                                      placeholder="Введите текст сообщения..."></textarea>
                            <small class="form-text text-muted">Это сообщение будет отправлено как уведомление от администратора</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>admin" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Отправить сообщение
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Поиск пользователей -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-search"></i> Поиск пользователей</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>admin/send-message" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search"
                                   value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Email или имя...">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <?php if (!empty($search)): ?>
                        <a href="<?= BASE_URL ?>admin/send-message" class="btn btn-sm btn-outline-secondary mb-3">
                            <i class="bi bi-x"></i> Сбросить поиск
                        </a>
                    <?php endif; ?>

                    <div class="list-group" style="max-height: 500px; overflow-y: auto;">
                        <?php if (empty($users)): ?>
                            <p class="text-muted mb-0">Пользователи не найдены</p>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <button type="button" class="list-group-item list-group-item-action user-select-btn"
                                        data-user-id="<?= $user['id'] ?>"
                                        data-user-email="<?= htmlspecialchars($user['email']) ?>"
                                        data-user-name="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= Helper::escape($user['full_name'] ?? $user['email']) ?></h6>
                                            <small class="text-muted">
                                                <?= Helper::escape($user['email']) ?>
                                                <?php if ($user['city']): ?>
                                                    <br><?= Helper::escape($user['city']) ?>
                                                <?php endif; ?>
                                                <?php if ($user['age']): ?>
                                                    • <?= $user['age'] ?> лет
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Обработка изменения типа получателя
    document.getElementById('recipient_type')?.addEventListener('change', function() {
        const recipientType = this.value;
        const singleUserField = document.getElementById('single-user-field');
        const allUsersWarning = document.getElementById('all-users-warning');
        const toUserIdSelect = document.getElementById('to_user_id');

        if (recipientType === 'all') {
            singleUserField.style.display = 'none';
            allUsersWarning.style.display = 'block';
            if (toUserIdSelect) {
                toUserIdSelect.removeAttribute('required');
            }
        } else {
            singleUserField.style.display = 'block';
            allUsersWarning.style.display = 'none';
            if (toUserIdSelect) {
                toUserIdSelect.setAttribute('required', 'required');
            }
        }
    });

    // Обработка выбора пользователя из списка
    document.querySelectorAll('.user-select-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const recipientType = document.getElementById('recipient_type').value;
            if (recipientType === 'all') {
                // Переключаем на "одному пользователю" при выборе из списка
                document.getElementById('recipient_type').value = 'single';
                document.getElementById('recipient_type').dispatchEvent(new Event('change'));
            }

            const userId = this.getAttribute('data-user-id');
            const select = document.getElementById('to_user_id');
            if (select) {
                select.value = userId;
                // Подсвечиваем выбранный элемент
                document.querySelectorAll('.user-select-btn').forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });

    // Подтверждение отправки
    document.getElementById('sendMessageForm')?.addEventListener('submit', function(e) {
        const recipientType = document.getElementById('recipient_type').value;
        const userId = document.getElementById('to_user_id').value;
        const message = document.getElementById('message').value.trim();

        if (recipientType === 'single' && !userId) {
            e.preventDefault();
            alert('Пожалуйста, выберите получателя');
            return false;
        }

        if (!message) {
            e.preventDefault();
            alert('Пожалуйста, введите сообщение');
            return false;
        }

        let confirmMessage;
        if (recipientType === 'all') {
            confirmMessage = 'Отправить сообщение ВСЕМ пользователям? Это действие может занять некоторое время.';
        } else {
            confirmMessage = 'Отправить сообщение выбранному пользователю?';
        }

        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Отправить сообщение - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>

