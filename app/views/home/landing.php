<?php

/**
 * LANDING СТРАНИЦА
 * Промо-страница приложения
 */

// Подключаем базовый шаблон
ob_start();

// Получаем SEO данные - оптимизировано для поисковых запросов "свидание", "мероприятия", "aru знакомство"
$seo = $seo ?? [];
$seoTitle = $seo['title'] ?? 'Свидания и Мероприятия - Aru | Платформа для знакомств и событий | aru.asia';
$seoDescription = $seo['description'] ?? 'Найдите свидание или создайте мероприятие на Aru. Платформа для знакомств и организации событий в Казахстане. Свидания онлайн, мероприятия, знакомства на aru.asia';
$seoKeywords = $seo['keywords'] ?? 'свидание, свидания, мероприятие, мероприятия, aru знакомство, aru, aru.asia, знакомства, знакомства онлайн, знакомства в Казахстане, сайт знакомств, найти свидание, создать мероприятие, события, организация мероприятий';
$ogTitle = $seo['og_title'] ?? $seoTitle;
$ogDescription = $seo['og_description'] ?? $seoDescription;
$ogImage = $seo['og_image'] ?? BASE_URL . 'assets/images/logo.jpg';
$ogUrl = $seo['og_url'] ?? BASE_URL;
$ogType = $seo['og_type'] ?? 'website';
$canonical = $seo['canonical'] ?? BASE_URL;
?>

