<?php

/**
 * УПРАВЛЕНИЕ ОБРАТНОЙ СВЯЗЬЮ
 * Используется админом и менеджером ($panelBase = 'admin' | 'manager')
 */

$panelBase = $panelBase ?? (Helper::isAdminLoggedIn() ? 'admin' : 'manager');
$panelTitle = $panelBase === 'admin' ? 'Админ-панель' : 'Панель менеджера';

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
.feedback-chat-box {
    background: #f4f6f8;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px;
    max-height: 280px;
    overflow-y: auto;
    margin-bottom: 10px;
}
.feedback-chat-empty {
    color: #888;
    text-align: center;
    padding: 24px 8px;
    font-size: 14px;
}
.feedback-chat-msg {
    max-width: 85%;
    margin-bottom: 8px;
    padding: 8px 10px;
    border-radius: 10px;
    font-size: 14px;
    white-space: pre-wrap;
    word-break: break-word;
}
.feedback-chat-msg.user {
    background: #fff;
    border: 1px solid #e5e5e5;
    margin-right: auto;
}
.feedback-chat-msg.staff {
    background: #d1e7dd;
    margin-left: auto;
}
.feedback-chat-meta {
    display: block;
    font-size: 11px;
    color: #6c757d;
    margin-top: 4px;
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
        <a href="<?= BASE_URL ?><?= $panelBase ?>/feedback" class="btn btn-sm <?= $currentStatus === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
            Все
        </a>
        <a href="<?= BASE_URL ?><?= $panelBase ?>/feedback?status=new" class="btn btn-sm <?= $currentStatus === 'new' ? 'btn-warning' : 'btn-outline-warning' ?>">
            Новые (<?= $stats['new'] ?>)
        </a>
        <a href="<?= BASE_URL ?><?= $panelBase ?>/feedback?status=in_progress" class="btn btn-sm <?= $currentStatus === 'in_progress' ? 'btn-info' : 'btn-outline-info' ?>">
            В работе
        </a>
        <a href="<?= BASE_URL ?><?= $panelBase ?>/feedback?status=resolved" class="btn btn-sm <?= $currentStatus === 'resolved' ? 'btn-success' : 'btn-outline-success' ?>">
            Решено
        </a>
        <a href="<?= BASE_URL ?><?= $panelBase ?>/feedback?status=closed" class="btn btn-sm <?= $currentStatus === 'closed' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
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
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td>
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
                                        <span class="badge bg-<?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($item['created_at'])) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal<?= $item['id'] ?>">
                                                <i class="bi bi-eye"></i> Просмотр
                                            </button>
                                            <form method="POST" action="<?= BASE_URL ?><?= $panelBase ?>/feedback/delete" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту заявку?');">
                                                <input type="hidden" name="feedback_id" value="<?= $item['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Удалить заявку">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Модальные окна (вне таблицы) -->
                <?php foreach ($feedback as $item): ?>
                    <?php
                    $typeLabels = [
                        'bug' => ['label' => 'Ошибка', 'class' => 'danger'],
                        'suggestion' => ['label' => 'Пожелание', 'class' => 'info'],
                        'feature' => ['label' => 'Функция', 'class' => 'primary'],
                        'other' => ['label' => 'Другое', 'class' => 'secondary']
                    ];
                    $typeInfo = $typeLabels[$item['type']] ?? $typeLabels['other'];
                    ?>
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
                                    <hr>

                                    <form method="POST" action="<?= BASE_URL ?><?= $panelBase ?>/feedback/update-status" class="<?= !empty($item['user_id']) ? 'js-feedback-staff-form' : '' ?>" data-feedback-id="<?= (int)$item['id'] ?>">
                                        <input type="hidden" name="feedback_id" value="<?= $item['id'] ?>">
                                        <div class="mb-3">
                                            <label for="status<?= $item['id'] ?>" class="form-label">Статус</label>
                                            <select name="status" id="status<?= $item['id'] ?>" class="form-select js-feedback-status" required>
                                                <option value="new" <?= $item['status'] === 'new' ? 'selected' : '' ?>>Новая</option>
                                                <option value="in_progress" <?= $item['status'] === 'in_progress' ? 'selected' : '' ?>>В работе</option>
                                                <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>Решено</option>
                                                <option value="closed" <?= $item['status'] === 'closed' ? 'selected' : '' ?>>Закрыто</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">
                                                Выберите статус — он сохранится сразу. «Закрыто» закрывает чат, пользователю придёт «Ваше обращение закрыто».
                                                <span class="js-status-saved text-success" hidden>Сохранено</span>
                                            </small>
                                        </div>
                                        <div class="mb-3">
                                            <?php
                                            $isRegisteredUser = !empty($item['user_id']);
                                            $replyEmail = $item['email'] ?? $item['user_email'] ?? '';
                                            ?>
                                            <label for="admin_reply<?= $item['id'] ?>" class="form-label">
                                                <?php if ($isRegisteredUser): ?>
                                                    <i class="bi bi-chat-dots"></i> Чат с пользователем
                                                <?php else: ?>
                                                    <i class="bi bi-envelope"></i> Ответ на email
                                                <?php endif; ?>
                                            </label>
                                            <?php if ($isRegisteredUser): ?>
                                                <div class="feedback-chat-box js-feedback-chat" data-feedback-id="<?= (int)$item['id'] ?>">
                                                    <div class="feedback-chat-empty">Загрузка переписки...</div>
                                                </div>
                                                <textarea name="admin_reply" id="admin_reply<?= $item['id'] ?>" class="form-control js-feedback-reply" rows="3" placeholder="Напишите ответ пользователю..."<?= $item['status'] === 'closed' ? ' disabled' : '' ?>></textarea>
                                                <div class="alert alert-secondary py-2 mt-2 mb-0 js-chat-closed-banner" <?= $item['status'] === 'closed' ? '' : 'hidden' ?>>
                                                    Чат закрыт. Пользователю отправлено: «Ваше обращение закрыто».
                                                </div>
                                                <small class="text-muted">
                                                    Пользователь получит сообщение в «Сообщениях» и сможет ответить. Вы отвечаете здесь, в заявке.
                                                    <?= $replyEmail !== '' ? ' Копия первого ответа также уйдёт на email: ' . Helper::escape($replyEmail) . '.' : '' ?>
                                                </small>
                                            <?php else: ?>
                                                <textarea name="admin_reply" id="admin_reply<?= $item['id'] ?>" class="form-control" rows="4" placeholder="Введите ответ. Гость получит его на email: <?= Helper::escape($replyEmail !== '' ? $replyEmail : 'не указан') ?>"></textarea>
                                                <small class="text-muted">
                                                    Это гость — личные сообщения недоступны.
                                                    <?= $replyEmail !== '' ? 'Ответ будет отправлен на email: ' . Helper::escape($replyEmail) . '.' : 'Email не указан — ответ доставить некуда.' ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                            <button type="submit" class="btn btn-primary js-feedback-send-btn"<?= $item['status'] === 'closed' ? ' disabled' : '' ?>>Отправить сообщение</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

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
                                        <form method="POST" action="<?= BASE_URL ?><?= $panelBase ?>/feedback/delete" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту заявку?');">
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

<script>
(function() {
    const chatUrl = <?= json_encode(BASE_URL . $panelBase . '/feedback/chat') ?>;
    const sendUrl = <?= json_encode(BASE_URL . $panelBase . '/feedback/chat/send') ?>;
    const pollers = {};

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function renderMessages(box, messages) {
        if (!box) return;
        if (!messages || !messages.length) {
            box.innerHTML = '<div class="feedback-chat-empty">Пока нет сообщений. Напишите ответ ниже.</div>';
            return;
        }
        box.innerHTML = messages.map(function(msg) {
            const cls = msg.is_staff ? 'staff' : 'user';
            const who = msg.is_staff ? 'Вы' : escapeHtml(msg.from_name || 'Пользователь');
            return '<div class="feedback-chat-msg ' + cls + '">' +
                escapeHtml(msg.message).replace(/\n/g, '<br>') +
                '<span class="feedback-chat-meta">' + who + ' · ' + escapeHtml(msg.time || '') + '</span>' +
                '</div>';
        }).join('');
        box.scrollTop = box.scrollHeight;
    }

    function applyChatClosed(form, closed) {
        if (!form) return;
        const reply = form.querySelector('.js-feedback-reply');
        const btn = form.querySelector('.js-feedback-send-btn');
        const banner = form.querySelector('.js-chat-closed-banner');
        if (reply) {
            reply.disabled = !!closed;
        }
        if (btn) {
            btn.disabled = !!closed;
        }
        if (banner) {
            banner.hidden = !closed;
        }
    }

    function loadChat(feedbackId, box, opts) {
        if (!feedbackId || !box) return;
        fetch(chatUrl + '?feedback_id=' + encodeURIComponent(feedbackId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.success) {
                    renderMessages(box, data.messages);
                    const modal = box.closest('.modal');
                    const form = box.closest('form');
                    const statusSelect = modal && modal.querySelector('.js-feedback-status');
                    if (statusSelect && data.status && !(opts && opts.skipStatus)) {
                        statusSelect.value = data.status;
                    }
                    applyChatClosed(form, data.closed);
                } else {
                    box.innerHTML = '<div class="feedback-chat-empty">Не удалось загрузить переписку</div>';
                }
            })
            .catch(function() {
                box.innerHTML = '<div class="feedback-chat-empty">Не удалось загрузить переписку</div>';
            });
    }

    function showStatusSaved(select) {
        const hint = select.closest('.mb-3');
        const mark = hint && hint.querySelector('.js-status-saved');
        if (!mark) return;
        mark.hidden = false;
        clearTimeout(select._savedTimer);
        select._savedTimer = setTimeout(function() {
            mark.hidden = true;
        }, 2000);
    }

    function saveStatus(select) {
        const form = select.closest('form');
        if (!form) return;
        const feedbackId = form.getAttribute('data-feedback-id') || (form.querySelector('[name="feedback_id"]') || {}).value;
        const status = select.value;
        if (!feedbackId || !status) return;

        const body = new FormData();
        body.append('feedback_id', feedbackId);
        body.append('message', '');
        body.append('status', status);

        fetch(sendUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.success) {
                    showStatusSaved(select);
                    if (data.status) {
                        select.value = data.status;
                    }
                    applyChatClosed(form, data.closed);
                    const box = form.querySelector('.js-feedback-chat');
                    if (box && data.messages) {
                        renderMessages(box, data.messages);
                    }
                } else {
                    alert((data && data.error) ? data.error : 'Не удалось сохранить статус');
                }
            })
            .catch(function() {
                alert('Не удалось сохранить статус');
            });
    }

    function sendChat(form) {
        const feedbackId = form.getAttribute('data-feedback-id');
        const reply = form.querySelector('.js-feedback-reply');
        const statusSelect = form.querySelector('.js-feedback-status');
        const box = form.querySelector('.js-feedback-chat');
        const btn = form.querySelector('button[type="submit"]');
        const text = reply ? reply.value.trim() : '';
        const status = statusSelect ? statusSelect.value : '';

        if (!text && !status) {
            return;
        }

        const body = new FormData();
        body.append('feedback_id', feedbackId);
        body.append('message', text);
        body.append('admin_reply', text);
        body.append('status', status);

        if (btn) {
            btn.disabled = true;
        }

        fetch(sendUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data || !data.success) {
                    if (btn && !(data && data.closed)) btn.disabled = false;
                    alert((data && data.error) ? data.error : 'Не удалось отправить сообщение');
                    if (data && data.closed) {
                        applyChatClosed(form, true);
                    }
                    return;
                }
                if (reply) reply.value = '';
                renderMessages(box, data.messages);
                if (statusSelect && data.status) {
                    statusSelect.value = data.status;
                }
                applyChatClosed(form, data.closed);
            })
            .catch(function() {
                if (btn) btn.disabled = false;
                alert('Не удалось отправить сообщение');
            });
    }

    document.addEventListener('submit', function(e) {
        const form = e.target.closest && e.target.closest('.js-feedback-staff-form');
        if (!form) return;
        e.preventDefault();
        sendChat(form);
    });

    document.addEventListener('change', function(e) {
        const select = e.target.closest && e.target.closest('.js-feedback-status');
        if (!select) return;
        saveStatus(select);
    });

    document.addEventListener('shown.bs.modal', function(e) {
        const modal = e.target;
        if (!modal || !modal.querySelector) return;
        const box = modal.querySelector('.js-feedback-chat');
        if (!box) return;
        const feedbackId = box.getAttribute('data-feedback-id');
        loadChat(feedbackId, box);
        if (pollers[feedbackId]) {
            clearInterval(pollers[feedbackId]);
        }
            pollers[feedbackId] = setInterval(function() {
                if (modal.classList.contains('show')) {
                    loadChat(feedbackId, box, { skipStatus: true });
                }
            }, 4000);
        });

        document.addEventListener('hidden.bs.modal', function(e) {
        const modal = e.target;
        if (!modal || !modal.querySelector) return;
        const box = modal.querySelector('.js-feedback-chat');
        if (!box) return;
        const feedbackId = box.getAttribute('data-feedback-id');
        if (pollers[feedbackId]) {
            clearInterval(pollers[feedbackId]);
            delete pollers[feedbackId];
        }
    });
})();
</script>

<?php
$content = ob_get_clean();
$title = 'Обратная связь - ' . $panelTitle;
include __DIR__ . '/../admin_layout.php';
?>
