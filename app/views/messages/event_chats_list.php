<?php

/**
 * СПИСОК ЧАТОВ МЕРОПРИЯТИЙ — layout как WhatsApp, цвета Aru
 */

ob_start();

$myEventChats = $myEventChats ?? [];
$currentUserId = $currentUserId ?? null;
?>

<style>
    :root {
        --aru-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --aru-primary: #667eea;
        --chat-text: #111b21;
        --chat-muted: #667781;
        --chat-border: #e9edef;
        --chat-hover: #f5f6f6;
    }

    body.chats-list-page {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.chats-list-page .container-fluid,
    body.chats-list-page .container-fluid.px-3,
    body.chats-list-page .desktop-layout {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }

    .chats-page-container {
        min-height: calc(100vh - 56px);
        min-height: calc(100dvh - 56px);
        background: #fff;
        display: flex;
        flex-direction: column;
        padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
        box-sizing: border-box;
    }

    .chats-header {
        background: #fff;
        padding: 8px 12px 8px 6px;
        position: sticky;
        top: 0;
        z-index: 100;
        border-bottom: 1px solid var(--chat-border);
    }

    .chats-header-content {
        display: flex;
        align-items: center;
        gap: 4px;
        max-width: 720px;
        margin: 0 auto;
        min-height: 44px;
    }

    .btn-back-modern {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: #54656f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        flex-shrink: 0;
        background: transparent;
        border: none;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .btn-back-modern:hover {
        background: #f0f2f5;
        color: var(--aru-primary);
    }

    .btn-back-modern:active {
        background: #e9edef;
    }

    .btn-back-modern i {
        font-size: 1.35rem;
        line-height: 1;
        margin-left: -1px;
    }

    .chats-header-title {
        flex: 1;
        min-width: 0;
    }

    .chats-header-title h1 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 600;
        color: var(--chat-text);
        letter-spacing: 0.01em;
        line-height: 1.25;
    }

    .chats-header-count {
        margin: 1px 0 0;
        font-size: 0.75rem;
        color: var(--chat-muted);
        line-height: 1.2;
    }

    .chats-empty-wrap {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 20px 40px;
    }

    .chats-empty-state {
        text-align: center;
        max-width: 300px;
        margin: 0 auto;
        color: var(--chat-muted);
    }

    .empty-icon-wrapper {
        width: 88px;
        height: 88px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: #f0f2f8;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e4e7f0;
    }

    .empty-icon-wrapper i {
        font-size: 36px;
        color: #667eea;
    }

    .chats-empty-state h2 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--chat-text);
        margin: 0 0 8px;
    }

    .chats-empty-state p {
        font-size: 0.9rem;
        margin: 0 0 24px;
        line-height: 1.5;
    }

    .btn-empty-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        background: var(--aru-grad);
        color: #fff;
        border-radius: 24px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.92rem;
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.28);
    }

    .btn-empty-action:hover {
        filter: brightness(1.05);
        color: #fff;
    }

    .chats-list-modern {
        max-width: 720px;
        margin: 0 auto;
        background: #fff;
    }

    .chat-card-modern {
        position: relative;
        display: flex;
        align-items: center;
        background: #fff;
        transition: background 0.12s;
    }

    .chat-card-modern::after {
        content: '';
        position: absolute;
        left: 78px;
        right: 0;
        bottom: 0;
        height: 1px;
        background: var(--chat-border);
    }

    .chat-card-modern:last-child::after {
        display: none;
    }

    .chat-card-modern:hover,
    .chat-card-modern.is-menu-open {
        background: var(--chat-hover);
    }

    .chat-card-link {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 0;
        padding: 11px 4px 11px 16px;
        text-decoration: none;
        color: inherit;
    }

    .chat-avatar-modern {
        position: relative;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        margin-right: 14px;
        background: var(--aru-grad);
    }

    .chat-avatar-modern img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .chat-avatar-placeholder-modern {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--aru-grad);
    }

    .chat-avatar-placeholder-modern i {
        font-size: 1.25rem;
        color: #fff;
    }

    .chat-info-modern {
        flex: 1;
        min-width: 0;
        padding-right: 4px;
    }

    .chat-header-modern {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 2px;
    }

    .chat-title-modern {
        font-size: 16px;
        font-weight: 500;
        color: var(--chat-text);
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
        line-height: 1.3;
    }

    .chat-title-placeholder {
        color: #8696a0;
        font-style: italic;
        font-weight: 400;
    }

    .chat-time-modern {
        font-size: 12px;
        color: var(--chat-muted);
        flex-shrink: 0;
        white-space: nowrap;
        line-height: 1.3;
    }

    .chat-time-modern.has-unread {
        color: var(--aru-primary);
        font-weight: 600;
    }

    .chat-meta-modern {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .chat-preview {
        font-size: 13.5px;
        color: var(--chat-muted);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
        margin: 0;
        line-height: 1.35;
    }

    .chat-unread-badge-modern {
        background: var(--aru-grad);
        color: #fff;
        font-size: 11px;
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

    .chat-actions-modern {
        position: relative;
        display: flex;
        align-items: center;
        padding: 0 10px 0 2px;
        flex-shrink: 0;
    }

    .chat-menu-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: transparent;
        color: #8696a0;
        transition: background 0.12s, color 0.12s;
    }

    .chat-menu-btn:hover,
    .chat-card-modern.is-menu-open .chat-menu-btn {
        background: rgba(102, 126, 234, 0.1);
        color: var(--aru-primary);
    }

    .chat-menu-btn i {
        font-size: 18px;
        line-height: 1;
    }

    .chat-menu-dropdown {
        position: absolute;
        top: calc(100% - 4px);
        right: 8px;
        min-width: 180px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(11, 20, 26, 0.16);
        padding: 6px 0;
        z-index: 50;
        display: none;
        overflow: hidden;
    }

    .chat-menu-dropdown.show {
        display: block;
    }

    .chat-menu-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 16px;
        border: none;
        background: transparent;
        color: var(--chat-text);
        font-size: 14px;
        text-align: left;
        cursor: pointer;
        transition: background 0.12s;
    }

    .chat-menu-item:hover {
        background: #f0f2f5;
    }

    .chat-menu-item i {
        font-size: 15px;
        width: 18px;
        text-align: center;
        color: #667781;
    }

    .chat-menu-item.is-danger {
        color: #dc2626;
    }

    .chat-menu-item.is-danger i {
        color: #dc2626;
    }

    .success-notification {
        position: fixed;
        top: 16px;
        left: 50%;
        transform: translateX(-50%) translateY(-20px);
        z-index: 10000;
        opacity: 0;
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .success-notification.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .success-notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 18px;
        background: var(--aru-grad);
        color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
        font-weight: 500;
        font-size: 14px;
    }

    .success-notification-content i {
        font-size: 1.2rem;
    }

    @media (max-width: 767px) {
        .chats-page-container {
            padding-bottom: calc(90px + env(safe-area-inset-bottom, 0px));
            width: 100%;
        }

        .chats-header-content,
        .chats-list-modern {
            max-width: none;
            width: 100%;
        }

        .chat-card-modern {
            width: 100%;
            max-width: 100%;
        }

        .chat-card-link {
            padding: 11px 2px 11px 14px;
            -webkit-tap-highlight-color: transparent;
        }

        .chat-card-modern:active {
            background: var(--chat-hover);
        }

        .chat-avatar-modern {
            width: 50px;
            height: 50px;
            margin-right: 12px;
        }

        .chat-card-modern::after {
            left: 74px;
        }

        .chat-actions-modern {
            padding-right: 6px;
        }
    }