<style>
    /* LANDING PAGE STYLES - МИНИМАЛИСТИЧНЫЙ ДИЗАЙН */
    .landing-container {
        min-height: 100vh;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .landing-content {
        text-align: center;
        color: #1a1a1a;
        max-width: 600px;
        width: 100%;
    }

    .landing-logo {
        font-size: 48px;
        margin-bottom: 24px;
        color: #667eea;
    }

    .landing-brand-logo-wrap {
        margin-bottom: 24px;
    }

    .landing-brand-logo {
        width: 120px;
        height: auto;
        max-width: 40vw;
        display: block;
        margin: 0 auto;
        border-radius: 16px;
    }

    .landing-title {
        font-size: 36px;
        font-weight: 700;
        margin-bottom: 16px;
        color: #1a1a1a;
        line-height: 1.3;
    }

    .landing-subtitle {
        font-size: 18px;
        margin-bottom: 40px;
        color: #6b7280;
        line-height: 1.6;
        font-weight: 400;
    }

    .landing-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: nowrap;
        margin-bottom: 60px;
    }

    .landing-more-btn-wrap {
        margin-top: 24px;
        text-align: center;
    }

    .landing-btn {
        padding: 14px 32px;
        font-size: 16px;
        font-weight: 500;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .landing-btn-primary {
        background: #667eea;
        color: white !important;
    }

    .landing-btn-primary:hover {
        background: #5568d3;
        color: white !important;
        text-decoration: none;
    }

    .landing-btn-primary a,
    .landing-btn.landing-btn-primary {
        color: white !important;
    }

    .landing-btn-secondary {
        background: transparent;
        color: #667eea;
        border: 1px solid #667eea;
    }

    .landing-btn-secondary:hover {
        background: #f3f4f6;
        color: #667eea;
        text-decoration: none;
    }

    .landing-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 24px;
        margin-top: 60px;
        padding-top: 60px;
        border-top: 1px solid #e5e7eb;
    }

    .landing-feature {
        padding: 0;
        background: transparent;
        border: none;
        transition: none;
    }

    .landing-feature:hover {
        transform: none;
        box-shadow: none;
        border-color: transparent;
    }

    .landing-feature-icon {
        font-size: 32px;
        margin-bottom: 12px;
        color: #667eea;
        display: block;
    }

    .landing-feature-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #1a1a1a;
    }

    .landing-feature-text {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
    }

    /* Дополнительная секция с преимуществами */
    .landing-advantages {
        margin-top: 60px;
        padding: 0;
        background: transparent;
        border-radius: 0;
    }

    .landing-advantages-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 32px;
        color: #1a1a1a;
        text-align: center;
    }

    .landing-advantages-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-width: 500px;
        margin: 0 auto;
    }

    .landing-advantage-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0;
        background: transparent;
        border-radius: 0;
        box-shadow: none;
    }

    .landing-advantage-icon {
        font-size: 20px;
        color: #667eea;
        flex-shrink: 0;
    }

    .landing-advantage-text {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.6;
        text-align: left;
    }

    /* Mobile styles */
    @media (max-width: 767px) {
        .landing-container {
            padding: 32px 16px;
        }

        .landing-logo {
            font-size: 40px;
            margin-bottom: 20px;
        }

        .landing-brand-logo-wrap {
            margin-bottom: 16px;
        }

        .landing-brand-logo {
            width: 96px;
        }

        .landing-title {
            font-size: 28px;
            margin-bottom: 12px;
        }

        .landing-subtitle {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .landing-buttons {
            flex-direction: row;
            align-items: center;
            margin-bottom: 12px;
            gap: 10px;
        }

        .landing-more-btn-wrap {
            margin-top: 10px;
            margin-bottom: 8px;
        }

        .landing-btn {
            flex: 1;
            padding: 14px 20px;
            font-size: 15px;
            justify-content: center;
            min-width: 0;
        }

        .landing-features {
            grid-template-columns: 1fr;
            gap: 32px;
            margin-top: 48px;
            padding-top: 48px;
        }

        .landing-feature-icon {
            font-size: 28px;
        }

        .landing-advantages {
            margin-top: 48px;
        }

        .landing-advantages-title {
            font-size: 20px;
            margin-bottom: 24px;
        }

        .landing-advantages-list {
            gap: 12px;
        }
    }

    /* Стили для секции статистики */
    .landing-stats {
        margin-top: 60px;
        padding: 48px 0 0 0;
        background: transparent;
        border-radius: 0;
        text-align: center;
        border-top: 1px solid #e5e7eb;
    }

    .landing-stats-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 32px;
        color: #1a1a1a;
    }

    .landing-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        max-width: 500px;
        margin: 0 auto;
    }

    .landing-stat-item {
        padding: 0;
        background: transparent;
        border-radius: 0;
        box-shadow: none;
        transition: none;
    }

    .landing-stat-item:hover {
        transform: none;
        box-shadow: none;
    }

    .landing-stat-icon {
        font-size: 24px;
        color: #667eea;
        margin-bottom: 12px;
    }

    .landing-stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 4px;
        line-height: 1;
    }

    .landing-stat-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 400;
    }

    /* Mobile styles для статистики */
    @media (max-width: 767px) {
        .landing-stats {
            margin-top: 48px;
            padding-top: 48px;
        }

        .landing-stats-title {
            font-size: 20px;
            margin-bottom: 24px;
        }

        .landing-stats-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .landing-stat-icon {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .landing-stat-number {
            font-size: 24px;
        }

        .landing-stat-label {
            font-size: 11px;
        }
    }

    /* Стили для секции рекламы */
    .landing-ads {
        margin-top: 60px;
        padding: 48px 0 0 0;
        background: transparent;
        border-top: 1px solid #e5e7eb;
    }

    .landing-ads-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 32px;
        color: #1a1a1a;
        text-align: center;
    }

    .landing-ads-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        max-width: 900px;
        margin: 0 auto;
    }

    .landing-ad-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .landing-ad-card:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        text-decoration: none;
        color: inherit;
    }

    .landing-ad-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
    }

    .landing-ad-card-placeholder {
        width: 100%;
        height: 200px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .landing-ad-card-placeholder i {
        font-size: 48px;
        color: #d1d5db;
    }

    .landing-ad-card-info {
        padding: 16px;
    }

    .landing-ad-card-name {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .landing-ad-card-country {
        font-size: 13px;
        color: #6b7280;
    }

    .landing-ads-empty {
        text-align: center;

    }

    .landing-ads-empty-text {
        color: #9ca3af;
        font-size: 16px;
        margin: 0;
    }

    .landing-ads-action {
        text-align: center;
        margin-top: 32px;
    }

    /* Mobile styles для рекламы */
    @media (max-width: 767px) {
        .landing-ads {
            margin-top: 48px;
            padding-top: 48px;
        }

        .landing-ads-title {
            font-size: 20px;
            margin-bottom: 24px;
        }

        .landing-ads-grid {
            grid-template-columns: 1fr;
            gap: 16px;
            max-width: 100%;
        }

        .landing-ad-card img,
        .landing-ad-card-placeholder {
            height: 180px;
        }

        .landing-ad-card-info {
            padding: 14px;
        }

        .landing-ad-card-name {
            font-size: 15px;
        }

        .landing-ad-card-country {
            font-size: 12px;
        }
    }

    /* Стили для секции пользователей */
    .landing-users {
        margin-top: 60px;
        padding: 48px 0 0 0;
        background: transparent;
        border-top: 1px solid #e5e7eb;
    }

    .landing-users-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 32px;
        color: #1a1a1a;
        text-align: center;
    }

    .landing-users-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 16px;
        max-width: 700px;
        margin: 0 auto;
    }

    .landing-user-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .landing-user-card:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        text-decoration: none;
        color: inherit;
    }

    .landing-user-card--no-photo {
        border-style: dashed;
        border-color: #d1d5db;
    }

    .landing-user-card--no-photo .landing-user-card-media {
        background: linear-gradient(160deg, #eef0f7 0%, #e8eaf0 45%, #e5e7eb 100%);
    }

    .landing-user-card-placeholder-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .landing-user-card-media {
        position: relative;
        width: 100%;
        height: 180px;
        overflow: hidden;
        background: #f3f4f6;
    }

    .landing-user-card-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        z-index: 1;
    }

    .landing-user-card-img.landing-user-card-img--err {
        display: none !important;
    }

    .landing-user-card-placeholder {
        width: 100%;
        height: 100%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .landing-user-card-placeholder--under {
        position: absolute;
        inset: 0;
        z-index: 0;
    }

    .landing-user-card-placeholder i {
        font-size: 40px;
        color: #d1d5db;
    }

    .landing-user-card-info {
        padding: 12px;
        text-align: center;
    }

    .landing-user-card-name {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .landing-user-card-age {
        font-size: 12px;
        color: #6b7280;
    }

    /* Mobile styles для пользователей */
    @media (max-width: 767px) {
        .landing-users {
            margin-top: 16px;
            padding-top: 16px;
        }

        .landing-users-title {
            font-size: 20px;
            margin-bottom: 24px;
        }

        .landing-users-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            max-width: 100%;
        }

        .landing-user-card-media {
            height: 160px;
        }

        .landing-user-card-info {
            padding: 10px;
        }

        .landing-user-card-name {
            font-size: 13px;
        }

        .landing-user-card-age {
            font-size: 11px;
        }
    }

    /* Стили для Footer */
    .landing-footer {
        margin-top: 60px;
        padding: 32px 0 0 0;
        text-align: center;
        border-top: 1px solid #e5e7eb;
    }

    .landing-footer-content {
        max-width: 600px;
        margin: 0 auto;
    }

    .landing-footer-text {
        font-size: 14px;
        color: #9ca3af;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .landing-footer-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 400;
        transition: all 0.2s ease;
    }

    .landing-footer-link:hover {
        color: #5568d3;
        text-decoration: none;
    }

    .landing-footer-heart {
        font-size: 14px;
    }

    .landing-footer-aru {
        color: #9ca3af;
        font-weight: 400;
    }

    /* Mobile styles для Footer */
    @media (max-width: 767px) {
        .landing-footer {
            margin-top: 48px;
            padding-top: 32px;
        }

        .landing-footer-text {
            font-size: 12px;
            gap: 4px;
        }

        /* Отступ снизу для контента, чтобы не перекрывалась навигацией */
        .landing-container {
            padding-bottom: 90px;
        }
    }

    /* Стили для мобильной нижней навигации */
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
        display: none;
    }

    @media (max-width: 767px) {
        .mobile-bottom-nav {
            display: block;
        }

        .mobile-bottom-nav .nav-item {
            flex: 1;
            text-align: center;
        }

        .mobile-bottom-nav .nav-link {
            color: #666;
            font-size: 12px;
            padding: 5px;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .mobile-bottom-nav .nav-link.active {
            color: #667eea;
        }

        .mobile-bottom-nav .nav-link i {
            font-size: 20px;
        }
    }

    /* Стили для модального окна */
    .info-modal .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .info-modal .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 24px;
    }

    .info-modal .modal-title {
        font-size: 24px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .info-modal .modal-body {
        padding: 24px;
        color: #6b7280;
        font-size: 15px;
        line-height: 1.7;
    }

    .info-modal .modal-body p {
        margin-bottom: 20px;
    }

    .info-modal .modal-body strong {
        color: #1a1a1a;
        font-weight: 600;
    }

    .info-modal .modal-body a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .info-modal .modal-body a:hover {
        color: #5568d3;
        text-decoration: underline;
    }

    .info-modal .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 16px 24px;
    }

    .info-modal .btn-close {
        background: none;
        opacity: 0.5;
    }

    .info-modal .btn-close:hover {
        opacity: 1;
    }
