<?php

/**
 * ЧАТ ДЛЯ СВИДАНИЯ — layout как WhatsApp, цвета Aru
 */

ob_start();
?>

<?php if ($selectedUserId): ?>
    <style>
        /* ========== Aru Chat (layout как WhatsApp, цвета бренда) ========== */
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

        /* Header — Aru gradient */
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

        .chat-page-header .btn {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 101;
            position: relative;
            flex-shrink: 0;
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
            margin-right: 4px;
            transition: background 0.15s;
        }

        .wa-back-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff !important;
        }

        .wa-back-btn i {
            font-size: 1.25rem;
        }

        #chat-view .chat-page-header .chat-header-user {
            gap: 0;
            cursor: default;
        }

        #chat-view .chat-page-header .chat-header-fio {
            min-width: 0;
            flex: 1;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 500;
            letter-spacing: 0.01em;
        }

        .wa-header-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            margin-right: 12px;
            border: 2px solid rgba(255, 255, 255, 0.35);
        }

        .wa-header-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 12px;
            border: 2px solid rgba(255, 255, 255, 0.35);
        }

        .wa-header-avatar-placeholder i {
            color: #fff;
            font-size: 1.4rem;
        }

        /* Chat card */
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

        /* Messages area — soft Aru tint */
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

        /* Date separator */
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

        /* Empty / blocked state */
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

        /* Messages */
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

        /* Tail — own (right) */
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

        /* Tail — other (left) */
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

        /* Input footer */
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

        .wa-block-alert {
            background: #fef3c7;
            color: #92400e;
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13.5px;
            margin: 0;
            text-align: center;
        }

        /* MOBILE */
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
                overflow: visible;
            }

            .chat-page-header .chat-header-fio {
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
<?php endif; ?>

<style>
    /* ========== Conversations list — Aru ========== */
    .wa-list-page .chat-page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 14px 16px;
        border: none;
        border-radius: 0 !important;
        margin: 0 !important;
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.25);
    }

    .wa-list-page .chat-page-header h2,
    .wa-list-page .chat-page-header h4 {
        color: #fff;
        font-weight: 500;
        margin: 0;
    }

    .wa-conversations-wrap {
        background: #fff;
        min-height: calc(100vh - 56px);
    }

    .wa-conversations-wrap .card {
        border: none;
        border-radius: 0;
        box-shadow: none;
        background: transparent;
    }

    .wa-conversations-wrap .card-header {
        display: none;
    }

    .wa-conversations-wrap .card-body {
        padding: 0;
    }

    .conversations-list {
        max-height: none;
        overflow-y: auto;
    }

    .wa-conv-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #eef0f6;
        text-decoration: none;
        color: inherit;
        background: #fff;
        transition: background 0.12s;
        gap: 0;
    }

    .wa-conv-item:hover {
        background: #f5f6fb;
        color: inherit;
        text-decoration: none;
    }

    .wa-conv-avatar {
        width: 49px;
        height: 49px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        margin-right: 15px;
    }

    .wa-conv-avatar-ph {
        width: 49px;
        height: 49px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 15px;
    }

    .wa-conv-avatar-ph i {
        color: #fff;
        font-size: 1.6rem;
    }

    .wa-conv-body {
        flex: 1;
        min-width: 0;
        border: none !important;
        padding: 0 !important;
    }

    .wa-conv-top {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 3px;
    }

    .wa-conv-name {
        font-size: 17px;
        font-weight: 500;
        color: #1f2937;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
    }

    .wa-conv-time {
        font-size: 12px;
        color: #9ca3af;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .wa-conv-time.has-unread {
        color: #667eea;
        font-weight: 600;
    }

    .wa-conv-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .wa-conv-preview {
        font-size: 14px;
        color: #6b7280;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
        margin: 0;
    }

    .wa-unread-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        min-width: 20px;
        height: 20px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        flex-shrink: 0;
        line-height: 1;
    }

    .wa-empty-list {
        text-align: center;
        padding: 48px 24px;
        color: #6b7280;
    }

    .wa-empty-list .btn-wa {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: #fff;
        border-radius: 24px;
        padding: 10px 24px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.35);
    }

    .wa-empty-list .btn-wa:hover {
        filter: brightness(1.05);
        color: #fff;
    }

    .list-group-item {
        position: relative;
    }

    .list-group-item a {
        flex: 1;
        min-width: 0;
        text-decoration: none;
        color: inherit;
    }