</style>

<?php
$chatsCount = count($myEventChats);
$chatsCountLabel = $chatsCount . ' ' . (
    $chatsCount % 10 === 1 && $chatsCount % 100 !== 11 ? 'чат' : (
        $chatsCount % 10 >= 2 && $chatsCount % 10 <= 4 && ($chatsCount % 100 < 10 || $chatsCount % 100 >= 20)
            ? 'чата' : 'чатов'
    )
);
?>

<div class="chats-page-container">
    <div class="chats-header">
        <div class="chats-header-content">
            <a href="<?= BASE_URL ?>events" class="btn-back-modern" title="Назад" aria-label="Назад">
                <i class="bi bi-chevron-left"></i>
            </a>
            <div class="chats-header-title">
                <h1>Мои чаты</h1>
                <?php if ($chatsCount > 0): ?>
                    <p class="chats-header-count"><?= $chatsCountLabel ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (empty($myEventChats)): ?>
        <div class="chats-empty-wrap">
            <div class="chats-empty-state">
                <div class="empty-icon-wrapper">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <h2>Пока нет чатов</h2>
                <p>Начните общение с кем-то интересным на странице мероприятий</p>
                <a href="<?= BASE_URL ?>events" class="btn-empty-action">
                    <i class="bi bi-calendar-plus"></i>
                    Найти мероприятие
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="chats-list-modern">
            <?php foreach ($myEventChats as $event): ?>
                <?php
                $unread = isset($event['unread_count']) ? (int)$event['unread_count'] : 0;
                $title = !empty($event['title']) ? $event['title'] : 'Без названия';
                $timeLabel = '';
                if (!empty($event['event_date'])) {
                    $ts = strtotime($event['event_date']);
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
                $previewParts = [];
                if (!empty($event['location'])) {
                    $previewParts[] = $event['location'];
                } elseif (!empty($event['event_date'])) {
                    // Время уже справа — в превью только дата, без дубля HH:mm
                    $previewParts[] = date('d.m.Y', strtotime($event['event_date']));
                }
                $preview = !empty($previewParts) ? implode(' · ', $previewParts) : 'Чат мероприятия';
                ?>
                <div class="chat-card-modern" id="chat-wrapper-<?= $event['id'] ?>">
                    <a href="<?= BASE_URL ?>messages/event?event_id=<?= $event['id'] ?>" class="chat-card-link">
                        <div class="chat-avatar-modern">
                            <?php if (!empty($event['photo'])): ?>
                                <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $event['photo'] ?>" alt="">
                            <?php else: ?>
                                <div class="chat-avatar-placeholder-modern">
                                    <i class="bi bi-calendar-event-fill"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="chat-info-modern">
                            <div class="chat-header-modern">
                                <h3 class="chat-title-modern <?= empty($event['title']) ? 'chat-title-placeholder' : '' ?>">
                                    <?= Helper::escape($title) ?>
                                </h3>
                                <?php if ($timeLabel !== ''): ?>
                                    <span class="chat-time-modern <?= $unread > 0 ? 'has-unread' : '' ?>">
                                        <?= $timeLabel ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="chat-meta-modern">
                                <p class="chat-preview"><?= Helper::escape($preview) ?></p>
                                <?php if ($unread > 0): ?>
                                    <span class="chat-unread-badge-modern">
                                        <?= $unread > 99 ? '99+' : $unread ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <div class="chat-actions-modern">
                        <button type="button"
                            class="chat-menu-btn"
                            aria-label="Действия"
                            aria-expanded="false"
                            onclick="toggleChatMenu(<?= (int)$event['id'] ?>, event)">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <div class="chat-menu-dropdown" id="chat-menu-<?= $event['id'] ?>">
                            <button type="button"
                                class="chat-menu-item is-danger"
                                onclick="deleteEventChat(<?= (int)$event['id'] ?>, event)">
                                <i class="bi bi-trash3"></i>
                                <span>Удалить чат</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const BASE_URL = '<?= BASE_URL ?>';

        function closeAllChatMenus() {
            document.querySelectorAll('.chat-menu-dropdown.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.chat-card-modern.is-menu-open').forEach(function(card) {
                card.classList.remove('is-menu-open');
            });
            document.querySelectorAll('.chat-menu-btn[aria-expanded="true"]').forEach(function(btn) {
                btn.setAttribute('aria-expanded', 'false');
            });
        }

        window.toggleChatMenu = function(eventId, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const menu = document.getElementById('chat-menu-' + eventId);
            const wrapper = document.getElementById('chat-wrapper-' + eventId);
            const btn = wrapper ? wrapper.querySelector('.chat-menu-btn') : null;
            if (!menu || !wrapper) return;

            const willOpen = !menu.classList.contains('show');
            closeAllChatMenus();

            if (willOpen) {
                menu.classList.add('show');
                wrapper.classList.add('is-menu-open');
                if (btn) btn.setAttribute('aria-expanded', 'true');
            }
        };

        document.addEventListener('click', function() {
            closeAllChatMenus();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAllChatMenus();
        });

        function showSuccessNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'success-notification';
            notification.innerHTML = `
                <div class="success-notification-content">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 10);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        window.deleteEventChat = function(eventId, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            closeAllChatMenus();

            const wrapper = document.getElementById('chat-wrapper-' + eventId);
            if (!wrapper) {
                alert('Элемент чата не найден.');
                return;
            }

            const chatTitle = wrapper.querySelector('.chat-title-modern')?.textContent?.trim() || 'этот чат';

            if (!confirm('Вы уверены, что хотите удалить чат "' + chatTitle + '"?\n\nВсе ваши сообщения в этом чате будут безвозвратно удалены.')) {
                return;
            }

            wrapper.style.opacity = '0.5';
            wrapper.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('event_id', eventId);

            fetch(BASE_URL + 'messages/deleteEventChat', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (data && data.success === true) {
                        showSuccessNotification('Чат успешно удален');
                        wrapper.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        wrapper.style.opacity = '0';
                        wrapper.style.transform = 'translateX(40px)';
                        setTimeout(function() {
                            wrapper.remove();
                            const list = document.querySelector('.chats-list-modern');
                            if (list && list.querySelectorAll('.chat-card-modern').length === 0) {
                                location.reload();
                            }
                        }, 300);
                    } else {
                        wrapper.style.opacity = '1';
                        wrapper.style.pointerEvents = 'auto';
                        alert(data?.error || data?.message || 'Не удалось удалить чат.');
                    }
                })
                .catch(error => {
                    wrapper.style.opacity = '1';
                    wrapper.style.pointerEvents = 'auto';
                    console.error('Ошибка при удалении чата:', error);
                    alert('Произошла ошибка при удалении чата. Попробуйте еще раз.');
                });
        };
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Мои чаты мероприятий';
$bodyClass = 'chats-list-page';
include __DIR__ . '/../layout.php';
?>
