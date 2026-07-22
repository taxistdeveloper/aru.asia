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
    }

    .chats-page-container {
        min-height: 100vh;
        background: #fff;
        padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
    }

    .chats-header {
        background: var(--aru-grad);
        padding: 12px 16px;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.25);
    }

    .chats-header-content {
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: 720px;
        margin: 0 auto;
    }

    .btn-back-modern {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        flex-shrink: 0;
        transition: background 0.15s;
    }

    .btn-back-modern:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
    }

    .btn-back-modern i {
        font-size: 1.25rem;
    }

    .chats-header-title h1 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 500;
        color: #fff;
        letter-spacing: 0.01em;
    }

    .chats-empty-state {
        text-align: center;
        padding: 64px 24px;
        max-width: 420px;
        margin: 0 auto;
        color: #6b7280;
    }

    .empty-icon-wrapper {
        width: 88px;
        height: 88px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: var(--aru-grad);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    }

    .empty-icon-wrapper i {
        font-size: 40px;
        color: #fff;
    }

    .chats-empty-state h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 8px;
    }

    .chats-empty-state p {
        font-size: 0.95rem;
        margin: 0 0 24px;
        line-height: 1.5;
    }

    .btn-empty-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--aru-grad);
        color: #fff;
        border-radius: 24px;
        text-decoration: none;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.35);
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
        align-items: stretch;
        border-bottom: 1px solid #eef0f6;
        background: #fff;
        transition: background 0.12s;
    }

    .chat-card-modern:hover {
        background: #f5f6fb;
    }

    .chat-card-link {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 0;
        padding: 12px 12px 12px 16px;
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
        font-size: 1.4rem;
        color: #fff;
    }

    .chat-info-modern {
        flex: 1;
        min-width: 0;
    }

    .chat-header-modern {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 3px;
    }

    .chat-title-modern {
        font-size: 16px;
        font-weight: 500;
        color: #1f2937;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
    }

    .chat-title-placeholder {
        color: #9ca3af;
        font-style: italic;
        font-weight: 400;
    }

    .chat-time-modern {
        font-size: 12px;
        color: #9ca3af;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .chat-time-modern.has-unread {
        color: #667eea;
        font-weight: 600;
    }

    .chat-meta-modern {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .chat-preview {
        font-size: 14px;
        color: #6b7280;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
        margin: 0;
    }

    .chat-unread-badge-modern {
        background: var(--aru-grad);
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

    .chat-actions-modern {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 6px;
        padding: 8px 12px 8px 0;
        flex-shrink: 0;
    }

    .chat-delete-btn-modern {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.12s, background 0.12s;
        background: #f3f4f6;
        color: #6b7280;
    }

    .chat-delete-btn-modern:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .chat-delete-btn-modern:active {
        transform: scale(0.94);
    }

    .chat-delete-btn-modern i {
        font-size: 14px;
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
        }

        .chat-card-link {
            padding: 12px 8px 12px 14px;
        }

        .chat-avatar-modern {
            width: 49px;
            height: 49px;
            margin-right: 12px;
        }

        .chat-title-modern {
            font-size: 16px;
        }

        .chat-actions-modern {
            padding-right: 10px;
        }
    }
</style>

<div class="chats-page-container">
    <div class="chats-header">
        <div class="chats-header-content">
            <a href="<?= BASE_URL ?>events" class="btn-back-modern" title="Назад">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="chats-header-title">
                <h1>Мои чаты</h1>
            </div>
        </div>
    </div>

    <?php if (empty($myEventChats)): ?>
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
                }
                if (!empty($event['event_date'])) {
                    $previewParts[] = date('d.m.Y H:i', strtotime($event['event_date']));
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
                        <button type="button" class="chat-delete-btn-modern"
                            data-event-id="<?= $event['id'] ?>"
                            onclick="deleteEventChat(<?= $event['id'] ?>, event)"
                            title="Удалить чат">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const BASE_URL = '<?= BASE_URL ?>';

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
include __DIR__ . '/../layout.php';
?>
