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

    // Проверка новых сообщений
    function checkNewMessages() {
        // Проверяем только если пользователь авторизован
        if (typeof BASE_URL === 'undefined') return;

        fetch(BASE_URL + 'messages/unread?last_check=' + encodeURIComponent(lastCheckTime))
            .then(response => response.json())
            .then(data => {
                const count = data.count || 0;
                console.log('Получено непрочитанных сообщений:', count);
                updateMessagesBadge(count);

                // Если есть новые сообщения и мы на странице сообщений, обновляем список
                if (count > 0 && window.location.pathname.includes('messages')) {
                    updateMessagesList();
                }

                // Если мы на странице свиданий или мероприятий, обновляем badge для каждого свидания/мероприятия
                if (window.location.pathname.includes('dates') || window.location.pathname.includes('events')) {
                    updateDatesEventsBadges();
                }

                // Обновляем время последней проверки
                lastCheckTime = new Date().toISOString();
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

        // Обновляем badge для мероприятий в нижней навигации
        fetch(BASE_URL + 'messages/unread-events-total')
            .then(response => response.json())
            .then(data => {
                const count = data.count || 0;
                updateEventsBadge(count);
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
    function updateMessagesBadge(count) {
        const badgeMobile = document.getElementById('messages-badge');
        const badgeDesktop = document.getElementById('messages-badge-desktop');

        console.log('updateMessagesBadge вызвана:', {
            count,
            badgeMobile: !!badgeMobile,
            badgeDesktop: !!badgeDesktop
        });

        // Проверяем, не был ли бейдж скрыт пользователем вручную
        let isManuallyHidden = localStorage.getItem('messagesBadgeManuallyHidden') === 'true';
        const savedCount = localStorage.getItem('messagesBadgeCount');
        const lastCount = savedCount ? parseInt(savedCount, 10) : 0;

        console.log('Состояние:', { isManuallyHidden, savedCount, lastCount, count });

        // Если пришло новое сообщение (count > 0), сбрасываем флаг manuallyHidden
        // Это нужно для того, чтобы бейдж показывался при новых сообщениях
        if (count > 0) {
            if (count > lastCount || (count > 0 && isManuallyHidden)) {
                console.log('Новое сообщение, сбрасываю флаг скрытия');
                isManuallyHidden = false;
                localStorage.setItem('messagesBadgeManuallyHidden', 'false');
            }
        }

        // Определяем, нужно ли показывать бейдж
        // Показываем если есть сообщения (count > 0) и бейдж не был скрыт вручную
        // Или если count = 0, но было сохраненное значение и бейдж не был скрыт
        const shouldShow = !isManuallyHidden && (count > 0 || (count === 0 && lastCount > 0));
        const displayCount = count > 0 ? count : (lastCount > 0 ? lastCount : 0);
        const badgeText = displayCount > 99 ? '99+' : displayCount.toString();

        console.log('Решение показать бейдж:', {
            shouldShow,
            displayCount,
            badgeText,
            isManuallyHidden,
            count,
            lastCount
        });

        if (shouldShow && displayCount > 0) {
            // Показываем бейдж
            if (badgeMobile) {
                badgeMobile.textContent = badgeText;
                badgeMobile.style.display = 'block';
                badgeMobile.style.visibility = 'visible';
                badgeMobile.style.opacity = '1';
                console.log('✅ Показываю мобильный бейдж:', badgeText);
            } else {
                console.error('❌ Элемент messages-badge не найден!');
            }
            if (badgeDesktop) {
                badgeDesktop.textContent = badgeText;
                badgeDesktop.style.display = 'block';
                badgeDesktop.style.visibility = 'visible';
                badgeDesktop.style.opacity = '1';
                console.log('✅ Показываю десктопный бейдж:', badgeText);
            } else {
                console.error('❌ Элемент messages-badge-desktop не найден!');
            }

            // Сохраняем состояние бейджа
            saveBadgeState(displayCount, true);
        } else {
            console.log('❌ Бейдж не показывается:', { shouldShow, displayCount });
            // Скрываем бейдж
            if (badgeMobile) {
                badgeMobile.style.display = 'none';
            }
            if (badgeDesktop) {
                badgeDesktop.style.display = 'none';
            }
        }

        // Показываем уведомление в браузере (если разрешено)
        if (count > 0 && 'Notification' in window && Notification.permission === 'granted') {
            // Можно добавить браузерные уведомления
        }
    }

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
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка при обновлении списка сообщений:', error);
            });
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
                ? '<span class="wa-checks" title="Доставлено"><i class="bi bi-check2-all"></i></span>'
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

        // Добавляем обработчики клика на ссылки уведомлений для скрытия бейджа
        // Бейдж скрывается ТОЛЬКО при клике на ссылку уведомлений, не при переходе на другие страницы
        const messagesLinks = document.querySelectorAll('a[href*="messages"]');
        messagesLinks.forEach(function(link) {
            link.addEventListener('click', function() {
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

    // Проверяем, является ли устройство мобильным
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               (window.innerWidth <= 768);
    }

    // Проверяем, установлено ли приложение
    function isPWAInstalled() {
        // Проверяем, запущено ли приложение в standalone режиме (открыто с главного экрана)
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        if (window.navigator.standalone) {
            return true; // iOS Safari
        }
        if (document.referrer.includes('android-app://')) {
            return true; // Android
        }
        return false;
    }

    // Показываем баннер для установки (отключено)
    function showInstallBanner() {
        // Показываем только на мобильных устройствах
        if (!isMobileDevice()) {
            return;
        }

        // Не показываем, если уже установлено
        if (isPWAInstalled()) {
            return;
        }

        // Проверяем, не показывали ли уже баннер
        const bannerShown = localStorage.getItem('pwa-banner-shown');
        if (bannerShown === 'true') {
            return;
        }

        // Показываем баннер для новых пользователей (проверяем, есть ли в localStorage отметка о том, что пользователь не новый)
        const isNewUser = !localStorage.getItem('pwa-user-seen');
        if (isNewUser && installBanner) {
            installBanner.style.display = 'block';
            localStorage.setItem('pwa-user-seen', 'true');
        }
    }

    // Показываем баннер сразу для новых пользователей (отключено)
    function showInstallBannerForNewUsers() {
        // Показываем только на мобильных устройствах
        if (!isMobileDevice()) {
            return;
        }

        // Не показываем, если уже установлено
        if (isPWAInstalled()) {
            return;
        }

        // Проверяем, не показывали ли уже баннер
        const bannerShown = localStorage.getItem('pwa-banner-shown');
        if (bannerShown === 'true') {
            return;
        }

        // Показываем баннер для новых пользователей сразу
        const isNewUser = !localStorage.getItem('pwa-user-seen');
        if (isNewUser && installBanner) {
            installBanner.style.display = 'block';
            localStorage.setItem('pwa-user-seen', 'true');
        }
    }

    // Скрываем баннер
    function hideInstallBanner() {
        if (installBanner) {
            installBanner.style.display = 'none';
        }
    }

    function setInstallUIVisible(isVisible) {
        const display = isVisible ? 'inline-flex' : 'none';
        if (installButton) installButton.style.display = display;
        document.querySelectorAll('.pwa-install-trigger').forEach(function(el) {
            el.style.display = isVisible ? '' : 'none';
        });
    }

    // Обработка события beforeinstallprompt
    window.addEventListener('beforeinstallprompt', function(e) {
        console.log('🔔 Событие beforeinstallprompt получено!');
        // Предотвращаем автоматическое отображение подсказки
        e.preventDefault();
        // Сохраняем событие для использования позже
        deferredPrompt = e;
        console.log('✅ deferredPrompt сохранен, готов к установке');

        // Показываем UI установки (кнопка/триггеры) и ждем клик пользователя
        if (!isPWAInstalled() && isMobileDevice()) {
            setInstallUIVisible(true);
        }

        // Показываем баннер сразу, если приложение не установлено и баннер еще не показывался (отключено)
        // Баннер отключен - не показываем
        /*
        if (!isPWAInstalled() && isMobileDevice()) {
            const bannerShown = localStorage.getItem('pwa-banner-shown');
            if (bannerShown !== 'true' && installBanner) {
                installBanner.style.display = 'block';
            }
        }
        */
    });

    // Определяем тип устройства
    function getDeviceType() {
        const ua = navigator.userAgent;
        const isIOS = /iPad|iPhone|iPod/.test(ua);
        const isAndroid = /Android/.test(ua);
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                           window.navigator.standalone ||
                           document.referrer.includes('android-app://');

        return { isIOS, isAndroid, isStandalone };
    }

    // Функция установки PWA
    async function installPWA() {
        const device = getDeviceType();
        const bannerButton = document.getElementById('pwa-install-banner-button');
        const originalText = bannerButton ? bannerButton.innerHTML : '';

        // Для iOS: нет системного install prompt как на Android.
        // Можно только через "Поделиться" → "На экран Домой", поэтому сразу показываем инструкцию.
        if (device.isIOS) {
            if (bannerButton) {
                bannerButton.disabled = false;
                bannerButton.innerHTML = originalText;
            }
            showInstallInstructions();
            return;
        }

        // Для Android - создание ярлыка на главном экране
        if (deferredPrompt) {
            console.log('📱 Нажата кнопка "Установить", показываю диалог...');
            try {
                // При нажатии "Установить" СРАЗУ показываем диалог для создания ярлыка
                await deferredPrompt.prompt();
                console.log('✅ Диалог установки показан, жду подтверждения...');

                // Ждем подтверждения пользователя
                const { outcome } = await deferredPrompt.userChoice;
                console.log('📋 Результат:', outcome);

                if (outcome === 'accepted') {
                    // Пользователь подтвердил - ярлык СРАЗУ создается на главном экране!
                    console.log('✅ Создание ярлыка на главном экране...');
                    localStorage.setItem('pwa-installed', 'true');
                    hideInstallBanner();
                    if (installButton) {
                        installButton.style.display = 'none';
                    }
                    if (bannerButton) {
                        bannerButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Ярлык создается...';
                        bannerButton.classList.add('btn-success');
                        bannerButton.classList.remove('btn-light');
                        bannerButton.disabled = true;
                    }
                    // Ярлык автоматически появится на главном экране телефона
                } else {
                    // Пользователь отклонил
                    console.log('Пользователь отклонил создание ярлыка');
                    if (bannerButton) {
                        bannerButton.disabled = false;
                        bannerButton.innerHTML = originalText;
                    }
                }
            } catch (error) {
                console.error('Ошибка при создании ярлыка:', error);
                if (bannerButton) {
                    bannerButton.disabled = false;
                    bannerButton.innerHTML = originalText;
                }
            }

            // Очищаем событие
            deferredPrompt = null;
        } else {
            // Если диалог недоступен, показываем инструкции
            console.warn('⚠️ deferredPrompt недоступен. Возможные причины:');
            console.warn('   - Приложение уже установлено');
            console.warn('   - Браузер не поддерживает PWA');
            console.warn('   - Событие beforeinstallprompt еще не получено');

            if (bannerButton) {
                bannerButton.disabled = false;
                bannerButton.innerHTML = originalText;
            }
            showInstallInstructions();
        }
    }

    // Делегирование: кнопки могут быть в контенте страницы, подгружаются до/после скрипта
    document.addEventListener('click', function(e) {
        const target = e.target && e.target.closest &&
            e.target.closest('.pwa-install-trigger, #pwa-install-banner-button, #pwa-install-button');
        if (!target) {
            return;
        }
        e.preventDefault();
        installPWA();
    });

    // Обработка закрытия баннера
    const closeBannerButton = document.getElementById('pwa-install-close');
    if (closeBannerButton) {
        closeBannerButton.addEventListener('click', function() {
            hideInstallBanner();
            localStorage.setItem('pwa-banner-shown', 'true');
        });
    }

    // Обработка успешного создания ярлыка на главном экране
    window.addEventListener('appinstalled', function(evt) {
        console.log('✅ Ярлык успешно создан на главном экране телефона!');
        localStorage.setItem('pwa-installed', 'true');
        hideInstallBanner();
        setInstallUIVisible(false);
        deferredPrompt = null;

        // Показываем сообщение об успешном создании ярлыка
        const bannerButton = document.getElementById('pwa-install-banner-button');
        if (bannerButton) {
            bannerButton.innerHTML = '<i class="bi bi-check-circle me-2"></i>Ярлык создан на главном экране!';
            bannerButton.classList.add('btn-success');
            bannerButton.classList.remove('btn-light');
            bannerButton.disabled = false;
        }

        // Скрываем сообщение через 3 секунды
        setTimeout(() => {
            if (bannerButton) {
                bannerButton.style.display = 'none';
            }
        }, 3000);
    });

    // Показываем инструкции для ручной установки
    function showInstallInstructions() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        const isChrome = /Chrome/.test(navigator.userAgent);
        const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent);
        const isFirefox = /Firefox/.test(navigator.userAgent);

        // Создаем модальное окно с инструкциями
        let modalHTML = '';

        if (isIOS) {
            modalHTML = `
                <div class="modal fade" id="pwa-install-modal" tabindex="-1" aria-labelledby="pwa-install-modal-label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="pwa-install-modal-label">
                                    <i class="bi bi-phone"></i> Добавить на экран Домой
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-grid gap-2 mb-3">
                                    <button id="ios-share-button" class="btn btn-primary btn-lg">
                                        <i class="bi bi-share"></i> Открыть меню "Поделиться"
                                    </button>
                                </div>
                                <div class="alert alert-info">
                                    <strong><i class="bi bi-info-circle"></i> Инструкция для iOS (iPhone/iPad):</strong>
                                </div>
                                <ol class="list-group list-group-numbered">
                                    <li class="list-group-item d-flex align-items-start">
                                        <span class="me-2"></span>
                                        <div>
                                            <strong>Нажмите "Поделиться" или  три точки : </strong>   <strong>и там "Поделиться"</strong>
                                           
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-start">
                                        <span class="me-2"></span>
                                        <div>
                                            <strong>Прокрутите список вниз, нажмите "Добавить на экран ДОМОЙ"</strong> 
                                        </div>
                                    </li>
                                   
                                </ol>
                              
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else if (isAndroid) {
            if (isChrome) {
                modalHTML = `
                    <div class="modal fade" id="pwa-install-modal" tabindex="-1" aria-labelledby="pwa-install-modal-label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="pwa-install-modal-label">
                                        <i class="bi bi-phone"></i> Добавить на экран Домой
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <strong><i class="bi bi-info-circle"></i> Для Android (Chrome):</strong>
                                    </div>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item">Нажмите на <strong>три точки</strong> <i class="bi bi-three-dots-vertical"></i> в правом верхнем углу браузера</li>
                                        <li class="list-group-item">Выберите <strong>"Добавить на экран Домой"</strong> или <strong>"Установить приложение"</strong></li>
                                        <li class="list-group-item">Подтвердите установку - приложение появится на главном экране!</li>
                                    </ol>
                                    
                                  
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Понятно</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else if (isFirefox) {
                modalHTML = `
                    <div class="modal fade" id="pwa-install-modal" tabindex="-1" aria-labelledby="pwa-install-modal-label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="pwa-install-modal-label">
                                        <i class="bi bi-phone"></i> Добавить на экран Домой
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <strong><i class="bi bi-info-circle"></i> Для Android (Firefox):</strong>
                                    </div>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item">Нажмите на <strong>три точки</strong> <i class="bi bi-three-dots-vertical"></i> в правом верхнем углу браузера</li>
                                        <li class="list-group-item">Выберите <strong>"Страница"</strong> → <strong>"Добавить на экран Домой"</strong></li>
                                        <li class="list-group-item">Подтвердите установку - приложение появится на главном экране!</li>
                                    </ol>
                                  
                                   
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Понятно</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                modalHTML = `
                    <div class="modal fade" id="pwa-install-modal" tabindex="-1" aria-labelledby="pwa-install-modal-label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="pwa-install-modal-label">
                                        <i class="bi bi-phone"></i> Добавить на экран Домой
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <strong><i class="bi bi-info-circle"></i> Для Android:</strong>
                                    </div>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item">Откройте меню браузера (обычно три точки или три линии)</li>
                                        <li class="list-group-item">Найдите опцию <strong>"Добавить на экран Домой"</strong> или <strong>"Установить приложение"</strong></li>
                                        <li class="list-group-item">Подтвердите установку - приложение появится на главном экране!</li>
                                    </ol>
                                    
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Понятно</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        } else {
            modalHTML = `
                <div class="modal fade" id="pwa-install-modal" tabindex="-1" aria-labelledby="pwa-install-modal-label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="pwa-install-modal-label">
                                    <i class="bi bi-phone"></i> Добавить на экран Домой
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <strong><i class="bi bi-info-circle"></i> Для компьютера:</strong>
                                </div>
                                <p>Используйте меню браузера для добавления на главный экран. Обычно это находится в настройках браузера или в меню установки приложений.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Понятно</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Обновляем разметку модального окна при каждом открытии,
        // чтобы изменения (например, блок "Совет" снизу) точно отображались.
        const existingModal = document.getElementById('pwa-install-modal');
        if (existingModal) {
            existingModal.outerHTML = modalHTML;
        } else {
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        // Добавляем обработчик для кнопки iOS Share, если она есть
        const iosShareButton = document.getElementById('ios-share-button');
        if (iosShareButton && navigator.share) {
            iosShareButton.onclick = async function() {
                try {
                    await navigator.share({
                        title: 'Aru App',
                        text: 'Установите приложение Aru на главный экран',
                        url: window.location.href
                    });
                } catch (error) {
                    console.log('Пользователь отменил или ошибка Web Share:', error);
                }
            };
        }

        // Показываем модальное окно
        const modal = new bootstrap.Modal(document.getElementById('pwa-install-modal'));
        modal.show();
    }

    // Инициализация при загрузке
    function initializePWAInstall() {
        // Если приложение уже установлено, скрываем кнопку
        if (isPWAInstalled()) {
            setInstallUIVisible(false);
            hideInstallBanner();
            return;
        }

        // На iOS нет beforeinstallprompt, поэтому показываем кнопку/триггеры сразу (только на мобилках)
        if (isMobileDevice()) {
            setInstallUIVisible(true);
        } else {
            setInstallUIVisible(false);
        }

        // Показываем баннер через 10 секунд, если приложение не установлено
        setTimeout(function() {
            // Проверяем еще раз, не установлено ли приложение за это время
            if (isPWAInstalled()) {
                hideInstallBanner();
                return;
            }

            // Проверяем, не показывали ли уже баннер
            const bannerShown = localStorage.getItem('pwa-banner-shown');
            if (bannerShown === 'true') {
                return;
            }

            // Показываем баннер только на мобильных устройствах
            // Баннер показывается даже если deferredPrompt еще не получен,
            // так как он может появиться позже, а при нажатии проверим его наличие
            if (isMobileDevice() && installBanner) {
                installBanner.style.display = 'block';
            }
        }, 10000); // 10 секунд = 10000 миллисекунд
    }

    // Экспортируем функцию
    window.initializePWAInstall = initializePWAInstall;
})();