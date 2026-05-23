<?php

/**
 * ЧАТ ДЛЯ СВИДАНИЯ - СПИСОК ДИАЛОГОВ
 */

ob_start();
?>

<?php if ($selectedUserId): ?>
    <style>
        /* Стили для страницы чата в стиле Telegram */
        body.chat-page {
            padding: 0 !important;
            margin: 0 !important;
            background: #e5e5e5;
            overflow: hidden;
        }

        body.chat-page .mobile-bottom-nav {
            display: none !important;
        }

        body.chat-page .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        body.chat-page .mobile-page-container {
            margin: 0 !important;
            padding: 0 !important;
            padding-bottom: env(safe-area-inset-bottom, 0px);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            width: 100%;
        }

        /* Контейнер сообщений */
        #messages-container {
            background: #e5e5e5;
            padding: 12px 16px;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }

        #messages-container::-webkit-scrollbar {
            width: 5px;
        }

        #messages-container::-webkit-scrollbar-track {
            background: transparent;
        }

        #messages-container::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        #messages-container::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Сообщения в стиле Telegram */
        .message-item {
            margin-bottom: 10px;
            display: flex;
        }

        .message-item.text-end {
            justify-content: flex-end;
        }

        .message-item.text-start {
            justify-content: flex-start;
        }

        .message-bubble {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 12px;
            max-width: 70%;
            word-wrap: break-word;
            position: relative;
        }

        .message-bubble.own {
            background: #3390ec;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-bubble.other {
            background: white;
            color: #000;
            border-bottom-left-radius: 4px;
        }

        .message-bubble .message-sender {
            font-size: 13px;
            font-weight: 600;
            color: #3390ec;
            margin-bottom: 4px;
        }

        .message-bubble.own .message-sender {
            color: rgba(255, 255, 255, 0.9);
        }

        .message-bubble .message-content {
            font-size: 15px;
            line-height: 1.4;
            margin: 0;
            word-break: break-word;
        }

        .message-bubble .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 4px;
            text-align: right;
        }

        .message-bubble.own .message-time {
            opacity: 0.9;
        }

        /* Заголовок страницы */
        .chat-page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
            flex-shrink: 0;
            position: relative;
            z-index: 100;
            overflow: visible;
            margin: 0 !important;
            width: 100%;
            border-radius: 0 !important;
        }

        .chat-page-header .d-flex {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            overflow: visible;
        }

        .chat-page-header h2,
        .chat-page-header h4 {
            margin: 0;
            font-size: 18px;
            color: white;
        }

        .chat-page-header .btn {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 101;
            position: relative;
            flex-shrink: 0;
        }

        /* Фото слева, ФИО сразу справа от картинки */
        #chat-view .chat-page-header .chat-header-user {
            gap: 0;
        }
        #chat-view .chat-page-header .chat-header-fio {
            min-width: 0;
            flex: 1;
        }

        /* Карточка чата */
        .chat-card {
            border: none;
            box-shadow: none;
            flex: 1;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 60px);
            min-height: 0;
            overflow: visible;
            border-radius: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
        }

        .chat-card .card-body {
            padding: 0;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        /* Форма ввода в стиле Telegram */
        .chat-card .card-footer {
            background: white;
            border-top: 1px solid #e0e0e0;
            padding: 8px 12px;
            padding-bottom: calc(8px + env(safe-area-inset-bottom, 0px));
            flex-shrink: 0;
            position: relative;
            z-index: 100;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .chat-input-form .form-control {
            border-radius: 22px;
            border: 1px solid #e0e0e0;
            padding: 8px 16px;
            font-size: 15px;
            background: #f0f0f0;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex: 1;
            min-width: 0;
        }

        .chat-input-form textarea.form-control {
            white-space: pre-wrap !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            overflow-x: hidden !important;
            resize: none !important;
        }

        .chat-input-form .form-control:focus {
            background: white;
            border-color: #3390ec;
            box-shadow: none;
        }

        .chat-input-form .btn-primary {
            border-radius: 22px;
            padding: 8px 20px;
            background: #3390ec;
            border: none;
            margin-left: 8px;
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex-shrink: 0;
        }

        .chat-input-form .btn-primary:hover {
            background: #2a7fd4;
        }

        /* МОБИЛЬНЫЕ УСТРОЙСТВА (до 767px) */
        @media (max-width: 767px) {
            body.chat-page {
                background: #e5e5e5;
                height: 100vh;
                height: 100dvh;
                overflow: hidden;
            }

            body.chat-page .mobile-top-nav {
                display: none !important;
            }

            .mobile-page-container {
                height: 100vh;
                height: 100dvh;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                padding-bottom: env(safe-area-inset-bottom, 0px);
                box-sizing: border-box;
            }

            .chat-page-header {
                padding: clamp(6px, 2vw, 10px) clamp(8px, 2.5vw, 12px);
                position: sticky;
                top: 0;
                z-index: 100;
                flex-shrink: 0;
                overflow: visible;
            }

            .chat-page-header .chat-header-fio {
                font-size: clamp(14px, 4vw, 18px);
            }

            .chat-card {
                flex: 1;
                min-height: 0;
                display: flex;
                flex-direction: column;
                height: 0;
                overflow: visible;
            }

            #messages-container {
                flex: 1;
                min-height: 0;
                padding: clamp(6px, 2vw, 10px) clamp(8px, 2.5vw, 12px);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .chat-card .card-footer {
                padding: clamp(4px, 1.5vw, 8px) clamp(6px, 2vw, 10px);
                padding-bottom: calc(clamp(4px, 1.5vw, 8px) + env(safe-area-inset-bottom, 0px));
                flex-shrink: 0;
                background: white;
                z-index: 100;
                position: relative;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                width: 100%;
                box-sizing: border-box;
                margin-bottom: 0;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
            }

            .chat-input-form .form-control {
                font-size: 16px;
                padding: clamp(6px, 1.8vw, 10px) clamp(10px, 3vw, 14px);
                min-height: clamp(36px, 10vw, 44px);
            }

            .chat-input-form .btn-primary {
                padding: clamp(6px, 1.8vw, 10px) clamp(12px, 3.5vw, 18px);
                margin-left: clamp(4px, 1.5vw, 8px);
                min-width: clamp(44px, 12vw, 56px);
                min-height: clamp(36px, 10vw, 44px);
            }

            .message-bubble {
                max-width: clamp(75%, 85%, 90%);
                padding: clamp(6px, 2vw, 10px) clamp(8px, 2.5vw, 12px);
            }
        }
    </style>
<?php endif; ?>

<style>
    /* Стили для списка диалогов */
    .list-group-item {
        position: relative;
    }

    .list-group-item a {
        flex: 1;
        min-width: 0;
        text-decoration: none;
        color: inherit;
    }

    .conversations-list {
        max-height: 600px;
        overflow-y: auto;
    }
</style>

<div class="mobile-page-container">
    <!-- Список диалогов -->
    <div id="conversations-view" class="<?= $selectedUserId ? 'd-none' : '' ?>">
        <div class="chat-page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div style="flex: 1; min-width: 0;">
                    <h2 class="d-none d-md-block mb-0">Чат: <?= Helper::escape($date['title']) ?></h2>
                    <h4 class="d-block d-md-none mb-0"><?= Helper::escape($date['title']) ?></h4>
                </div>

            </div>
        </div>

        <div class="container-fluid mt-3">
            <div class="row">
                <div class="col-lg-8 col-xl-6 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Диалоги с участниками</h5>
                        </div>
                        <div class="card-body conversations-list">
                            <?php if (empty($conversations)): ?>
                                <?php
                                // Показываем кнопку "Написать владельцу", если текущий пользователь не является владельцем
                                $canWriteToOwner = false;
                                if ($currentUserId != $date['user_id'] && $dateOwner) {
                                    $isOwnerBlockedByMe = $isOwnerBlockedByMe ?? false;
                                    $isOwnerBlockedByOther = $isOwnerBlockedByOther ?? false;
                                    $canWriteToOwner = !$isOwnerBlockedByMe && !$isOwnerBlockedByOther;
                                }
                                ?>
                                <?php if ($canWriteToOwner): ?>
                                    <div class="text-center mb-3">
                                        <p class="text-muted mb-3">Нет диалогов. Начните общение с владельцем свидания!</p>
                                        <a href="<?= BASE_URL ?>messages/date?date_id=<?= $date['id'] ?>&user_id=<?= $date['user_id'] ?>"
                                            class="btn btn-primary">
                                            <i class="bi bi-chat-dots"></i> Написать владельцу
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Нет диалогов. Начните общение с участниками свидания!</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($conversations as $conv): ?>
                                        <a href="<?= BASE_URL ?>messages/date?date_id=<?= $date['id'] ?>&user_id=<?= $conv['other_user_id'] ?>"
                                            class="list-group-item list-group-item-action d-flex align-items-center">
                                            <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                                                <?php if (!empty($conv['photo'])): ?>
                                                    <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $conv['photo'] ?>"
                                                        class="rounded-circle"
                                                        style="width: 40px; height: 40px; object-fit: cover; flex-shrink: 0; margin-right: 4px;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px; flex-shrink: 0; margin-right: 4px;">
                                                        <i class="bi bi-person text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h6 class="mb-0" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">
                                                            <?= Helper::escape($conv['other_user_full_name'] ?? $conv['other_user_email']) ?>
                                                        </h6>
                                                        <?php if (isset($conv['unread_count']) && $conv['unread_count'] > 0): ?>
                                                            <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.7rem; min-width: 18px; padding: 2px 5px; flex-shrink: 0;">
                                                                <?= $conv['unread_count'] > 99 ? '99+' : $conv['unread_count'] ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <?= Helper::escape($conv['last_message'] ?? 'Нет сообщений') ?>
                                                    </small>
                                                    <?php if (!empty($conv['last_message_time'])): ?>
                                                        <small class="text-muted">
                                                            <?= date('d.m.Y H:i', strtotime($conv['last_message_time'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Полноэкранный чат -->
    <?php if ($selectedUserId): ?>
        <?php
        $userModel = new User();
        $selectedUser = $userModel->findById($selectedUserId);
        $photoModel = new UserPhoto();
        $photos = $photoModel->getByUserId($selectedUserId);
        $firstPhoto = !empty($photos) ? $photos[0]['photo'] : null;
        ?>
        <div id="chat-view" class="mobile-page-container">
            <!-- Заголовок -->
            <div class="chat-page-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center flex-grow-1 chat-header-user" style="min-width: 0;">
                        <?php if ($firstPhoto): ?>
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $firstPhoto ?>"
                                class="rounded-circle flex-shrink-0"
                                style="width: 40px; height: 40px; object-fit: cover; margin-right: 8px;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width: 40px; height: 40px; margin-right: 8px;">
                                <i class="bi bi-person text-white"></i>
                            </div>
                        <?php endif; ?>
                        <span class="chat-header-fio" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: white; font-size: 1rem; font-weight: 500;">
                            <?= Helper::escape($selectedUser['full_name'] ?? $selectedUser['email'] ?? 'Пользователь') ?>
                        </span>
                    </div>
                    <a href="<?= BASE_URL ?>messages/dates-list" class="btn btn-sm btn-secondary flex-shrink-0 ms-2">
                        <span class="d-none d-md-inline">Назад</span> <i class="bi bi-arrow-left"></i>
                    </a>
                    <!-- <?php if (!$isBlockedByMe && !$isBlockedByOther): ?>
                        <div class="d-flex gap-2 ms-2">
                            <button type="button" class="btn btn-sm btn-warning flex-shrink-0" 
                                onclick="blockAndDeleteDateChat(<?= $date['id'] ?>, <?= $selectedUserId ?>, event)"
                                title="Заблокировать и удалить"
                                style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-lock-fill"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger flex-shrink-0" 
                                onclick="deleteDateChatFromChat(<?= $date['id'] ?>, event)"
                                title="Удалить чат"
                                style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    <?php endif; ?> -->
                </div>
            </div>

            <!-- Чат -->
            <div class="card chat-card">
                <div class="card-body" id="messages-container">
                    <?php if ($isBlockedByOther): ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <p class="mb-0">Собеседник вас заблокировал. Вы не можете видеть сообщения.</p>
                        </div>
                    <?php elseif ($isBlockedByMe): ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <p class="mb-0">Вы заблокировали этого пользователя. Вы не можете видеть сообщения.</p>
                        </div>
                    <?php elseif (empty($messages)): ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <p class="mb-0">Нет сообщений. Начните общение!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-item <?= $msg['from_user_id'] == $currentUserId ? 'text-end' : 'text-start' ?>" data-message-id="<?= $msg['id'] ?>">
                                <div class="message-bubble <?= $msg['from_user_id'] == $currentUserId ? 'own' : 'other' ?>">
                                    <?php if ($msg['from_user_id'] != $currentUserId): ?>
                                        <div class="message-sender"><?= Helper::escape($msg['from_full_name'] ?? $msg['from_email'] ?? 'Пользователь') ?></div>
                                    <?php endif; ?>
                                    <div class="message-content">
                                        <?= nl2br(Helper::escape($msg['message'])) ?>
                                    </div>
                                    <div class="message-time">
                                        <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!$isBlockedByMe && !$isBlockedByOther): ?>
                    <div class="card-footer">
                        <form method="POST" action="<?= BASE_URL ?>messages/send" id="message-form" class="chat-input-form">
                            <input type="hidden" name="date_id" value="<?= $date['id'] ?>">
                            <input type="hidden" name="to_user_id" value="<?= $selectedUserId ?>">
                            <div class="d-flex align-items-end">
                                <textarea
                                    class="form-control flex-grow-1"
                                    name="message"
                                    id="message-input"
                                    placeholder="Сообщение..."
                                    autocomplete="off"
                                    rows="1"
                                    style="min-height: 38px; max-height: 150px; resize: none; word-wrap: break-word; white-space: pre-wrap; overflow-wrap: break-word;"
                                    required></textarea>
                                <button type="submit" class="btn btn-primary ms-2">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card-footer">
                        <div class="alert alert-warning mb-0" role="alert">
                            <?php if ($isBlockedByOther): ?>
                                Собеседник вас заблокировал. Вы не можете отправлять сообщения.
                            <?php elseif ($isBlockedByMe): ?>
                                Вы заблокировали этого пользователя. Вы не можете отправлять сообщения.
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($selectedUserId): ?>
    <script>
        // Передаем ID текущего пользователя в JavaScript
        window.currentUserId = <?= $currentUserId ?>;
        window.dateId = <?= $date['id'] ?>;

        // Инициализация push-уведомлений для чата
        if (typeof window.PushNotifications !== 'undefined' && window.PushNotifications.isSupported()) {
            // Убеждаемся, что push-уведомления инициализированы
            if (typeof window.PushNotifications.init === 'function') {
                window.PushNotifications.init();
            }
        }

        // Функция для добавления сообщения в чат
        window.addMessageToChat = function(container, messageData) {
            const isOwnMessage = messageData.from_user_id == window.currentUserId;
            const messageItem = document.createElement('div');
            messageItem.className = `message-item ${isOwnMessage ? 'text-end' : 'text-start'}`;
            messageItem.setAttribute('data-message-id', messageData.id);

            const bubbleClass = isOwnMessage ? 'own' : 'other';
            const date = new Date(messageData.created_at);
            const timeStr = date.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const senderName = messageData.from_full_name || messageData.from_email || 'Пользователь';
            const senderHtml = isOwnMessage ? '' : `<div class="message-sender">${senderName}</div>`;

            messageItem.innerHTML = `
                <div class="message-bubble ${bubbleClass}">
                    ${senderHtml}
                    <div class="message-content">${(messageData.message || '').replace(/\n/g, '<br>')}</div>
                    <div class="message-time">${timeStr}</div>
                </div>
            `;

            container.appendChild(messageItem);
            container.scrollTop = container.scrollHeight;
        };

        // Функция для автоматического изменения размера textarea
        window.autoResizeTextarea = function(textarea) {
            if (!textarea) return;
            textarea.style.height = 'auto';
            const scrollHeight = textarea.scrollHeight;
            const minHeight = 38;
            const maxHeight = 150;
            const newHeight = Math.max(minHeight, Math.min(scrollHeight, maxHeight));
            textarea.style.height = newHeight + 'px';
            textarea.style.overflowY = scrollHeight > maxHeight ? 'auto' : 'hidden';
        };

        // Инициализация textarea
        const messageInput = document.getElementById('message-input');
        if (messageInput) {
            window.autoResizeTextarea(messageInput);
            messageInput.addEventListener('input', function() {
                window.autoResizeTextarea(this);
            });
            messageInput.addEventListener('paste', function() {
                setTimeout(() => window.autoResizeTextarea(this), 0);
            });
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const form = document.getElementById('message-form');
                    if (form) {
                        form.dispatchEvent(new Event('submit'));
                    }
                }
            });
        }

        // Отправка сообщения через AJAX
        document.getElementById('message-form')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();

            if (!message) return;

            const formData = new FormData(form);
            const messagesContainer = document.getElementById('messages-container');

            fetch(BASE_URL + 'messages/send', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.message) {
                        messageInput.value = '';
                        if (typeof window.autoResizeTextarea === 'function') {
                            window.autoResizeTextarea(messageInput);
                        } else {
                            messageInput.style.height = 'auto';
                        }

                        if (messagesContainer && typeof window.addMessageToChat === 'function') {
                            window.addMessageToChat(messagesContainer, data.message);
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        const errorMsg = data?.error || 'Не удалось отправить сообщение. Попробуйте еще раз.';
                        alert(errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при отправке сообщения:', error);
                    alert('Не удалось отправить сообщение. Попробуйте еще раз.');
                });
        });

        // Прокрутка к последнему сообщению
        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            setTimeout(() => {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 100);
        }

        // Функция для удаления чата из чата
        window.deleteDateChatFromChat = function(dateId, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            if (!confirm('Вы уверены, что хотите удалить чат?\n\nВсе ваши сообщения в этом чате будут безвозвратно удалены.')) {
                return;
            }

            const formData = new FormData();
            formData.append('date_id', dateId);

            fetch(BASE_URL + 'messages/deleteDateChat', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success === true) {
                        alert('Чат успешно удален');
                        // Перенаправляем на список чатов
                        window.location.href = BASE_URL + 'messages/dates-list';
                    } else {
                        const errorMsg = data?.error || data?.message || 'Не удалось удалить чат. Попробуйте еще раз.';
                        alert(errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при удалении чата:', error);
                    alert('Произошла ошибка при удалении чата. Попробуйте еще раз.');
                });
        };

        // Функция для блокировки и удаления чата свидания
        window.blockAndDeleteDateChat = function(dateId, userId, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            if (!confirm('Вы уверены, что хотите заблокировать пользователя и удалить чат?\n\nПользователь будет заблокирован навсегда и не сможет писать вам ни в личку, ни в свидания. Все ваши сообщения в этом чате будут безвозвратно удалены.')) {
                return;
            }

            const formData = new FormData();
            formData.append('date_id', dateId);
            formData.append('user_id', userId);

            fetch(BASE_URL + 'messages/blockAndDeleteDateChat', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success === true) {
                        alert('Пользователь заблокирован и чат удален');
                        // Перенаправляем на список чатов
                        window.location.href = BASE_URL + 'messages/dates-list';
                    } else {
                        const errorMsg = data?.error || data?.message || 'Не удалось заблокировать пользователя и удалить чат. Попробуйте еще раз.';
                        alert(errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при блокировке и удалении чата:', error);
                    alert('Произошла ошибка при блокировке и удалении чата. Попробуйте еще раз.');
                });
        };
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = 'Чат: ' . Helper::escape($date['title']);
$bodyClass = $selectedUserId ? 'chat-page' : '';
include __DIR__ . '/../layout.php';
?>
