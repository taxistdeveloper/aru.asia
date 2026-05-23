<?php

/**
 * СТРАНИЦА СПИСКА ЧАТОВ СВИДАНИЙ
 */

ob_start();

// Инициализируем переменные, если они не переданы
$myDateChats = $myDateChats ?? [];
$currentUserId = $currentUserId ?? null;
?>

<div class="chats-page-container">
    <div class="chats-header">
        <div class="chats-header-content">
            <a href="<?= BASE_URL ?>dates" class="btn-back-modern">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="chats-header-title">
                <h1>Мои чаты</h1>
                
            </div>
        </div>
    </div>

    <?php if (empty($myDateChats)): ?>
        <div class="chats-empty-state">
            <div class="empty-icon-wrapper">
                <i class="bi bi-chat-heart"></i>
            </div>
            <h2>Пока нет чатов</h2>
            <p>Начните общение с кем-то интересным на странице свиданий</p>
            <a href="<?= BASE_URL ?>dates" class="btn-empty-action">
                <i class="bi bi-calendar-plus"></i>
                Найти свидание
            </a>
        </div>
    <?php else: ?>
        <div class="chats-list-modern">
            <?php foreach ($myDateChats as $date): ?>
                <div class="chat-card-modern" id="chat-wrapper-<?= $date['id'] ?>">
                    <?php 
                    // Используем ID собеседника, если он определен, иначе используем владельца свидания
                    $chatUserId = $date['chat_participant_id'] ?? $date['user_id'];
                    ?>
                    <a href="<?= BASE_URL ?>messages/date?date_id=<?= $date['id'] ?>&user_id=<?= $chatUserId ?>" class="chat-card-link">
                        <div class="chat-avatar-modern">
                            <?php if (!empty($date['photo'])): ?>
                                <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $date['photo'] ?>" alt="<?= Helper::escape($date['title']) ?>">
                            <?php else: ?>
                                <div class="chat-avatar-placeholder-modern">
                                    <i class="bi bi-heart-fill"></i>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($date['unread_count']) && $date['unread_count'] > 0): ?>
                                <span class="chat-unread-badge-modern">
                                    <?= $date['unread_count'] > 99 ? '99+' : $date['unread_count'] ?>
                                </span>
                            <?php endif; ?>
                           
                        </div>
                        <div class="chat-info-modern">
                            <div class="chat-header-modern">
                                <?php if (!empty($date['title'])): ?>
                                    <!-- <h3 class="chat-title-modern"><?= Helper::escape($date['title']) ?></h3> -->
                                <?php else: ?>
                                    <h3 class="chat-title-modern chat-title-placeholder">Без названия</h3>
                                <?php endif; ?>
                                <span class="chat-time-modern">
                                    <?php
                                    $dateTime = strtotime($date['date_time']);
                                    $now = time();
                                    $diff = $now - $dateTime;
                                    $timeClass = '';
                                    $timeIcon = 'bi-calendar3';
                                    $dateText = date('d.m.Y', $dateTime);
                                    $timeText = date('H:i', $dateTime);
                                    
                                    if ($diff < 86400) {
                                        // Сегодня - показываем дату и время
                                        $timeClass = 'time-today';
                                    } elseif ($diff < 604800) {
                                        // На этой неделе
                                        $timeClass = 'time-week';
                                    } else {
                                        // Старше недели
                                        $timeClass = 'time-old';
                                    }
                                    ?>
                                    <span class="time-badge <?= $timeClass ?>">
                                        <i class="bi <?= $timeIcon ?>"></i>
                                        <span><?= $dateText ?> в <?= $timeText ?></span>
                                    </span>
                                </span>
                            </div>
                            <div class="chat-meta-modern">
                                <?php if (!empty($date['category_name'])): ?>
                                    <span class="chat-category-badge">
                                        <i class="bi bi-tag-fill"></i>
                                        <?= Helper::escape($date['category_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                       
                    </a>
                    <div class="chat-actions-modern">
                        <button type="button" class="chat-block-btn-modern"
                            data-date-id="<?= $date['id'] ?>"
                            data-user-id="<?= $chatUserId ?>"
                            onclick="blockAndDeleteDateChat(<?= $date['id'] ?>, <?= $chatUserId ?>, event)"
                            title="Заблокировать и удалить">
                            <i class="bi bi-lock-fill"></i>
                        </button>
                        <button type="button" class="chat-delete-btn-modern"
                            data-date-id="<?= $date['id'] ?>"
                            onclick="deleteDateChat(<?= $date['id'] ?>, event)"
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

        // Функция для блокировки и удаления чата свидания
        window.blockAndDeleteDateChat = function(dateId, userId, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Находим элемент чата для лучшего UX
            const wrapper = document.getElementById('chat-wrapper-' + dateId);
            if (!wrapper) {
                alert('Элемент чата не найден.');
                return;
            }

            if (!confirm('Вы уверены, что хотите заблокировать этот «чат»?\n\nПользователь будет заблокирован навсегда. Все ваши сообщения в этом чате будут безвозвратно удалены.')) {
                return;
            }

            // Показываем индикатор загрузки
            wrapper.style.opacity = '0.5';
            wrapper.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('date_id', dateId);
            formData.append('user_id', userId);

            console.log('Отправка запроса на блокировку и удаление чата:', dateId, userId);
            console.log('URL:', BASE_URL + 'messages/blockAndDeleteDateChat');

            fetch(BASE_URL + 'messages/blockAndDeleteDateChat', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Получен ответ:', response.status, response.statusText);
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.text().then(text => {
                        console.log('Текст ответа:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Ошибка парсинга JSON:', e, text);
                            throw new Error('Неверный формат ответа от сервера');
                        }
                    });
                })
                .then(data => {
                    console.log('Получены данные от сервера:', data);
                    
                    if (data && data.success === true) {
                        console.log('Успешно! Удаляем элемент из DOM');
                        
                        // Показываем уведомление об успешной блокировке
                        showSuccessNotification('Пользователь заблокирован и чат удален');
                        
                        // Удаляем элемент из списка с анимацией
                        wrapper.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        wrapper.style.opacity = '0';
                        wrapper.style.transform = 'scale(0.8) translateY(-20px)';
                        
                        setTimeout(function() {
                            wrapper.remove();

                            // Если список пуст, перезагружаем страницу
                            const list = document.querySelector('.chats-list-modern');
                            if (list && list.querySelectorAll('.chat-card-modern').length === 0) {
                                location.reload();
                            }
                        }, 400);
                    } else {
                        // Восстанавливаем видимость элемента при ошибке
                        wrapper.style.opacity = '1';
                        wrapper.style.pointerEvents = 'auto';
                        
                        const errorMsg = data?.error || data?.message || 'Не удалось заблокировать пользователя и удалить чат. Попробуйте еще раз.';
                        console.error('Ошибка блокировки и удаления чата:', errorMsg, data);
                        alert(errorMsg);
                    }
                })
                .catch(error => {
                    // Восстанавливаем видимость элемента при ошибке
                    wrapper.style.opacity = '1';
                    wrapper.style.pointerEvents = 'auto';
                    
                    console.error('Ошибка при блокировке и удалении чата:', error);
                    alert('Произошла ошибка при блокировке и удалении чата. Попробуйте еще раз.');
                });
        };

        // Функция для удаления чата свидания
        window.deleteDateChat = function(dateId, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Находим элемент чата для лучшего UX
            const wrapper = document.getElementById('chat-wrapper-' + dateId);
            if (!wrapper) {
                alert('Элемент чата не найден.');
                return;
            }

            const chatTitle = wrapper.querySelector('.chat-title-modern')?.textContent?.trim() || 'этот чат';
            
            if (!confirm('Вы уверены, что хотите удалить чат "' + chatTitle + '"?\n\nВсе ваши сообщения в этом чате будут безвозвратно удалены.')) {
                return;
            }

            // Показываем индикатор загрузки
            wrapper.style.opacity = '0.5';
            wrapper.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('date_id', dateId);

            console.log('Отправка запроса на удаление чата:', dateId);
            console.log('URL:', BASE_URL + 'messages/deleteDateChat');

            fetch(BASE_URL + 'messages/deleteDateChat', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Получен ответ:', response.status, response.statusText);
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.text().then(text => {
                        console.log('Текст ответа:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Ошибка парсинга JSON:', e, text);
                            throw new Error('Неверный формат ответа от сервера');
                        }
                    });
                })
                .then(data => {
                    console.log('Получены данные от сервера:', data);
                    
                    if (data && data.success === true) {
                        console.log('Успешно! Удаляем элемент из DOM');
                        
                        // Показываем уведомление об успешном удалении
                        showSuccessNotification('Чат успешно удален');
                        
                        // Удаляем элемент из списка с анимацией
                        wrapper.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        wrapper.style.opacity = '0';
                        wrapper.style.transform = 'scale(0.8) translateY(-20px)';
                        
                        setTimeout(function() {
                            wrapper.remove();

                            // Если список пуст, перезагружаем страницу
                            const list = document.querySelector('.chats-list-modern');
                            if (list && list.querySelectorAll('.chat-card-modern').length === 0) {
                                location.reload();
                            }
                        }, 400);
                    } else {
                        // Восстанавливаем видимость элемента при ошибке
                        wrapper.style.opacity = '1';
                        wrapper.style.pointerEvents = 'auto';
                        
                        const errorMsg = data?.error || data?.message || 'Не удалось удалить чат. Попробуйте еще раз.';
                        console.error('Ошибка удаления чата:', errorMsg, data);
                        alert(errorMsg);
                    }
                })
                .catch(error => {
                    // Восстанавливаем видимость элемента при ошибке
                    wrapper.style.opacity = '1';
                    wrapper.style.pointerEvents = 'auto';
                    
                    console.error('Ошибка при удалении чата:', error);
                    alert('Произошла ошибка при удалении чата. Попробуйте еще раз.');
                });
        };

        // Функция для показа уведомления об успехе
        function showSuccessNotification(message) {
            // Создаем элемент уведомления
            const notification = document.createElement('div');
            notification.className = 'success-notification';
            notification.innerHTML = `
                <div class="success-notification-content">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>${message}</span>
                </div>
            `;
            
            // Добавляем в body
            document.body.appendChild(notification);
            
            // Анимация появления
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            // Удаляем через 3 секунды
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    });
</script>

