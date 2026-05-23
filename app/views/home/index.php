<?php

/**
 * ГЛАВНАЯ СТРАНИЦА
 * Показывает фотографии пользователей
 */

// Подключаем базовый шаблон
ob_start();
?>

<div class="mobile-page-container">
   
    <!-- Рекламный баннер -->
    <style>
        @keyframes sparkle {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
        .new-year-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            border-radius: 20px;
            padding: 24px 20px;
            margin-bottom: 20px;
            text-align: center;
            color: white;
            box-shadow: 
                0 10px 25px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(99, 102, 241, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.5s ease-in;
            border: 2px solid rgba(99, 102, 241, 0.2);
        }
        
        .new-year-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .new-year-banner::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                linear-gradient(90deg, transparent 0%, rgba(99, 102, 241, 0.05) 50%, transparent 100%),
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 2px,
                    rgba(99, 102, 241, 0.03) 2px,
                    rgba(99, 102, 241, 0.03) 4px
                );
            pointer-events: none;
            opacity: 0.5;
        }
        /* Общие стили карточек - одинаковые для всех устройств */
        .user-photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            grid-auto-rows: 1fr;
            gap: 12px;
            margin-top: 20px;
            padding: 0 8px;
        }

        .user-card {
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 
                0 3px 12px rgba(0, 0, 0, 0.08),
                0 1px 4px rgba(0, 0, 0, 0.04),
                0 0 0 1px rgba(0, 0, 0, 0.02);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 2px solid transparent;
            position: relative;
            cursor: pointer;
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100%;
            min-height: 300px;
        }

        .user-card--no-photo {
            border: 2px dashed rgba(102, 126, 234, 0.28);
        }

        .user-card:nth-child(1) { animation-delay: 0.05s; }
        .user-card:nth-child(2) { animation-delay: 0.1s; }
        .user-card:nth-child(3) { animation-delay: 0.15s; }
        .user-card:nth-child(4) { animation-delay: 0.2s; }
        .user-card:nth-child(5) { animation-delay: 0.25s; }
        .user-card:nth-child(6) { animation-delay: 0.3s; }
        .user-card:nth-child(7) { animation-delay: 0.35s; }
        .user-card:nth-child(8) { animation-delay: 0.4s; }
        .user-card:nth-child(n+9) { animation-delay: 0.45s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .user-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                #667eea 0%, 
                #764ba2 25%,
                #f093fb 50%,
                #4facfe 75%,
                #00f2fe 100%);
            background-size: 200% 100%;
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: 3;
            animation: gradientMove 3s ease infinite;
        }

        @keyframes gradientMove {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .user-card:active::after,
        .user-card:hover::after {
            opacity: 1;
        }

        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(102, 126, 234, 0.08) 0%, 
                rgba(118, 75, 162, 0.08) 50%,
                rgba(240, 147, 251, 0.08) 100%);
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: 1;
            pointer-events: none;
        }

        .user-card:active,
        .user-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 12px 35px rgba(102, 126, 234, 0.2),
                0 6px 15px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(102, 126, 234, 0.1);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .user-card:active::before,
        .user-card:hover::before {
            opacity: 1;
        }

        .user-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            object-position: center;
            transition: transform 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            z-index: 0;
            filter: brightness(1) saturate(1);
            flex-shrink: 0;
        }

        .user-card:active img,
        .user-card:hover img {
            transform: scale(1.1);
            filter: brightness(1.05) saturate(1.1);
        }

        .user-card-placeholder {
            height: 220px;
            min-height: 220px;
            max-height: 220px;
            background: linear-gradient(135deg, 
                #f8f9fa 0%, 
                #e9ecef 30%,
                #f8f9fa 60%,
                #e9ecef 100%);
            background-size: 200% 200%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            animation: gradientShift 4s ease infinite;
            flex-shrink: 0;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .user-card-placeholder::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, 
                transparent 30%, 
                rgba(255,255,255,0.7) 50%, 
                transparent 70%);
            animation: shimmer 2.5s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .user-card-placeholder-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            position: relative;
            z-index: 1;
        }

        .user-card > div {
            padding: 14px 12px;
            background: linear-gradient(to bottom, 
                rgba(255, 255, 255, 0.98) 0%,
                #ffffff 100%);
            position: relative;
            z-index: 2;
            border-top: 1px solid rgba(0, 0, 0, 0.04);
            height: 70px;
            min-height: 70px;
            max-height: 70px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex-shrink: 0;
        }

        .user-card strong {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 6px;
            display: block;
            line-height: 1.3;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-card:active strong,
        .user-card:hover strong {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-card small {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .user-card small::before {
            content: '🎂';
            font-size: 14px;
            filter: grayscale(0.2);
        }

        /* Ссылка карточки - занимает всю высоту */
        .user-card-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            height: 100%;
        }

        .user-card-link:hover {
            text-decoration: none;
            color: inherit;
        }

        /* ============================================
           СТИЛИ ДЛЯ МОБИЛЬНОЙ ВЕРСИИ (max-width: 767px)
           ============================================ */
        @media (max-width: 767px) {
            .user-photo-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-auto-rows: 280px;
                gap: 12px;
                padding: 0 12px;
            }

            .user-card {
                border-radius: 16px;
                box-shadow: 
                    0 4px 14px rgba(0, 0, 0, 0.1),
                    0 2px 6px rgba(0, 0, 0, 0.06),
                    0 0 0 1px rgba(102, 126, 234, 0.05);
                min-height: 280px;
                height: 280px;
            }

            .user-card img {
                height: 200px;
            }

            .user-card-placeholder {
                height: 200px;
                min-height: 200px;
                max-height: 200px;
            }

            .user-card-placeholder i {
                font-size: 48px;
            }

            .user-card > div {
                padding: 12px 10px;
                height: 65px;
                min-height: 65px;
                max-height: 65px;
            }

            .user-card strong {
                font-size: 14px;
                margin-bottom: 4px;
            }

            .user-card small {
                font-size: 12px;
            }

            .user-card small::before {
                font-size: 13px;
            }

            .user-card:active {
                transform: translateY(-4px) scale(1.01);
                box-shadow: 
                    0 10px 25px rgba(102, 126, 234, 0.2),
                    0 5px 12px rgba(0, 0, 0, 0.1),
                    0 0 0 1px rgba(102, 126, 234, 0.15);
            }

            .user-card:active img {
                transform: scale(1.08);
            }
        }

        /* ============================================
           СТИЛИ ДЛЯ ПК ВЕРСИИ (min-width: 768px)
           ============================================ */
        @media (min-width: 768px) {
            .mobile-page-container {
                background: #f8f9fa;
                background-image: 
                    linear-gradient(135deg, rgba(99, 102, 241, 0.03) 0%, transparent 50%),
                    linear-gradient(225deg, rgba(236, 72, 153, 0.03) 0%, transparent 50%);
                min-height: calc(100vh - 80px);
                padding: 60px 40px;
            }

            /* Рекламный баннер для ПК - темный дизайн */
            .new-year-banner {
                background: linear-gradient(135deg, 
                    #0f172a 0%, 
                    #1e293b 50%, 
                    #334155 100%);
                border-radius: 24px;
                padding: 60px 80px;
                margin-bottom: 50px;
                box-shadow: 
                    0 20px 60px rgba(0, 0, 0, 0.4),
                    0 0 0 1px rgba(99, 102, 241, 0.4),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                border: 2px solid rgba(99, 102, 241, 0.3);
            }

            .new-year-banner h3 {
                font-size: 44px !important;
                font-weight: 800 !important;
                margin-bottom: 20px !important;
                text-shadow: 
                    0 0 20px rgba(99, 102, 241, 0.5),
                    0 2px 10px rgba(0, 0, 0, 0.5) !important;
                letter-spacing: 1px;
                position: relative;
                z-index: 2;
                color: #ffffff !important;
                background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .new-year-banner p {
                font-size: 20px !important;
                opacity: 0.9 !important;
                font-weight: 400;
                position: relative;
                z-index: 2;
                text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
                color: #cbd5e1 !important;
                line-height: 1.6;
            }

            /* Сетка карточек для ПК - одинаковые размеры */
            .user-photo-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                grid-auto-rows: 300px;
                gap: 16px;
                margin-top: 40px;
                padding: 0;
            }

            .user-card {
                min-height: 300px;
                height: 300px;
            }

            .user-card img {
                height: 220px;
            }

            .user-card-placeholder {
                height: 220px;
                min-height: 220px;
                max-height: 220px;
            }

            .user-card > div {
                height: 70px;
                min-height: 70px;
                max-height: 70px;
            }

            /* Алерты для ПК - светлый стиль */
            .alert {
                border-radius: 12px;
                padding: 24px 32px;
                font-size: 16px;
                margin-bottom: 30px;
                border: 1px solid rgba(0, 0, 0, 0.08);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }

            .alert-danger {
                background: #fee2e2;
                color: #991b1b;
                border-color: #fecaca;
            }

            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border-color: #a7f3d0;
            }

            .alert-info {
                background: #dbeafe;
                color: #1e40af;
                border-color: #bfdbfe;
            }

            .alert-warning {
                background: #fef3c7;
                color: #92400e;
                border-color: #fde68a;
            }

            /* Анимация появления карточек */
            .user-card {
                animation: fadeInUp 0.6s ease-out;
                animation-fill-mode: both;
            }

            .user-card:nth-child(1) { animation-delay: 0.05s; }
            .user-card:nth-child(2) { animation-delay: 0.1s; }
            .user-card:nth-child(3) { animation-delay: 0.15s; }
            .user-card:nth-child(4) { animation-delay: 0.2s; }
            .user-card:nth-child(5) { animation-delay: 0.25s; }
            .user-card:nth-child(6) { animation-delay: 0.3s; }
            .user-card:nth-child(7) { animation-delay: 0.35s; }
            .user-card:nth-child(8) { animation-delay: 0.4s; }
            .user-card:nth-child(n+9) { animation-delay: 0.45s; }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px) scale(0.98);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }


            /* Пустое состояние для ПК */
            .alert-info p {
                font-size: 18px;
                margin: 0;
                text-align: center;
                padding: 20px 0;
                font-weight: 500;
            }

            /* Дополнительные эффекты для ссылок карточек */
            .user-card-link {
                text-decoration: none;
                color: inherit;
                display: block;
            }

            .user-card-link:hover {
                text-decoration: none;
                color: inherit;
            }
        }
    </style>
    
    <div class="new-year-banner">
        <div style="position: relative; z-index: 2;">
            <div style="display: inline-flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 12px;">
                <span style="font-size: 32px; filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.6));">💻</span>
                <h3 style="margin: 0; font-size: 24px; font-weight: 700; text-shadow: 0 0 15px rgba(99, 102, 241, 0.5), 2px 2px 6px rgba(0,0,0,0.4);">
                    Создание сайтов от Shotayev
                </h3>
            </div>
            <p style="margin: 0; font-size: 15px; opacity: 0.9; text-shadow: 1px 1px 4px rgba(0,0,0,0.3); line-height: 1.5; color: #cbd5e1;">
                Профессиональная разработка сайтов любой сложности<br>
                <span style="color: #a5b4fc; font-weight: 500;">✨ Современный дизайн • ⚡ Быстрая работа • 💰 Доступные цены</span>
            </p>
        </div>
    </div>

    <?php if (isset($isBlocked) && $isBlocked): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i> Ваш профиль заблокирован
            </h5>
            <p class="mb-2"><strong>Вы заблокированы администратором.</strong></p>
            <?php if (!empty($adminRemark)): ?>
                <div class="alert alert-warning mb-2 mt-3">
                    <?php
                    $fieldNames = [
                        'full_name' => 'ФИО (Полное имя)',
                        'about' => 'О себе',
                        'photo' => 'Фотография'
                    ];
                    $fieldName = isset($remarkType) && isset($fieldNames[$remarkType]) ? $fieldNames[$remarkType] : null;
                    ?>
                    <?php if ($fieldName): ?>
                        <strong>Замечание по полю: <span class="text-danger"><?= Helper::escape($fieldName) ?></span></strong>
                    <?php else: ?>
                        <strong>Замечание администратора:</strong>
                    <?php endif; ?>
                    <p class="mb-0 mt-2"><?= nl2br(Helper::escape($adminRemark)) ?></p>
                </div>
            <?php endif; ?>
            <p class="mb-0">
                <strong>Пожалуйста, исправьте указанные замечания и обратитесь к администратору для разблокировки профиля.</strong>
            </p>
            <hr>
            <p class="mb-0">
                <a href="<?= BASE_URL ?>profile/edit" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil"></i> Перейти к редактированию профиля
                </a>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($logoutMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-heart-fill"></i> <?= Helper::escape($logoutMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <div class="alert alert-info">
            <p>Пока нет зарегистрированных пользователей</p>
        </div>
    <?php else: ?>
        <?php
        $malePlaceholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320"><defs><linearGradient id="bgM" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#dbeafe"/><stop offset="55%" stop-color="#93c5fd"/><stop offset="100%" stop-color="#60a5fa"/></linearGradient></defs><rect width="320" height="320" fill="url(#bgM)"/><circle cx="160" cy="118" r="52" fill="#1d4ed8"/><path d="M72 298c8-58 44-92 88-92s80 34 88 92H72z" fill="#1e40af"/><circle cx="146" cy="112" r="5" fill="#dbeafe"/><circle cx="174" cy="112" r="5" fill="#dbeafe"/><path d="M144 132c10 8 22 8 32 0" fill="none" stroke="#dbeafe" stroke-width="4" stroke-linecap="round"/></svg>';
        $femalePlaceholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320"><defs><linearGradient id="bgF" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fce7f3"/><stop offset="55%" stop-color="#f9a8d4"/><stop offset="100%" stop-color="#f472b6"/></linearGradient></defs><rect width="320" height="320" fill="url(#bgF)"/><circle cx="160" cy="114" r="50" fill="#be185d"/><path d="M160 184l86 114H74l86-114z" fill="#9d174d"/><circle cx="146" cy="110" r="5" fill="#fce7f3"/><circle cx="174" cy="110" r="5" fill="#fce7f3"/><path d="M144 130c10 8 22 8 32 0" fill="none" stroke="#fce7f3" stroke-width="4" stroke-linecap="round"/></svg>';
        ?>
        <div class="user-photo-grid">
            <?php foreach ($users as $user): ?>
                <?php
                $hasMainPhoto = !empty($user['main_photo']) && trim((string) $user['main_photo']) !== '';
                $isFemale = ($user['gender'] ?? '') === 'female';
                $placeholderSvg = $isFemale ? $femalePlaceholderSvg : $malePlaceholderSvg;
                $placeholderImage = 'data:image/svg+xml;utf8,' . rawurlencode($placeholderSvg);
                ?>
                <a href="<?= BASE_URL ?>profile/view?id=<?= $user['id'] ?>" class="user-card-link">
                    <div class="user-card<?= $hasMainPhoto ? '' : ' user-card--no-photo' ?>">
                        <?php if ($hasMainPhoto): ?>
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . rawurlencode($user['main_photo']) ?>"
                                alt="<?= !empty($user['full_name']) ? Helper::escape($user['full_name']) : 'Фото пользователя' ?>"
                                class="img-fluid"
                                loading="lazy">
                        <?php else: ?>
                            <div class="user-card-placeholder">
                                <img src="<?= htmlspecialchars($placeholderImage, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= $isFemale ? 'Заглушка: девушка' : 'Заглушка: мужчина' ?>"
                                    class="user-card-placeholder-image"
                                    loading="lazy"
                                    decoding="async">
                            </div>
                        <?php endif; ?>
                        <div>
                            <?php if (!empty($user['full_name'])): ?>
                                <strong><?= Helper::escape($user['full_name']) ?></strong>
                            <?php endif; ?>
                            <?php if (!empty($user['age'])): ?>
                                <small><?= $user['age'] ?> лет</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Главная страница';
include __DIR__ . '/../layout.php';
?>
