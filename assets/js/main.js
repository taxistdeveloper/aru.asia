/**
 * ОСНОВНОЙ JAVASCRIPT ФАЙЛ
 *
 * Здесь общие функции для всего приложения
 */

// Регистрация Service Worker для PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        const swPath = typeof BASE_URL !== 'undefined' ? BASE_URL + 'service-worker.js' : '/aru-app/service-worker.js';
        navigator.serviceWorker.register(swPath)
            .then(function(registration) {
                console.log('Service Worker зарегистрирован:', registration.scope);

                // Проверяем обновления service worker каждые 60 секунд
                setInterval(function() {
                    registration.update();
                }, 60000);

                // Обработка обновления service worker
                registration.addEventListener('updatefound', function() {
                    const newWorker = registration.installing;
                    console.log('Найдена новая версия Service Worker');

                    newWorker.addEventListener('statechange', function() {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // Новая версия установлена, перезагружаем страницу для активации
                            console.log('Новая версия Service Worker установлена. Перезагрузка страницы...');
                            window.location.reload();
                        }
                    });
                });
            })
            .catch(function(error) {
                console.error('Ошибка регистрации Service Worker:', error);
            });
    });
}

/**
 * Функция для ручного обновления Service Worker
 * Можно вызвать из консоли браузера: updateServiceWorker()
 */
window.updateServiceWorker = async function() {
    if ('serviceWorker' in navigator) {
        try {
            const registrations = await navigator.serviceWorker.getRegistrations();
            for (let registration of registrations) {
                await registration.unregister();
                console.log('Service Worker отменен');
            }

            // Перезагружаем страницу для повторной регистрации
            console.log('Перезагрузка страницы для обновления Service Worker...');
            window.location.reload();
        } catch (error) {
            console.error('Ошибка при обновлении Service Worker:', error);
        }
    } else {
        console.log('Service Worker не поддерживается в этом браузере');
    }
};

// Инициализация приложения
document.addEventListener('DOMContentLoaded', function() {
    console.log('Tanisu App загружен');

    // Прокрутка к последнему сообщению в чате
    const messagesContainer = document.querySelector('.card-body[style*="overflow-y"]');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

});

// PWA: после загрузки страницы и (по возможности) активации Service Worker —
// иначе Chrome может не успеть выдать beforeinstallprompt до клика «Установить».
window.addEventListener('load', function() {
    const runPwaInit = function() {
        if (typeof initializePWAInstall === 'function') {
            initializePWAInstall();
        }
    };
    if ('serviceWorker' in navigator) {
        Promise.race([
            navigator.serviceWorker.ready,
            new Promise(function(resolve) {
                setTimeout(resolve, 4000);
            })
        ]).then(runPwaInit).catch(runPwaInit);
    } else {
        runPwaInit();
    }
});

// Функция для получения геолокации
function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => resolve({
                    lat: position.coords.latitude,
                    lon: position.coords.longitude
                }),
                error => reject(error)
            );
        } else {
            reject(new Error('Геолокация не поддерживается'));
        }
    });
}

// Валидация форм
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (form && !form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    return true;
}

