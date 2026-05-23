<?php

/**
 * СТРАНИЦА СВИДАНИЙ
 */

ob_start();

// Инициализируем переменные, если они не переданы
$myDates = $myDates ?? [];
$myDateChats = $myDateChats ?? [];
$dates = $dates ?? [];
$currentUserId = $currentUserId ?? null;
$userLat = $userLat ?? null;
$userLon = $userLon ?? null;
$user = $user ?? null;
$userDate = $userDate ?? null;

// ВРЕМЕННАЯ ОТЛАДКА (можно удалить после исправления)
if (Helper::isLoggedIn() && isset($_GET['debug'])) {
    echo "<!-- DEBUG INFO -->";
    echo "<!-- myDates count: " . count($myDates) . " -->";
    echo "<!-- dates count: " . count($dates) . " -->";
    echo "<!-- currentUserId: " . ($currentUserId ?? 'null') . " -->";
    echo "<!-- userLat: " . ($userLat ?? 'null') . " -->";
    echo "<!-- userLon: " . ($userLon ?? 'null') . " -->";
    echo "<!-- user gender: " . (isset($user['gender']) ? $user['gender'] : 'null') . " -->";
    if (!empty($dates)) {
        echo "<!-- First date: " . print_r($dates[0], true) . " -->";
    }
    echo "<!-- END DEBUG INFO -->";
}

// Находим категорию текущего пользователя из первого активного свидания
$myCategoryName = null;
if (Helper::isLoggedIn() && !empty($myDates) && is_array($myDates)) {
    foreach ($myDates as $myDate) {
        if (strtotime($myDate['date_time']) >= time() && !empty($myDate['category_name'])) {
            $myCategoryName = $myDate['category_name'];
            break;
        }
    }
}
?>