</style>

<script>
    // Анимация счетчика статистики
    document.addEventListener('DOMContentLoaded', function() {
        const statNumbers = document.querySelectorAll('.landing-stat-number');

        const animateCounter = (element) => {
            const target = parseInt(element.getAttribute('data-count'));
            const duration = 2000; // 2 секунды
            const increment = target / (duration / 16); // 60 FPS
            let current = 0;

            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    element.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target;
                }
            };

            updateCounter();
        };

        // Запускаем анимацию при видимости элемента
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        statNumbers.forEach(stat => {
            stat.textContent = '0';
            observer.observe(stat);
        });
    });
</script>

<div class="landing-container">
    <div class="landing-content">

        <div class="landing-brand-logo-wrap">
            <img src="<?= htmlspecialchars(BASE_URL . 'assets/images/logo.jpg', ENT_QUOTES, 'UTF-8') ?>"
                alt="Aru"
                class="landing-brand-logo"
                width="240"
                height="240"
                decoding="async">
        </div>

        <h1 class="landing-title">Свидания и Мероприятия на Aru</h1>

        <p class="landing-subtitle">Найдите интересное свидание или создайте мероприятие. Платформа для знакомств и организаций событий </p>

        <div class="landing-buttons">
            <?php if (Helper::isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>platform" class="landing-btn landing-btn-primary" aria-label="Перейти к платформе Aru">
                    Перейти к платформе
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>auth/register" class="landing-btn landing-btn-primary" aria-label="Зарегистрироваться на платформе Aru">
                    Начать
                </a>
                <a href="<?= BASE_URL ?>auth/login" class="landing-btn landing-btn-secondary" aria-label="Войти в аккаунт Aru">
                    Войти
                </a>
            <?php endif; ?>
        </div>







        <!-- Кнопка "Подробнее" -->
        <div class="landing-more-btn-wrap">
            <button type="button" class="landing-btn landing-btn-secondary" data-bs-toggle="modal" data-bs-target="#infoModal" style="background: transparent; border: 1px solid #667eea; color: #667eea;">
                <i class="bi bi-info-circle"></i> Подробнее
            </button>
        </div>

        <!-- Пользователи -->
        <?php if (!empty($users) && is_array($users)): ?>
            <section class="landing-users">

                <?php
                $malePlaceholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320"><defs><linearGradient id="bgM" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#dbeafe"/><stop offset="55%" stop-color="#93c5fd"/><stop offset="100%" stop-color="#60a5fa"/></linearGradient></defs><rect width="320" height="320" fill="url(#bgM)"/><circle cx="160" cy="118" r="52" fill="#1d4ed8"/><path d="M72 298c8-58 44-92 88-92s80 34 88 92H72z" fill="#1e40af"/><circle cx="146" cy="112" r="5" fill="#dbeafe"/><circle cx="174" cy="112" r="5" fill="#dbeafe"/><path d="M144 132c10 8 22 8 32 0" fill="none" stroke="#dbeafe" stroke-width="4" stroke-linecap="round"/></svg>';
                $femalePlaceholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320"><defs><linearGradient id="bgF" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fce7f3"/><stop offset="55%" stop-color="#f9a8d4"/><stop offset="100%" stop-color="#f472b6"/></linearGradient></defs><rect width="320" height="320" fill="url(#bgF)"/><circle cx="160" cy="114" r="50" fill="#be185d"/><path d="M160 184l86 114H74l86-114z" fill="#9d174d"/><circle cx="146" cy="110" r="5" fill="#fce7f3"/><circle cx="174" cy="110" r="5" fill="#fce7f3"/><path d="M144 130c10 8 22 8 32 0" fill="none" stroke="#fce7f3" stroke-width="4" stroke-linecap="round"/></svg>';

                usort($users, function (array $a, array $b): int {
                    $aHasPhoto = !empty($a['main_photo']) && trim((string) $a['main_photo']) !== '';
                    $bHasPhoto = !empty($b['main_photo']) && trim((string) $b['main_photo']) !== '';

                    if ($aHasPhoto !== $bHasPhoto) {
                        return $aHasPhoto ? -1 : 1;
                    }

                    return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
                });
                ?>
                <div class="landing-users-grid">
                    <?php foreach ($users as $user): ?>
                        <?php
                        $hasMainPhoto = !empty($user['main_photo']) && trim((string) $user['main_photo']) !== '';
                        $isFemale = ($user['gender'] ?? '') === 'female';
                        $placeholderSvg = $isFemale ? $femalePlaceholderSvg : $malePlaceholderSvg;
                        $placeholderImage = 'data:image/svg+xml;utf8,' . rawurlencode($placeholderSvg);
                        ?>
                        <a href="<?= BASE_URL ?>profile/view?id=<?= $user['id'] ?>"
                            class="landing-user-card<?= $hasMainPhoto ? '' : ' landing-user-card--no-photo' ?>">
                            <div class="landing-user-card-media">
                                <?php if ($hasMainPhoto): ?>
                                    <?php
                                    $photoName = $user['main_photo'];
                                    $photoSrc = BASE_URL . UPLOAD_DIR . 'photos/' . rawurlencode($photoName);
                                    ?>
                                    <img src="<?= htmlspecialchars($photoSrc, ENT_QUOTES, 'UTF-8') ?>"
                                        class="landing-user-card-img"
                                        alt="<?= !empty($user['full_name']) ? Helper::escape($user['full_name']) : 'Фото пользователя' ?>"
                                        loading="lazy"
                                        decoding="async"
                                        onerror="this.classList.add('landing-user-card-img--err')">
                                    <div class="landing-user-card-placeholder landing-user-card-placeholder--under" aria-hidden="true">
                                        <img src="<?= htmlspecialchars($placeholderImage, ENT_QUOTES, 'UTF-8') ?>"
                                            alt=""
                                            class="landing-user-card-placeholder-image"
                                            loading="lazy"
                                            decoding="async">
                                    </div>
                                <?php else: ?>
                                    <div class="landing-user-card-placeholder">
                                        <img src="<?= htmlspecialchars($placeholderImage, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= $isFemale ? 'Заглушка: девушка' : 'Заглушка: мужчина' ?>"
                                            class="landing-user-card-placeholder-image"
                                            loading="lazy"
                                            decoding="async">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="landing-user-card-info">
                                <?php if (!empty($user['full_name'])): ?>
                                    <div class="landing-user-card-name"><?= Helper::escape($user['full_name']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($user['age'])): ?>
                                    <div class="landing-user-card-age"><?= $user['age'] ?> лет</div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Footer -->
        <!-- <footer class="landing-footer">
            <div class="landing-footer-content">
                <p class="landing-footer-text">
                    <a href="https://shotayev.kz" target="_blank" rel="noopener noreferrer" class="landing-footer-link">developer shotayev.kz</a>
                    <span class="landing-footer-heart" aria-hidden="true">❤️</span>
                    <span class="landing-footer-aru">aru</span>
                </p>
            </div>
        </footer> -->
    </div>
</div>

<!-- Модальное окно с подробной информацией -->
<div class="modal fade info-modal" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalLabel">
                    <i class="bi bi-info-circle" style="color: #667eea; margin-right: 8px;"></i>
                    О платформе Aru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 24px;">
                    <h6 style="color: #1a1a1a; font-weight: 600; margin-bottom: 12px; font-size: 18px;">
                        <i class="bi bi-heart" style="color: #667eea; margin-right: 8px;"></i>
                        Свидания на Aru
                    </h6>
                    <p>
                        <strong>Найдите свидание</strong> на платформе Aru. Мы помогаем людям находить интересные <strong>свидания</strong> и создавать новые знакомства. Просматривайте профили пользователей и находите подходящее <strong>свидание</strong> для себя.
                    </p>
                    <p>
                        На платформе вы можете:
                    </p>
                    <ul style="margin-left: 20px; margin-bottom: 16px;">
                        <li>Просматривать профили пользователей</li>
                        <li>Создавать предложения для свиданий</li>
                        <li>Находить интересных людей рядом с вами</li>
                        <li>Общаться с потенциальными партнерами</li>
                    </ul>
                    <a href="<?= BASE_URL ?>dates" class="landing-btn landing-btn-primary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: white !important;">
                        Перейти к свиданиям <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div style="border-top: 1px solid #e5e7eb; padding-top: 24px; margin-top: 24px;">
                    <h6 style="color: #1a1a1a; font-weight: 600; margin-bottom: 12px; font-size: 18px;">
                        <i class="bi bi-calendar-event" style="color: #667eea; margin-right: 8px;"></i>
                        Мероприятия на Aru
                    </h6>
                    <p>
                        <strong>Создавайте мероприятия</strong> и присоединяйтесь к событиям в вашем городе. На платформе Aru вы можете организовать <strong>мероприятие</strong> или найти интересные <strong>мероприятия</strong> рядом с вами. Откройте для себя новые возможности для общения и развлечений.
                    </p>
                    <p>
                        Возможности для организаторов и участников:
                    </p>
                    <ul style="margin-left: 20px; margin-bottom: 16px;">
                        <li>Создавать и публиковать мероприятия</li>
                        <li>Находить интересные события в вашем городе</li>
                        <li>Присоединяться к мероприятиям других пользователей</li>
                        <li>Организовывать встречи и активности</li>
                    </ul>
                    <a href="<?= BASE_URL ?>events" class="landing-btn landing-btn-primary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: white !important;">
                        Перейти к мероприятиям <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div style="border-top: 1px solid #e5e7eb; padding-top: 24px; margin-top: 24px;">
                    <h6 style="color: #1a1a1a; font-weight: 600; margin-bottom: 12px; font-size: 18px;">
                        <i class="bi bi-megaphone" style="color: #667eea; margin-right: 8px;"></i>
                        Бизнес и реклама
                    </h6>
                    <p>
                        <strong>Размещайте рекламу</strong> на платформе Aru и привлекайте целевую аудиторию. Бизнес может подать заявку на <strong>рекламный баннер</strong>, который увидят пользователи в вашем городе или регионе. Модерация обеспечивает соответствие рекламы правилам платформы.
                    </p>
                    <p>
                        Для рекламодателей:
                    </p>
                    <ul style="margin-left: 20px; margin-bottom: 16px;">
                        <li>Подать заявку на размещение баннера</li>
                        <li>Указать город или страну показа</li>
                        <li>Отслеживать статус рекламы в личном кабинете</li>
                        <li>Достигать аудиторию свиданий и мероприятий</li>
                    </ul>
                    <a href="<?= BASE_URL ?>ads/create" class="landing-btn landing-btn-primary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: white !important;">
                        Разместить рекламу <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div style="border-top: 1px solid #e5e7eb; padding-top: 24px; margin-top: 24px; background: #f9fafb; padding: 20px; border-radius: 8px;">
                    <p style="margin-bottom: 0;">
                        Платформа Aru объединяет возможности для <strong>свиданий</strong>, организации <strong>мероприятий</strong> и <strong>рекламы для бизнеса</strong> в одном месте. Присоединяйтесь к сообществу и начните находить интересные <strong>свидания</strong>, <strong>мероприятия</strong> или размещать <strong>рекламу</strong> уже сегодня.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="landing-btn landing-btn-secondary" data-bs-dismiss="modal" style="background: transparent; border: 1px solid #667eea; color: #667eea;">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Мобильная нижняя навигация -->
<nav class="mobile-bottom-nav">
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

    // Определяем текущий маршрут
    $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
    $currentUri = strtok($currentUri, '?');
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptName !== '/' && $scriptName !== '\\') {
        $currentUri = str_replace($scriptName, '', $currentUri);
    }
    $currentUri = trim($currentUri, '/');

    // Определяем активные классы
    $isHomeActive = empty($currentUri) || $currentUri === 'home' || $currentUri === 'platform';
    $isDatesActive = strpos($currentUri, 'dates') === 0;
    $isEventsActive = strpos($currentUri, 'events') === 0;
    $isMapActive = strpos($currentUri, 'map') === 0;
    ?>
    <div class="d-flex">
        <?php if ($showDatingMenu): ?>
            <div class="nav-item">
                <a href="<?= BASE_URL ?><?= Helper::isLoggedIn() ? 'platform' : '' ?>" class="nav-link <?= $isHomeActive ? 'active' : '' ?>">
                    <i class="bi bi-house-door"></i>
                    <span>Главная</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= BASE_URL ?>dates" class="nav-link <?= $isDatesActive ? 'active' : '' ?>">
                    <i class="bi bi-heart"></i>
                    <span>Свидания</span>
                </a>
            </div>
        <?php endif; ?>
        <div class="nav-item">
            <a href="<?= BASE_URL ?>events" class="nav-link <?= $isEventsActive ? 'active' : '' ?>">
                <i class="bi bi-calendar-event"></i>
                <span>Мероприятия</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= BASE_URL ?>map" class="nav-link <?= $isMapActive ? 'active' : '' ?>">
                <i class="bi bi-map"></i>
                <span>Карта</span>
            </a>
        </div>
    </div>
