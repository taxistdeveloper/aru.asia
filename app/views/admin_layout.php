<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Админ-панель - Tanisu App' ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Кастомные стили -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <style>
        /* Стили для админ-панели */
        body {
            background-color: #f5f7fa;
        }

        .admin-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .admin-header .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-layout {
            width: 100%;
            padding: 0 20px 2rem;
        }

        .admin-layout-row {
            min-height: calc(100vh - 140px); /* высота экрана минус шапка */
        }

        .admin-sidebar {
            height: 100%;
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            padding: 1rem 0.75rem;
        }

        .admin-sidebar .nav-link {
            color: #495057;
            font-size: 0.9rem;
            padding: 0.45rem 0.75rem;
            border-radius: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .admin-sidebar .nav-link i {
            font-size: 1rem;
        }

        .admin-sidebar .nav-link:hover {
            background-color: #f1f3f5;
            color: #1e3c72;
        }

        .admin-sidebar .nav-link.active {
            background-color: #1e3c72;
            color: #fff;
        }

        .admin-sidebar .nav-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #adb5bd;
            margin: 0.25rem 0 0.5rem;
            padding: 0 0.25rem;
        }

        .admin-main {
            /* основная колонка с контентом */
        }

        /* Мобильный режим: стиль "как приложение" */
        @media (max-width: 767.98px) {
            body {
                background-color: #f1f3f5;
            }

            .admin-layout {
                padding-bottom: 4rem; /* место под плавающую кнопку */
            }

            .mobile-menu-fab {
                position: fixed;
                right: 1.25rem;
                bottom: 1.25rem;
                z-index: 1040;
                border-radius: 999px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                padding: 0.6rem 1.1rem;
                font-size: 0.95rem;
            }

            .mobile-menu-fab i {
                font-size: 1rem;
            }

            .admin-main {
                padding-bottom: 1rem;
            }
        }

        @media (max-width: 767px) {
            .admin-layout {
                padding: 0 15px 1.5rem;
            }

            .admin-sidebar {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER АДМИН-ПАНЕЛИ -->
    <header class="admin-header">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <a class="navbar-brand" href="<?= Helper::isAdminLoggedIn() ? BASE_URL . 'admin' : BASE_URL . 'manager' ?>">
                        <i class="bi bi-shield-check"></i>
                        <?= Helper::isAdminLoggedIn() ? 'Админ-панель' : 'Панель менеджера' ?>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="adminNavbar">
                        <div class="ms-auto admin-user-info">
                            <?php if (Helper::isAdminLoggedIn()): ?>
                                <span class="text-white">
                                    <i class="bi bi-person-circle"></i>
                                    <?= Helper::escape($_SESSION['admin_email'] ?? 'Администратор') ?>
                                </span>
                                <a href="<?= BASE_URL ?>home" class="btn btn-outline-light btn-sm" title="На сайт">
                                    <i class="bi bi-house"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/logout" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-box-arrow-right"></i> Выход
                                </a>
                            <?php elseif (Helper::isManager()): ?>
                                <span class="text-white">
                                    <i class="bi bi-person-circle"></i>
                                    Менеджер: <?= Helper::escape($_SESSION['user_email'] ?? 'Менеджер') ?>
                                </span>
                                <a href="<?= BASE_URL ?>profile" class="btn btn-outline-light btn-sm" title="Профиль">
                                    <i class="bi bi-person"></i>
                                </a>
                                <a href="<?= BASE_URL ?>auth/logout" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-box-arrow-right"></i> Выход
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- ОСНОВНОЙ КОНТЕНТ С ЛЕВЫМ МЕНЮ -->
    <main class="admin-layout">
        <div class="row g-3 admin-layout-row">
            <aside class="col-12 col-md-3 col-lg-2 d-none d-md-block">
                <div class="admin-sidebar">
                    <?php if (Helper::isAdminLoggedIn()): ?>
                        <div class="nav-title">Администрирование</div>
                        <nav class="nav flex-column mb-2">
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/users') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/ads') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/events') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/feedback') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/send-message') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/stats') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/login') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/activity-logs') === false) ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin">
                                <i class="bi bi-speedometer2"></i>
                                <span>Dashboard</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/stats') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin/stats">
                                <i class="bi bi-graph-up"></i>
                                <span>Статистика</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/users') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin/users">
                                <i class="bi bi-people"></i>
                                <span>Пользователи</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/activity-logs') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin/activity-logs">
                                <i class="bi bi-clock-history"></i>
                                <span>Логи действий</span>
                            </a>
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/events') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/events/all') === false) ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin/events">
                                <i class="bi bi-clock-history"></i>
                                <span>Модерация мероприятий</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/events/all') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin/events/all">
                                <i class="bi bi-calendar-event"></i>
                                <span>Все мероприятия</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/ads') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin/ads">
                                <i class="bi bi-megaphone"></i>
                                <span>Реклама</span>
                            </a>
                            <?php
                            $feedbackBadge = '';
                            if (class_exists('Feedback')) {
                                $feedbackModel = new Feedback();
                                $newCount = $feedbackModel->getNewCount();
                                if ($newCount > 0) {
                                    $feedbackBadge = '<span class="badge bg-warning text-dark ms-auto">' . (int)$newCount . '</span>';
                                }
                            }
                            ?>
                            <a class="nav-link d-flex align-items-center <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/feedback') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin/feedback">
                                <i class="bi bi-chat-dots"></i>
                                <span>Обратная связь</span>
                                <?= $feedbackBadge ?>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/send-message') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>admin/send-message">
                                <i class="bi bi-send"></i>
                                <span>Рассылка</span>
                            </a>
                        </nav>
                        <div class="nav-title">Сайт</div>
                        <nav class="nav flex-column">
                            <a class="nav-link" href="<?= BASE_URL ?>home" target="_blank">
                                <i class="bi bi-house"></i>
                                <span>На сайт</span>
                            </a>
                        </nav>
                    <?php elseif (Helper::isManager()): ?>
                        <div class="nav-title">Панель менеджера</div>
                        <nav class="nav flex-column mb-2">
                            <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/manager') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/stats') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/users') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/categories') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/events') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/dates') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/feedback') === false) ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>manager">
                                <i class="bi bi-speedometer2"></i>
                                <span>Dashboard</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/stats') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>manager/stats">
                                <i class="bi bi-graph-up"></i>
                                <span>Статистика</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/users') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>manager/users">
                                <i class="bi bi-people"></i>
                                <span>Пользователи</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/categories') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>manager/categories">
                                <i class="bi bi-tags"></i>
                                <span>Категории</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/events') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>manager/events">
                                <i class="bi bi-clock-history"></i>
                                <span>Модерация мероприятий</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/events/all') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>manager/events/all">
                                <i class="bi bi-calendar-event"></i>
                                <span>Все мероприятия</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/dates/all') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>manager/dates/all">
                                <i class="bi bi-heart"></i>
                                <span>Все свидания</span>
                            </a>
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/feedback') !== false ? 'active' : '' ?>"
                               href="<?= BASE_URL ?>manager/feedback">
                                <i class="bi bi-chat-dots"></i>
                                <span>Обратная связь</span>
                            </a>
                        </nav>
                        <div class="nav-title">Профиль</div>
                        <nav class="nav flex-column">
                            <a class="nav-link" href="<?= BASE_URL ?>profile" target="_blank">
                                <i class="bi bi-person"></i>
                                <span>Профиль</span>
                            </a>
                        </nav>
                    <?php endif; ?>
                </div>
            </aside>
            <section class="col-12 col-md-9 col-lg-10 admin-main">
                <?= $content ?? '' ?>
            </section>
        </div>
    </main>

    <!-- Мобильное меню (offcanvas) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileAdminMenu" aria-labelledby="mobileAdminMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileAdminMenuLabel">
                <i class="bi bi-shield-lock"></i>
                <?= Helper::isAdminLoggedIn() ? 'Админ-панель' : (Helper::isManager() ? 'Панель менеджера' : 'Панель') ?>
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?php if (Helper::isAdminLoggedIn()): ?>
                <div class="nav-title">Навигация</div>
                <nav class="nav flex-column mb-3">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/users') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/ads') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/events') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/feedback') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/send-message') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/stats') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/login') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/activity-logs') === false) ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/stats') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin/stats">
                        <i class="bi bi-graph-up"></i>
                        <span>Статистика</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/users') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin/users">
                        <i class="bi bi-people"></i>
                        <span>Пользователи</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/activity-logs') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin/activity-logs">
                        <i class="bi bi-clock-history"></i>
                        <span>Логи действий</span>
                    </a>
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/events') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/events/all') === false) ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin/events">
                        <i class="bi bi-clock-history"></i>
                        <span>Модерация мероприятий</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/events/all') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin/events/all">
                        <i class="bi bi-calendar-event"></i>
                        <span>Все мероприятия</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/ads') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin/ads">
                        <i class="bi bi-megaphone"></i>
                        <span>Реклама</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/feedback') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin/feedback">
                        <i class="bi bi-chat-dots"></i>
                        <span>Обратная связь</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/send-message') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>admin/send-message">
                        <i class="bi bi-send"></i>
                        <span>Рассылка</span>
                    </a>
                </nav>
                <div class="nav-title">Другое</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="<?= BASE_URL ?>home" target="_blank">
                        <i class="bi bi-house"></i>
                        <span>На сайт</span>
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Выход</span>
                    </a>
                </nav>
            <?php elseif (Helper::isManager()): ?>
                <div class="nav-title">Навигация</div>
                <nav class="nav flex-column mb-3">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/manager') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/stats') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/users') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/categories') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/events') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/dates') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/feedback') === false) ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>manager">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/stats') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>manager/stats">
                        <i class="bi bi-graph-up"></i>
                        <span>Статистика</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/users') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>manager/users">
                        <i class="bi bi-people"></i>
                        <span>Пользователи</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/categories') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>manager/categories">
                        <i class="bi bi-tags"></i>
                        <span>Категории</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/events') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>manager/events">
                        <i class="bi bi-clock-history"></i>
                        <span>Модерация мероприятий</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/events/all') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>manager/events/all">
                        <i class="bi bi-calendar-event"></i>
                        <span>Все мероприятия</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/dates/all') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>manager/dates/all">
                        <i class="bi bi-heart"></i>
                        <span>Все свидания</span>
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/manager/feedback') !== false ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>manager/feedback">
                        <i class="bi bi-chat-dots"></i>
                        <span>Обратная связь</span>
                    </a>
                </nav>
                <div class="nav-title">Профиль</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="<?= BASE_URL ?>profile" target="_blank">
                        <i class="bi bi-person"></i>
                        <span>Профиль</span>
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>auth/logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Выход</span>
                    </a>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Плавающая кнопка меню только для мобильных -->
    <button class="btn btn-primary mobile-menu-fab d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAdminMenu" aria-controls="mobileAdminMenu">
        <i class="bi bi-list"></i>
        <span>Меню</span>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Кастомные скрипты -->
    <script>
        // Определяем BASE_URL для JavaScript
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>

</html>