<div class="mobile-page-container">
    <div class="dates-header">
        <h2 class="dates-title"></h2>
        <div class="dates-header-buttons">
            <?php if (Helper::isLoggedIn()): ?>
                <?php
                $totalUnread = 0;
                if (!empty($myDateChats)) {
                    foreach ($myDateChats as $date) {
                        $totalUnread += $date['unread_count'] ?? 0;
                    }
                }
                ?>
                <a href="<?= BASE_URL ?>messages/dates-list" class="btn-my-chats">
                    <i class="bi bi-chat-dots"></i>
                    Мои чаты
                    <?php if ($totalUnread > 0): ?>
                        <span class="chats-badge"><?= $totalUnread > 99 ? '99+' : $totalUnread ?></span>
                    <?php endif; ?>
                </a>
                <!-- Старый выпадающий список (скрыт) -->
                <div class="chats-dropdown-wrapper" style="display: none;">
                    <button type="button" class="btn-my-chats" id="myChatsDropdownBtn" onclick="toggleChatsDropdown()">
                        <i class="bi bi-chat-dots"></i>
                        Мои чаты
                        <?php if ($totalUnread > 0): ?>
                            <span class="chats-badge"><?= $totalUnread > 99 ? '99+' : $totalUnread ?></span>
                        <?php endif; ?>
                        <i class="bi bi-chevron-down chats-dropdown-arrow"></i>
                    </button>
                    <div class="chats-dropdown-menu" id="myChatsDropdown">
                        <?php if (empty($myDateChats)): ?>
                            <div class="chats-dropdown-empty">
                                <i class="bi bi-inbox"></i>
                                <p>У вас пока нет чатов свиданий</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($myDateChats as $date): ?>
                                <a href="<?= BASE_URL ?>messages/date?date_id=<?= $date['id'] ?>&user_id=<?= $date['user_id'] ?>" class="chats-dropdown-item">
                                    <div class="chats-dropdown-avatar">
                                        <?php if (!empty($date['photo'])): ?>
                                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $date['photo'] ?>" alt="<?= Helper::escape($date['title']) ?>">
                                        <?php else: ?>
                                            <div class="chats-dropdown-avatar-placeholder">
                                                <i class="bi bi-heart"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($date['unread_count']) && $date['unread_count'] > 0): ?>
                                            <span class="chats-dropdown-unread-badge"><?= $date['unread_count'] > 99 ? '99+' : $date['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="chats-dropdown-content">
                                        <div class="chats-dropdown-header">
                                            <h5 class="chats-dropdown-title"><?= Helper::escape($date['title']) ?></h5>
                                            <span class="chats-dropdown-time">
                                                <?= date('d.m.Y', strtotime($date['date_time'])) ?>
                                            </span>
                                        </div>
                                        <div class="chats-dropdown-preview">
                                            <span class="chats-dropdown-meta">
                                                <i class="bi bi-calendar3"></i>
                                                <?= date('d.m.Y', strtotime($date['date_time'])) ?> в <?= date('H:i', strtotime($date['date_time'])) ?>
                                            </span>
                                            <?php if (!empty($date['category_name'])): ?>
                                                <span class="chats-dropdown-meta">
                                                    <i class="bi bi-tag"></i>
                                                    <?= Helper::escape($date['category_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (Helper::isLoggedIn()): ?>
                <?php if (($user['marital_status'] ?? '') !== 'married'): ?>
                    <button type="button" class="btn-create-date" data-bs-toggle="modal" data-bs-target="#createDateModal">
                        Создать свидание
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="dates-register-hint">
                    <a href="<?= BASE_URL ?>auth/register">Зарегистрируйтесь</a> чтобы создать объявление
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // Фильтруем прошедшие свидания из списка "Мои свидания"
    $activeMyDates = [];
    if (Helper::isLoggedIn() && !empty($myDates) && is_array($myDates)) {
        foreach ($myDates as $date) {
            // Показываем только будущие свидания (включая сегодняшние, если время еще не прошло)
            if (isset($date['date_time']) && strtotime($date['date_time']) >= time()) {
                $activeMyDates[] = $date;
            }
        }
    }
    ?>
    <?php if (!empty($activeMyDates)): ?>
        <h3 class="dates-section-title">Мои свидания</h3>
        <div class="dates-list">
            <?php foreach ($activeMyDates as $date): ?>
                <div class="date-item-compact date-item-mine" onclick="openDateModal(<?= htmlspecialchars(json_encode($date), ENT_QUOTES, 'UTF-8') ?>, true)">
                    <?php if (!empty($date['photo'])): ?>
                        <div class="date-banner">
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $date['photo'] ?>" alt="Фото">
                        </div>
                    <?php else: ?>
                        <div class="date-banner-placeholder">
                            <i class="bi bi-heart"></i>
                        </div>
                    <?php endif; ?>
                    <div class="date-compact-content">
                        <h4 class="date-compact-title"><?= Helper::escape($date['title']) ?></h4>
                        <div class="date-compact-time">
                            <i class="bi bi-calendar3"></i>
                            <span><?= date('d.m.Y', strtotime($date['date_time'])) ?> в <?= date('H:i', strtotime($date['date_time'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (Helper::isLoggedIn() && (empty($userLat) || empty($userLon))): ?>
        <div class="dates-warning">
            <i class="bi bi-geo-alt"></i>
            <p>Укажите геолокацию в <a href="<?= BASE_URL ?>profile/edit">профиле</a> для просмотра свиданий рядом с вами</p>
        </div>
    <?php endif; ?>

    <?php if (empty($dates) || !is_array($dates)): ?>
        <h3 class="dates-section-title"><?= Helper::isLoggedIn() ? 'Другие свидания' : 'Свидания' ?></h3>
        <div class="dates-empty">
            <i class="bi bi-inbox"></i>
            <p><?= Helper::isLoggedIn() ? 'В радиусе 50км нет объявлений' : 'Пока нет доступных объявлений' ?></p>
            <?php if (!Helper::isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>auth/register">Зарегистрируйтесь</a> чтобы создать объявление
            <?php endif; ?>
        </div>
    <?php else: ?>
        <h3 class="dates-section-title"><?= Helper::isLoggedIn() ? 'Другие свидания' : 'Свидания' ?></h3>
        <?php
        // Фильтруем свои свидания из списка других
        $otherDates = [];
        if ($currentUserId) {
            // Получаем ID своих свиданий
            $myDateIds = [];
            if (!empty($myDates) && is_array($myDates)) {
                $myDateIds = array_column($myDates, 'id');
                $myDateIds = array_filter($myDateIds, function ($id) {
                    return !empty($id) && is_numeric($id);
                }); // Убираем пустые и нечисловые значения
                $myDateIds = array_values($myDateIds); // Переиндексируем массив
            }

            // Фильтруем: исключаем свои свидания
            foreach ($dates as $date) {
                if (isset($date['id']) && isset($date['user_id'])) {
                    $dateId = (int)$date['id'];
                    $dateUserId = (int)$date['user_id'];
                    $currentUserIdInt = (int)$currentUserId;

                    // Исключаем если это мое свидание (по ID или по user_id)
                    $isMyDateById = in_array($dateId, $myDateIds);
                    $isMyDateByUserId = ($dateUserId === $currentUserIdInt);

                    if (!$isMyDateById && !$isMyDateByUserId) {
                        $otherDates[] = $date;
                    }
                } else {
                    // Если нет ID или user_id, все равно добавляем (на случай ошибки данных)
                    $otherDates[] = $date;
                }
            }
        } else {
            // Для неавторизованных пользователей показываем все
            $otherDates = $dates;
        }

        // ВРЕМЕННАЯ ОТЛАДКА (можно удалить после исправления)
        if (Helper::isLoggedIn() && isset($_GET['debug'])) {
            echo "<!-- DEBUG: otherDates count: " . count($otherDates) . " -->";
            echo "<!-- DEBUG: myDateIds: " . print_r($myDateIds, true) . " -->";
        }
        ?>
        <?php if (empty($otherDates)): ?>
            <div class="dates-empty">
                <i class="bi bi-inbox"></i>
                <p>В радиусе 50км нет других объявлений</p>
            </div>
        <?php else: ?>
            <div class="dates-list">
                <?php foreach ($otherDates as $date): ?>
                    <div class="date-item-compact" onclick="openDateModal(<?= htmlspecialchars(json_encode($date), ENT_QUOTES, 'UTF-8') ?>, false)">
                        <?php if (!empty($date['photo'])): ?>
                            <div class="date-banner">
                                <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $date['photo'] ?>" alt="Фото">
                            </div>
                        <?php else: ?>
                            <div class="date-banner-placeholder">
                                <i class="bi bi-heart"></i>
                            </div>
                        <?php endif; ?>
                        <div class="date-compact-content">
                            <h4 class="date-compact-title"><?= Helper::escape($date['title']) ?></h4>
                            <div class="date-compact-time">
                                <i class="bi bi-calendar3"></i>
                                <span><?= date('d.m.Y', strtotime($date['date_time'])) ?> в <?= date('H:i', strtotime($date['date_time'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>


<!-- Модальное окно для создания свидания -->
<?php if (Helper::isLoggedIn()): ?>
    <div class="modal fade" id="createDateModal" tabindex="-1" aria-labelledby="createDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createDateModalLabel">Создать свидание</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($userDate) && $userDate): ?>
                        <div class="dates-modal-warning">
                            <i class="bi bi-info-circle"></i>
                            <p><strong>У вас уже есть свидание</strong></p>
                            <p>Редактируйте или удалите существующее свидание в разделе "Мои свидания"</p>
                        </div>
                    <?php else: ?>
                        <p class="dates-modal-text">Вы будете перенаправлены на страницу создания свидания</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <?php if (!isset($userDate) || !$userDate): ?>
                        <a href="<?= BASE_URL ?>dates/create" class="btn btn-primary">Создать</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Модальное окно для просмотра полной информации о свидании -->
<div class="modal fade" id="dateDetailModal" tabindex="-1" aria-labelledby="dateDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content date-modal-content">
            <div class="modal-header date-modal-header">
                <h5 class="modal-title" id="dateDetailModalLabel">Детали свидания</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body date-modal-body">
                <div id="dateDetailContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для просмотра фото -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="photoModalImage" src="" alt="Фото" class="img-fluid" style="max-height: 90vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для регистрации -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Регистрация</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="dates-modal-warning">
                    <i class="bi bi-info-circle"></i>
                    <p><strong>Зарегистрируйтесь чтобы написать сообщение</strong></p>
                    <p>Создайте аккаунт, чтобы начать общение с другими пользователями</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <a href="<?= BASE_URL ?>auth/register" class="btn btn-primary">Зарегистрироваться</a>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для списка чатов -->
<div class="modal fade" id="myDateChatsModal" tabindex="-1" aria-labelledby="myDateChatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myDateChatsModalLabel">Мои чаты свиданий</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($myDateChats)): ?>
                    <div class="dates-empty">
                        <i class="bi bi-inbox"></i>
                        <p>У вас пока нет чатов свиданий</p>
                    </div>
                <?php else: ?>
                    <div class="chats-dialog-list">
                        <?php foreach ($myDateChats as $date): ?>
                            <div class="chat-dialog-item-wrapper" id="chat-wrapper-<?= $date['id'] ?>">
                                <a href="<?= BASE_URL ?>messages/date?date_id=<?= $date['id'] ?>&user_id=<?= $date['user_id'] ?>" class="chat-dialog-item">
                                    <div class="chat-dialog-avatar">
                                        <?php if (!empty($date['photo'])): ?>
                                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $date['photo'] ?>" alt="<?= Helper::escape($date['title']) ?>">
                                        <?php else: ?>
                                            <div class="chat-dialog-avatar-placeholder">
                                                <i class="bi bi-heart"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($date['unread_count']) && $date['unread_count'] > 0): ?>
                                            <span class="chat-dialog-unread-badge"><?= $date['unread_count'] > 99 ? '99+' : $date['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="chat-dialog-content">
                                        <div class="chat-dialog-header">
                                            <h5 class="chat-dialog-title"><?= Helper::escape($date['title']) ?></h5>
                                            <span class="chat-dialog-time">
                                                <?= date('d.m.Y', strtotime($date['date_time'])) ?>
                                            </span>
                                        </div>
                                        <div class="chat-dialog-preview">
                                            <span class="chat-dialog-meta">
                                                <i class="bi bi-calendar3"></i>
                                                <?= date('d.m.Y', strtotime($date['date_time'])) ?> в <?= date('H:i', strtotime($date['date_time'])) ?>
                                            </span>
                                            <?php if (!empty($date['category_name'])): ?>
                                                <span class="chat-dialog-meta">
                                                    <i class="bi bi-tag"></i>
                                                    <?= Helper::escape($date['category_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="chat-dialog-arrow">
                                        <i class="bi bi-chevron-right"></i>
                                    </div>
                                </a>
                                <button type="button" class="chat-dialog-delete-btn"
                                    data-date-id="<?= $date['id'] ?>"
                                    onclick="deleteDateChat(<?= $date['id'] ?>, event)"
                                    title="Удалить чат">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const BASE_URL = '<?= BASE_URL ?>';
        const UPLOAD_DIR = '<?= UPLOAD_DIR ?>';
        const IS_LOGGED_IN = <?= Helper::isLoggedIn() ? 'true' : 'false' ?>;

        // Закрываем dropdown при клике вне его
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('myChatsDropdown');
            const btn = document.getElementById('myChatsDropdownBtn');
            if (dropdown && btn && !dropdown.contains(event.target) && !btn.contains(event.target)) {
                dropdown.classList.remove('show');
                btn.classList.remove('active');
            }
        });

        let expiredCleanupInProgress = false;

        function updateCountdown() {
            const timers = document.querySelectorAll('.countdown-timer');

            timers.forEach(function(timer) {
                const deadline = new Date(timer.getAttribute('data-deadline')).getTime();
                const now = new Date().getTime();
                const distance = deadline - now;

                if (distance < 0) {
                    timer.className = 'countdown-timer countdown-expired';
                    timer.innerHTML =
                        '<div class="countdown-expired-content">' +
                        '<i class="bi bi-clock-history"></i>' +
                        '<span>Истекло</span>' +
                        '</div>';

                    // Защита от множественных запросов и бесконечной перезагрузки
                    if (expiredCleanupInProgress) return;

                    const dateId = timer.getAttribute('data-date-id');
                    const reloadKey = 'expired_reload_date_' + dateId;
                    if (sessionStorage.getItem(reloadKey)) {
                        // Уже пытались перезагрузить - скрываем карточку
                        const card = timer.closest('.date-card, .chat-dialog-item-wrapper');
                        if (card) card.style.display = 'none';
                        sessionStorage.removeItem(reloadKey);
                        return;
                    }

                    expiredCleanupInProgress = true;
                    sessionStorage.setItem(reloadKey, '1');

                    // Вызываем AJAX для удаления просроченных свиданий из базы
                    fetch(BASE_URL + 'dates/deleteExpired', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            const card = timer.closest('.date-card, .chat-dialog-item-wrapper');
                            if (card) card.style.display = 'none';
                            sessionStorage.removeItem(reloadKey);
                        }
                    }).catch(function() {
                        const card = timer.closest('.date-card, .chat-dialog-item-wrapper');
                        if (card) card.style.display = 'none';
                        sessionStorage.removeItem(reloadKey);
                    });
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Определяем класс для стилизации
                let countdownClass = 'countdown-normal';
                if (distance < 3600000) { // Меньше часа
                    countdownClass = 'countdown-urgent';
                } else if (distance < 86400000) { // Меньше суток
                    countdownClass = 'countdown-warning';
                }

                // Форматируем значения с ведущими нулями
                const formatValue = (val) => String(val).padStart(2, '0');

                timer.className = 'countdown-timer ' + countdownClass;
                timer.innerHTML =
                    '<div class="countdown-item"><div class="countdown-value">' + formatValue(days) + '</div><div class="countdown-label">дн</div></div>' +
                    '<div class="countdown-separator">:</div>' +
                    '<div class="countdown-item"><div class="countdown-value">' + formatValue(hours) + '</div><div class="countdown-label">ч</div></div>' +
                    '<div class="countdown-separator">:</div>' +
                    '<div class="countdown-item"><div class="countdown-value">' + formatValue(minutes) + '</div><div class="countdown-label">мин</div></div>' +
                    '<div class="countdown-separator">:</div>' +
                    '<div class="countdown-item"><div class="countdown-value">' + formatValue(seconds) + '</div><div class="countdown-label">сек</div></div>';
            });
        }

        // Обновляем счетчик каждую секунду
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Функция для открытия фото в модальном окне
        window.openPhotoModal = function(photoUrl) {
            const modal = new bootstrap.Modal(document.getElementById('photoModal'));
            document.getElementById('photoModalImage').src = photoUrl;
            modal.show();
        };

        // Функция для открытия модального окна регистрации
        window.openRegisterModal = function() {
            // Закрываем модальное окно деталей свидания
            const dateModal = bootstrap.Modal.getInstance(document.getElementById('dateDetailModal'));
            if (dateModal) {
                dateModal.hide();
            }
            // Открываем модальное окно регистрации
            const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();
        };

        // Функция для удаления чата свидания
        window.deleteDateChat = function(dateId, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            if (!confirm('Вы уверены, что хотите удалить этот чат? Все ваши сообщения в этом чате будут удалены.')) {
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
                    if (data.success) {
                        // Удаляем элемент из списка
                        const wrapper = document.getElementById('chat-wrapper-' + dateId);
                        if (wrapper) {
                            wrapper.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            wrapper.style.opacity = '0';
                            wrapper.style.transform = 'translateX(100%)';
                            setTimeout(function() {
                                wrapper.remove();

                                // Если список пуст, показываем сообщение
                                const list = document.querySelector('#myDateChatsModal .chats-dialog-list');
                                if (list && list.querySelectorAll('.chat-dialog-item-wrapper').length === 0) {
                                    list.innerHTML = '<div class="dates-empty"><i class="bi bi-inbox"></i><p>У вас пока нет чатов свиданий</p></div>';
                                }
                            }, 300);
                        }
                    } else {
                        alert(data.error || 'Не удалось удалить чат. Попробуйте еще раз.');
                    }
                })
                .catch(error => {
                    console.error('Ошибка при удалении чата:', error);
                    alert('Произошла ошибка при удалении чата. Попробуйте еще раз.');
                });
        };

        // Функция для открытия детальной информации о свидании
        window.openDateModal = function(date, isMine) {
            const modal = new bootstrap.Modal(document.getElementById('dateDetailModal'));
            const content = document.getElementById('dateDetailContent');

            let html = '';

            // Баннер
            if (date.photo) {
                const photoUrl = BASE_URL + UPLOAD_DIR + 'photos/' + date.photo;
                html += '<div class="date-modal-banner" onclick="openPhotoModal(\'' + photoUrl + '\')">';
                html += '<img src="' + photoUrl + '" alt="Фото">';
                html += '</div>';
            } else {
                html += '<div class="date-modal-banner-placeholder">';
                html += '<i class="bi bi-heart"></i>';
                html += '</div>';
            }

            // Заголовок
            html += '<h4 class="date-modal-title">' + escapeHtml(date.title) + '</h4>';

            // Категория
            if (date.category_name) {
                html += '<div class="date-modal-category">';
                html += '<span>Я ' + escapeHtml(date.category_name) + '</span>';
                html += '</div>';
                if (date.category_description) {
                    html += '<div class="date-modal-category-desc">';
                    html += escapeHtml(date.category_description);
                    html += '</div>';
                }
            }

            // Информация
            html += '<div class="date-modal-info">';

            // Дата и время
            const dateTime = new Date(date.date_time);
            const dateStr = dateTime.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const timeStr = dateTime.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            });
            html += '<div class="date-modal-info-item">';
            html += '<i class="bi bi-calendar3"></i>';
            html += '<span>' + dateStr + ' в ' + timeStr + '</span>';
            html += '</div>';

            // Местоположение
            // if (date.location) {
            //     html += '<div class="date-modal-info-item">';
            //     html += '<i class="bi bi-geo-alt"></i>';
            //     html += '<span>' + escapeHtml(date.location) + '</span>';
            //     html += '</div>';
            // }

            // Расстояние (только для других свиданий)
            if (!isMine && date.distance && date.distance > 0) {
                html += '<div class="date-modal-info-item">';
                html += '<i class="bi bi-rulers"></i>';
                html += '<span>' + parseFloat(date.distance).toFixed(1) + ' км</span>';
                html += '</div>';
            }

            // Счетчик времени
            if (new Date(date.date_time).getTime() >= Date.now()) {
                html += '<div class="date-modal-countdown-wrapper">';
                html += '<div class="date-modal-countdown-label">';
                html += '<i class="bi bi-clock"></i>';
                html += '<span>До дедлайна</span>';
                html += '</div>';
                html += '<div class="date-modal-countdown">';
                html += '<div class="countdown-timer" data-deadline="' + date.date_time + '" data-date-id="' + date.id + '">';
                html += '<div class="countdown-item"><div class="countdown-value">-</div><div class="countdown-label">дн</div></div>';
                html += '<div class="countdown-separator">:</div>';
                html += '<div class="countdown-item"><div class="countdown-value">-</div><div class="countdown-label">ч</div></div>';
                html += '<div class="countdown-separator">:</div>';
                html += '<div class="countdown-item"><div class="countdown-value">-</div><div class="countdown-label">мин</div></div>';
                html += '<div class="countdown-separator">:</div>';
                html += '<div class="countdown-item"><div class="countdown-value">-</div><div class="countdown-label">сек</div></div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            } else {
                html += '<div class="date-modal-status-wrapper">';
                html += '<div class="date-modal-status">';
                html += '<i class="bi bi-x-circle"></i>';
                html += '<span>Прошло</span>';
                html += '</div>';
                html += '</div>';
            }

            html += '</div>';

            // Кнопки действий
            html += '<div class="date-modal-actions">';
            if (isMine) {
                // Для своих свиданий
                const unreadCount = date.unread_count || 0;
                html += '<a href="' + BASE_URL + 'messages/date?date_id=' + date.id + '" class="date-modal-btn date-modal-btn-chat">';
                html += '<i class="bi bi-chat"></i>';
                html += '<span>Чат</span>';
                if (unreadCount > 0) {
                    html += '<span class="date-modal-badge">' + (unreadCount > 99 ? '99+' : unreadCount) + '</span>';
                }
                html += '</a>';
                html += '<a href="' + BASE_URL + 'dates/edit?id=' + date.id + '" class="date-modal-btn date-modal-btn-edit">';
                html += '<i class="bi bi-pencil"></i>';
                html += '<span>Редактировать</span>';
                html += '</a>';
            } else {
                // Для других свиданий
                if (IS_LOGGED_IN) {
                    const unreadCount = date.unread_count || 0;
                    html += '<a href="' + BASE_URL + 'messages/date?date_id=' + date.id + '&user_id=' + date.user_id + '" class="date-modal-btn date-modal-btn-chat date-modal-btn-full">';
                    html += '<i class="bi bi-chat"></i>';
                    html += '<span>Чат</span>';
                    if (unreadCount > 0) {
                        html += '<span class="date-modal-badge">' + (unreadCount > 99 ? '99+' : unreadCount) + '</span>';
                    }
                    html += '</a>';
                } else {
                    html += '<div class="date-modal-register-prompt">';
                    html += '<p class="date-modal-register-text">Зарегистрируйтесь чтобы написать сообщение</p>';
                    html += '<button type="button" class="date-modal-btn date-modal-btn-chat date-modal-btn-full" onclick="openRegisterModal()">';
                    html += '<i class="bi bi-person-plus"></i>';
                    html += '<span>Чат</span>';
                    html += '</button>';
                    html += '</div>';
                }
            }
            html += '</div>';

            content.innerHTML = html;

            // Обновляем счетчики времени
            updateCountdown();

            modal.show();
        };

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });

    // Функция для переключения выпадающего списка чатов
    function toggleChatsDropdown() {
        const dropdown = document.getElementById('myChatsDropdown');
        const btn = document.getElementById('myChatsDropdownBtn');
        if (dropdown && btn) {
            const isShowing = dropdown.classList.contains('show');

            if (!isShowing) {
                // Показываем dropdown
                dropdown.classList.add('show');
                btn.classList.add('active');

                // На мобильных устройствах корректируем позицию
                if (window.innerWidth <= 767) {
                    const rect = btn.getBoundingClientRect();
                    const dropdownRect = dropdown.getBoundingClientRect();

                    // Если dropdown выходит за правый край экрана
                    if (rect.right + dropdownRect.width > window.innerWidth) {
                        dropdown.style.right = 'auto';
                        dropdown.style.left = '0';
                    }

                    // Если dropdown выходит за нижний край экрана
                    if (rect.bottom + dropdownRect.height > window.innerHeight) {
                        dropdown.style.top = 'auto';
                        dropdown.style.bottom = 'calc(100% + 8px)';
                    }
                }
            } else {
                // Скрываем dropdown
                dropdown.classList.remove('show');
                btn.classList.remove('active');

                // Сбрасываем стили позиционирования
                dropdown.style.right = '';
                dropdown.style.left = '';
                dropdown.style.top = '';
                dropdown.style.bottom = '';
            }
        }
    }
</script>

<style>
    /* Минималистичный дизайн для мобильных устройств */
    @media (max-width: 767px) {

        /* Заголовок страницы */
        .dates-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
            position: relative;
            z-index: 100;
            overflow: visible;
        }

        .dates-header-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            position: relative;
            overflow: visible;
        }

        .chats-dropdown-wrapper {
            position: relative;
            z-index: 1001;
        }

        .btn-my-chats {
            padding: 10px 16px;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            text-decoration: none;
        }

        .btn-my-chats,
        .btn-my-chats * {
            color: white !important;
        }

        .chats-dropdown-arrow {
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .btn-my-chats.active .chats-dropdown-arrow {
            transform: rotate(180deg);
        }

        .btn-my-chats:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-my-chats:active {
            transform: scale(0.98);
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        .btn-my-chats i {
            font-size: 16px;
            color: white !important;
        }

        .chats-badge {
            background: #dc2626;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
        }

        /* Выпадающий список чатов */
        .chats-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            left: auto;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            box-shadow:
                0 10px 40px rgba(0, 0, 0, 0.12),
                0 4px 12px rgba(0, 0, 0, 0.08),
                0 0 0 1px rgba(0, 0, 0, 0.04);
            width: calc(100vw - 32px);
            max-width: 400px;
            max-height: 60vh;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999;
            display: none;
            border: 1px solid rgba(236, 72, 153, 0.1);
            backdrop-filter: blur(10px);
        }

        /* На очень маленьких экранах делаем на всю ширину */
        @media (max-width: 360px) {
            .chats-dropdown-menu {
                width: calc(100vw - 16px);
                right: 0;
                left: auto;
            }

            .chats-dropdown-wrapper {
                width: 100%;
            }
        }

        .chats-dropdown-menu.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            animation: dropdownFadeIn 0.2s ease;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Убеждаемся, что dropdown виден на мобильных */
        @media (max-width: 767px) {
            .mobile-page-container {
                overflow: visible !important;
            }

            .dates-header {
                overflow: visible !important;
            }

            .dates-header-buttons {
                overflow: visible !important;
            }

            .chats-dropdown-menu {
                position: absolute !important;
                display: none;
            }

            .chats-dropdown-menu.show {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
        }

        .chats-dropdown-empty {
            padding: 48px 24px;
            text-align: center;
            color: #6b7280;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
        }

        .chats-dropdown-empty i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
            display: block;
            opacity: 0.6;
        }

        .chats-dropdown-empty p {
            font-size: 15px;
            margin: 0;
            color: #9ca3af;
            font-weight: 500;
        }

        .chats-dropdown-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid rgba(243, 244, 246, 0.8);
            gap: 14px;
            min-width: 0;
            position: relative;
            background: transparent;
        }

        .chats-dropdown-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .chats-dropdown-item:last-child {
            border-bottom: none;
        }

        .chats-dropdown-item:active,
        .chats-dropdown-item:hover {
            background: linear-gradient(135deg, #fef3f2 0%, #fdf2f8 100%);
            transform: translateX(4px);
            border-left: 3px solid transparent;
        }

        .chats-dropdown-item:hover::before {
            opacity: 1;
        }

        .chats-dropdown-avatar {
            position: relative;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            box-shadow:
                0 4px 12px rgba(236, 72, 153, 0.3),
                0 0 0 2px rgba(255, 255, 255, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .chats-dropdown-item:hover .chats-dropdown-avatar {
            transform: scale(1.05);
            box-shadow:
                0 6px 16px rgba(236, 72, 153, 0.4),
                0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .chats-dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chats-dropdown-avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }

        .chats-dropdown-avatar-placeholder i {
            font-size: 20px;
            color: white;
            opacity: 0.9;
        }

        .chats-dropdown-unread-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 6px;
            border-radius: 12px;
            min-width: 18px;
            text-align: center;
            line-height: 1.3;
            border: 2.5px solid white;
            box-shadow:
                0 2px 8px rgba(220, 38, 38, 0.4),
                0 0 0 1px rgba(220, 38, 38, 0.2);
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .chats-dropdown-content {
            flex: 1;
            min-width: 0;
        }

        .chats-dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
            gap: 8px;
            min-width: 0;
        }

        .chats-dropdown-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            min-width: 0;
            letter-spacing: -0.2px;
            transition: color 0.2s ease;
        }

        .chats-dropdown-item:hover .chats-dropdown-title {
            color: #ec4899;
        }

        .chats-dropdown-time {
            font-size: 11px;
            color: #9ca3af;
            flex-shrink: 0;
            white-space: nowrap;
            font-weight: 500;
        }

        .chats-dropdown-preview {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            min-width: 0;
        }

        .chats-dropdown-meta {
            font-size: 12px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex-shrink: 1;
            min-width: 0;
            background: rgba(243, 244, 246, 0.6);
            padding: 4px 8px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .chats-dropdown-item:hover .chats-dropdown-meta {
            background: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }

        /* На маленьких экранах скрываем некоторые метаданные */
        @media (max-width: 360px) {
            .chats-dropdown-meta:last-child {
                display: none;
            }

            .chats-dropdown-time {
                font-size: 10px;
            }

            .chats-dropdown-title {
                font-size: 13px;
            }
        }

        .chats-dropdown-meta i {
            font-size: 12px;
            color: #ec4899;
            transition: color 0.2s ease;
        }

        .chats-dropdown-item:hover .chats-dropdown-meta i {
            color: #be185d;
        }

        .date-item-chat {
            border: 1px solid #10b981;
            border-left: 3px solid #10b981;
        }

        .dates-title {
            font-size: 28px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .btn-create-date {
            padding: 10px 16px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-create-date:active {
            transform: scale(0.98);
            background: #374151;
        }

        .dates-register-hint {
            font-size: 13px;
            color: #6b7280;
            text-align: right;
        }

        .dates-register-hint a {
            color: #111827;
            font-weight: 600;
            text-decoration: none;
        }

        /* Секции */
        .dates-section-title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
            margin-top: 32px;
        }

        .dates-section-title:first-of-type {
            margin-top: 0;
        }

        /* Список свиданий */
        .dates-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Компактная карточка свидания */
        .date-item-compact {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .date-item-compact:active {
            transform: scale(0.98);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .date-item-mine {
            border: 1px solid #e5e7eb;
        }

        /* Баннер */
        .date-banner {
            width: 100%;
            height: 180px;
            overflow: hidden;
            background: #f3f4f6;
        }

        .date-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .date-banner-placeholder {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .date-banner-placeholder i {
            font-size: 48px;
            color: #9ca3af;
        }

        /* Компактный контент */
        .date-compact-content {
            padding: 14px 16px;
        }

        .date-compact-title {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }

        .date-compact-time {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
        }

        .date-compact-time i {
            font-size: 14px;
            color: #9ca3af;
        }

        /* Модальное окно деталей */
        .date-modal-content {
            border-radius: 16px;
            border: none;
            overflow: hidden;
        }

        .date-modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .date-modal-header .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .date-modal-body {
            padding: 0;
            max-height: 80vh;
            overflow-y: auto;
        }

        .date-modal-banner {
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: #f3f4f6;
            cursor: pointer;
        }

        .date-modal-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .date-modal-banner-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .date-modal-banner-placeholder i {
            font-size: 64px;
            color: #9ca3af;
        }

        .date-modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin: 20px 20px 12px 20px;
            line-height: 1.3;
        }

        .date-modal-category {
            margin: 0 20px 12px 20px;
        }

        .date-modal-category span {
            display: inline-block;
            font-size: 14px;
            font-weight: 500;
            color: #111827;
            background: #f3f4f6;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .date-modal-category-desc {
            font-size: 13px;
            color: #6b7280;
            margin: 0 20px 16px 20px;
            line-height: 1.5;
        }

        .date-modal-info {
            padding: 16px 20px;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .date-modal-info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #374151;
        }

        .date-modal-info-item i {
            font-size: 16px;
            color: #9ca3af;
            width: 20px;
        }

        /* Красивый счетчик времени */
        .date-modal-countdown-wrapper {
            margin-top: 16px;
            padding: 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .date-modal-countdown-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .date-modal-countdown-label i {
            font-size: 16px;
            color: #94a3b8;
        }

        .date-modal-countdown {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .countdown-timer {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 50px;
        }

        .countdown-value {
            font-size: 24px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
            color: #111827;
            background: white;
            padding: 10px 12px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            min-width: 50px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .countdown-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .countdown-separator {
            font-size: 20px;
            font-weight: 700;
            color: #cbd5e1;
            margin: 0 2px;
            padding-bottom: 20px;
        }

        /* Цветовые варианты счетчика */
        .countdown-timer.countdown-normal .countdown-value {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .countdown-timer.countdown-warning .countdown-value {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            animation: pulse-warning 2s infinite;
        }

        .countdown-timer.countdown-urgent .countdown-value {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            animation: pulse-urgent 1s infinite;
        }

        .countdown-timer.countdown-expired {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
        }

        .countdown-expired-content {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #9ca3af;
        }

        .countdown-expired-content i {
            font-size: 18px;
        }

        @keyframes pulse-warning {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @keyframes pulse-urgent {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            }

            50% {
                transform: scale(1.08);
                box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6);
            }
        }

        /* Статус "Прошло" */
        .date-modal-status-wrapper {
            margin-top: 16px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .date-modal-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #9ca3af;
        }

        .date-modal-status i {
            font-size: 18px;
        }

        .date-modal-actions {
            padding: 16px 20px;
            display: flex;
            gap: 10px;
        }

        .date-modal-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .date-modal-btn-full {
            width: 100%;
        }

        .date-modal-btn-chat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .date-modal-btn-chat:active {
            background: #374151;
            transform: scale(0.98);
        }

        .date-modal-btn-edit {
            background: #f3f4f6;
            color: #111827;
        }

        .date-modal-btn-edit:active {
            background: #e5e7eb;
            transform: scale(0.98);
        }

        .date-modal-btn i {
            font-size: 18px;
        }

        .date-modal-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #dc2626;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
        }

        .date-modal-register-prompt {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .date-modal-register-text {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin: 0;
            padding: 0 10px;
        }

        /* Пустое состояние */
        .dates-empty {
            text-align: center;
            padding: 48px 24px;
            color: #6b7280;
        }

        .dates-empty i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .dates-empty p {
            font-size: 15px;
            margin-bottom: 8px;
            color: #6b7280;
        }

        .dates-empty a {
            color: #111827;
            font-weight: 600;
            text-decoration: none;
        }

        /* Предупреждение */
        .dates-warning {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .dates-warning i {
            font-size: 18px;
            color: #f59e0b;
            margin-top: 2px;
        }

        .dates-warning p {
            font-size: 13px;
            color: #92400e;
            margin: 0;
            line-height: 1.5;
        }

        .dates-warning a {
            color: #92400e;
            font-weight: 600;
            text-decoration: underline;
        }

        /* Модальное окно */
        .dates-modal-warning {
            text-align: center;
            padding: 8px 0;
        }

        .dates-modal-warning i {
            font-size: 32px;
            color: #f59e0b;
            margin-bottom: 12px;
        }

        .dates-modal-warning p {
            font-size: 14px;
            color: #374151;
            margin: 8px 0;
            line-height: 1.5;
        }

        .dates-modal-text {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        /* Диалоговый формат для чатов */
        .chats-dialog-list {
            padding: 0 !important;
        }

        .chat-dialog-item-wrapper {
            position: relative;
            border-bottom: 1px solid #e5e7eb;
        }

        .chat-dialog-item-wrapper:last-child {
            border-bottom: none;
        }

        .chat-dialog-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            text-decoration: none;
            color: inherit;
            transition: background-color 0.2s ease;
            cursor: pointer;
        }

        .chat-dialog-item:active {
            background-color: #f3f4f6;
        }

        .chat-dialog-delete-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: #fee2e2;
            border: none;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 10;
            color: #dc2626;
            opacity: 0;
        }

        .chat-dialog-item-wrapper:hover .chat-dialog-delete-btn {
            opacity: 1;
        }

        .chat-dialog-delete-btn:active {
            background: #fecaca;
            transform: translateY(-50%) scale(0.95);
        }

        .chat-dialog-delete-btn i {
            font-size: 16px;
        }

        .chat-dialog-avatar {
            position: relative;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            margin-right: 12px;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }

        .chat-dialog-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chat-dialog-avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }

        .chat-dialog-avatar-placeholder i {
            font-size: 24px;
            color: white;
            opacity: 0.9;
        }

        .chat-dialog-unread-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #dc2626;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
            border: 2px solid white;
        }

        .chat-dialog-content {
            flex: 1;
            min-width: 0;
        }

        .chat-dialog-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }

        .chat-dialog-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }

        .chat-dialog-time {
            font-size: 12px;
            color: #9ca3af;
            margin-left: 8px;
            flex-shrink: 0;
        }

        .chat-dialog-preview {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .chat-dialog-meta {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chat-dialog-meta i {
            font-size: 12px;
            color: #9ca3af;
        }

        .chat-dialog-arrow {
            color: #9ca3af;
            font-size: 18px;
            margin-left: 8px;
            flex-shrink: 0;
        }

        /* Модальное окно для чатов */
        #myDateChatsModal .modal-body {
            padding: 0;
            max-height: 70vh;
            overflow-y: auto;
        }

        #myDateChatsModal .modal-content {
            border-radius: 16px;
            overflow: hidden;
        }

        #myDateChatsModal .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        #myDateChatsModal .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }
    }

    /* Десктоп версия - современный дизайн */
    @media (min-width: 768px) {
        .mobile-page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Заголовок страницы */
        .dates-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e5e7eb;
        }

        .dates-header-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .chats-dropdown-wrapper {
            position: relative;
            z-index: 1000;
        }

        .btn-my-chats {
            padding: 14px 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            position: relative;
            text-decoration: none;
        }

        .chats-dropdown-arrow {
            font-size: 14px;
            transition: transform 0.2s ease;
        }

        .btn-my-chats.active .chats-dropdown-arrow {
            transform: rotate(180deg);
        }

        .btn-my-chats:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-my-chats.active {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-my-chats:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-my-chats:active {
            transform: translateY(0);
        }

        .btn-my-chats.active {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-my-chats i {
            font-size: 18px;
        }

        .chats-badge {
            background: #dc2626;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 12px;
            min-width: 22px;
            text-align: center;
            line-height: 1.4;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
        }

        /* Выпадающий список чатов - десктоп */
        .chats-dropdown-menu {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            left: auto;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 20px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.15),
                0 8px 24px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(0, 0, 0, 0.05);
            width: 480px;
            max-width: calc(100vw - 40px);
            max-height: 65vh;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999;
            display: none;
            border: 1px solid rgba(236, 72, 153, 0.15);
            backdrop-filter: blur(12px);
        }

        /* Кастомный скроллбар для выпадающего списка - десктоп */
        .chats-dropdown-menu::-webkit-scrollbar {
            width: 8px;
        }

        .chats-dropdown-menu::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 0.5);
            border-radius: 10px;
        }

        .chats-dropdown-menu::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .chats-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #be185d 0%, #9f1239 100%);
            background-clip: padding-box;
        }

        .chats-dropdown-menu.show {
            display: block;
            animation: dropdownFadeIn 0.2s ease;
        }

        .chats-dropdown-empty {
            padding: 64px 32px;
            text-align: center;
            color: #6b7280;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
        }

        .chats-dropdown-empty i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 20px;
            display: block;
            opacity: 0.6;
        }

        .chats-dropdown-empty p {
            font-size: 16px;
            margin: 0;
            color: #9ca3af;
            font-weight: 500;
        }

        .chats-dropdown-item {
            display: flex;
            align-items: center;
            padding: 18px 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid rgba(243, 244, 246, 0.8);
            gap: 18px;
            position: relative;
            background: transparent;
        }

        .chats-dropdown-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .chats-dropdown-item:last-child {
            border-bottom: none;
        }

        .chats-dropdown-item:hover {
            background: linear-gradient(135deg, #fef3f2 0%, #fdf2f8 100%);
            transform: translateX(6px);
            border-left: 4px solid transparent;
        }

        .chats-dropdown-item:hover::before {
            opacity: 1;
        }

        .chats-dropdown-avatar {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            box-shadow:
                0 4px 16px rgba(236, 72, 153, 0.3),
                0 0 0 3px rgba(255, 255, 255, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .chats-dropdown-item:hover .chats-dropdown-avatar {
            transform: scale(1.08);
            box-shadow:
                0 8px 24px rgba(236, 72, 153, 0.4),
                0 0 0 4px rgba(236, 72, 153, 0.15);
        }

        .chats-dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chats-dropdown-avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }

        .chats-dropdown-avatar-placeholder i {
            font-size: 24px;
            color: white;
            opacity: 0.9;
        }

        .chats-dropdown-unread-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 14px;
            min-width: 20px;
            text-align: center;
            line-height: 1.3;
            border: 3px solid white;
            box-shadow:
                0 4px 12px rgba(220, 38, 38, 0.5),
                0 0 0 1px rgba(220, 38, 38, 0.3);
            animation: pulse-badge 2s infinite;
        }

        .chats-dropdown-content {
            flex: 1;
            min-width: 0;
        }

        .chats-dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .chats-dropdown-title {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            letter-spacing: -0.3px;
            transition: color 0.2s ease;
        }

        .chats-dropdown-item:hover .chats-dropdown-title {
            color: #ec4899;
        }

        .chats-dropdown-time {
            font-size: 13px;
            color: #9ca3af;
            margin-left: 12px;
            flex-shrink: 0;
            font-weight: 500;
        }

        .chats-dropdown-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .chats-dropdown-meta {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            background: rgba(243, 244, 246, 0.6);
            padding: 5px 10px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .chats-dropdown-item:hover .chats-dropdown-meta {
            background: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }

        .chats-dropdown-meta i {
            font-size: 13px;
            color: #ec4899;
            transition: color 0.2s ease;
        }

        .chats-dropdown-item:hover .chats-dropdown-meta i {
            color: #be185d;
        }

        .date-item-chat {
            border: 2px solid #10b981;
            border-left: 4px solid #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
        }

        .date-item-chat:hover {
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.2);
            border-color: #059669;
        }

        .dates-title {
            font-size: 36px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-create-date {
            padding: 14px 28px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-create-date:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-create-date:active {
            transform: translateY(0);
        }

        .dates-register-hint {
            font-size: 15px;
            color: #6b7280;
        }

        .dates-register-hint a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .dates-register-hint a:hover {
            color: #764ba2;
        }

        /* Секции */
        .dates-section-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 24px;
            margin-top: 48px;
            position: relative;
            padding-left: 16px;
        }

        .dates-section-title:first-of-type {
            margin-top: 0;
        }

        .dates-section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        /* Список свиданий - сетка */
        .dates-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        /* Карточка свидания для десктопа */
        .date-item-compact {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
        }

        .date-item-compact:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: #667eea;
        }

        .date-item-mine {
            border: 2px solid #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .date-item-mine:hover {
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);
        }

        /* Баннер */
        .date-banner {
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: #f3f4f6;
            position: relative;
        }

        .date-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .date-item-compact:hover .date-banner img {
            transform: scale(1.05);
        }

        .date-banner-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .date-banner-placeholder i {
            font-size: 64px;
            color: #9ca3af;
        }

        /* Контент карточки */
        .date-compact-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .date-compact-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 12px 0;
            line-height: 1.4;
        }

        .date-compact-time {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
            margin-top: auto;
        }

        .date-compact-time i {
            font-size: 16px;
            color: #667eea;
        }

        /* Модальное окно деталей для десктопа */
        .date-modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
            max-width: 700px;
        }

        .date-modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 24px 32px;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }

        .date-modal-header .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        .date-modal-body {
            padding: 0;
            max-height: 85vh;
            overflow-y: auto;
        }

        .date-modal-banner {
            width: 100%;
            height: 300px;
            overflow: hidden;
            background: #f3f4f6;
            cursor: pointer;
            position: relative;
        }

        .date-modal-banner::after {
            content: 'Нажмите для увеличения';
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .date-modal-banner:hover::after {
            opacity: 1;
        }

        .date-modal-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .date-modal-banner:hover img {
            transform: scale(1.05);
        }

        .date-modal-banner-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .date-modal-banner-placeholder i {
            font-size: 80px;
            color: #9ca3af;
        }

        .date-modal-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin: 32px 32px 16px 32px;
            line-height: 1.3;
        }

        .date-modal-category {
            margin: 0 32px 16px 32px;
        }

        .date-modal-category span {
            display: inline-block;
            font-size: 15px;
            font-weight: 600;
            color: #667eea;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid #dbeafe;
        }

        .date-modal-category-desc {
            font-size: 15px;
            color: #6b7280;
            margin: 0 32px 24px 32px;
            line-height: 1.6;
        }

        .date-modal-info {
            padding: 24px 32px;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
            background: #fafbfc;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .date-modal-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            color: #374151;
        }

        .date-modal-info-item i {
            font-size: 20px;
            color: #667eea;
            width: 24px;
        }

        /* Счетчик времени для десктопа */
        .date-modal-countdown-wrapper {
            margin: 24px 32px;
            padding: 24px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            border: 2px solid #e2e8f0;
        }

        .date-modal-countdown-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .date-modal-countdown-label i {
            font-size: 18px;
            color: #94a3b8;
        }

        .date-modal-countdown {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .countdown-timer {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 70px;
        }

        .countdown-value {
            font-size: 32px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
            color: #111827;
            background: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 70px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .countdown-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .countdown-separator {
            font-size: 28px;
            font-weight: 700;
            color: #cbd5e1;
            margin: 0 4px;
            padding-bottom: 28px;
        }

        /* Цветовые варианты счетчика */
        .countdown-timer.countdown-normal .countdown-value {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }

        .countdown-timer.countdown-warning .countdown-value {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
            animation: pulse-warning 2s infinite;
        }

        .countdown-timer.countdown-urgent .countdown-value {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.45);
            animation: pulse-urgent 1s infinite;
        }

        .countdown-timer.countdown-expired {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .countdown-expired-content {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 600;
            color: #9ca3af;
        }

        .countdown-expired-content i {
            font-size: 20px;
        }

        /* Статус "Прошло" */
        .date-modal-status-wrapper {
            margin: 24px 32px;
            padding: 24px;
            background: #f8fafc;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
        }

        .date-modal-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 600;
            color: #9ca3af;
        }

        .date-modal-status i {
            font-size: 20px;
        }

        .date-modal-actions {
            padding: 24px 32px;
            display: flex;
            gap: 12px;
            background: #fafbfc;
        }

        .date-modal-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .date-modal-btn-full {
            width: 100%;
        }

        .date-modal-btn-chat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .date-modal-btn-chat:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .date-modal-btn-edit {
            background: #f3f4f6;
            color: #111827;
            border: 1px solid #e5e7eb;
        }

        .date-modal-btn-edit:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
        }

        .date-modal-btn i {
            font-size: 20px;
        }

        .date-modal-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #dc2626;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 12px;
            min-width: 22px;
            text-align: center;
            line-height: 1.4;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
        }

        .date-modal-register-prompt {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .date-modal-register-text {
            text-align: center;
            font-size: 15px;
            color: #6b7280;
            margin: 0;
            padding: 0 10px;
        }

        /* Пустое состояние */
        .dates-empty {
            text-align: center;
            padding: 80px 40px;
            color: #6b7280;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e5e7eb;
        }

        .dates-empty i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 24px;
        }

        .dates-empty p {
            font-size: 18px;
            margin-bottom: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .dates-empty a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .dates-empty a:hover {
            color: #764ba2;
        }

        /* Предупреждение */
        .dates-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 32px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
        }

        .dates-warning i {
            font-size: 24px;
            color: #f59e0b;
            margin-top: 2px;
        }

        .dates-warning p {
            font-size: 15px;
            color: #92400e;
            margin: 0;
            line-height: 1.6;
        }

        .dates-warning a {
            color: #92400e;
            font-weight: 600;
            text-decoration: underline;
        }

        /* Модальное окно создания */
        .dates-modal-warning {
            text-align: center;
            padding: 16px 0;
        }

        .dates-modal-warning i {
            font-size: 48px;
            color: #f59e0b;
            margin-bottom: 16px;
        }

        .dates-modal-warning p {
            font-size: 16px;
            color: #374151;
            margin: 12px 0;
            line-height: 1.6;
        }

        .dates-modal-text {
            text-align: center;
            font-size: 16px;
            color: #6b7280;
            margin: 0;
        }

        /* Диалоговый формат для чатов - десктоп */
        .chats-dialog-list {
            padding: 0 !important;
        }

        .chat-dialog-item-wrapper {
            position: relative;
            border-bottom: 1px solid #e5e7eb;
        }

        .chat-dialog-item-wrapper:last-child {
            border-bottom: none;
        }

        .chat-dialog-item {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .chat-dialog-item:hover {
            background-color: #f9fafb;
        }

        .chat-dialog-item:active {
            background-color: #f3f4f6;
        }

        .chat-dialog-delete-btn {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: #fee2e2;
            border: none;
            border-radius: 10px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 10;
            color: #dc2626;
            opacity: 0;
        }

        .chat-dialog-item-wrapper:hover .chat-dialog-delete-btn {
            opacity: 1;
        }

        .chat-dialog-delete-btn:hover {
            background: #fecaca;
            transform: translateY(-50%) scale(1.05);
        }

        .chat-dialog-delete-btn:active {
            background: #fca5a5;
            transform: translateY(-50%) scale(0.95);
        }

        .chat-dialog-delete-btn i {
            font-size: 18px;
        }

        .chat-dialog-avatar {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            margin-right: 16px;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .chat-dialog-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chat-dialog-avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }

        .chat-dialog-avatar-placeholder i {
            font-size: 28px;
            color: white;
            opacity: 0.9;
        }

        .chat-dialog-unread-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #dc2626;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 12px;
            min-width: 22px;
            text-align: center;
            line-height: 1.4;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
        }

        .chat-dialog-content {
            flex: 1;
            min-width: 0;
        }

        .chat-dialog-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .chat-dialog-title {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }

        .chat-dialog-time {
            font-size: 13px;
            color: #9ca3af;
            margin-left: 12px;
            flex-shrink: 0;
        }

        .chat-dialog-preview {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .chat-dialog-meta {
            font-size: 14px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chat-dialog-meta i {
            font-size: 14px;
            color: #ec4899;
        }

        .chat-dialog-arrow {
            color: #9ca3af;
            font-size: 20px;
            margin-left: 12px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .chat-dialog-item:hover .chat-dialog-arrow {
            transform: translateX(4px);
            color: #ec4899;
        }

        /* Модальное окно для чатов - десктоп */
        #myDateChatsModal .modal-body {
            padding: 0;
            max-height: 75vh;
            overflow-y: auto;
        }

        #myDateChatsModal .modal-content {
            border-radius: 20px;
            overflow: hidden;
            max-width: 600px;
        }

        #myDateChatsModal .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 24px 32px;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }

        #myDateChatsModal .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }
    }
</style>

<?php
$content = ob_get_clean();
$title = 'Свидания';
include __DIR__ . '/../layout.php';
?>