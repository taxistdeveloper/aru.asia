/**
 * SERVICE WORKER ДЛЯ PUSH-УВЕДОМЛЕНИЙ
 *
 * Обрабатывает push-уведомления в фоновом режиме
 */

const CACHE_NAME = 'aru-app-v5'; // v5: BASE_URL от пути к service-worker.js (не хардкод /aru-app/)
// Папка приложения = каталог, в котором лежит service-worker.js (например /aru-app/)
const SW_SCRIPT = self.location.pathname || '/';
const BASE_PATH = SW_SCRIPT.replace(/[^/]+$/, '');
const BASE_URL = self.location.origin + (BASE_PATH.endsWith('/') ? BASE_PATH : BASE_PATH + '/');
const MAINTENANCE_URL = BASE_URL + 'maintenance.html';

// Установка Service Worker
self.addEventListener('install', function(event) {
    console.log('Service Worker: Установка');
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll([
                BASE_URL,
                BASE_URL + 'maintenance.html',
                BASE_URL + 'assets/css/style.css',
                BASE_URL + 'assets/js/main.js'
            ]);
        })
    );
    self.skipWaiting();
});

// Активация Service Worker
self.addEventListener('activate', function(event) {
    console.log('Service Worker: Активация');
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Удаление старого кеша', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Обработка fetch (нужно для installability в Chrome + базовый оффлайн)
self.addEventListener('fetch', function(event) {
    // Обрабатываем только GET
    if (event.request.method !== 'GET') return;

    const requestUrl = new URL(event.request.url);

    // Не трогаем чужие домены
    if (requestUrl.origin !== self.location.origin) return;

    // Network-first для HTML (чтобы контент обновлялся), cache-first для остального
    const isHtmlRequest =
        event.request.mode === 'navigate' ||
        (event.request.headers.get('accept') || '').includes('text/html');

    if (isHtmlRequest) {
        event.respondWith(
            fetch(event.request)
                .then(function(response) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(function(cache) {
                        cache.put(event.request, responseClone);
                    });
                    return response;
                })
                .catch(function() {
                    return caches.match(event.request).then(function(cached) {
                        if (cached) return cached;
                        return caches.match(MAINTENANCE_URL).then(function(maintenance) {
                            if (maintenance) return maintenance;
                            return caches.match(BASE_URL);
                        });
                    });
                })
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then(function(cached) {
            if (cached) return cached;
            return fetch(event.request).then(function(response) {
                const responseClone = response.clone();
                caches.open(CACHE_NAME).then(function(cache) {
                    cache.put(event.request, responseClone);
                });
                return response;
            });
        })
    );
});

// Обработка ошибок сообщений
self.addEventListener('message', function(event) {
    try {
        // Обрабатываем сообщения от клиента
        if (event.data && event.data.type) {
            console.log('Service Worker: Получено сообщение', event.data);
        }
    } catch (error) {
        console.error('Service Worker: Ошибка обработки сообщения', error);
    }
});

// Обработка push-уведомлений
self.addEventListener('push', function(event) {
    console.log('Service Worker: Получено push-уведомление', event);

    let notificationData = {
        title: 'Aru App',
        body: 'У вас новое уведомление',
        icon: BASE_URL + 'assets/images/logo.jpg',
        badge: BASE_URL + 'assets/images/logo.jpg',
        tag: 'aru-notification',
        data: {
            url: BASE_URL
        }
    };

    // Если есть данные в уведомлении, используем их
    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = {
                title: data.title || notificationData.title,
                body: data.body || notificationData.body,
                icon: data.icon || notificationData.icon,
                badge: data.badge || notificationData.badge,
                tag: data.tag || notificationData.tag,
                data: data.data || notificationData.data
            };
        } catch (e) {
            console.error('Ошибка парсинга данных уведомления:', e);
            // Если данные не в формате JSON, пытаемся использовать как текст
            if (event.data.text) {
                try {
                    const textData = JSON.parse(event.data.text());
                    notificationData = {
                        title: textData.title || notificationData.title,
                        body: textData.body || notificationData.body,
                        icon: textData.icon || notificationData.icon,
                        badge: textData.badge || notificationData.badge,
                        tag: textData.tag || notificationData.tag,
                        data: textData.data || notificationData.data
                    };
                } catch (e2) {
                    // Если и это не сработало, используем текст как есть
                    notificationData.body = event.data.text();
                }
            }
        }
    }

    // Определяем параметры уведомления в зависимости от типа
    let notificationOptions = {
        body: notificationData.body,
        icon: notificationData.icon,
        badge: notificationData.badge,
        tag: notificationData.tag || 'aru-notification',
        data: notificationData.data,
        requireInteraction: false,
        vibrate: [200, 100, 200],
        actions: [
            {
                action: 'open',
                title: 'Открыть'
            },
            {
                action: 'close',
                title: 'Закрыть'
            }
        ]
    };

    // Для уведомлений о сообщениях делаем более заметными
    if (notificationData.data && notificationData.data.type) {
        const notificationType = notificationData.data.type;
        if (notificationType === 'message' || notificationType === 'date_message' || notificationType === 'event_message') {
            // Для сообщений делаем более длинную вибрацию
            notificationOptions.vibrate = [300, 200, 300, 200, 300];
            notificationOptions.requireInteraction = false;
            notificationOptions.silent = false; // Включаем звук

            // Используем notification_tag из данных для группировки сообщений от одного отправителя
            if (notificationData.data.notification_tag) {
                notificationOptions.tag = notificationData.data.notification_tag;
            } else {
                // Fallback на старую логику, если notification_tag не передан
                if (notificationData.data.date_id) {
                    notificationOptions.tag = 'date_message_' + notificationData.data.date_id + '_' + notificationData.data.from_user_id;
                } else if (notificationData.data.event_id) {
                    notificationOptions.tag = 'event_message_' + notificationData.data.event_id + '_' + notificationData.data.from_user_id;
                } else if (notificationData.data.from_user_id) {
                    notificationOptions.tag = 'message_' + notificationData.data.from_user_id;
                }
            }

            // Иконка сердца (bi-heart) в формате SVG data URL
            const heartSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#e91e63" viewBox="0 0 16 16"><path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/></svg>';
            const heartIconSvg = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(heartSvg);
            notificationOptions.icon = heartIconSvg;
        }
    }

    // Проверяем, не нужно ли скрыть уведомление, если пользователь на странице соответствующего чата
    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function(clientList) {
            // Проверяем, открыта ли страница сообщений
            const isMessagesPageOpen = clientList.some(function(client) {
                const url = client.url || '';
                return url.includes('/messages') || url.includes('messages?') || url.includes('messages/date') || url.includes('messages/event');
            });

            // Если это уведомление о сообщении и открыта страница сообщений, проверяем конкретный чат
            if (isMessagesPageOpen && notificationData.data && notificationData.data.type) {
                const notificationType = notificationData.data.type;
                const notificationDataObj = notificationData.data;

                // Проверяем, открыт ли конкретный чат с отправителем
                const isSpecificChatOpen = clientList.some(function(client) {
                    const url = client.url || '';

                    // Для обычных сообщений проверяем user_id
                    if (notificationType === 'message' && notificationDataObj.from_user_id) {
                        return url.includes('messages?user_id=' + notificationDataObj.from_user_id);
                    }

                    // Для сообщений в свидании проверяем date_id
                    if (notificationType === 'date_message' && notificationDataObj.date_id) {
                        return url.includes('messages/date?date_id=' + notificationDataObj.date_id) ||
                               url.includes('messages/date&date_id=' + notificationDataObj.date_id);
                    }

                    // Для сообщений в мероприятии проверяем event_id
                    if (notificationType === 'event_message' && notificationDataObj.event_id) {
                        return url.includes('messages/event?event_id=' + notificationDataObj.event_id) ||
                               url.includes('messages/event&event_id=' + notificationDataObj.event_id);
                    }

                    return false;
                });

                // Если открыт конкретный чат с отправителем, не показываем уведомление
                if (isSpecificChatOpen) {
                    console.log('Service Worker: Уведомление скрыто, так как открыт соответствующий чат');
                    return; // Не показываем уведомление
                }

                // Если это уведомление о свидании (new_date) и открыта страница сообщений, не показываем
                if (notificationType === 'new_date') {
                    console.log('Service Worker: Уведомление о свидании скрыто, так как открыта страница сообщений');
                    return; // Не показываем уведомление
                }
            }

            // Для уведомлений о сообщениях проверяем, есть ли уже уведомление с таким же tag
            if (notificationData.data && notificationData.data.type) {
                const notificationType = notificationData.data.type;
                if (notificationType === 'message' || notificationType === 'date_message' || notificationType === 'event_message') {
                    // Получаем все активные уведомления
                    return self.registration.getNotifications({ tag: notificationOptions.tag }).then(function(existingNotifications) {
                        let messageCount = 1;
                        let messageNumbers = [1];
                        let fromName = notificationData.body.split(':')[0] || 'Пользователь';

                        // Если есть существующее уведомление, получаем счетчик из его данных
                        if (existingNotifications.length > 0) {
                            const existingNotification = existingNotifications[0];
                            const existingData = existingNotification.data || {};
                            messageCount = (existingData.message_count || 1) + 1;
                            messageNumbers = existingData.message_numbers || [1];
                            messageNumbers.push(messageCount);

                            // Закрываем старое уведомление
                            existingNotification.close();
                        }

                        // Формируем текст уведомления с номерами сообщений
                        let bodyText = '';
                        if (messageCount === 1) {
                            // Первое сообщение: показываем обычный текст
                            bodyText = notificationData.body;
                        } else {
                            // Последующие сообщения: показываем "1 пришло и 2, 3, 4, 5, 6"
                            const additionalNumbers = messageNumbers.slice(1).join(', ');
                            bodyText = fromName + ': пришло ' + messageNumbers[0] + ' и ' + additionalNumbers;
                        }

                        // Обновляем данные уведомления с счетчиком
                        notificationOptions.data = Object.assign({}, notificationOptions.data, {
                            message_count: messageCount,
                            message_numbers: messageNumbers,
                            original_body: notificationData.body
                        });
                        notificationOptions.body = bodyText;

                        // Показываем обновленное уведомление
                        return self.registration.showNotification(notificationData.title, notificationOptions);
                    });
                }
            }

            // Показываем уведомление
            return self.registration.showNotification(notificationData.title, notificationOptions);
        })
    );
});