// Система уведомлений о новых сообщениях
(function() {
    let lastCheckTime = new Date().toISOString();
    let checkInterval = null;
    let lastKnownUnreadCount = 0;
    let lastKnownUnreadEventsCount = null; // null = ещё не инициализировано (не пушим при первой загрузке)
    let lastNotifiedEventMessageId = null;

    function isOpenEventChat(eventId) {
        if (!eventId) return false;
        try {
            const params = new URLSearchParams(window.location.search);
            const openId = params.get('event_id');
            const path = window.location.pathname || '';
            return path.indexOf('/messages/event') !== -1 && String(openId) === String(eventId);
        } catch (e) {
            return false;
        }
    }

    function showEventMessagePush(latest) {
        if (!latest || !latest.event_id) return;
        if (isOpenEventChat(latest.event_id)) return;
        if (latest.id && String(latest.id) === String(lastNotifiedEventMessageId)) return;
        if (!('Notification' in window) || Notification.permission !== 'granted') return;

        lastNotifiedEventMessageId = latest.id || null;

        const eventTitle = latest.event_title || 'мероприятии';
        const fromName = latest.from_name || 'Пользователь';
        const messageText = (latest.message || '').toString();
        const body = fromName + ': ' + (messageText.length > 100 ? messageText.slice(0, 100) + '…' : messageText);
        const tag = 'event_message_' + latest.event_id + '_' + (latest.from_user_id || '0');
        const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'messages/event?event_id=' + latest.event_id;
        const notificationData = {
            type: 'event_message',
            event_id: latest.event_id,
            from_user_id: latest.from_user_id,
            notification_tag: tag,
            url: url
        };

        const options = {
            body: body,
            tag: tag,
            renotify: true,
            data: notificationData,
            icon: (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'assets/images/icon-192x192.png'
        };

        if ('serviceWorker' in navigator && navigator.serviceWorker.ready) {
            navigator.serviceWorker.ready.then(function(registration) {
                return registration.showNotification('Сообщение: ' + eventTitle, options);
            }).catch(function() {
                try { new Notification('Сообщение: ' + eventTitle, options); } catch (e) { /* ignore */ }
            });
        } else {
            try { new Notification('Сообщение: ' + eventTitle, options); } catch (e) { /* ignore */ }
        }
    }

    // Проверка новых сообщений
    function checkNewMessages() {
        // Проверяем только если пользователь авторизован
        if (typeof BASE_URL === 'undefined') return;

        // Для бейджа всегда берём полное число непрочитанных (без last_check)
        fetch(BASE_URL + 'messages/unread')
            .then(response => response.json())
            .then(data => {
                const count = data.count || 0;
                const hadNewSinceLastPoll = count > lastKnownUnreadCount;
                lastKnownUnreadCount = count;
                updateMessagesBadge(count);

                // Если есть новые сообщения и мы на странице сообщений, обновляем список
                if (hadNewSinceLastPoll && count > 0 && window.location.pathname.includes('messages')) {
                    updateMessagesList();
                }

                // Если мы на странице свиданий или мероприятий, обновляем badge для каждого свидания/мероприятия
                if (window.location.pathname.includes('dates') || window.location.pathname.includes('events')) {
                    updateDatesEventsBadges();
                }

                // Обновляем время последней проверки (для подгрузки новых сообщений в открытый чат)
                lastCheckTime = new Date().toISOString();

                // Обновляем синие/серые галочки прочтения в открытом чате
                updateReadReceipts();
            })
            .catch(error => {
                console.error('Ошибка при проверке новых сообщений:', error);
            });

        // Обновляем badge для свиданий в нижней навигации
        fetch(BASE_URL + 'messages/unread-dates-total')
            .then(response => response.json())
            .then(data => {
                const count = data.count || 0;
                updateDatesBadge(count);
            })
            .catch(error => {
                console.error('Ошибка при проверке непрочитанных сообщений из свиданий:', error);
            });

        // Обновляем badge для мероприятий + push создателю при новом сообщении
        fetch(BASE_URL + 'messages/unread-events-total')
            .then(response => response.json())
            .then(data => {
                const count = data.count || 0;
                const hadNewEvents = lastKnownUnreadEventsCount !== null && count > lastKnownUnreadEventsCount;
                lastKnownUnreadEventsCount = count;
                updateEventsBadge(count);

                if (hadNewEvents && count > 0 && data.latest) {
                    showEventMessagePush(data.latest);
                }
            })
            .catch(error => {
                console.error('Ошибка при проверке непрочитанных сообщений из мероприятий:', error);
            });
    }

    // Обновление badge для свиданий и мероприятий на страницах dates и events
    function updateDatesEventsBadges() {
        // Получаем все ссылки на чаты со свиданиями
        const dateChatLinks = document.querySelectorAll('a[href*="messages/date?date_id="]');
        dateChatLinks.forEach(link => {
            const urlParams = new URLSearchParams(link.getAttribute('href').split('?')[1]);
            const dateId = urlParams.get('date_id');
            if (dateId) {
                fetch(BASE_URL + 'messages/unread-date?date_id=' + dateId + '&last_check=' + encodeURIComponent(lastCheckTime))
                    .then(response => response.json())
                    .then(data => {
                        const count = data.count || 0;
                        updateChatLinkBadge(link, count);
                    })
                    .catch(error => {
                        console.error('Ошибка при проверке непрочитанных сообщений для свидания:', error);
                    });
            }
        });

        // Получаем все ссылки на чаты с мероприятиями
        const eventChatLinks = document.querySelectorAll('a[href*="messages/event?event_id="]');
        eventChatLinks.forEach(link => {
            const urlParams = new URLSearchParams(link.getAttribute('href').split('?')[1]);
            const eventId = urlParams.get('event_id');
            if (eventId) {
                fetch(BASE_URL + 'messages/unread-event?event_id=' + eventId + '&last_check=' + encodeURIComponent(lastCheckTime))
                    .then(response => response.json())
                    .then(data => {
                        const count = data.count || 0;
                        updateChatLinkBadge(link, count);
                    })
                    .catch(error => {
                        console.error('Ошибка при проверке непрочитанных сообщений для мероприятия:', error);
                    });
            }
        });
    }

    // Обновление badge на ссылке чата
    function updateChatLinkBadge(link, count) {
        let badge = link.querySelector('.badge');
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                badge.style.cssText = 'font-size: 0.7rem; min-width: 18px; padding: 2px 5px;';
                link.classList.add('position-relative');
                link.appendChild(badge);
            }
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
        } else {
            if (badge) {
                badge.style.display = 'none';
            }
        }
    }

    // Функция для сохранения состояния бейджа в localStorage
    function saveBadgeState(count, isVisible) {
        if (typeof Storage !== 'undefined') {
            localStorage.setItem('messagesBadgeCount', count.toString());
            localStorage.setItem('messagesBadgeVisible', isVisible ? 'true' : 'false');
        }
    }

    // Функция для восстановления состояния бейджа из localStorage
    function restoreBadgeState() {
        if (typeof Storage !== 'undefined') {
            const savedCount = localStorage.getItem('messagesBadgeCount');
            const isManuallyHidden = localStorage.getItem('messagesBadgeManuallyHidden') === 'true';

            // Восстанавливаем бейдж только если он не был скрыт вручную и есть сохраненное значение
            if (!isManuallyHidden && savedCount) {
                const count = parseInt(savedCount, 10);
                if (count > 0) {
                    const badgeMobile = document.getElementById('messages-badge');
                    const badgeDesktop = document.getElementById('messages-badge-desktop');
                    const badgeText = count > 99 ? '99+' : count.toString();

                    console.log('Восстанавливаю бейдж:', { count, badgeMobile: !!badgeMobile, badgeDesktop: !!badgeDesktop });

                    if (badgeMobile) {
                        badgeMobile.textContent = badgeText;
                        badgeMobile.style.display = 'block';
                        badgeMobile.style.visibility = 'visible';
                        badgeMobile.style.opacity = '1';
                    } else {
                        console.warn('Элемент messages-badge не найден в DOM');
                    }
                    if (badgeDesktop) {
                        badgeDesktop.textContent = badgeText;
                        badgeDesktop.style.display = 'block';
                        badgeDesktop.style.visibility = 'visible';
                        badgeDesktop.style.opacity = '1';
                    } else {
                        console.warn('Элемент messages-badge-desktop не найден в DOM');
                    }
                    return count;
                }
            }
        }
        return 0;
    }

    // Обновление badge с количеством непрочитанных сообщений
    // count с сервера — источник истины: 0 = скрыть, >0 = показать
    function updateMessagesBadge(count) {
        const badgeMobile = document.getElementById('messages-badge');
        const badgeDesktop = document.getElementById('messages-badge-desktop');

        let isManuallyHidden = localStorage.getItem('messagesBadgeManuallyHidden') === 'true';
        const savedCount = localStorage.getItem('messagesBadgeCount');
        const lastCount = savedCount ? parseInt(savedCount, 10) : 0;

        // Новые непрочитанные снова показывают бейдж (только если число выросло)
        if (count > 0 && count > lastCount) {
            isManuallyHidden = false;
            localStorage.setItem('messagesBadgeManuallyHidden', 'false');
        }

        // Все прочитано — сразу убираем бейдж и сбрасываем кэш
        if (count === 0) {
            if (badgeMobile) badgeMobile.style.display = 'none';
            if (badgeDesktop) badgeDesktop.style.display = 'none';
            saveBadgeState(0, false);
            localStorage.setItem('messagesBadgeManuallyHidden', 'false');
            return;
        }

        const shouldShow = !isManuallyHidden;
        const badgeText = count > 99 ? '99+' : count.toString();

        if (shouldShow) {
            if (badgeMobile) {
                badgeMobile.textContent = badgeText;
                badgeMobile.style.display = 'block';
                badgeMobile.style.visibility = 'visible';
                badgeMobile.style.opacity = '1';
            }
            if (badgeDesktop) {
                badgeDesktop.textContent = badgeText;
                badgeDesktop.style.display = 'block';
                badgeDesktop.style.visibility = 'visible';
                badgeDesktop.style.opacity = '1';
            }
            saveBadgeState(count, true);
        } else {
            if (badgeMobile) badgeMobile.style.display = 'none';
            if (badgeDesktop) badgeDesktop.style.display = 'none';
            saveBadgeState(count, false);
        }
    }

    // Закрыть системные push по диалогу (и общий тег), когда пользователь открыл чат
    function closeDialogPushNotifications(otherUserId) {
        if (!('serviceWorker' in navigator) || !navigator.serviceWorker.ready) return;

        navigator.serviceWorker.ready.then(function(registration) {
            const tags = ['aru-notification'];
            if (otherUserId) {
                tags.push('message_' + otherUserId);
            }

            tags.forEach(function(tag) {
                registration.getNotifications({ tag: tag }).then(function(notifications) {
                    notifications.forEach(function(notification) {
                        notification.close();
                    });
                });
            });

            // На случай кастомных notification_tag — закрываем все message-* от этого пользователя
            if (otherUserId) {
                registration.getNotifications().then(function(notifications) {
                    notifications.forEach(function(notification) {
                        const data = notification.data || {};
                        if (data.type === 'message' && String(data.from_user_id) === String(otherUserId)) {
                            notification.close();
                        }
                    });
                });
            }
        }).catch(function() { /* ignore */ });
    }

    // Закрыть push по чату мероприятия, когда пользователь открыл этот чат
    function closeEventPushNotifications(eventId) {
        if (!eventId) return;
        if (!('serviceWorker' in navigator) || !navigator.serviceWorker.ready) return;

        navigator.serviceWorker.ready.then(function(registration) {
            registration.getNotifications().then(function(notifications) {
                notifications.forEach(function(notification) {
                    const data = notification.data || {};
                    if (data.type === 'event_message' && String(data.event_id) === String(eventId)) {
                        notification.close();
                    }
                });
            });
        }).catch(function() { /* ignore */ });
    }

    // Экспорт: обновить бейдж сразу после прочтения диалога
    window.refreshMessagesBadge = function() {
        if (typeof BASE_URL === 'undefined') return;
        fetch(BASE_URL + 'messages/unread')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                const count = data.count || 0;
                lastKnownUnreadCount = count;
                updateMessagesBadge(count);
            })
            .catch(function() { /* ignore */ });
    };

    // Скрытие бейджа при клике на ссылку уведомлений
    function hideMessagesBadge() {
        const badgeMobile = document.getElementById('messages-badge');
        const badgeDesktop = document.getElementById('messages-badge-desktop');
        if (badgeMobile) badgeMobile.style.display = 'none';
        if (badgeDesktop) badgeDesktop.style.display = 'none';

        // Сохраняем флаг, что бейдж был скрыт вручную
        localStorage.setItem('messagesBadgeManuallyHidden', 'true');
        localStorage.setItem('messagesBadgeVisible', 'false');
    }

    // Обновление badge для свиданий в нижней навигации
    function updateDatesBadge(count) {
        const badgeDates = document.getElementById('dates-badge');

        if (count > 0) {
            if (badgeDates) {
                badgeDates.textContent = count > 99 ? '99+' : count;
                badgeDates.style.display = 'block';
            }
        } else {
            if (badgeDates) badgeDates.style.display = 'none';
        }
    }

    // Обновление badge для мероприятий в нижней навигации
    function updateEventsBadge(count) {
        const badgeEvents = document.getElementById('events-badge');

        if (count > 0) {
            if (badgeEvents) {
                badgeEvents.textContent = count > 99 ? '99+' : count;
                badgeEvents.style.display = 'block';
            }
        } else {
            if (badgeEvents) badgeEvents.style.display = 'none';
        }
    }

    // Обновление списка сообщений без перезагрузки страницы
    function updateMessagesList() {
        const selectedUserId = window.selectedUserId || new URLSearchParams(window.location.search).get('user_id');
        if (!selectedUserId) return;

        fetch(BASE_URL + 'messages/new?last_check=' + encodeURIComponent(lastCheckTime))
            .then(response => response.json())
            .then(data => {
                const messages = data.messages || [];
                if (messages.length > 0) {
                    // Добавляем новые сообщения в список
                    const messagesContainer = document.getElementById('messages-container') ||
                                             document.querySelector('.card-body[style*="overflow-y"]');
                    if (messagesContainer) {
                        let hasNewMessages = false;
                        messages.forEach(msg => {
                            // Проверяем, не добавлено ли уже это сообщение
                            const existingMsg = messagesContainer.querySelector(`[data-message-id="${msg.id}"]`);
                            if (!existingMsg) {
                                // Добавляем только сообщения из текущего диалога
                                if (msg.from_user_id == selectedUserId || msg.to_user_id == selectedUserId) {
                                    window.addMessageToChat(messagesContainer, msg);
                                    hasNewMessages = true;
                                }
                            }
                        });
                        // Прокручиваем вниз если есть новые сообщения
                        if (hasNewMessages) {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            // Собеседник смотрит чат — помечаем входящие прочитанными
                            markOpenChatAsRead();
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка при обновлении списка сообщений:', error);
            });
    }

    function markOpenChatAsRead() {
        if (typeof BASE_URL === 'undefined') return;

        const params = new URLSearchParams(window.location.search);
        const otherUserId = window.selectedUserId || params.get('user_id');
        const dateId = window.dateId || params.get('date_id');
        const eventId = window.eventId || params.get('event_id');

        if (!otherUserId && !eventId) return;

        const body = new URLSearchParams();
        if (otherUserId) body.set('other_user_id', otherUserId);
        if (dateId) body.set('date_id', dateId);
        if (eventId) body.set('event_id', eventId);

        fetch(BASE_URL + 'messages/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString()
        }).catch(function() { /* ignore */ });
    }

    // Серые → синие галочки, когда собеседник открыл чат
    function updateReadReceipts() {
        if (typeof BASE_URL === 'undefined') return;

        const params = new URLSearchParams(window.location.search);
        const otherUserId = window.selectedUserId || params.get('user_id');
        const dateId = window.dateId || params.get('date_id');
        const eventId = window.eventId || params.get('event_id');

        if (!otherUserId && !eventId) return;

        const query = new URLSearchParams();
        if (otherUserId) query.set('other_user_id', otherUserId);
        if (dateId) query.set('date_id', dateId);
        if (eventId) query.set('event_id', eventId);

        fetch(BASE_URL + 'messages/read-receipts?' + query.toString())
            .then(function(response) { return response.json(); })
            .then(function(data) {
                const ids = data.ids || [];
                if (!ids.length) return;

                ids.forEach(function(id) {
                    const item = document.querySelector('[data-message-id="' + id + '"]');
                    if (!item) return;
                    const checks = item.querySelector('.wa-checks');
                    if (checks && !checks.classList.contains('read')) {
                        checks.classList.add('read');
                        checks.setAttribute('title', 'Прочитано');
                    }
                });
            })
            .catch(function() { /* ignore */ });
    }

    // Fallback: только если страница чата сама не задала addMessageToChat
    if (typeof window.addMessageToChat !== 'function') {
        window.addMessageToChat = function(container, msg) {
            const isOwnMessage = msg.from_user_id == (window.currentUserId || 0);
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-item ' + (isOwnMessage ? 'text-end' : 'text-start');
            messageDiv.setAttribute('data-message-id', msg.id);

            const date = new Date(msg.created_at);
            const timeStr = date.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const empty = container.querySelector('.wa-empty-state, .text-muted.text-center');
            if (empty) empty.remove();

            const senderName = msg.from_full_name || msg.from_email || '';
            const senderHtml = (!isOwnMessage && senderName)
                ? `<div class="message-sender">${escapeHtml(senderName)}</div>`
                : '';
            const checksHtml = isOwnMessage
                ? (() => {
                    const isMsgRead = !!(msg.is_read == 1 || msg.is_read === true || msg.is_read === '1');
                    return `<span class="wa-checks${isMsgRead ? ' read' : ''}" title="${isMsgRead ? 'Прочитано' : 'Доставлено'}"><i class="bi bi-check2-all"></i></span>`;
                })()
                : '';
            const safeMessage = escapeHtml(msg.message || '').replace(/\n/g, '<br>');

            messageDiv.innerHTML = `
                <div class="message-bubble ${isOwnMessage ? 'own' : 'other'}">
                    ${senderHtml}
                    <div class="message-content">${safeMessage}</div>
                    <div class="message-meta">
                        <span class="message-time">${timeStr}</span>
                        ${checksHtml}
                    </div>
                </div>
            `;

            container.appendChild(messageDiv);
        };
    }

    // Экранирование HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Запрос разрешения на уведомления
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Проверяем наличие BASE_URL
        if (typeof BASE_URL === 'undefined') return;

        // Запрашиваем разрешение на уведомления
        requestNotificationPermission();

        // Восстанавливаем состояние бейджа из localStorage при загрузке страницы
        restoreBadgeState();

        // Открыт конкретный диалог — сообщения уже помечены прочитанными на сервере
        const pageParams = new URLSearchParams(window.location.search);
        const openDialogUserId = pageParams.get('user_id');
        const openEventId = pageParams.get('event_id');
        const isMessagesPage = window.location.pathname.includes('messages');
        if (isMessagesPage && openDialogUserId) {
            // Клик по ссылке диалога мог выставить manuallyHidden — сбрасываем,
            // чтобы бейдж отражал оставшиеся непрочитанные после прочтения
            localStorage.setItem('messagesBadgeManuallyHidden', 'false');
            closeDialogPushNotifications(openDialogUserId);
            if (typeof window.refreshMessagesBadge === 'function') {
                window.refreshMessagesBadge();
            }
        }
        if (isMessagesPage && openEventId && window.location.pathname.indexOf('/messages/event') !== -1) {
            closeEventPushNotifications(openEventId);
        }

        // Добавляем обработчики клика на ссылки уведомлений для скрытия бейджа
        // Бейдж скрывается только при клике на «Уведомления» (список), не при входе в диалог
        const messagesLinks = document.querySelectorAll('a[href*="messages"]');
        messagesLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                try {
                    const href = link.getAttribute('href') || '';
                    const url = new URL(href, window.location.origin);
                    // Вход в конкретный диалог / чат — не прячем «насовсем», сервер обновит счётчик
                    if (url.searchParams.has('user_id') || url.searchParams.has('date_id') || url.searchParams.has('event_id')) {
                        return;
                    }
                    if (url.pathname.indexOf('/messages/date') !== -1 || url.pathname.indexOf('/messages/event') !== -1) {
                        return;
                    }
                } catch (e) { /* ignore */ }
                hideMessagesBadge();
            });
        });

        // Первая проверка сразу
        checkNewMessages();

        // Проверяем каждые 5 секунд
        checkInterval = setInterval(checkNewMessages, 5000);

        // Останавливаем проверку когда страница неактивна
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (checkInterval) {
                    clearInterval(checkInterval);
                    checkInterval = null;
                }
            } else {
                if (!checkInterval) {
                    checkNewMessages();
                    checkInterval = setInterval(checkNewMessages, 5000);
                }
            }
        });
    });

    // Очистка при выгрузке страницы
    window.addEventListener('beforeunload', function() {
        if (checkInterval) {
            clearInterval(checkInterval);
        }
    });
})();

