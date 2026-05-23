<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <!-- SEO Meta Tags -->
    <title><?= isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'Aru - Платформа для знакомств' ?></title>
    <meta name="description" content="<?= isset($metaDescription) ? htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') : 'Платформа для знакомств и мероприятий. Найдите свою вторую половинку на Aru.' ?>">
    <?php if (!empty($metaKeywords)): ?>
        <meta name="keywords" content="<?= htmlspecialchars($metaKeywords, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta name="author" content="Aru">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="yandex" content="index, follow">
    <link rel="canonical" href="<?= isset($canonical) ? htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') : BASE_URL ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= isset($ogTitle) ? htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8') : ($title ?? 'Aru - Платформа для знакомств') ?>">
    <meta property="og:description" content="<?= isset($ogDescription) ? htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') : ($metaDescription ?? 'Платформа для знакомств и мероприятий') ?>">
    <meta property="og:image" content="<?= isset($ogImage) ? htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') : (BASE_URL . 'assets/images/logo.jpg') ?>">
    <meta property="og:url" content="<?= isset($ogUrl) ? htmlspecialchars($ogUrl, ENT_QUOTES, 'UTF-8') : BASE_URL ?>">
    <meta property="og:type" content="<?= isset($ogType) ? htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') : 'website' ?>">
    <meta property="og:site_name" content="Aru знакомство">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:locale:alternate" content="kk_KZ">

    <!-- Yandex Verification (если есть верификационный код) -->
    <meta name="yandex-verification" content="3c70019d37da69d3" />

    <!-- Google Search Console Verification (если есть верификационный код) -->
    <!-- <meta name="google-site-verification" content="your-verification-code" /> -->

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= isset($ogTitle) ? htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8') : ($title ?? 'Aru - Платформа для знакомств') ?>">
    <meta name="twitter:description" content="<?= isset($ogDescription) ? htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') : ($metaDescription ?? 'Платформа для знакомств и мероприятий') ?>">
    <meta name="twitter:image" content="<?= isset($ogImage) ? htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') : (BASE_URL . 'assets/images/logo.jpg') ?>">

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="theme-color" content="#667eea">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Aru">

    <!-- Apple iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Aru">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/images/logo.jpg">

    <!-- Android Chrome -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@latest/font/bootstrap-icons.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Кастомные стили -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <style>
        /* Дополнительные стили для адаптивности */
        @media (max-width: 767px) {
            .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
    </style>

    <style>
        /* АДАПТИВНЫЙ ДИЗАЙН */
        /* ПК версия - показываем на экранах больше 768px */
        @media (min-width: 768px) {
            .mobile-only {
                display: none !important;
            }

            .desktop-layout {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            .desktop-nav {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 15px 0;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .desktop-nav .nav-link {
                color: white !important;
                font-weight: 500;
                margin: 0 10px;
            }

            .desktop-nav .nav-link:hover {
                color: #f0f0f0 !important;
            }
        }

        /* МОБИЛЬНАЯ ВЕРСИЯ - показываем на экранах меньше 768px */
        @media (max-width: 767px) {
            .desktop-only {
                display: none !important;
            }

            .mobile-bottom-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                border-top: 1px solid #ddd;
                padding: 10px 0;
                z-index: 1000;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            }

            .mobile-bottom-nav .nav-item {
                flex: 1;
                text-align: center;
            }

            .mobile-bottom-nav .nav-link {
                color: #666;
                font-size: 12px;
                padding: 5px;
            }

            .mobile-bottom-nav .nav-link.active {
                color: #667eea;
            }

            .mobile-top-nav {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 15px;
                color: white;
            }

            .mobile-top-nav img {
                height: 32px;
                width: auto;
                object-fit: contain;
            }

            .mobile-top-nav .position-absolute {
                z-index: 10;
            }

            /* На очень маленьких экранах уменьшаем размер текста кнопки */
            @media (max-width: 360px) {
                .mobile-top-nav button[data-bs-target="#feedbackModal"] {
                    font-size: 10px !important;
                    padding: 4px 8px !important;
                }
            }

            body {
                padding-bottom: 70px;
                /* Место для нижней навигации */
            }
        }

        /* ОБЩИЕ СТИЛИ */
        .user-photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        @media (max-width: 767px) {
            .user-photo-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
        }

        .user-card-link {
            height: 100%;
            display: flex;
        }

        .user-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .user-card:hover {
            transform: translateY(-5px);
        }

        .user-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .user-card>div {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .user-card-placeholder {
            flex-shrink: 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* Стили для кнопки установки PWA */
        #pwa-install-button {
            border-radius: 20px;
            font-size: 12px;
        }

        #pwa-install-banner {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Анимированная иконка профиля */
        .profile-icon-link {
            display: inline-block;
            text-decoration: none;
            position: relative;
            transition: transform 0.3s ease;
        }

        .profile-icon-link:hover {
            transform: scale(1.1);
        }

        .profile-icon-link:active {
            transform: scale(0.95);
        }

        .profile-icon {
            font-size: 28px !important;
            color: #ffffff !important;
            display: inline-block;
            position: relative;
            animation: gentle-pulse 2s ease-in-out infinite;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
            z-index: 1;
        }

        .profile-icon::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            animation: gentle-glow 2s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes gentle-pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
                text-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
            }

            50% {
                opacity: 0.9;
                transform: scale(1.05);
                text-shadow: 0 0 12px rgba(255, 255, 255, 0.7);
            }
        }

        @keyframes gentle-glow {

            0%,
            100% {
                opacity: 0.3;
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                opacity: 0.5;
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        /* Стили для бейджей уведомлений */
        .mobile-bottom-nav .badge {
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);
            animation: badge-pulse 2s ease-in-out infinite;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        @keyframes badge-pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 3px 8px rgba(220, 53, 69, 0.6);
            }
        }

        .desktop-nav .badge {
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);
            font-weight: 600;
            animation: badge-pulse 2s ease-in-out infinite;
        }

        /* Анимированный логотип */
        .logo-link {
            display: inline-block;
            text-decoration: none;
            position: relative;
            transition: transform 0.3s ease;
        }

        .logo-link:hover {
            transform: scale(1.1);
        }

        .logo-link:active {
            transform: scale(0.95);
        }

        .logo-animated {
            display: inline-block;
            position: relative;
            animation: gentle-pulse 2s ease-in-out infinite;
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.5));
            z-index: 1;
        }

        .logo-animated::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            animation: gentle-glow 2s ease-in-out infinite;
            z-index: -1;
        }

        /* Шапка деда мороза на логотипе */
        .logo-wrapper {
            position: relative;
            display: inline-block;
            overflow: visible !important;
        }

        .logo-link {
            overflow: visible !important;
        }

        .mobile-top-nav {
            overflow: visible !important;
        }

        .mobile-top-nav>div {
            overflow: visible !important;
        }

        /* Красная шапка деда мороза (треугольник с изгибом) */
        .santa-hat {
            position: absolute;
            top: -22px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 12px solid transparent;
            border-right: 12px solid transparent;
            border-top: 22px solid #dc2626;
            z-index: 1000 !important;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            pointer-events: none;
        }

        /* Белая пушистая опушка внизу шапки */
        .santa-hat::before {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 28px;
            height: 8px;
            background: white;
            border-radius: 4px;
            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.15),
                inset 0 1px 2px rgba(255, 255, 255, 0.8);
            z-index: 1001 !important;
        }

        /* Белый помпон на конце шапки */
        .santa-hat::after {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 10px;
            height: 10px;
            background: radial-gradient(circle at 30% 30%, #ffffff, #f0f0f0);
            border-radius: 50%;
            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.3),
                inset -2px -2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1002 !important;
        }
    </style>