// Обработка клика по уведомлению
self.addEventListener('notificationclick', function(event) {
    console.log('Service Worker: Клик по уведомлению', event);

    event.notification.close();

    const action = event.action;
    const notificationData = event.notification.data || {};

    if (action === 'close') {
        return;
    }

    // Определяем URL для перехода
    let urlToOpen = notificationData.url || BASE_URL;

    // Если есть специфический URL в данных, используем его
    if (notificationData.type) {
        switch (notificationData.type) {
            case 'date_message':
                if (notificationData.date_id) {
                    urlToOpen = BASE_URL + 'messages/date?date_id=' + notificationData.date_id;
                }
                break;
            case 'event_message':
                if (notificationData.event_id) {
                    urlToOpen = BASE_URL + 'messages/event?event_id=' + notificationData.event_id;
                }
                break;
            case 'new_date':
                urlToOpen = BASE_URL + 'dates';
                break;
            case 'new_event':
                urlToOpen = BASE_URL + 'events';
                break;
            case 'admin_message':
                if (notificationData.event_id) {
                    urlToOpen = BASE_URL + 'events';
                } else {
                    urlToOpen = BASE_URL + 'messages';
                }
                break;
            case 'message':
            default:
                if (notificationData.from_user_id) {
                    urlToOpen = BASE_URL + 'messages?user_id=' + notificationData.from_user_id;
                } else {
                    urlToOpen = BASE_URL + 'messages';
                }
                break;
        }
    }

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function(clientList) {
            // Если есть открытое окно, фокусируемся на нем
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            // Если окна нет, открываем новое
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Обработка закрытия уведомления
self.addEventListener('notificationclose', function(event) {
    console.log('Service Worker: Уведомление закрыто', event);
});

