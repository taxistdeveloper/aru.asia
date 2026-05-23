/**
 * PUSH-УВЕДОМЛЕНИЯ
 *
 * Регистрация и управление push-уведомлениями
 */

(function() {
    'use strict';

    // Проверяем поддержку Service Worker и Push API
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.log('Push-уведомления не поддерживаются в этом браузере');
        return;
    }

    let registration = null;
    let subscription = null;

    /**
     * Регистрация Service Worker
     */
    async function registerServiceWorker() {
        try {
            registration = await navigator.serviceWorker.register(BASE_URL + 'service-worker.js');
            console.log('Service Worker зарегистрирован:', registration);

            // Проверяем обновления service worker
            registration.addEventListener('updatefound', function() {
                const newWorker = registration.installing;
                console.log('Найдена новая версия Service Worker для push-уведомлений');

                newWorker.addEventListener('statechange', function() {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        console.log('Новая версия Service Worker установлена. Перезагрузка страницы...');
                        window.location.reload();
                    }
                });
            });

            // Периодически проверяем обновления
            setInterval(function() {
                registration.update();
            }, 60000);

            return registration;
        } catch (error) {
            console.error('Ошибка регистрации Service Worker:', error);
            return null;
        }
    }

    /**
     * Запрос разрешения на уведомления
     */
    async function requestNotificationPermission() {
        if (!('Notification' in window)) {
            console.log('Браузер не поддерживает уведомления');
            return false;
        }

        if (Notification.permission === 'granted') {
            return true;
        }

        if (Notification.permission === 'denied') {
            console.log('Разрешение на уведомления отклонено');
            return false;
        }

        const permission = await Notification.requestPermission();
        return permission === 'granted';
    }

    /**
     * Подписка на push-уведомления
     */
    async function subscribeToPush() {
        if (!registration) {
            registration = await registerServiceWorker();
            if (!registration) {
                return null;
            }
        }

        try {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(getVapidPublicKey())
            });

            console.log('Подписка на push-уведомления создана:', subscription);
            return subscription;
        } catch (error) {
            console.error('Ошибка подписки на push-уведомления:', error);
            return null;
        }
    }

    /**
     * Отписка от push-уведомлений
     */
    async function unsubscribeFromPush() {
        if (!subscription) {
            return false;
        }

        try {
            const successful = await subscription.unsubscribe();
            if (successful) {
                subscription = null;
                console.log('Отписка от push-уведомлений выполнена');
            }
            return successful;
        } catch (error) {
            console.error('Ошибка отписки от push-уведомлений:', error);
            return false;
        }
    }

    /**
     * Регистрация токена на сервере
     */
    async function registerTokenOnServer(subscription) {
        if (!subscription) {
            return false;
        }

        const token = JSON.stringify(subscription);

        try {
            const response = await fetch(BASE_URL + 'push-notifications/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    token: token,
                    device_type: 'web'
                })
            });

            const data = await response.json();
            if (data.success) {
                console.log('Токен зарегистрирован на сервере');
                localStorage.setItem('push_token_registered', 'true');
                return true;
            } else {
                console.error('Ошибка регистрации токена:', data.error);
                return false;
            }
        } catch (error) {
            console.error('Ошибка отправки токена на сервер:', error);
            return false;
        }
    }

    /**
     * Удаление токена с сервера
     */
    async function unregisterTokenOnServer(subscription) {
        if (!subscription) {
            return false;
        }

        const token = JSON.stringify(subscription);

        try {
            const response = await fetch(BASE_URL + 'push-notifications/unregister', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    token: token
                })
            });

            const data = await response.json();
            if (data.success) {
                console.log('Токен удален с сервера');
                localStorage.removeItem('push_token_registered');
                return true;
            } else {
                console.error('Ошибка удаления токена:', data.error);
                return false;
            }
        } catch (error) {
            console.error('Ошибка удаления токена с сервера:', error);
            return false;
        }
    }

    /**
     * Инициализация push-уведомлений
     */
    async function initializePushNotifications() {
        // Проверяем, авторизован ли пользователь
        // Если нет BASE_URL, значит пользователь не авторизован
        if (typeof BASE_URL === 'undefined') {
            return;
        }

        // Регистрируем Service Worker
        registration = await registerServiceWorker();
        if (!registration) {
            return;
        }

        // Проверяем существующую подписку
        subscription = await registration.pushManager.getSubscription();

        // Если подписка уже есть, регистрируем её на сервере
        if (subscription) {
            const isRegistered = localStorage.getItem('push_token_registered');
            if (!isRegistered) {
                await registerTokenOnServer(subscription);
            }
            return;
        }

        // Запрашиваем разрешение
        const hasPermission = await requestNotificationPermission();
        if (!hasPermission) {
            console.log('Разрешение на уведомления не получено');
            return;
        }

        // Создаем подписку
        subscription = await subscribeToPush();
        if (subscription) {
            await registerTokenOnServer(subscription);
        }
    }

    /**
     * Отключение push-уведомлений
     */
    async function disablePushNotifications() {
        if (subscription) {
            await unsubscribeFromPush();
            await unregisterTokenOnServer(subscription);
        }
    }

    /**
     * Конвертация VAPID ключа из base64 в Uint8Array
     */
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Получение VAPID публичного ключа
     * В реальном проекте это должно быть в конфигурации
     */
    function getVapidPublicKey() {
        // Это пример ключа, в реальном проекте нужно сгенерировать свои ключи
        // Можно использовать: https://web-push-codelab.glitch.me/
        return 'BEl62iUYgUivxIkv69yViEuiBIa40HIe8F5jVvNQx8gJ8ryW40OCc6sSxf8ZVksf-LYSz6lyBr7i2VIKvFv2h0';
    }

    // Экспортируем функции в глобальную область
    window.PushNotifications = {
        init: initializePushNotifications,
        disable: disablePushNotifications,
        isSupported: function() {
            return 'serviceWorker' in navigator && 'PushManager' in window;
        }
    };

    // Автоматическая инициализация при загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePushNotifications);
    } else {
        initializePushNotifications();
    }

    // Обработка выхода пользователя
    window.addEventListener('beforeunload', function() {
        // При выходе можно отписаться, но обычно лучше оставить подписку
        // чтобы пользователь получал уведомления даже после закрытия браузера
    });
})();

