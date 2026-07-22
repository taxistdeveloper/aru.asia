<?php

/**
 * ЧАТ ДЛЯ МЕРОПРИЯТИЯ — layout как WhatsApp, цвета Aru
 */

ob_start();

$formatChatDate = function ($dateStr) {
    $ts = strtotime($dateStr);
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');
    if ($ts >= $today) {
        return 'Сегодня';
    }
    if ($ts >= $yesterday) {
        return 'Вчера';
    }
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    ];
    $d = (int)date('j', $ts);
    $m = (int)date('n', $ts);
    $y = (int)date('Y', $ts);
    $label = $d . ' ' . $months[$m];
    if ($y !== (int)date('Y')) {
        $label .= ' ' . $y;
    }
    return $label;
};
?>

<style>
    /* ========== Aru Event Chat ========== */
    :root {
        --aru-1: #667eea;
        --aru-2: #764ba2;
        --aru-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --aru-own: #e8e4ff;
        --aru-own-text: #2d2a4a;
        --aru-chat-bg: #f0f2f8;
        --aru-footer: #f5f6fb;
    }

    body.chat-page {
        padding: 0 !important;
        margin: 0 !important;
        background: #f0f2f8;
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
        background: var(--aru-chat-bg);
    }

    .chat-page-header {
        background: var(--aru-grad);
        padding: 10px 12px;
        flex-shrink: 0;
        position: relative;
        z-index: 100;
        overflow: visible;
        margin: 0 !important;
        width: 100%;
        border-radius: 0 !important;
        border: none;
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.25);
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
        color: #fff;
        font-weight: 500;
    }

    .wa-back-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: #fff !important;
        background: transparent;
        border: none;
        text-decoration: none;
        flex-shrink: 0;
        margin-right: 8px;
        transition: background 0.15s;
    }

    .wa-back-btn:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #fff !important;
    }

    .wa-back-btn i {
        font-size: 1.25rem;
    }

    .chat-header-title {
        min-width: 0;
        flex: 1;
        color: #fff;
        font-size: 1.05rem;
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

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
        background: transparent;
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

    #messages-container {
        background-color: #f0f2f8;
        background-image:
            radial-gradient(circle at 18% 22%, rgba(102, 126, 234, 0.07) 0, transparent 42%),
            radial-gradient(circle at 82% 78%, rgba(118, 75, 162, 0.06) 0, transparent 45%),
            radial-gradient(circle at 50% 50%, rgba(102, 126, 234, 0.03) 1px, transparent 1px);
        background-size: auto, auto, 28px 28px;
        padding: 12px 7% 8px;
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }

    #messages-container::-webkit-scrollbar {
        width: 6px;
    }

    #messages-container::-webkit-scrollbar-track {
        background: transparent;
    }

    #messages-container::-webkit-scrollbar-thumb {
        background: rgba(102, 126, 234, 0.28);
        border-radius: 6px;
    }

    #messages-container::-webkit-scrollbar-thumb:hover {
        background: rgba(102, 126, 234, 0.42);
    }

    .wa-date-sep {
        display: flex;
        justify-content: center;
        margin: 12px 0 10px;
    }

    .wa-date-sep span {
        background: rgba(255, 255, 255, 0.92);
        color: #667eea;
        font-size: 12.5px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(102, 126, 234, 0.12);
        text-transform: capitalize;
    }

    .wa-empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #6b7280;
        font-size: 14px;
        margin: auto;
    }

    .wa-empty-state p {
        background: rgba(255, 255, 255, 0.92);
        display: inline-block;
        padding: 8px 16px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(102, 126, 234, 0.1);
        margin: 0;
        color: #5b6472;
    }

    .message-item {
        margin-bottom: 3px;
        display: flex;
        clear: both;
    }

    .message-item + .message-item {
        margin-top: 2px;
    }

    .message-item.text-end {
        justify-content: flex-end;
    }

    .message-item.text-start {
        justify-content: flex-start;
    }

    .message-bubble {
        display: inline-block;
        padding: 6px 7px 8px 9px;
        border-radius: 12px;
        max-width: 65%;
        word-wrap: break-word;
        position: relative;
        box-shadow: 0 1px 2px rgba(102, 126, 234, 0.12);
    }

    .message-bubble.own {
        background: var(--aru-own);
        color: var(--aru-own-text);
        border-top-right-radius: 2px;
    }

    .message-bubble.own::before {
        content: "";
        position: absolute;
        top: 0;
        right: -8px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 0 10px 8px;
        border-color: transparent transparent transparent var(--aru-own);
    }

    .message-bubble.other {
        background: #fff;
        color: #1f2937;
        border-top-left-radius: 2px;
    }

    .message-bubble.other::before {
        content: "";
        position: absolute;
        top: 0;
        left: -8px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 8px 10px 0;
        border-color: transparent #fff transparent transparent;
    }

    .message-bubble .message-sender {
        font-size: 12.8px;
        font-weight: 600;
        color: #667eea;
        margin-bottom: 2px;
        line-height: 1.3;
    }

    .message-bubble .message-sender.role-admin {
        color: #dc2626;
    }

    .message-bubble .message-sender.role-manager {
        color: #764ba2;
    }

    .message-bubble .message-content {
        font-size: 14.2px;
        line-height: 1.35;
        margin: 0;
        word-break: break-word;
        white-space: pre-wrap;
        padding-right: 4px;
    }

    .message-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 3px;
        margin-top: 2px;
        clear: both;
    }

    .message-bubble .message-time {
        font-size: 11px;
        color: #8b92a8;
        line-height: 1;
        white-space: nowrap;
    }

    .message-bubble.own .message-time {
        color: #8b84b0;
    }

    .wa-checks {
        display: inline-flex;
        align-items: center;
        color: #667eea;
        font-size: 14px;
        line-height: 1;
        margin-left: 1px;
    }

    .wa-checks i {
        font-size: 14px;
    }

    .chat-card .card-footer {
        background: var(--aru-footer);
        border-top: 1px solid rgba(102, 126, 234, 0.1);
        padding: 6px 8px;
        padding-bottom: calc(6px + env(safe-area-inset-bottom, 0px));
        flex-shrink: 0;
        position: relative;
        z-index: 100;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .chat-input-form .input-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        width: 100%;
    }

    .chat-input-form .input-wrap {
        flex: 1;
        min-width: 0;
        background: #fff;
        border-radius: 24px;
        padding: 0;
        display: flex;
        align-items: flex-end;
        box-shadow: 0 1px 3px rgba(102, 126, 234, 0.1);
        border: 1px solid rgba(102, 126, 234, 0.12);
    }

    .chat-input-form .form-control {
        border-radius: 24px;
        border: none;
        padding: 10px 16px;
        font-size: 15px;
        background: transparent;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        flex: 1;
        min-width: 0;
        box-shadow: none !important;
        color: #374151;
    }

    .chat-input-form textarea.form-control {
        white-space: pre-wrap !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
        overflow-x: hidden !important;
        resize: none !important;
        line-height: 1.4;
    }

    .chat-input-form .form-control:focus {
        background: transparent;
        border: none;
        box-shadow: none !important;
        outline: none;
    }

    .chat-input-form .form-control::placeholder {
        color: #9ca3af;
    }

    .wa-send-btn {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 50% !important;
        background: var(--aru-grad) !important;
        border: none !important;
        color: #fff !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        margin: 0 !important;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        transition: filter 0.15s, transform 0.1s;
    }

    .wa-send-btn:hover,
    .wa-send-btn:focus {
        filter: brightness(1.05);
        color: #fff !important;
    }

    .wa-send-btn:active {
        transform: scale(0.94);
    }

    .wa-send-btn i {
        font-size: 1.15rem;
        margin-left: 2px;
    }

    .event-info-section {
        display: none;
    }

    @media (max-width: 767px) {
        body.chat-page {
            background: var(--aru-chat-bg);
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
            padding: 8px 10px;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-shrink: 0;
        }

        .chat-header-title {
            font-size: 16px;
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
            padding: 8px 4% 6px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .chat-card .card-footer {
            padding: 5px 6px;
            padding-bottom: calc(5px + env(safe-area-inset-bottom, 0px));
            flex-shrink: 0;
            z-index: 100;
            position: relative;
            display: block !important;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 0;
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
        }

        .chat-input-form .form-control {
            font-size: 16px;
            padding: 9px 14px;
            min-height: 42px;
        }

        .wa-send-btn {
            width: 44px;
            height: 44px;
            min-width: 44px;
        }

        .message-bubble {
            max-width: 85%;
        }
    }

    @media (min-width: 768px) {
        #messages-container {
            padding-left: max(7%, calc(50% - 420px));
            padding-right: max(7%, calc(50% - 420px));
        }
    }