/**
 * PWA INSTALL FUNCTIONALITY
 * Обработка установки приложения на главный экран
 */
(function() {
    let deferredPrompt = null;
    const installButton = document.getElementById('pwa-install-button');
    const installBanner = document.getElementById('pwa-install-banner');
    const DISMISS_KEY = 'pwa-install-dismissed';
    const BANNER_KEY = 'pwa-banner-shown';
    const INSTALLED_KEY = 'pwa-installed';

    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               (window.innerWidth <= 768);
    }

    function isInstallDismissed() {
        return localStorage.getItem(DISMISS_KEY) === 'true';
    }

    function isPWAInstalled() {
        if (localStorage.getItem(INSTALLED_KEY) === 'true') {
            return true;
        }
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        if (window.navigator.standalone) {
            return true;
        }
        if (document.referrer.includes('android-app://')) {
            return true;
        }
        return false;
    }

    function hideInstallBanner() {
        if (installBanner) {
            installBanner.style.display = 'none';
        }
    }

    function setInstallUIVisible(isVisible) {
        if (installButton) {
            installButton.style.display = isVisible ? 'inline-flex' : 'none';
        }
        document.querySelectorAll('.pwa-install-trigger').forEach(function(el) {
            el.style.display = isVisible ? '' : 'none';
        });
        // Плавающая кнопка на platform — скрываем весь блок
        document.querySelectorAll('.pwa-fab').forEach(function(el) {
            el.style.display = isVisible ? '' : 'none';
        });
    }

    function dismissInstallPrompts() {
        localStorage.setItem(DISMISS_KEY, 'true');
        localStorage.setItem(BANNER_KEY, 'true');
        hideInstallBanner();
        setInstallUIVisible(false);
    }

    function markInstalled() {
        localStorage.setItem(INSTALLED_KEY, 'true');
        localStorage.setItem(DISMISS_KEY, 'true');
        localStorage.setItem(BANNER_KEY, 'true');
        hideInstallBanner();
        setInstallUIVisible(false);
        deferredPrompt = null;
    }

    function getDeviceType() {
        const ua = navigator.userAgent || '';
        const isIOS = /iPad|iPhone|iPod/.test(ua) ||
            (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        const isAndroid = /Android/.test(ua);
        const isChrome = /Chrome|CriOS|EdgA|EdgiOS/.test(ua) && !/OPR|Opera/.test(ua);
        const isSafari = /Safari/.test(ua) && !/Chrome|CriOS|FxiOS|EdgiOS|OPiOS/.test(ua);
        const isFirefox = /Firefox|FxiOS/.test(ua);
        // Встроенные браузеры Instagram / Telegram / VK / Facebook — пункта «на экран» часто нет
        const isInAppBrowser = /FBAN|FBAV|Instagram|Line\/|Twitter|TikTok|BytedanceWebview|MicroMessenger|VKAndroidApp|Viber|WhatsApp|Telegram|Snapchat|; wv\)/i.test(ua);
        return { isIOS, isAndroid, isChrome, isSafari, isFirefox, isInAppBrowser };
    }

    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;

        if (!isPWAInstalled() && !isInstallDismissed() && isMobileDevice()) {
            setInstallUIVisible(true);
        }
    });

    async function installPWA() {
        if (isPWAInstalled()) {
            markInstalled();
            return;
        }

        const device = getDeviceType();
        const bannerButton = document.getElementById('pwa-install-banner-button');
        const originalText = bannerButton ? bannerButton.innerHTML : '';

        // iOS и in-app: только ручная инструкция
        if (device.isIOS || device.isInAppBrowser || !deferredPrompt) {
            if (bannerButton) {
                bannerButton.disabled = false;
                bannerButton.innerHTML = originalText;
            }
            showInstallInstructions();
            return;
        }

        try {
            await deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;

            if (outcome === 'accepted') {
                markInstalled();
                if (bannerButton) {
                    bannerButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Готово';
                    bannerButton.classList.add('btn-success');
                    bannerButton.classList.remove('btn-light');
                    bannerButton.disabled = true;
                }
            } else if (bannerButton) {
                bannerButton.disabled = false;
                bannerButton.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Ошибка при создании ярлыка:', error);
            if (bannerButton) {
                bannerButton.disabled = false;
                bannerButton.innerHTML = originalText;
            }
            showInstallInstructions();
        }

        deferredPrompt = null;
    }

    document.addEventListener('click', function(e) {
        const target = e.target && e.target.closest &&
            e.target.closest('.pwa-install-trigger, #pwa-install-banner-button, #pwa-install-button');
        if (!target) {
            return;
        }
        e.preventDefault();
        installPWA();
    });

    const closeBannerButton = document.getElementById('pwa-install-close');
    if (closeBannerButton) {
        closeBannerButton.addEventListener('click', function() {
            dismissInstallPrompts();
        });
    }

    window.addEventListener('appinstalled', function() {
        markInstalled();
    });

    function modalFooterHtml() {
        return `
            <div class="modal-footer flex-column align-items-stretch gap-2">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="pwa-modal-done">
                    Уже на экране
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="pwa-modal-dismiss">
                    Больше не показывать
                </button>
                <button type="button" class="btn btn-link text-muted btn-sm" data-bs-dismiss="modal">
                    Закрыть
                </button>
            </div>
        `;
    }

    function buildInstructionBody(device) {
        if (device.isInAppBrowser) {
            return `
                <div class="alert alert-warning mb-3">
                    <strong>Сейчас сайт открыт внутри другого приложения</strong>
                    (Telegram, Instagram и т.п.). В таком меню пункта «на экран» обычно нет.
                </div>
                <ol class="list-group list-group-numbered mb-0">
                    <li class="list-group-item">Нажмите <strong>⋯</strong> или иконку меню вверху</li>
                    <li class="list-group-item">Выберите <strong>«Открыть в браузере»</strong> / <strong>«Open in Chrome»</strong> / <strong>«В Safari»</strong></li>
                    <li class="list-group-item">В обычном браузере снова нажмите кнопку «Добавить на экран» на сайте</li>
                </ol>
            `;
        }

        if (device.isIOS) {
            if (!device.isSafari) {
                return `
                    <div class="alert alert-warning mb-3">
                        На iPhone ярлык можно добавить <strong>только через Safari</strong>.
                        В Chrome / Telegram / Instagram этого пункта в меню нет.
                    </div>
                    <ol class="list-group list-group-numbered mb-0">
                        <li class="list-group-item">Скопируйте адрес сайта из строки браузера</li>
                        <li class="list-group-item">Откройте <strong>Safari</strong> и вставьте адрес</li>
                        <li class="list-group-item">Внизу нажмите <strong>«Поделиться»</strong> <i class="bi bi-box-arrow-up"></i></li>
                        <li class="list-group-item">Прокрутите вниз → <strong>«На экран „Домой“»</strong> → «Добавить»</li>
                    </ol>
                `;
            }
            return `
                <div class="alert alert-info mb-3">
                    <strong>Safari (iPhone / iPad)</strong>
                </div>
                <ol class="list-group list-group-numbered mb-0">
                    <li class="list-group-item">Внизу экрана нажмите <strong>«Поделиться»</strong> <i class="bi bi-box-arrow-up"></i> (квадрат со стрелкой)</li>
                    <li class="list-group-item">Прокрутите список вниз</li>
                    <li class="list-group-item">Нажмите <strong>«На экран „Домой“»</strong> → «Добавить»</li>
                </ol>
                <p class="small text-muted mt-3 mb-0">Важно: ищите именно кнопку «Поделиться» внизу Safari, а не три точки в другом приложении.</p>
            `;
        }

        if (device.isAndroid) {
            if (device.isFirefox) {
                return `
                    <div class="alert alert-info mb-3"><strong>Firefox (Android)</strong></div>
                    <ol class="list-group list-group-numbered mb-0">
                        <li class="list-group-item">Нажмите <strong>три точки</strong> <i class="bi bi-three-dots-vertical"></i></li>
                        <li class="list-group-item"><strong>«Страница»</strong> → <strong>«Добавить на экран Домой»</strong></li>
                        <li class="list-group-item">Подтвердите добавление</li>
                    </ol>
                `;
            }
            return `
                <div class="alert alert-info mb-3"><strong>Chrome / браузер Android</strong></div>
                <ol class="list-group list-group-numbered mb-0">
                    <li class="list-group-item">Нажмите <strong>три точки</strong> <i class="bi bi-three-dots-vertical"></i> справа вверху</li>
                    <li class="list-group-item">Найдите <strong>«Установить приложение»</strong> или <strong>«Добавить на главный экран»</strong>.
                        Иногда пункт спрятан в <strong>«Добавить на гл. экран»</strong> / <strong>«Сохранить и поделиться»</strong> — прокрутите меню вниз.</li>
                    <li class="list-group-item">Подтвердите — ярлык появится на экране телефона</li>
                </ol>
                <p class="small text-muted mt-3 mb-0">Если пункта нет: откройте сайт в обычном Chrome (не из Telegram/Instagram) и обновите страницу.</p>
            `;
        }

        return `
            <div class="alert alert-info mb-3"><strong>Компьютер</strong></div>
            <p class="mb-0">В Chrome: значок установки справа в адресной строке, либо меню ⋮ → «Установить приложение».</p>
        `;
    }

    function showInstallInstructions() {
        const device = getDeviceType();
        const body = buildInstructionBody(device);
        const modalHTML = `
            <div class="modal fade" id="pwa-install-modal" tabindex="-1" aria-labelledby="pwa-install-modal-label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="pwa-install-modal-label">
                                <i class="bi bi-phone"></i> Добавить на экран Домой
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">${body}</div>
                        ${modalFooterHtml()}
                    </div>
                </div>
            </div>
        `;

        const existingModal = document.getElementById('pwa-install-modal');
        if (existingModal) {
            const instance = bootstrap.Modal.getInstance(existingModal);
            if (instance) {
                instance.dispose();
            }
            existingModal.outerHTML = modalHTML;
        } else {
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        const modalEl = document.getElementById('pwa-install-modal');
        const doneBtn = document.getElementById('pwa-modal-done');
        const dismissBtn = document.getElementById('pwa-modal-dismiss');
        if (doneBtn) {
            doneBtn.addEventListener('click', function() {
                markInstalled();
            });
        }
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function() {
                dismissInstallPrompts();
            });
        }

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function initializePWAInstall() {
        if (isPWAInstalled() || isInstallDismissed()) {
            setInstallUIVisible(false);
            hideInstallBanner();
            return;
        }

        if (isMobileDevice()) {
            setInstallUIVisible(true);
        } else {
            setInstallUIVisible(false);
        }

        setTimeout(function() {
            if (isPWAInstalled() || isInstallDismissed()) {
                hideInstallBanner();
                return;
            }
            if (localStorage.getItem(BANNER_KEY) === 'true') {
                return;
            }
            if (isMobileDevice() && installBanner) {
                installBanner.style.display = 'block';
            }
        }, 10000);
    }

    window.initializePWAInstall = initializePWAInstall;
})();