</nav>

<!-- Структурированные данные для SEO (JSON-LD) -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Aru - Свидания и Мероприятия",
        "alternateName": "Aru - Платформа для знакомств и событий | aru.asia",
        "url": "<?= BASE_URL ?>",
        "description": "<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>",
        "keywords": "свидание, свидания, мероприятие, мероприятия, знакомства",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "<?= BASE_URL ?>platform?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Aru знакомство",
        "alternateName": "aru.asia",
        "url": "<?= BASE_URL ?>",
        "logo": "<?= BASE_URL ?>assets/images/logo.jpg",
        "description": "Aru - платформа для знакомств и мероприятий. Найдите свидание или создайте мероприятие на aru.asia",
        "sameAs": []
    }
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Aru - Свидания и Мероприятия",
        "alternateName": "aru.asia",
        "applicationCategory": "SocialNetworkingApplication",
        "operatingSystem": "Web",
        "url": "<?= BASE_URL ?>",
        "description": "Aru - современная платформа для знакомств и мероприятий. Найдите свидание или создайте мероприятие на aru.asia",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "KZT"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "1000"
        }
    }
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "serviceType": "DatingService",
        "name": "Свидания на Aru",
        "description": "Платформа для поиска свиданий и знакомств. Найдите интересное свидание на aru.asia",
        "provider": {
            "@type": "Organization",
            "name": "Aru",
            "url": "<?= BASE_URL ?>"
        },
        "areaServed": {
            "@type": "Country",
            "name": "Казахстан"
        },
        "url": "<?= BASE_URL ?>dates",
        "keywords": "свидание, свидания, знакомства, найти свидание"
    }
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "serviceType": "EventPlanningService",
        "name": "Мероприятия на Aru",
        "description": "Создавайте и находите мероприятия на платформе Aru. Организация событий и мероприятий в Казахстане",
        "provider": {
            "@type": "Organization",
            "name": "Aru",
            "url": "<?= BASE_URL ?>"
        },
        "areaServed": {
            "@type": "Country",
            "name": "Казахстан"
        },
        "url": "<?= BASE_URL ?>events",
        "keywords": "мероприятие, мероприятия, события, создать мероприятие, организация мероприятий"
    }
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [{
                "@type": "ListItem",
                "position": 1,
                "name": "Главная",
                "item": "<?= BASE_URL ?>"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Свидания",
                "item": "<?= BASE_URL ?>dates"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "Мероприятия",
                "item": "<?= BASE_URL ?>events"
            }
        ]
    }
</script>

<?php
$content = ob_get_clean();
$title = $seoTitle;
$metaDescription = $seoDescription;
$metaKeywords = $seoKeywords;
$ogTitle = $ogTitle ?? $seoTitle;
$ogDescription = $ogDescription ?? $seoDescription;
$ogImage = $ogImage ?? BASE_URL . 'assets/images/logo.jpg';
$ogUrl = $ogUrl ?? BASE_URL;
$ogType = $ogType ?? 'website';
$canonical = $canonical ?? BASE_URL;
include __DIR__ . '/../layout.php';
?>