</style>

<div class="mobile-page-container">
    <div class="chat-page-header">
        <div class="d-flex align-items-center w-100">
            <a href="<?= BASE_URL ?>messages/events-list" class="wa-back-btn" title="Назад">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="chat-header-title">
                <?= Helper::escape($event['title']) ?>
            </div>
        </div>
    </div>

    <div class="card chat-card">
        <div class="card-body" id="messages-container">
            <?php if (empty($messages)): ?>
                <div class="wa-empty-state">
                    <p>Нет сообщений. Начните общение!</p>
                </div>
            <?php else: ?>
                <?php
                $lastDateKey = null;
                foreach ($messages as $msg):
                    $isManager = isset($msg['from_role']) && $msg['from_role'] === 'manager';
                    $isAdmin = isset($msg['from_is_admin']) && $msg['from_is_admin'] == 1;
                    $isOwnMessage = $msg['from_user_id'] == $currentUserId;

                    if ($isAdmin) {
                        $senderName = 'Админ';
                        $senderRoleClass = 'role-admin';
                    } elseif ($isManager) {
                        $senderName = 'Менеджер';
                        $senderRoleClass = 'role-manager';
                    } else {
                        $senderName = !empty($msg['from_full_name']) ? trim($msg['from_full_name']) : (!empty($msg['from_email']) ? trim($msg['from_email']) : 'Пользователь');
                        if (strpos($senderName, '@') !== false) {
                            $senderName = explode('@', $senderName)[0];
                        }
                        $senderRoleClass = '';
                    }

                    $dateKey = date('Y-m-d', strtotime($msg['created_at']));
                    if ($dateKey !== $lastDateKey):
                        $lastDateKey = $dateKey;
                ?>
                        <div class="wa-date-sep" data-date-key="<?= $dateKey ?>">
                            <span><?= $formatChatDate($msg['created_at']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="message-item <?= $isOwnMessage ? 'text-end' : 'text-start' ?>" data-message-id="<?= $msg['id'] ?>">
                        <div class="message-bubble <?= $isOwnMessage ? 'own' : 'other' ?>">
                            <?php if (!$isOwnMessage): ?>
                                <div class="message-sender <?= $senderRoleClass ?>"><?= Helper::escape($senderName) ?></div>
                            <?php endif; ?>
                            <div class="message-content"><?= nl2br(Helper::escape($msg['message'])) ?></div>
                            <div class="message-meta">
                                <span class="message-time" data-time="<?= Helper::escape($msg['created_at']) ?>"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                                <?php if ($isOwnMessage): ?>
                                    <span class="wa-checks" title="Доставлено"><i class="bi bi-check2-all"></i></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <form method="POST" action="<?= BASE_URL ?>messages/send" id="message-form" class="chat-input-form">
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                <div class="input-row">
                    <div class="input-wrap">
                        <textarea
                            class="form-control flex-grow-1"
                            name="message"
                            id="message-input"
                            placeholder="Сообщение"
                            autocomplete="off"
                            rows="1"
                            style="min-height: 42px; max-height: 120px; resize: none; word-wrap: break-word; white-space: pre-wrap; overflow-wrap: break-word;"
                            required></textarea>
                    </div>
                    <button type="submit" class="wa-send-btn" title="Отправить">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.currentUserId = <?= $currentUserId ?>;
    window.eventId = <?= $event['id'] ?>;
    window.lastMessageId = null;
    window.pollingPaused = false;

    if (typeof window.PushNotifications !== 'undefined' && window.PushNotifications.isSupported()) {
        if (typeof window.PushNotifications.init === 'function') {
            window.PushNotifications.init();
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function formatWaDateLabel(date) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);
        if (d.getTime() === today.getTime()) return 'Сегодня';
        if (d.getTime() === yesterday.getTime()) return 'Вчера';
        const months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        let label = d.getDate() + ' ' + months[d.getMonth()];
        if (d.getFullYear() !== today.getFullYear()) label += ' ' + d.getFullYear();
        return label;
    }

    function ensureDateSeparator(container, createdAt) {
        const date = new Date(createdAt);
        const key = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
        const empty = container.querySelector('.wa-empty-state');
        if (empty) empty.remove();

        let lastDateKey = null;
        const children = Array.from(container.children);
        for (let i = children.length - 1; i >= 0; i--) {
            if (children[i].classList.contains('wa-date-sep')) {
                lastDateKey = children[i].getAttribute('data-date-key');
                break;
            }
        }
        if (lastDateKey === key) return;

        const sep = document.createElement('div');
        sep.className = 'wa-date-sep';
        sep.setAttribute('data-date-key', key);
        sep.innerHTML = '<span>' + formatWaDateLabel(date) + '</span>';
        container.appendChild(sep);
    }

    window.addMessageToChat = function(container, messageData) {
        const messageText = messageData.message || '';
        if (messageText.indexOf('успешно одобрено и теперь отображается на сайте') !== -1) {
            return;
        }

        if (messageData.id) {
            const messageId = String(messageData.id);
            const existingMessage = container.querySelector(`[data-message-id="${messageId}"]`);
            if (existingMessage) return;

            const allMessages = container.querySelectorAll('.message-item');
            for (let i = 0; i < allMessages.length; i++) {
                const msg = allMessages[i];
                const msgId = msg.getAttribute('data-message-id');
                if (msgId && (String(msgId) === messageId || parseInt(msgId, 10) === parseInt(messageId, 10))) {
                    return;
                }
            }
        }

        const trimmedText = (messageData.message || '').trim();
        const messageTime = messageData.created_at;
        if (!messageData.id && trimmedText && messageTime) {
            const existingMessages = container.querySelectorAll('.message-item');
            for (let i = 0; i < existingMessages.length; i++) {
                const msg = existingMessages[i];
                const msgText = msg.querySelector('.message-content')?.textContent?.trim() || '';
                if (msgText === trimmedText) {
                    const msgTimeAttr = msg.querySelector('.message-time')?.getAttribute('data-time');
                    if (msgTimeAttr) {
                        const timeDiff = Math.abs(new Date(messageTime) - new Date(msgTimeAttr));
                        if (timeDiff < 10000) return;
                    }
                }
            }
        }

        ensureDateSeparator(container, messageData.created_at);

        const isOwnMessage = messageData.from_user_id == window.currentUserId;
        const messageItem = document.createElement('div');
        messageItem.className = `message-item ${isOwnMessage ? 'text-end' : 'text-start'}`;
        if (messageData.id) {
            messageItem.setAttribute('data-message-id', String(messageData.id));
        }

        const bubbleClass = isOwnMessage ? 'own' : 'other';
        const date = new Date(messageData.created_at);
        const timeStr = date.toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const isManager = messageData.from_role === 'manager';
        const isAdmin = messageData.from_is_admin === true || messageData.from_is_admin === 1 || messageData.from_role === 'admin';

        let senderName = '';
        let roleClass = '';
        if (isAdmin) {
            senderName = 'Админ';
            roleClass = 'role-admin';
        } else if (isManager) {
            senderName = 'Менеджер';
            roleClass = 'role-manager';
        } else {
            senderName = (messageData.from_full_name || messageData.from_email || 'Пользователь').trim();
            if (senderName.includes('@')) {
                senderName = senderName.split('@')[0];
            }
        }

        const senderHtml = isOwnMessage ? '' :
            `<div class="message-sender ${roleClass}">${escapeHtml(senderName)}</div>`;
        const checksHtml = isOwnMessage ?
            '<span class="wa-checks" title="Доставлено"><i class="bi bi-check2-all"></i></span>' : '';
        const messageContent = escapeHtml(messageData.message || '').replace(/\n/g, '<br>');

        messageItem.innerHTML = `
            <div class="message-bubble ${bubbleClass}">
                ${senderHtml}
                <div class="message-content">${messageContent}</div>
                <div class="message-meta">
                    <span class="message-time" data-time="${escapeHtml(messageData.created_at || '')}">${timeStr}</span>
                    ${checksHtml}
                </div>
            </div>
        `;

        if (messageData.id) {
            const messageIdInt = parseInt(messageData.id, 10);
            if (!isNaN(messageIdInt)) {
                if (!window.lastMessageId || messageIdInt > window.lastMessageId) {
                    window.lastMessageId = messageIdInt;
                }
            }
        }

        container.appendChild(messageItem);
        requestAnimationFrame(() => {
            container.scrollTop = container.scrollHeight;
        });
    };

    window.autoResizeTextarea = function(textarea) {
        if (!textarea) return;
        textarea.style.height = 'auto';
        const scrollHeight = textarea.scrollHeight;
        const minHeight = 42;
        const maxHeight = 120;
        const newHeight = Math.max(minHeight, Math.min(scrollHeight, maxHeight));
        textarea.style.height = newHeight + 'px';
        textarea.style.overflowY = scrollHeight > maxHeight ? 'auto' : 'hidden';
    };

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

    document.getElementById('message-form')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const messageInputEl = document.getElementById('message-input');
        const message = messageInputEl.value.trim();

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
                    messageInputEl.value = '';
                    if (typeof window.autoResizeTextarea === 'function') {
                        window.autoResizeTextarea(messageInputEl);
                    } else {
                        messageInputEl.style.height = 'auto';
                    }

                    if (messagesContainer && typeof window.addMessageToChat === 'function') {
                        if (data.message && data.message.id) {
                            const messageIdInt = parseInt(data.message.id, 10);
                            if (!isNaN(messageIdInt) && (!window.lastMessageId || messageIdInt > window.lastMessageId)) {
                                window.lastMessageId = messageIdInt;
                            }
                        }

                        window.addMessageToChat(messagesContainer, data.message);
                        setTimeout(() => {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }, 50);

                        window.pollingPaused = true;
                        setTimeout(() => {
                            window.pollingPaused = false;
                        }, 5000);
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

    window.scrollToBottom = function(container) {
        if (!container) return;
        container.scrollTop = container.scrollHeight;
    };

    function scrollToLastMessage() {
        const messagesContainer = document.getElementById('messages-container');
        if (!messagesContainer) return;

        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        const lastItem = messagesContainer.querySelector('.message-item:last-child');
        if (lastItem) {
            const lastId = parseInt(lastItem.getAttribute('data-message-id') || '0', 10);
            if (lastId > 0 && (!window.lastMessageId || lastId > window.lastMessageId)) {
                window.lastMessageId = lastId;
            }
        }
    }

    function initScroll() {
        scrollToLastMessage();
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initScroll, 0);
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initScroll, 100);
        setTimeout(initScroll, 300);
        setTimeout(initScroll, 600);
    });

    window.addEventListener('load', function() {
        setTimeout(initScroll, 100);
        setTimeout(initScroll, 500);
    });

    setTimeout(initScroll, 200);
    setTimeout(initScroll, 500);
    setTimeout(initScroll, 1000);

    function fetchEventChatUpdates() {
        if (!window.eventId || window.pollingPaused) return;

        const params = new URLSearchParams();
        params.append('event_id', window.eventId);
        if (window.lastMessageId) {
            params.append('last_id', window.lastMessageId);
        }

        fetch(BASE_URL + 'messages/event-updates?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data || !data.success || !Array.isArray(data.messages)) {
                    return;
                }

                const container = document.getElementById('messages-container');
                if (!container) return;

                data.messages.forEach(function(msg) {
                    if (typeof window.addMessageToChat === 'function') {
                        window.addMessageToChat(container, msg);
                    }
                });

                if (data.messages.length > 0) {
                    const lastMsg = data.messages[data.messages.length - 1];
                    if (lastMsg && lastMsg.id) {
                        const lastId = parseInt(lastMsg.id, 10);
                        if (!isNaN(lastId) && (!window.lastMessageId || lastId > window.lastMessageId)) {
                            window.lastMessageId = lastId;
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка при получении новых сообщений чата мероприятия:', error);
            });
    }

    setInterval(fetchEventChatUpdates, 3000);

    function adjustMessagesContainerHeight() {
        if (window.innerWidth <= 1024) return;

        const container = document.getElementById('messages-container');
        if (container) {
            const headerHeight = document.querySelector('.chat-page-header')?.offsetHeight || 0;
            const cardFooterHeight = document.querySelector('.chat-card .card-footer')?.offsetHeight || 0;
            const totalHeight = window.innerHeight - headerHeight - cardFooterHeight;
            container.style.height = totalHeight + 'px';
        }
    }

    function handleOrientationChange() {
        setTimeout(() => {
            adjustMessagesContainerHeight();
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }, 100);
    }

    window.addEventListener('resize', adjustMessagesContainerHeight);
    window.addEventListener('orientationchange', handleOrientationChange);
    window.addEventListener('load', adjustMessagesContainerHeight);
    setTimeout(adjustMessagesContainerHeight, 100);

    if (window.innerWidth <= 767) {
        let viewportHeight = window.innerHeight;

        function ensureFooterVisible() {
            const footer = document.querySelector('.chat-card .card-footer');
            if (footer) {
                const rect = footer.getBoundingClientRect();
                const vh = window.innerHeight;
                const safeAreaBottom = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--safe-area-inset-bottom') || '0');

                if (rect.bottom > vh - safeAreaBottom) {
                    footer.style.marginBottom = '0';
                    footer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'end'
                    });
                }
            }
        }

        window.addEventListener('resize', function() {
            const currentHeight = window.innerHeight;
            if (currentHeight < viewportHeight - 150) {
                setTimeout(() => {
                    const messagesContainer = document.getElementById('messages-container');
                    if (messagesContainer) {
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                    ensureFooterVisible();
                }, 300);
            } else {
                viewportHeight = currentHeight;
                ensureFooterVisible();
            }
        });

        window.addEventListener('load', ensureFooterVisible);
        window.addEventListener('scroll', ensureFooterVisible);
        setTimeout(ensureFooterVisible, 500);
    }

    window.deleteEventChatFromChat = function(eventId, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        if (!confirm('Вы уверены, что хотите удалить чат?\n\nВсе ваши сообщения в этом чате будут безвозвратно удалены.')) {
            return;
        }

        const formData = new FormData();
        formData.append('event_id', eventId);

        fetch(BASE_URL + 'messages/deleteEventChat', {
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
                    window.location.href = BASE_URL + 'messages/events-list';
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

    window.blockAndDeleteEventChat = function(eventId, userId, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        if (!confirm('Вы уверены, что хотите заблокировать пользователя и удалить чат?\n\nПользователь будет заблокирован навсегда и не сможет писать вам ни в личку, ни в мероприятия. Все ваши сообщения в этом чате будут безвозвратно удалены.')) {
            return;
        }

        const formData = new FormData();
        formData.append('event_id', eventId);
        formData.append('user_id', userId);

        fetch(BASE_URL + 'messages/blockAndDeleteEventChat', {
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
                    window.location.href = BASE_URL + 'messages/events-list';
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

    function toggleEventDescription(btn) {
        const cardText = btn.closest('.card-text');
        const shortSpan = cardText.querySelector('.event-description-short');
        const fullSpan = cardText.querySelector('.event-description-full');
        const showMore = btn.querySelector('.show-more');
        const showLess = btn.querySelector('.show-less');

        if (shortSpan.classList.contains('d-none')) {
            shortSpan.classList.remove('d-none');
            fullSpan.classList.add('d-none');
            showMore.classList.remove('d-none');
            showLess.classList.add('d-none');
        } else {
            shortSpan.classList.add('d-none');
            fullSpan.classList.remove('d-none');
            showMore.classList.add('d-none');
            showLess.classList.remove('d-none');
        }
    }
</script>

<?php
$content = ob_get_clean();
$title = 'Чат: ' . Helper::escape($event['title']);
$bodyClass = 'chat-page';
include __DIR__ . '/../layout.php';
?>
