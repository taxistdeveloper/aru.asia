<?php

/**
 * ЧАТ ДЛЯ МЕРОПРИЯТИЯ
 */

ob_start();
?>

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

    .chat-page-header h2,
    .chat-page-header h4 {
        margin: 0;
        font-size: 18px;
        color: white;
    }

    .chat-page-header .d-flex {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        overflow: visible;
    }

    .chat-page-header .btn {
        display: inline-flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 101;
        position: relative;
        flex-shrink: 0;
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

    .chat-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-bottom: 1px solid #e0e0e0;
        padding: 12px 16px;
        flex-shrink: 0;
        overflow: hidden;
        border-radius: 0 !important;
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .chat-card .card-header>div {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .chat-card .card-header strong {
        font-size: 16px;
        display: block;
        word-wrap: break-word;
        overflow-wrap: break-word;
        color: white;
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

    .chat-input-form {
        margin: 0;
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: 100%;
    }

    .chat-input-form .d-flex {
        display: flex !important;
        width: 100%;
        align-items: center;
    }

    /* Информация о мероприятии */
    .event-info-section {
        display: none;
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

    .chat-input-form .form-control::-webkit-scrollbar {
        height: 4px;
    }

    .chat-input-form .form-control::-webkit-scrollbar-track {
        background: transparent;
    }

    .chat-input-form .form-control::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 2px;
    }

    .chat-input-form .form-control:focus {
        background: white;
        border-color: #3390ec;
        box-shadow: none;
    }

    .chat-input-form textarea.form-control:focus {
        white-space: pre-wrap !important;
        overflow-x: hidden !important;
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

    /* ПЛАНШЕТЫ (768px - 1024px) */
    @media (min-width: 768px) and (max-width: 1024px) {
        .chat-page-header {
            padding: clamp(10px, 1.5vw, 14px) clamp(12px, 2vw, 18px);
        }

        .chat-page-header h2 {
            font-size: clamp(16px, 2.2vw, 20px);
        }

        .chat-card .card-header {
            padding: clamp(10px, 1.5vw, 14px) clamp(12px, 2vw, 18px);
        }

        .chat-card .card-header strong {
            font-size: clamp(14px, 1.8vw, 17px);
        }

        #messages-container {
            padding: clamp(10px, 1.5vw, 14px) clamp(12px, 2vw, 18px);
        }

        .message-bubble {
            max-width: 75%;
            padding: clamp(8px, 1.2vw, 12px) clamp(10px, 1.5vw, 14px);
        }

        .message-bubble .message-content {
            font-size: clamp(14px, 1.6vw, 16px);
        }

        .chat-card .card-footer {
            padding: clamp(8px, 1.2vw, 12px) clamp(10px, 1.5vw, 14px);
        }

        .chat-input-form .form-control {
            font-size: clamp(14px, 1.6vw, 16px);
            padding: clamp(7px, 1vw, 10px) clamp(12px, 1.8vw, 16px);
        }

        .chat-input-form textarea.form-control {
            white-space: pre-wrap !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            overflow-x: hidden !important;
        }

        .chat-input-form .btn-primary {
            padding: clamp(7px, 1vw, 10px) clamp(16px, 2.2vw, 20px);
        }
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

        .chat-page-header h4 {
            font-size: clamp(14px, 4vw, 18px);
        }

        .chat-page-header .btn {
            font-size: clamp(12px, 3.5vw, 16px);
            padding: clamp(4px, 1.2vw, 6px) clamp(8px, 2vw, 12px);
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 101;
            position: relative;
            flex-shrink: 0;
            min-width: auto;
        }

        .chat-page-header .d-flex {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            overflow: visible;
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

        .chat-card .card-header {
            padding: clamp(6px, 2vw, 10px) clamp(8px, 2.5vw, 12px);
            flex-shrink: 0;
        }

        .chat-card .card-header strong {
            font-size: clamp(13px, 3.8vw, 16px);
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

        .chat-input-form {
            margin: 0;
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100%;
        }

        .chat-input-form .d-flex {
            display: flex !important;
            width: 100%;
            align-items: center;
        }

        .chat-input-form .form-control {
            font-size: 16px;
            padding: clamp(6px, 1.8vw, 10px) clamp(10px, 3vw, 14px);
            min-height: clamp(36px, 10vw, 44px);
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
            overflow-y: auto !important;
            resize: none !important;
        }

        .chat-input-form .btn-primary {
            padding: clamp(6px, 1.8vw, 10px) clamp(12px, 3.5vw, 18px);
            margin-left: clamp(4px, 1.5vw, 8px);
            min-width: clamp(44px, 12vw, 56px);
            min-height: clamp(36px, 10vw, 44px);
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex-shrink: 0;
        }

        .message-bubble {
            max-width: clamp(75%, 85%, 90%);
            padding: clamp(6px, 2vw, 10px) clamp(8px, 2.5vw, 12px);
        }

        .message-bubble .message-sender {
            font-size: clamp(11px, 3.2vw, 14px);
            margin-bottom: clamp(2px, 0.6vw, 4px);
        }

        .message-bubble .message-content {
            font-size: clamp(13px, 3.8vw, 16px);
            line-height: 1.4;
        }

        .message-bubble .message-time {
            font-size: clamp(10px, 2.8vw, 12px);
            margin-top: clamp(2px, 0.6vw, 4px);
        }

        .message-item {
            margin-bottom: clamp(6px, 2vw, 10px);
        }
    }

    /* СРЕДНИЕ МОБИЛЬНЫЕ (481px - 767px) */
    @media (min-width: 481px) and (max-width: 767px) {
        .chat-page-header {
            padding: 10px 14px;
        }

        .chat-page-header h4 {
            font-size: 17px;
        }

        #messages-container {
            padding: 10px 14px;
        }

        .chat-card .card-header {
            padding: 10px 14px;
        }

        .chat-card .card-header strong {
            font-size: 15px;
        }

        .chat-card .card-footer {
            padding: 8px 10px;
        }

        .message-bubble {
            max-width: 80%;
            padding: 9px 11px;
        }

        .message-bubble .message-content {
            font-size: 15px;
        }
    }

    /* МАЛЕНЬКИЕ МОБИЛЬНЫЕ (375px - 480px) */
    @media (min-width: 375px) and (max-width: 480px) {
        .chat-page-header {
            padding: 8px 10px;
        }

        .chat-page-header h4 {
            font-size: 16px;
        }

        #messages-container {
            padding: 8px 10px;
        }

        .chat-card .card-header {
            padding: 8px 10px;
        }

        .chat-card .card-header strong {
            font-size: 14px;
        }

        .chat-card .card-footer {
            padding: 6px 8px;
        }

        .message-bubble {
            max-width: 85%;
            padding: 8px 10px;
        }

        .message-bubble .message-content {
            font-size: 14px;
        }
    }

    /* ОЧЕНЬ МАЛЕНЬКИЕ ЭКРАНЫ (320px - 374px) */
    @media (min-width: 320px) and (max-width: 374px) {
        .chat-page-header {
            padding: 6px 8px;
        }

        .chat-page-header h4 {
            font-size: 15px;
        }

        .chat-card .card-header {
            padding: 6px 8px;
        }

        .chat-card .card-header strong {
            font-size: 13px;
        }

        .chat-card .card-footer {
            padding: 5px 7px;
        }

        .chat-input-form .form-control {
            padding: 6px 10px;
        }

        .chat-input-form textarea.form-control {
            white-space: pre-wrap !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            overflow-x: hidden !important;
        }

        .chat-input-form .btn-primary {
            padding: 6px 12px;
            margin-left: 4px;
        }

        #messages-container {
            padding: 6px 8px;
        }

        .message-bubble {
            max-width: 88%;
            padding: 7px 9px;
        }

        .message-bubble .message-content {
            font-size: 13px;
        }
    }

    /* ЭКСТРА МАЛЕНЬКИЕ ЭКРАНЫ (до 320px) */
    @media (max-width: 319px) {
        .chat-page-header {
            padding: 5px 6px;
        }

        .chat-page-header h4 {
            font-size: 14px;
        }

        .chat-page-header .btn {
            font-size: 11px;
            padding: 4px 6px;
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 101;
            position: relative;
            flex-shrink: 0;
            min-width: auto;
        }

        .chat-card .card-header {
            padding: 5px 6px;
        }

        .chat-card .card-header strong {
            font-size: 12px;
        }

        .chat-card .card-footer {
            padding: 4px 6px;
        }

        .chat-input-form .form-control {
            font-size: 16px;
            padding: 5px 8px;
            min-height: 34px;
        }

        .chat-input-form textarea.form-control {
            white-space: pre-wrap !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            overflow-x: hidden !important;
        }

        .chat-input-form .btn-primary {
            padding: 5px 10px;
            margin-left: 3px;
            min-width: 40px;
            min-height: 34px;
        }

        #messages-container {
            padding: 5px 6px;
        }

        .message-bubble {
            max-width: 92%;
            padding: 6px 8px;
        }

        .message-bubble .message-sender {
            font-size: 11px;
        }

        .message-bubble .message-content {
            font-size: 12px;
        }

        .message-bubble .message-time {
            font-size: 10px;
        }

        .message-item {
            margin-bottom: 6px;
        }
    }

    /* ЛАНДШАФТНАЯ ОРИЕНТАЦИЯ НА МОБИЛЬНЫХ */
    @media (max-width: 767px) and (orientation: landscape) {
        .chat-page-header {
            padding: 6px 10px;
        }

        .chat-page-header h4 {
            font-size: clamp(14px, 2.5vw, 16px);
        }

        .chat-card .card-header {
            padding: 6px 10px;
        }

        .chat-card .card-footer {
            padding: 5px 8px;
        }

        .message-bubble {
            max-width: 70%;
        }
    }
</style>

<div class="mobile-page-container">
    <!-- Заголовок -->
    <div class="chat-page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="d-none d-md-block mb-0">Чат: <?= Helper::escape($event['title']) ?></h2>
                <h4 class="d-block d-md-none mb-0"><?= Helper::escape($event['title']) ?></h4>
            </div>
            <a href="<?= BASE_URL ?>messages/events-list" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Назад</span>
            </a>
        </div>
    </div>

    <!-- Чат -->
    <div class="card chat-card">

        <div class="card-body" id="messages-container">
            <?php if (empty($messages)): ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <p class="mb-0">Нет сообщений. Начните общение!</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php
                    $isManager = isset($msg['from_role']) && $msg['from_role'] === 'manager';
                    $isAdmin = isset($msg['from_is_admin']) && $msg['from_is_admin'] == 1;
                    $isOwnMessage = $msg['from_user_id'] == $currentUserId;
                    
                    // Получаем имя отправителя - всегда показываем имя для всех сообщений
                    $senderName = '';
                    
                    // Для админов/менеджеров показываем префикс
                    if ($isAdmin) {
                        $senderName = 'Админ';
                    } elseif ($isManager) {
                        $senderName = 'Менеджер';
                    } else {
                        // Для обычных пользователей - сначала full_name, потом email, потом дефолтное имя
                        $senderName = !empty($msg['from_full_name']) ? trim($msg['from_full_name']) : (!empty($msg['from_email']) ? trim($msg['from_email']) : 'Пользователь');
                        
                        // Если это email, извлекаем только часть до @
                        if (strpos($senderName, '@') !== false) {
                            $senderName = explode('@', $senderName)[0];
                        }
                    }
                    
                    // Всегда показываем имя отправителя
                    $showSenderName = true;
                    ?>
                    <div class="message-item <?= $isOwnMessage ? 'text-end' : 'text-start' ?>" data-message-id="<?= $msg['id'] ?>">
                        <div class="message-bubble <?= $isOwnMessage ? 'own' : 'other' ?>">
                            <div class="message-sender" style="display: block;">
                                <?= Helper::escape($senderName) ?>
                            </div>
                            <div class="message-content">
                                <?= nl2br(Helper::escape($msg['message'])) ?>
                            </div>
                            <div class="message-time">
                                <?php
                                // Используем правильное форматирование времени с учетом часового пояса
                                $timestamp = strtotime($msg['created_at']);
                                // Форматируем время в формате H:i (часы:минуты)
                                echo date('H:i', $timestamp);
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <form method="POST" action="<?= BASE_URL ?>messages/send" id="message-form" class="chat-input-form">
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
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
    </div>
</div>

<script>
    // Передаем ID текущего пользователя и мероприятия в JavaScript
    window.currentUserId = <?= $currentUserId ?>;
    window.eventId = <?= $event['id'] ?>;
    window.lastMessageId = null;
    window.pollingPaused = false; // Флаг для временной остановки опроса

    // Инициализация push-уведомлений для чата
    if (typeof window.PushNotifications !== 'undefined' && window.PushNotifications.isSupported()) {
        // Убеждаемся, что push-уведомления инициализированы
        if (typeof window.PushNotifications.init === 'function') {
            window.PushNotifications.init();
        }
    }

    // Функция для добавления сообщения в чат
    window.addMessageToChat = function(container, messageData) {
        // Пропускаем системные сообщения об одобрении мероприятия (они уже есть в уведомлениях)
        const messageText = messageData.message || '';
        if (messageText.indexOf('успешно одобрено и теперь отображается на сайте') !== -1) {
            return; // Не добавляем это сообщение в чат
        }

        // СТРОГАЯ ПРОВЕРКА НА ДУБЛИКАТЫ - проверяем ПЕРЕД любыми действиями
        if (messageData.id) {
            const messageId = String(messageData.id);
            // Проверяем разными способами для надежности
            const existingMessage = container.querySelector(`[data-message-id="${messageId}"]`);
            if (existingMessage) {
                console.log('❌ Сообщение уже существует, пропускаем:', messageId);
                return; // Сообщение уже существует, не добавляем его снова
            }
            
            // Дополнительная проверка: ищем все сообщения и сравниваем ID
            const allMessages = container.querySelectorAll('.message-item');
            for (let i = 0; i < allMessages.length; i++) {
                const msg = allMessages[i];
                const msgId = msg.getAttribute('data-message-id');
                if (msgId && (String(msgId) === messageId || parseInt(msgId, 10) === parseInt(messageId, 10))) {
                    console.log('❌ Дубликат найден при дополнительной проверке, пропускаем:', messageId);
                    return;
                }
            }
        }
        
        // Если нет ID, проверяем по содержимому и времени (на случай если ID не пришел)
        const messageText = (messageData.message || '').trim();
        const messageTime = messageData.created_at;
        if (!messageData.id && messageText && messageTime) {
            const existingMessages = container.querySelectorAll('.message-item');
            for (let i = 0; i < existingMessages.length; i++) {
                const msg = existingMessages[i];
                const msgText = msg.querySelector('.message-content')?.textContent?.trim() || '';
                if (msgText === messageText) {
                    // Проверяем время создания (в пределах 10 секунд)
                    const msgTimeAttr = msg.querySelector('.message-time')?.getAttribute('data-time');
                    if (msgTimeAttr) {
                        const timeDiff = Math.abs(new Date(messageTime) - new Date(msgTimeAttr));
                        if (timeDiff < 10000) { // 10 секунд
                            console.log('❌ Дубликат сообщения найден по содержимому и времени, пропускаем');
                            return;
                        }
                    }
                }
            }
        }

        const isOwnMessage = messageData.from_user_id == window.currentUserId;
        const messageItem = document.createElement('div');
        messageItem.className = `message-item ${isOwnMessage ? 'text-end' : 'text-start'}`;
        // Устанавливаем ID как строку для надежности
        if (messageData.id) {
            messageItem.setAttribute('data-message-id', String(messageData.id));
        }

        const bubbleClass = isOwnMessage ? 'own' : 'other';
        const date = new Date(messageData.created_at);
        const timeStr = date.toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit'
        });

        // Определяем роль отправителя
        const isManager = messageData.from_role === 'manager';
        const isAdmin = messageData.from_is_admin === true || messageData.from_is_admin === 1 || messageData.from_role === 'admin';
        
        // Получаем имя отправителя - всегда показываем имя для всех сообщений
        let senderName = '';
        
        // Для админов/менеджеров показываем префикс
        if (isAdmin) {
            senderName = 'Админ';
        } else if (isManager) {
            senderName = 'Менеджер';
        } else {
            // Для обычных пользователей - сначала from_full_name, потом from_email, потом дефолтное имя
            senderName = (messageData.from_full_name || messageData.from_email || 'Пользователь').trim();
            
            // Если это email, извлекаем только часть до @
            if (senderName.includes('@')) {
                senderName = senderName.split('@')[0];
            }
        }
        
        // Экранируем HTML для безопасности
        const escapeHtmlForSender = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Всегда показываем имя отправителя
        const senderHtml = `<div class="message-sender" style="display: block;">${escapeHtmlForSender(senderName)}</div>`;

        // Экранируем содержимое сообщения
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        const messageContent = escapeHtml(messageData.message || '').replace(/\n/g, '<br>');
        
        messageItem.innerHTML = `
            <div class="message-bubble ${bubbleClass}">
                ${senderHtml}
                <div class="message-content">${messageContent}</div>
                <div class="message-time" data-time="${messageData.created_at || ''}">${timeStr}</div>
            </div>
        `;

        // ВАЖНО: Обновляем lastMessageId ПЕРЕД добавлением в DOM, чтобы избежать дублирования
        if (messageData.id) {
            const messageIdInt = parseInt(messageData.id, 10);
            if (!isNaN(messageIdInt)) {
                // Обновляем lastMessageId только если новый ID больше текущего
                if (!window.lastMessageId || messageIdInt > window.lastMessageId) {
                    window.lastMessageId = messageIdInt;
                    console.log('✅ Обновлен lastMessageId:', window.lastMessageId);
                } else {
                    console.log('⚠️ Сообщение с ID', messageIdInt, 'уже обработано (lastMessageId:', window.lastMessageId, ')');
                }
            }
        }
        
        container.appendChild(messageItem);
        // Прокручиваем к новому сообщению
        requestAnimationFrame(() => {
            container.scrollTop = container.scrollHeight;
        });
    };

    // Функция для автоматического изменения размера textarea
    window.autoResizeTextarea = function(textarea) {
        if (!textarea) return;

        // Сбрасываем высоту, чтобы получить правильный scrollHeight
        textarea.style.height = 'auto';

        // Вычисляем новую высоту на основе содержимого
        const scrollHeight = textarea.scrollHeight;
        const minHeight = 38;
        const maxHeight = 150;
        const newHeight = Math.max(minHeight, Math.min(scrollHeight, maxHeight));

        textarea.style.height = newHeight + 'px';

        // Если текст превышает максимальную высоту, показываем скролл
        if (scrollHeight > maxHeight) {
            textarea.style.overflowY = 'auto';
        } else {
            textarea.style.overflowY = 'hidden';
        }
    };

    // Инициализация textarea после загрузки DOM
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        // Устанавливаем начальную высоту
        window.autoResizeTextarea(messageInput);

        // Автоматическое изменение размера при вводе
        messageInput.addEventListener('input', function() {
            window.autoResizeTextarea(this);
        });

        // Автоматическое изменение размера при вставке текста
        messageInput.addEventListener('paste', function() {
            setTimeout(() => window.autoResizeTextarea(this), 0);
        });

        // Обработка клавиш: Enter - отправка, Shift+Enter - новая строка
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
                    // Очищаем поле ввода и сбрасываем высоту
                    messageInput.value = '';
                    if (typeof window.autoResizeTextarea === 'function') {
                        window.autoResizeTextarea(messageInput);
                    } else {
                        messageInput.style.height = 'auto';
                    }

                    // Добавляем сообщение в чат
                    if (messagesContainer && typeof window.addMessageToChat === 'function') {
                        // Сначала обновляем lastMessageId ДО добавления сообщения, чтобы избежать дублирования
                        if (data.message && data.message.id) {
                            const messageIdInt = parseInt(data.message.id, 10);
                            if (!isNaN(messageIdInt) && (!window.lastMessageId || messageIdInt > window.lastMessageId)) {
                                window.lastMessageId = messageIdInt;
                                console.log('Обновлен lastMessageId при отправке:', window.lastMessageId);
                            }
                        }
                        
                        // Теперь добавляем сообщение (функция addMessageToChat также проверит на дубликаты)
                        window.addMessageToChat(messagesContainer, data.message);
                        // Прокручиваем к новому сообщению
                        setTimeout(() => {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }, 50);
                        
                        // Временно останавливаем опрос на 5 секунд, чтобы избежать дублирования
                        window.pollingPaused = true;
                        setTimeout(() => {
                            window.pollingPaused = false;
                        }, 5000);
                    } else {
                        // Если функция не доступна, перезагружаем страницу
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

    // Функция для прокрутки к последнему сообщению
    window.scrollToBottom = function(container) {
        if (!container) return;
        // Устанавливаем scrollTop в максимальное значение
        container.scrollTop = container.scrollHeight;
    };

    // Функция для прокрутки к последним сообщениям
    function scrollToLastMessage() {
        const messagesContainer = document.getElementById('messages-container');
        if (!messagesContainer) return;
        
        // Прокручиваем к последнему сообщению
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Находим ID последнего сообщения при загрузке страницы
        const lastItem = messagesContainer.querySelector('.message-item:last-child');
        if (lastItem) {
            const lastId = parseInt(lastItem.getAttribute('data-message-id') || '0', 10);
            if (lastId > 0 && (!window.lastMessageId || lastId > window.lastMessageId)) {
                window.lastMessageId = lastId;
                console.log('Инициализирован lastMessageId при загрузке:', window.lastMessageId);
            }
        }
    }

    // Прокрутка к последнему сообщению - используем несколько подходов для надежности
    function initScroll() {
        scrollToLastMessage();
    }

    // Выполняем прокрутку сразу, если DOM готов
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initScroll, 0);
    }

    // Прокрутка после загрузки DOM
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initScroll, 100);
        setTimeout(initScroll, 300);
        setTimeout(initScroll, 600);
    });

    // Прокрутка после полной загрузки страницы
    window.addEventListener('load', function() {
        setTimeout(initScroll, 100);
        setTimeout(initScroll, 500);
    });

    // Дополнительная прокрутка через небольшие интервалы для надежности
    setTimeout(initScroll, 200);
    setTimeout(initScroll, 500);
    setTimeout(initScroll, 1000);

    // Периодическое получение новых сообщений для чата мероприятия
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

                // Обрабатываем сообщения по одному, чтобы избежать дублирования
                data.messages.forEach(function(msg) {
                    // Используем уже существующую функцию добавления сообщения
                    if (typeof window.addMessageToChat === 'function') {
                        // Функция addMessageToChat сама проверяет на дубликаты и обновляет lastMessageId
                        window.addMessageToChat(container, msg);
                    }
                });
                
                // После обработки всех сообщений убеждаемся, что lastMessageId обновлен
                if (data.messages.length > 0) {
                    const lastMsg = data.messages[data.messages.length - 1];
                    if (lastMsg && lastMsg.id) {
                        const lastId = parseInt(lastMsg.id, 10);
                        if (!isNaN(lastId) && (!window.lastMessageId || lastId > window.lastMessageId)) {
                            window.lastMessageId = lastId;
                            console.log('✅ Обновлен lastMessageId после получения обновлений:', window.lastMessageId);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка при получении новых сообщений чата мероприятия:', error);
            });
    }

    // Запускаем периодический опрос сервера (поллинг) раз в 3 секунды
    setInterval(fetchEventChatUpdates, 3000);

    // Автоматическая высота контейнера (только для десктопной версии)
    function adjustMessagesContainerHeight() {
        // На мобильных и планшетах используем flexbox, не устанавливаем фиксированную высоту
        if (window.innerWidth <= 1024) {
            return;
        }

        const container = document.getElementById('messages-container');
        if (container) {
            const headerHeight = document.querySelector('.chat-page-header')?.offsetHeight || 0;
            const cardHeaderHeight = document.querySelector('.chat-card .card-header')?.offsetHeight || 0;
            const cardFooterHeight = document.querySelector('.chat-card .card-footer')?.offsetHeight || 0;
            const totalHeight = window.innerHeight - headerHeight - cardHeaderHeight - cardFooterHeight;
            container.style.height = totalHeight + 'px';
        }
    }

    // Обработка изменения ориентации экрана
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

    // Обработка виртуальной клавиатуры на мобильных устройствах
    if (window.innerWidth <= 767) {
        let viewportHeight = window.innerHeight;

        // Функция для обеспечения видимости footer
        function ensureFooterVisible() {
            const footer = document.querySelector('.chat-card .card-footer');
            if (footer) {
                const rect = footer.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const safeAreaBottom = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--safe-area-inset-bottom') || '0');

                // Если footer скрывается за нижней границей экрана
                if (rect.bottom > viewportHeight - safeAreaBottom) {
                    footer.style.marginBottom = '0';
                    // Прокручиваем страницу, чтобы footer был виден
                    footer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'end'
                    });
                }
            }
        }

        window.addEventListener('resize', function() {
            const currentHeight = window.innerHeight;
            // Если высота уменьшилась более чем на 150px, вероятно открылась клавиатура
            if (currentHeight < viewportHeight - 150) {
                // Клавиатура открыта
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

        // Проверяем видимость footer при загрузке и после изменений
        window.addEventListener('load', ensureFooterVisible);
        window.addEventListener('scroll', ensureFooterVisible);
        setTimeout(ensureFooterVisible, 500);
    }

    // Функция для удаления чата мероприятия из чата
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
                    // Перенаправляем на список чатов мероприятий
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

    // Функция для блокировки и удаления чата мероприятия
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
                    // Перенаправляем на список чатов мероприятий
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

    // Функция для переключения описания мероприятия
    function toggleEventDescription(btn) {
        const cardText = btn.closest('.card-text');
        const shortSpan = cardText.querySelector('.event-description-short');
        const fullSpan = cardText.querySelector('.event-description-full');
        const showMore = btn.querySelector('.show-more');
        const showLess = btn.querySelector('.show-less');

        if (shortSpan.classList.contains('d-none')) {
            // Сворачиваем
            shortSpan.classList.remove('d-none');
            fullSpan.classList.add('d-none');
            showMore.classList.remove('d-none');
            showLess.classList.add('d-none');
        } else {
            // Разворачиваем
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
// Добавляем класс для страницы чата
$bodyClass = 'chat-page';
include __DIR__ . '/../layout.php';
?>