</head>

<body<?= isset($bodyClass) ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
    <?php
    // Определяем, находимся ли мы на landing странице
    $currentUriForNav = $_SERVER['REQUEST_URI'] ?? '/';
    $currentUriForNav = strtok($currentUriForNav, '?');
    $scriptNameForNav = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptNameForNav !== '/' && $scriptNameForNav !== '\\') {
        $currentUriForNav = str_replace($scriptNameForNav, '', $currentUriForNav);
    }
    $currentUriForNav = trim($currentUriForNav, '/');
    $isLandingPageNav = empty($currentUriForNav) && !Helper::isLoggedIn();
    ?>
    <?php if (!$isLandingPageNav): ?>
        <!-- НАВИГАЦИЯ ДЛЯ ПК -->
        <nav class="desktop-only desktop-nav">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= BASE_URL ?>" class="navbar-brand text-white">
                        <strong>Aru</strong>
                    </a>
                    <div class="d-flex">
                        <?php
                        // Проверяем семейный статус для скрытия пунктов меню
                        $showDatingMenu = true;
                        if (Helper::isLoggedIn()) {
                            $userModel = new User();
                            $currentUser = $userModel->findById(Helper::getUserId());
                            if ($currentUser && ($currentUser['marital_status'] ?? '') === 'married') {
                                $showDatingMenu = false;
                            }
                        }
                        ?>
                        <?php if ($showDatingMenu): ?>
                            <a href="<?= BASE_URL ?><?= Helper::isLoggedIn() ? 'platform' : '' ?>" class="nav-link">Главная</a>
                            <a href="<?= BASE_URL ?>dates" class="nav-link">Свидания</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>events" class="nav-link">Мероприятия</a>
                        <a href="<?= BASE_URL ?>map" class="nav-link">Карта</a>
                        <a href="<?= BASE_URL ?>messages" class="nav-link position-relative">
                            Уведомления
                            <span id="messages-badge-desktop" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                0
                            </span>
                        </a>
                        <?php if (Helper::isLoggedIn()): ?>
                            <a href="<?= BASE_URL ?>profile" class="nav-link">Мой кабинет</a>
                            <?php if (Helper::isManager()): ?>
                                <a href="<?= BASE_URL ?>manager" class="nav-link">Панель менеджера</a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>auth/logout" class="nav-link">Выход</a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>auth/login" class="nav-link">Вход</a>
                            <a href="<?= BASE_URL ?>auth/register" class="nav-link">Регистрация</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- НАВИГАЦИЯ ДЛЯ МОБИЛЬНЫХ -->
    <?php if (!$isLandingPageNav): ?>
        <div class="mobile-only mobile-top-nav">
            <div class="d-flex justify-content-between align-items-center position-relative">
                <!-- Логотип слева -->
                <a href="<?= BASE_URL ?>info" class="logo-link d-flex align-items-center text-white text-decoration-none">
                    <div class="logo-wrapper position-relative">

                        <img src="<?= BASE_URL ?>assets/images/logo.jpg" alt="Aru App" class="logo-animated" style="height: 40px; width: auto; margin-right: 8px;">
                    </div>
                    <!-- <strong>Aru</strong> -->
                </a>



                <!-- Правая часть: PWA кнопка и профиль -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Кнопка установки PWA — показывается после удаления приложения при повторном посещении -->

                    <?php if (Helper::isLoggedIn()):
                        // Получаем пол пользователя для отображения соответствующей иконки
                        $userModel = new User();
                        $currentUser = $userModel->findById(Helper::getUserId());
                        $userGender = $currentUser['gender'] ?? null;

                        // Выбираем иконку в зависимости от пола
                        $profileIconClass = 'bi bi-person-circle'; // Значение по умолчанию
                        if ($userGender === 'female') {
                            $profileIconClass = 'bi bi-person-standing-dress'; // Иконка женщины
                        } elseif ($userGender === 'male') {
                            $profileIconClass = 'bi bi-person'; // Иконка мужчины
                        }
                    ?>
                        <a href="<?= BASE_URL ?>profile" class="profile-icon-link" title="Профиль">
                            <i class="<?= htmlspecialchars($profileIconClass) ?> profile-icon"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>auth/login" class="text-white">
                            <i class="bi bi-box-arrow-in-right" style="font-size: 24px;"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- БАННЕР ДЛЯ УСТАНОВКИ PWA — показывается на мобильных после удаления приложения -->
    <div id="pwa-install-banner" class="mobile-only" style="display: none; position: fixed; bottom: 70px; left: 0; right: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; z-index: 999; box-shadow: 0 -2px 10px rgba(0,0,0,0.2);">
        <div class="d-flex justify-content-between align-items-start" style="gap: 12px;">
            <div class="flex-grow-1">
                <strong style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 900; line-height: 1.15;">
                    <i class="bi bi-plus-lg" style="font-size: 16px; line-height: 1;"></i>
                    Добавить на главный экран
                </strong>
                <p class="mb-0" style="font-size: 11.5px; opacity: 0.92; line-height: 1.2; margin-top: -1px;">Установите приложение для быстрого доступа</p>
            </div>
            <div class="d-flex gap-2" style="align-items: flex-start;">
                <button id="pwa-install-banner-button" class="btn btn-sm btn-light" style="padding: 7px 16px; border-radius: 12px; font-weight: 800; box-shadow: 0 10px 22px rgba(0,0,0,0.16); margin-top: -4px;">
                    Установить
                </button>
                <button id="pwa-install-close"
                    class="btn btn-sm text-white"
                    style="padding: 0; width: 34px; height: 34px; border-radius: 999px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 900; margin-top: -6px;">
                    <i class="bi bi-x-lg" style="font-size: 1.15rem; font-weight: 900; line-height: 1;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ОСНОВНОЙ КОНТЕНТ -->
    <div class="<?= View::isMobile() ? 'container-fluid px-3' : 'desktop-layout' ?>">
        <?= $content ?? '' ?>
    </div>

    <!-- НИЖНЯЯ НАВИГАЦИЯ ДЛЯ МОБИЛЬНЫХ -->
    <?php
    // Определяем текущий маршрут для активного пункта меню
    $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
    $currentUri = strtok($currentUri, '?'); // Убираем параметры запроса
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptName !== '/' && $scriptName !== '\\') {
        $currentUri = str_replace($scriptName, '', $currentUri);
    }
    $currentUri = trim($currentUri, '/');

    // Определяем активные классы для каждого пункта меню
    $isHomeActive = empty($currentUri) || $currentUri === 'home' || $currentUri === 'platform';
    $isDatesActive = strpos($currentUri, 'dates') === 0;
    $isEventsActive = strpos($currentUri, 'events') === 0;
    $isMapActive = strpos($currentUri, 'map') === 0;
    $isMessagesActive = strpos($currentUri, 'messages') === 0;
    ?>
    <?php if (!$isLandingPageNav): ?>
        <nav class="mobile-only mobile-bottom-nav">
            <?php
            // Проверяем семейный статус для скрытия пунктов меню
            $showDatingMenu = true;
            if (Helper::isLoggedIn()) {
                $userModel = new User();
                $currentUser = $userModel->findById(Helper::getUserId());
                if ($currentUser && ($currentUser['marital_status'] ?? '') === 'married') {
                    $showDatingMenu = false;
                }
            }
            ?>
            <div class="d-flex">
                <?php if ($showDatingMenu): ?>
                    <div class="nav-item">
                        <a href="<?= BASE_URL ?><?= Helper::isLoggedIn() ? 'platform' : '' ?>" class="nav-link d-flex flex-column align-items-center <?= $isHomeActive ? 'active' : '' ?>">
                            <i class="bi bi-house-door" style="font-size: 20px;"></i>
                            <span>Главная</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?= BASE_URL ?>dates" class="nav-link d-flex flex-column align-items-center <?= $isDatesActive ? 'active' : '' ?>">
                            <span class="position-relative" style="display: inline-block;">
                                <i class="bi bi-heart" style="font-size: 20px;"></i>
                                <span id="dates-badge" class="position-absolute badge rounded-pill bg-danger" style="display: none; top: -5px; right: -8px; font-size: 0.65rem; min-width: 18px; padding: 2px 5px; z-index: 10; line-height: 1;">
                                    0
                                </span>
                            </span>
                            <span>Свидания</span>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="nav-item">
                    <a href="<?= BASE_URL ?>events" class="nav-link d-flex flex-column align-items-center <?= $isEventsActive ? 'active' : '' ?>">
                        <span class="position-relative" style="display: inline-block;">
                            <i class="bi bi-calendar-event" style="font-size: 20px;"></i>
                            <span id="events-badge" class="position-absolute badge rounded-pill bg-danger" style="display: none; top: -5px; right: -8px; font-size: 0.65rem; min-width: 18px; padding: 2px 5px; z-index: 10; line-height: 1;">
                                0
                            </span>
                        </span>
                        <span>Мероприятия</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= BASE_URL ?>map" class="nav-link d-flex flex-column align-items-center <?= $isMapActive ? 'active' : '' ?>">
                        <i class="bi bi-map" style="font-size: 20px;"></i>
                        <span>Карта</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= BASE_URL ?>messages" class="nav-link d-flex flex-column align-items-center position-relative <?= $isMessagesActive ? 'active' : '' ?>">
                        <span class="position-relative" style="display: inline-block;">
                            <i class="bi bi-bell" style="font-size: 20px;"></i>
                            <span id="messages-badge" class="position-absolute badge rounded-pill bg-danger" style="display: none; top: -5px; right: -8px; font-size: 0.65rem; min-width: 18px; padding: 2px 5px; z-index: 10; line-height: 1;">
                                0
                            </span>
                        </span>
                        <span>Уведомления</span>
                    </a>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- МОДАЛЬНОЕ ОКНО "СООБЩЕНИЕ РАЗРАБОТЧИКУ" -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">
                        <i class="bi bi-chat-dots"></i> Сообщение разработчику
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <form id="feedbackForm" method="POST" action="<?= BASE_URL ?>feedback/submit">
                    <div class="modal-body">
                        <div id="feedbackAlert" class="alert" style="display: none;"></div>

                        <!-- Anti-spam: honeypot + timestamp (боты часто заполняют скрытые поля) -->
                        <div style="position:absolute; left:-10000px; top:auto; width:1px; height:1px; overflow:hidden;">
                            <label for="feedbackWebsite">Website</label>
                            <input type="text" id="feedbackWebsite" name="website" tabindex="-1" autocomplete="off" value="">
                        </div>
                        <input type="hidden" id="feedbackStartedAt" name="_fb_started_at" value="">

                        <div class="mb-3">
                            <label for="feedbackType" class="form-label">Тип сообщения <span class="text-danger">*</span></label>
                            <select class="form-select" id="feedbackType" name="type" required>
                                <option value="">Выберите тип</option>
                                <option value="bug">Нашли ошибку</option>
                                <option value="suggestion">Пожелание / Предложение</option>
                                <option value="feature">Предложение новой функции</option>
                                <option value="other">Другое</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="feedbackSubject" class="form-label">Тема <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="feedbackSubject" name="subject" required
                                placeholder="Краткое описание проблемы или предложения">
                        </div>

                        <div class="mb-3">
                            <label for="feedbackMessage" class="form-label">Сообщение <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="feedbackMessage" name="message" rows="5" required
                                placeholder="Опишите подробно проблему, пожелание или предложение..."></textarea>
                        </div>

                        <?php if (Helper::isLoggedIn()): ?>
                            <?php
                            $userModel = new User();
                            $currentUser = $userModel->findById(Helper::getUserId());
                            ?>
                            <div class="mb-3">
                                <label for="feedbackEmail" class="form-label">Email для связи</label>
                                <input type="email" class="form-control" id="feedbackEmail" name="email"
                                    value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"
                                    placeholder="Ваш email">
                                <small class="form-text text-muted">Оставьте пустым, если не хотите получать ответ</small>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label for="feedbackEmail" class="form-label">Email для связи</label>
                                <input type="email" class="form-control" id="feedbackEmail" name="email"
                                    placeholder="Ваш email (необязательно)">
                                <small class="form-text text-muted">Оставьте пустым, если не хотите получать ответ</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Отправить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Кастомные скрипты -->
    <script>
        // Определяем BASE_URL для JavaScript
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <?php if (Helper::isLoggedIn()): ?>
        <script src="<?= BASE_URL ?>assets/js/push-notifications.js"></script>
    <?php endif; ?>

    <!-- Скрипт для обработки формы обратной связи -->
    <script>
        const feedbackModal = document.getElementById('feedbackModal');
        const feedbackForm = document.getElementById('feedbackForm');
        const feedbackAlert = document.getElementById('feedbackAlert');
        const feedbackSubmitBtn = feedbackForm?.querySelector('button[type="submit"]');

        // Проверка статуса при открытии модального окна
        feedbackModal?.addEventListener('show.bs.modal', async function() {
            const alertDiv = document.getElementById('feedbackAlert');
            alertDiv.style.display = 'none';

            // Anti-spam: проставляем время открытия формы и очищаем honeypot
            const startedAtInput = document.getElementById('feedbackStartedAt');
            if (startedAtInput) startedAtInput.value = Date.now().toString();
            const hp = document.getElementById('feedbackWebsite');
            if (hp) hp.value = '';

            // Получаем email из формы
            const emailInput = document.getElementById('feedbackEmail');
            const email = emailInput?.value || '';

            try {
                const url = BASE_URL + 'feedback/check-status' + (email ? '?email=' + encodeURIComponent(email) : '');
                const response = await fetch(url);
                const result = await response.json();

                if (result.hasActive) {
                    // Есть активная заявка - блокируем форму
                    alertDiv.className = 'alert alert-warning';
                    alertDiv.textContent = result.message || 'Ваша заявка еще обрабатывается. Пожалуйста, дождитесь ответа администратора.';
                    alertDiv.style.display = 'block';

                    // Блокируем все поля формы
                    feedbackForm?.querySelectorAll('input, textarea, select, button[type="submit"]').forEach(el => {
                        el.disabled = true;
                    });
                } else if (result.message && result.lastStatus && ['resolved', 'closed'].includes(result.lastStatus)) {
                    // Последняя заявка была resolved/closed - показываем информационное сообщение
                    alertDiv.className = 'alert alert-info';
                    alertDiv.textContent = result.message || 'Исправили ваш запрос. Можете еще раз отправить заявку, если что-то нашли.';
                    alertDiv.style.display = 'block';

                    // Разрешаем отправку
                    feedbackForm?.querySelectorAll('input, textarea, select, button[type="submit"]').forEach(el => {
                        el.disabled = false;
                    });
                } else {
                    // Нет активных заявок - разрешаем отправку
                    alertDiv.style.display = 'none';
                    feedbackForm?.querySelectorAll('input, textarea, select, button[type="submit"]').forEach(el => {
                        el.disabled = false;
                    });
                }
            } catch (error) {
                console.error('Ошибка при проверке статуса:', error);
                // При ошибке разрешаем отправку
                feedbackForm?.querySelectorAll('input, textarea, select, button[type="submit"]').forEach(el => {
                    el.disabled = false;
                });
            }
        });

        // Сброс формы при закрытии модального окна
        feedbackModal?.addEventListener('hidden.bs.modal', function() {
            feedbackForm?.reset();
            feedbackAlert.style.display = 'none';
            feedbackForm?.querySelectorAll('input, textarea, select, button[type="submit"]').forEach(el => {
                el.disabled = false;
            });
        });

        // Обработка отправки формы
        feedbackForm?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            const alertDiv = document.getElementById('feedbackAlert');
            const originalBtnText = submitBtn.innerHTML;

            // Показываем индикатор загрузки
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Отправка...';
            alertDiv.style.display = 'none';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alertDiv.className = 'alert alert-success';
                    alertDiv.textContent = result.message || 'Спасибо! Ваше сообщение отправлено разработчикам.';
                    alertDiv.style.display = 'block';
                    form.reset();

                    // Закрываем модальное окно через 2 секунды
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
                        if (modal) modal.hide();
                    }, 2000);
                } else {
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.textContent = result.message || 'Произошла ошибка при отправке сообщения.';
                    alertDiv.style.display = 'block';
                }
            } catch (error) {
                alertDiv.className = 'alert alert-danger';
                alertDiv.textContent = 'Произошла ошибка при отправке сообщения. Попробуйте позже.';
                alertDiv.style.display = 'block';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    </script>
    </body>

</html>