</style>

<div class="mobile-page-container <?= $selectedUserId ? '' : 'wa-list-page' ?>">
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

        <div class="wa-conversations-wrap">
            <div class="container-fluid px-0">
                <div class="row g-0">
                    <div class="col-lg-8 col-xl-6 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Диалоги с участниками</h5>
                            </div>
                            <div class="card-body conversations-list">
                                <?php if (empty($conversations)): ?>
                                    <?php
                                    $canWriteToOwner = false;
                                    if ($currentUserId != $date['user_id'] && $dateOwner) {
                                        $isOwnerBlockedByMe = $isOwnerBlockedByMe ?? false;
                                        $isOwnerBlockedByOther = $isOwnerBlockedByOther ?? false;
                                        $canWriteToOwner = !$isOwnerBlockedByMe && !$isOwnerBlockedByOther;
                                    }
                                    ?>
                                    <?php if ($canWriteToOwner): ?>
                                        <div class="wa-empty-list">
                                            <p class="mb-3">Нет диалогов. Начните общение с владельцем свидания!</p>
                                            <a href="<?= BASE_URL ?>messages/date?date_id=<?= $date['id'] ?>&user_id=<?= $date['user_id'] ?>"
                                                class="btn btn-wa">
                                                <i class="bi bi-chat-dots"></i> Написать владельцу
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="wa-empty-list">
                                            <p class="mb-0">Нет диалогов. Начните общение с участниками свидания!</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($conversations as $conv): ?>
                                            <?php
                                            $unread = isset($conv['unread_count']) ? (int)$conv['unread_count'] : 0;
                                            $timeLabel = '';
                                            if (!empty($conv['last_message_time'])) {
                                                $ts = strtotime($conv['last_message_time']);
                                                $today = strtotime('today');
                                                $yesterday = strtotime('yesterday');
                                                if ($ts >= $today) {
                                                    $timeLabel = date('H:i', $ts);
                                                } elseif ($ts >= $yesterday) {
                                                    $timeLabel = 'Вчера';
                                                } else {
                                                    $timeLabel = date('d.m.Y', $ts);
                                                }
                                            }
                                            ?>
                                            <a href="<?= BASE_URL ?>messages/date?date_id=<?= $date['id'] ?>&user_id=<?= $conv['other_user_id'] ?>"
                                                class="wa-conv-item list-group-item list-group-item-action border-0">
                                                <?php if (!empty($conv['photo'])): ?>
                                                    <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $conv['photo'] ?>"
                                                        class="wa-conv-avatar"
                                                        alt="">
                                                <?php else: ?>
                                                    <div class="wa-conv-avatar-ph">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="wa-conv-body flex-grow-1">
                                                    <div class="wa-conv-top">
                                                        <h6 class="wa-conv-name">
                                                            <?= Helper::escape($conv['other_user_full_name'] ?? $conv['other_user_email']) ?>
                                                        </h6>
                                                        <?php if ($timeLabel !== ''): ?>
                                                            <span class="wa-conv-time <?= $unread > 0 ? 'has-unread' : '' ?>">
                                                                <?= $timeLabel ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="wa-conv-bottom">
                                                        <p class="wa-conv-preview">
                                                            <?= Helper::escape($conv['last_message'] ?? 'Нет сообщений') ?>
                                                        </p>
                                                        <?php if ($unread > 0): ?>
                                                            <span class="wa-unread-badge">
                                                                <?= $unread > 99 ? '99+' : $unread ?>
                                                            </span>
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
    </div>

    <!-- Полноэкранный чат -->
    <?php if ($selectedUserId): ?>
        <?php
        $userModel = new User();
        $selectedUser = $userModel->findById($selectedUserId);
        $photoModel = new UserPhoto();
        $photos = $photoModel->getByUserId($selectedUserId);
        $firstPhoto = !empty($photos) ? $photos[0]['photo'] : null;

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
        <div id="chat-view" class="mobile-page-container">
            <!-- Заголовок -->
            <div class="chat-page-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center flex-grow-1 chat-header-user" style="min-width: 0;">
                        <a href="<?= BASE_URL ?>messages/dates-list" class="wa-back-btn" title="Назад">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <?php if ($firstPhoto): ?>
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $firstPhoto ?>"
                                class="wa-header-avatar"
                                alt="">
                        <?php else: ?>
                            <div class="wa-header-avatar-placeholder">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        <?php endif; ?>
                        <div class="chat-header-fio" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= Helper::escape($selectedUser['full_name'] ?? $selectedUser['email'] ?? 'Пользователь') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Чат -->
            <div class="card chat-card">
                <div class="card-body" id="messages-container">
                    <?php if ($isBlockedByOther): ?>
                        <div class="wa-empty-state">
                            <p>Собеседник вас заблокировал. Вы не можете видеть сообщения.</p>
                        </div>
                    <?php elseif ($isBlockedByMe): ?>
                        <div class="wa-empty-state">
                            <p>Вы заблокировали этого пользователя. Вы не можете видеть сообщения.</p>
                        </div>
                    <?php elseif (empty($messages)): ?>
                        <div class="wa-empty-state">
                            <p>Нет сообщений. Начните общение!</p>
                        </div>
                    <?php else: ?>
                        <?php
                        $lastDateKey = null;
                        foreach ($messages as $msg):
                            $dateKey = date('Y-m-d', strtotime($msg['created_at']));
                            if ($dateKey !== $lastDateKey):
                                $lastDateKey = $dateKey;
                        ?>
                                <div class="wa-date-sep" data-date-key="<?= $dateKey ?>">
                                    <span><?= $formatChatDate($msg['created_at']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="message-item <?= $msg['from_user_id'] == $currentUserId ? 'text-end' : 'text-start' ?>" data-message-id="<?= $msg['id'] ?>">
                                <div class="message-bubble <?= $msg['from_user_id'] == $currentUserId ? 'own' : 'other' ?>">
                                    <?php if ($msg['from_user_id'] != $currentUserId): ?>
                                        <div class="message-sender"><?= Helper::escape($msg['from_full_name'] ?? $msg['from_email'] ?? 'Пользователь') ?></div>
                                    <?php endif; ?>
                                    <div class="message-content"><?= nl2br(Helper::escape($msg['message'])) ?></div>
                                    <div class="message-meta">
                                        <span class="message-time"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                                        <?php if ($msg['from_user_id'] == $currentUserId): ?>
                                            <span class="wa-checks" title="Доставлено"><i class="bi bi-check2-all"></i></span>
                                        <?php endif; ?>
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
                <?php else: ?>
                    <div class="card-footer">
                        <div class="wa-block-alert" role="alert">
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
        window.currentUserId = <?= $currentUserId ?>;
        window.dateId = <?= $date['id'] ?>;

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
            const isOwnMessage = messageData.from_user_id == window.currentUserId;
            ensureDateSeparator(container, messageData.created_at);

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
            const senderHtml = isOwnMessage ? '' : `<div class="message-sender">${escapeHtml(senderName)}</div>`;
            const checksHtml = isOwnMessage ?
                '<span class="wa-checks" title="Доставлено"><i class="bi bi-check2-all"></i></span>' : '';

            const safeMessage = escapeHtml(messageData.message || '').replace(/\n/g, '<br>');

            messageItem.innerHTML = `
                <div class="message-bubble ${bubbleClass}">
                    ${senderHtml}
                    <div class="message-content">${safeMessage}</div>
                    <div class="message-meta">
                        <span class="message-time">${timeStr}</span>
                        ${checksHtml}
                    </div>
                </div>
            `;

            container.appendChild(messageItem);
            container.scrollTop = container.scrollHeight;
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

        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            setTimeout(() => {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 100);
        }

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