<style>
    /* Общие стили */
    .chats-page-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        background-attachment: fixed;
        padding-bottom: 80px;
        position: relative;
    }

    .chats-page-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Header */
    .chats-header {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        padding: 20px 24px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.6);
        position: sticky;
        top: 0;
        z-index: 100;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .chats-header-content {
        display: flex;
        align-items: center;
        gap: 16px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .btn-back-modern {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 
            0 4px 16px rgba(102, 126, 234, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
        flex-shrink: 0;
        position: relative;
        overflow: hidden;
    }

    .btn-back-modern::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn-back-modern:hover::before {
        width: 300px;
        height: 300px;
    }

    .btn-back-modern:hover {
        transform: translateX(-4px) scale(1.05);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-back-modern:active {
        transform: translateX(-2px) scale(0.98);
    }

    .btn-back-modern i {
        font-size: 20px;
    }

    .chats-header-title h1 {
        font-size: 32px;
        font-weight: 800;
        margin: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.5px;
        position: relative;
    }

    .chats-header-title h1::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }

    .chats-subtitle {
        font-size: 14px;
        color: #6b7280;
        margin: 4px 0 0 0;
    }

    /* Empty State */
    .chats-empty-state {
        text-align: center;
        padding: 80px 24px;
        max-width: 500px;
        margin: 60px auto;
    }

    .empty-icon-wrapper {
        width: 120px;
        height: 120px;
        margin: 0 auto 32px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .empty-icon-wrapper i {
        font-size: 56px;
        color: white;
    }

    .chats-empty-state h2 {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin: 0 0 12px 0;
    }

    .chats-empty-state p {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
        margin: 0 0 32px 0;
        line-height: 1.6;
    }

    .btn-empty-action {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        background: white;
        color: #667eea;
        border-radius: 16px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .btn-empty-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }

    .btn-empty-action i {
        font-size: 20px;
    }

    /* Список чатов */
    .chats-list-modern {
        padding: 24px 20px;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Карточка чата */
    .chat-card-modern {
        position: relative;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 
            0 8px 32px rgba(0, 0, 0, 0.12),
            0 2px 8px rgba(0, 0, 0, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.5);
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        animation: slideInUp 0.5s ease-out;
        animation-fill-mode: both;
    }

    .chat-card-modern:nth-child(1) { animation-delay: 0.05s; }
    .chat-card-modern:nth-child(2) { animation-delay: 0.1s; }
    .chat-card-modern:nth-child(3) { animation-delay: 0.15s; }
    .chat-card-modern:nth-child(4) { animation-delay: 0.2s; }
    .chat-card-modern:nth-child(5) { animation-delay: 0.25s; }
    .chat-card-modern:nth-child(n+6) { animation-delay: 0.3s; }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .chat-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .chat-card-modern:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 
            0 20px 60px rgba(102, 126, 234, 0.25),
            0 8px 24px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
        border-color: rgba(102, 126, 234, 0.3);
    }

    .chat-card-modern:hover::before {
        opacity: 1;
    }

    .chat-card-link {
        display: flex;
        align-items: center;
        padding: 20px;
        text-decoration: none;
        color: inherit;
        position: relative;
        z-index: 1;
    }

    /* Аватар */
    .chat-avatar-modern {
        position: relative;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        margin-right: 18px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        box-shadow: 
            0 8px 24px rgba(102, 126, 234, 0.4),
            inset 0 2px 8px rgba(255, 255, 255, 0.3);
        border: 4px solid #10b981;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .chat-card-modern:hover .chat-avatar-modern {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 
            0 12px 32px rgba(102, 126, 234, 0.5),
            inset 0 2px 8px rgba(255, 255, 255, 0.4);
        border-color: #059669;
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .chat-avatar-placeholder-modern i {
        font-size: 28px;
        color: white;
    }

    .chat-status-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 18px;
        height: 18px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: 3px solid white;
        border-radius: 50%;
        box-shadow: 
            0 2px 8px rgba(16, 185, 129, 0.5),
            0 0 0 2px rgba(16, 185, 129, 0.2),
            inset 0 1px 2px rgba(255, 255, 255, 0.3);
        z-index: 3;
        animation: pulse-green 2s ease-in-out infinite;
    }

    @keyframes pulse-green {
        0%, 100% {
            box-shadow: 
                0 2px 8px rgba(16, 185, 129, 0.5),
                0 0 0 2px rgba(16, 185, 129, 0.2),
                inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }
        50% {
            box-shadow: 
                0 4px 12px rgba(16, 185, 129, 0.6),
                0 0 0 4px rgba(16, 185, 129, 0.1),
                inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }
    }

    .chat-unread-badge-modern {
        position: absolute;
        top: -4px;
        right: -4px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 12px;
        min-width: 22px;
        text-align: center;
        line-height: 1.4;
        border: 3px solid white;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        z-index: 2;
    }

    /* Информация о чате */
    .chat-info-modern {
        flex: 1;
        min-width: 0;
    }

    .chat-header-modern {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 8px;
        gap: 8px;
    }

    .chat-title-modern {
        font-size: 19px;
        font-weight: 700;
        color: #111827;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        letter-spacing: -0.3px;
        line-height: 1.3;
        transition: color 0.3s ease;
    }

    .chat-title-placeholder {
        color: #9ca3af;
        font-style: italic;
        font-weight: 500;
    }

    .chat-card-modern:hover .chat-title-modern {
        color: #667eea;
    }

    .chat-time-modern {
        margin-left: 0;
        margin-top: 4px;
        flex-shrink: 0;
        width: 100%;
    }

    .time-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .time-badge i {
        font-size: 13px;
    }

    .time-badge span {
        line-height: 1;
    }

    .time-today {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        box-shadow: 
            0 4px 12px rgba(30, 64, 175, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .time-today i {
        color: #3b82f6;
    }

    .time-week {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        box-shadow: 
            0 4px 12px rgba(146, 64, 14, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .time-week i {
        color: #f59e0b;
    }

    .time-old {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #6b7280;
        box-shadow: 
            0 4px 12px rgba(107, 114, 128, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .time-old i {
        color: #9ca3af;
    }

    .chat-card-modern:hover .time-badge {
        transform: scale(1.08) translateY(-2px);
    }

    .chat-card-modern:hover .time-today {
        box-shadow: 
            0 6px 16px rgba(30, 64, 175, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .chat-card-modern:hover .time-week {
        box-shadow: 
            0 6px 16px rgba(146, 64, 14, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .chat-card-modern:hover .time-old {
        box-shadow: 
            0 6px 16px rgba(107, 114, 128, 0.25),
            inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .chat-meta-modern {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .chat-date-badge,
    .chat-category-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #6b7280;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        padding: 6px 12px;
        border-radius: 12px;
        font-weight: 500;
    }

    .chat-date-badge i,
    .chat-category-badge i {
        font-size: 12px;
        color: #667eea;
    }

    .chat-category-badge {
        background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
        color: #7c3aed;
    }

    .chat-category-badge i {
        color: #7c3aed;
    }

    /* Стрелка */
    .chat-arrow-modern {
        color: #d1d5db;
        font-size: 20px;
        margin-left: 12px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .chat-card-link:hover .chat-arrow-modern {
        transform: translateX(6px);
        color: #667eea;
    }

    /* Кнопки действий */
    .chat-actions-modern {
        position: absolute;
        right: 16px;
        top: 16px;
        display: flex;
        gap: 8px;
        z-index: 10;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .chat-card-modern:hover .chat-actions-modern {
        opacity: 1;
        pointer-events: auto;
    }

    .chat-block-btn-modern,
    .chat-delete-btn-modern {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .chat-block-btn-modern {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .chat-block-btn-modern:hover {
        background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%);
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(146, 64, 14, 0.4);
    }

    .chat-block-btn-modern:active {
        background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%);
        transform: scale(0.95);
    }

    .chat-block-btn-modern i {
        font-size: 18px;
        transition: transform 0.2s ease;
    }

    .chat-block-btn-modern:hover i {
        transform: rotate(15deg) scale(1.1);
    }

    .chat-delete-btn-modern {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
    }

    .chat-delete-btn-modern:hover {
        background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    .chat-delete-btn-modern:active {
        background: linear-gradient(135deg, #fca5a5 0%, #ef4444 100%);
        transform: scale(0.95);
    }

    .chat-delete-btn-modern:hover {
        background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    .chat-delete-btn-modern:active {
        background: linear-gradient(135deg, #fca5a5 0%, #ef4444 100%);
        transform: scale(0.95);
    }

    .chat-delete-btn-modern i {
        font-size: 18px;
        transition: transform 0.2s ease;
    }

    .chat-delete-btn-modern:hover i {
        transform: rotate(15deg) scale(1.1);
    }

    /* Мобильная версия */
    @media (max-width: 767px) {
        .chats-page-container {
            padding-bottom: 100px;
        }

        .chats-header {
            padding: 12px 16px;
        }

        .chats-header-title h1 {
            font-size: 22px;
        }

        .chats-subtitle {
            font-size: 12px;
        }

        .btn-back-modern {
            width: 40px;
            height: 40px;
        }

        .btn-back-modern i {
            font-size: 18px;
        }

        .chats-list-modern {
            padding: 16px;
            gap: 12px;
        }

        .chat-card-link {
            padding: 16px;
        }

        .chat-avatar-modern {
            width: 56px;
            height: 56px;
            margin-right: 12px;
        }

        .chat-title-modern {
            font-size: 16px;
        }

        .chat-time-modern {
            font-size: 12px;
        }

        .chat-date-badge,
        .chat-category-badge {
            font-size: 12px;
            padding: 4px 10px;
        }

        .chat-actions-modern {
            right: 12px;
            top: 12px;
            opacity: 1;
            pointer-events: auto;
        }

        .chat-block-btn-modern,
        .chat-delete-btn-modern {
            width: 36px;
            height: 36px;
        }

        .empty-icon-wrapper {
            width: 100px;
            height: 100px;
        }

        .empty-icon-wrapper i {
            font-size: 48px;
        }

        .chats-empty-state h2 {
            font-size: 20px;
        }

        .chats-empty-state p {
            font-size: 14px;
        }
    }

    /* Десктоп версия */
    @media (min-width: 768px) {
        .chats-list-modern {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
            gap: 24px;
            padding: 40px;
        }

        .chat-card-link {
            padding: 24px;
        }

        .chat-avatar-modern {
            width: 72px;
            height: 72px;
            margin-right: 20px;
        }

        .chat-title-modern {
            font-size: 20px;
        }

        .chat-time-modern {
            font-size: 14px;
        }
    }

    /* Уведомление об успешном удалении */
    .success-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        opacity: 0;
        transform: translateX(400px);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .success-notification.show {
        opacity: 1;
        transform: translateX(0);
    }

    .success-notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 24px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-radius: 16px;
        box-shadow: 
            0 8px 32px rgba(16, 185, 129, 0.4),
            0 4px 16px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-weight: 600;
        font-size: 15px;
        min-width: 250px;
    }

    .success-notification-content i {
        font-size: 24px;
        color: white;
        animation: checkmark 0.6s ease-out;
    }

    @keyframes checkmark {
        0% {
            transform: scale(0) rotate(-45deg);
        }
        50% {
            transform: scale(1.2) rotate(0deg);
        }
        100% {
            transform: scale(1) rotate(0deg);
        }
    }

    .success-notification-content span {
        flex: 1;
    }

    /* Мобильная версия уведомления */
    @media (max-width: 767px) {
        .success-notification {
            top: 16px;
            right: 16px;
            left: 16px;
            transform: translateY(-100px);
        }

        .success-notification.show {
            transform: translateY(0);
        }

        .success-notification-content {
            padding: 14px 20px;
            font-size: 14px;
            min-width: auto;
        }

        .success-notification-content i {
            font-size: 20px;
        }
    }
</style>

<?php
$content = ob_get_clean();
$title = 'Мои чаты свиданий';
include __DIR__ . '/../layout.php';
?>
