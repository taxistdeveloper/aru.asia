<?php

/**
 * ПЛАТФОРМА
 * Показывает фотографии пользователей
 */

// Подключаем базовый шаблон
ob_start();
?>

<div class="mobile-page-container">

    <!-- Мини-кнопка "Добавить на экран Домой" (только для телефонов) -->
    <div class="mobile-only pwa-fab">
        <button type="button"
            class="btn btn-primary pwa-install-trigger pwa-fab-btn"
            id="pwa-install-trigger-platform"
            aria-label="Добавить на экран Домой"
            title="Добавить на экран Домой">
            <i class="bi bi-plus-lg"></i>
        </button>
        <div class="pwa-fab-hint">На экран Домой</div>
    </div>

    <!-- Рекламный баннер -->
    <style>
        @keyframes sparkle {

            0%,
            100% {
                opacity: 0.2;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }

        /* Стили для рекламного слайдера */
        .ad-carousel-container {
            margin-bottom: 20px;
            animation: fadeInUp 0.6s ease-out;
            position: relative;
        }

        /* Реклама: превью в духе WhatsApp (скругление, лёгкая тень, «пузырь» на фоне чата) */
        .ad-carousel {
            background: #f0f2f5;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.12), 0 1px 2px rgba(11, 20, 26, 0.08);
            position: relative;
            border: 1px solid rgba(11, 20, 26, 0.06);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .ad-carousel:hover {
            box-shadow: 0 2px 4px rgba(11, 20, 26, 0.14), 0 1px 2px rgba(11, 20, 26, 0.1);
        }

        .ad-carousel .carousel-inner {
            border-radius: 8px;
            overflow: hidden;
            margin: 6px;
            background: #fff;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.08);
        }

        .ad-carousel-item {
            position: relative;
            cursor: pointer;
        }

        .ad-banner-content {
            position: relative;
        }

        .ad-banner-image-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #eceff1;
            padding: 4px;
            box-sizing: border-box;
        }

        .ad-banner-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            transition: transform 0.25s ease;
            border-radius: 7.5px;
        }

        .ad-carousel-item:hover .ad-banner-image {
            transform: scale(1.02);
        }

        .ad-banner-overlay {
            display: none;
        }

        .ad-carousel-item:hover .ad-banner-overlay {
            opacity: 0;
        }

        .ad-banner-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(102, 126, 234, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            z-index: 3;
            animation: pulse 2s ease-in-out infinite;
        }

        .ad-banner-badge i {
            font-size: 14px;
        }

        /* Иконка руки - индикатор кликабельности (слева) */
        .ad-click-indicator {
            position: absolute;
            bottom: 20px;
            left: 20px;
            z-index: 10;
            pointer-events: none;
        }

        .ad-click-indicator__hand {
            display: block;
            width: 84px;
            height: 120px;
            position: relative;
            background: url('<?= BASE_URL ?>assets/images/hand-tap.png') no-repeat center/contain;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
            animation: handTap 2s ease-in-out 1 forwards;
        }

        /* Зеркальное отражение руки (внизу) */
        .ad-click-indicator__hand::after {
            content: '';
            position: absolute;
            left: 0;
            top: 100%;
            width: 100%;
            height: 80%;
            background: url('<?= BASE_URL ?>assets/images/hand-tap.png') no-repeat center/contain;
            transform: scale(-1, -1);
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.35), transparent);
            -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.35), transparent);
            pointer-events: none;
        }

        /* Кружок-волна на месте (рука двигается отдельно) */
        .ad-click-indicator::before {
            content: '';
            position: absolute;
            top: 32px;
            left: 38px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: transparent;
            box-shadow:
                0 0 0 0 rgba(102, 126, 234, 0.6),
                0 0 0 0 rgba(102, 126, 234, 0.4),
                0 0 0 0 rgba(102, 126, 234, 0.2);
            animation: rippleWave 2s ease-in-out 1 forwards;
            pointer-events: none;
        }

        @keyframes rippleWave {

            0%,
            49% {
                box-shadow:
                    0 0 0 0 rgba(102, 126, 234, 0),
                    0 0 0 0 rgba(102, 126, 234, 0),
                    0 0 0 0 rgba(102, 126, 234, 0);
            }

            50% {
                box-shadow:
                    0 0 0 5px rgba(102, 126, 234, 0.5),
                    0 0 0 10px rgba(102, 126, 234, 0.3),
                    0 0 0 15px rgba(102, 126, 234, 0.1);
            }

            75% {
                box-shadow:
                    0 0 0 40px rgba(102, 126, 234, 0),
                    0 0 0 60px rgba(102, 126, 234, 0),
                    0 0 0 80px rgba(102, 126, 234, 0);
            }

            100% {
                box-shadow:
                    0 0 0 0 rgba(102, 126, 234, 0),
                    0 0 0 0 rgba(102, 126, 234, 0),
                    0 0 0 0 rgba(102, 126, 234, 0);
            }
        }

        @keyframes handTap {

            /* Старт: рука слева внизу (за кадром) */
            0% {
                transform: translate(-200px, 280px);
                opacity: 0;
            }

            /* Выезд с левой стороны */
            28% {
                transform: translate(0, 25px);
                opacity: 1;
            }

            /* Один нажим */
            50% {
                transform: translate(-5px, 50px);
                opacity: 1;
            }

            /* Уходит влево вниз */
            100% {
                transform: translate(-200px, 280px);
                opacity: 0;
            }
        }

        /* Скрываем при наведении */
        .ad-carousel-item:hover .ad-click-indicator {
            opacity: 0;
            transform: translate(-200px, 280px) scale(0.5);
            transition: all 0.3s ease;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .ad-banner-placeholder {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dfe5e8;
            color: #94a3b8;
            border-radius: 7.5px;
        }

        .ad-banner-placeholder i {
            font-size: 48px;
        }

        .ad-banner-footer {
            padding: 14px 16px;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.98) 0%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .ad-banner-name {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .ad-banner-icon {
            font-size: 16px;
            color: #667eea;
            transition: transform 0.3s ease;
        }

        .ad-carousel-item:hover .ad-banner-icon {
            transform: translate(4px, -4px);
        }

        /* Кнопки навигации слайдера - скрыты */
        .ad-carousel .carousel-control-prev,
        .ad-carousel .carousel-control-next {
            display: none !important;
        }

        /* Индикаторы слайдера - скрыты */
        .ad-carousel .carousel-indicators {
            display: none !important;
        }

        /* Стили для секции чатов */
        .conversations-section {
            margin-bottom: 24px;
            padding: 0;
            padding-bottom: 20px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.03) 0%, rgba(118, 75, 162, 0.03) 100%);
            border-radius: 20px;
            border: 1px solid rgba(102, 126, 234, 0.1);
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .conversations-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg,
                    #667eea 0%,
                    #764ba2 25%,
                    #f093fb 50%,
                    #4facfe 75%,
                    #00f2fe 100%);
            background-size: 200% 100%;
            animation: gradientMove 3s ease infinite;
        }

        @keyframes gradientMove {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .conversations-section h5 {
            font-size: 20px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 16px;
            padding: 0 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
        }

        .conversations-section h5 i {
            font-size: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        /* Стили для заголовка чатов в стиле date_chat.php */
        .chat-page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
            flex-shrink: 0;
            position: relative;
            z-index: 100;
            overflow: visible;
            margin: 0 !important;
            width: 100%;
            border-radius: 20px 20px 0 0;
            margin-bottom: 0;
        }

        .chat-page-header .d-flex {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            overflow: visible;
        }

        .chat-page-header h2,
        .chat-page-header h4 {
            margin: 0;
            font-size: 18px;
            color: white;
        }

        .chat-page-header h2 i,
        .chat-page-header h4 i {
            color: white;
            margin-right: 8px;
        }

        .chat-page-header .btn {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 101;
            position: relative;
            flex-shrink: 0;
        }

        .conversations-list {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding: 8px;
            scrollbar-width: thin;
            scrollbar-color: rgba(102, 126, 234, 0.4) rgba(102, 126, 234, 0.1);
            position: relative;
            z-index: 1;
        }

        .conversations-list::-webkit-scrollbar {
            height: 8px;
        }

        .conversations-list::-webkit-scrollbar-track {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
        }

        .conversations-list::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }

        .conversations-list::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2 0%, #f093fb 100%);
        }

        .conversation-card {
            min-width: 90px;
            max-width: 90px;
            background: #ffffff;
            border-radius: 20px;
            overflow: visible;
            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.08),
                0 2px 8px rgba(0, 0, 0, 0.04),
                0 0 0 2px rgba(102, 126, 234, 0.1);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 2px solid transparent;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            position: relative;
            animation: fadeInScale 0.5s ease-out backwards;
        }

        .conversation-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .conversation-card:nth-child(2) {
            animation-delay: 0.15s;
        }

        .conversation-card:nth-child(3) {
            animation-delay: 0.2s;
        }

        .conversation-card:nth-child(4) {
            animation-delay: 0.25s;
        }

        .conversation-card:nth-child(5) {
            animation-delay: 0.3s;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .conversation-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            border-radius: 22px;
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }

        .conversation-card:hover {
            transform: translateY(-8px) scale(1.08);
            box-shadow:
                0 12px 40px rgba(102, 126, 234, 0.25),
                0 6px 20px rgba(0, 0, 0, 0.12),
                0 0 0 2px rgba(102, 126, 234, 0.3);
            border-color: rgba(102, 126, 234, 0.4);
            text-decoration: none;
            color: inherit;
        }

        .conversation-card:hover::before {
            opacity: 0.2;
        }

        .conversation-card:active {
            transform: translateY(-4px) scale(1.04);
        }

        .conversation-photo-wrapper {
            width: 100%;
            height: 90px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 18px 18px 0 0;
        }

        .conversation-photo-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom,
                    rgba(102, 126, 234, 0) 0%,
                    rgba(102, 126, 234, 0) 70%,
                    rgba(102, 126, 234, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .conversation-card:hover .conversation-photo-wrapper::after {
            opacity: 1;
        }

        .conversation-photo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            filter: brightness(1) saturate(1);
        }

        .conversation-card:hover .conversation-photo-wrapper img {
            transform: scale(1.15);
            filter: brightness(1.05) saturate(1.2);
        }

        .conversation-photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg,
                    #f8f9fa 0%,
                    #e9ecef 30%,
                    #f8f9fa 60%,
                    #e9ecef 100%);
            background-size: 200% 200%;
            animation: gradientShift 4s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .conversation-photo-placeholder i {
            font-size: 36px;
            color: #ced4da;
            position: relative;
            z-index: 1;
        }

        .conversation-info {
            padding: 10px 8px;
            text-align: center;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.98) 0%, #ffffff 100%);
            border-radius: 0 0 18px 18px;
            border-top: 1px solid rgba(102, 126, 234, 0.1);
            position: relative;
        }

        .conversation-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.3) 50%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .conversation-card:hover .conversation-info::before {
            opacity: 1;
        }

        .conversation-name {
            font-size: 12px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.3;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.2px;
            transition: all 0.3s ease;
        }

        .conversation-card:hover .conversation-name {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .conversation-unread-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            background: linear-gradient(135deg, #ff4757 0%, #ff6348 100%);
            color: white;
            border-radius: 50%;
            min-width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
            box-shadow:
                0 4px 12px rgba(255, 71, 87, 0.5),
                0 2px 6px rgba(255, 71, 87, 0.3),
                0 0 0 3px rgba(255, 255, 255, 0.9);
            border: 2px solid white;
            z-index: 10;
            animation: badgePulse 2s ease-in-out infinite;
            padding: 0 6px;
        }

        .conversation-unread-badge::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #ff4757 0%, #ff6348 100%);
            border-radius: 50%;
            opacity: 0.3;
            animation: ripple 2s ease-out infinite;
            z-index: -1;
        }

        @keyframes badgePulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        @keyframes ripple {
            0% {
                transform: scale(1);
                opacity: 0.3;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
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
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
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
                repeating-linear-gradient(0deg,
                    transparent,
                    transparent 2px,
                    rgba(99, 102, 241, 0.03) 2px,
                    rgba(99, 102, 241, 0.03) 4px);
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

        .user-card:nth-child(1) {
            animation-delay: 0.05s;
        }

        .user-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .user-card:nth-child(3) {
            animation-delay: 0.15s;
        }

        .user-card:nth-child(4) {
            animation-delay: 0.2s;
        }

        .user-card:nth-child(5) {
            animation-delay: 0.25s;
        }

        .user-card:nth-child(6) {
            animation-delay: 0.3s;
        }

        .user-card:nth-child(7) {
            animation-delay: 0.35s;
        }

        .user-card:nth-child(8) {
            animation-delay: 0.4s;
        }

        .user-card:nth-child(n+9) {
            animation-delay: 0.45s;
        }

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

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
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

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
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
                    rgba(255, 255, 255, 0.7) 50%,
                    transparent 70%);
            animation: shimmer 2.5s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .user-card-placeholder-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            position: relative;
            z-index: 1;
        }

        .user-card>div {
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

            /* Рекламный слайдер для мобильных */
            .ad-carousel-container {
                margin-bottom: 16px;
                padding: 0 4px;
            }

            .ad-carousel {
                border-radius: 10px;
            }

            .ad-carousel .carousel-inner {
                margin: 5px;
            }

            .ad-banner-image-wrapper {
                height: 180px;
            }

            .ad-banner-image {
                height: 100%;
                object-fit: cover;
            }

            .ad-banner-placeholder {
                height: 180px;
            }

            .ad-banner-badge {
                top: 8px;
                right: 8px;
                padding: 5px 10px;
                font-size: 11px;
            }

            .ad-click-indicator__hand {
                width: 70px;
                height: 100px;
            }

            .chat-page-header {
                padding: 10px 12px;
                border-radius: 16px 16px 0 0;
            }

            .chat-page-header h2,
            .chat-page-header h4 {
                font-size: 16px;
            }

            .chat-page-header h2 i,
            .chat-page-header h4 i {
                font-size: 18px;
            }

            .user-card-link {
                display: block;
                height: auto;
            }

            .user-photo-grid {
                /* Всегда 2 колонки на мобилках */
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-auto-rows: auto;
                gap: 12px;
                padding: 0 12px;
                /* Адаптивные высоты карточки */
                --card-image-height: clamp(150px, 32vw, 210px);
                --card-info-min-height: clamp(56px, 12vw, 70px);
            }

            .user-card {
                border-radius: 16px;
                box-shadow:
                    0 4px 14px rgba(0, 0, 0, 0.1),
                    0 2px 6px rgba(0, 0, 0, 0.06),
                    0 0 0 1px rgba(102, 126, 234, 0.05);
                height: auto;
                min-height: calc(var(--card-image-height) + var(--card-info-min-height));
            }

            .user-card img {
                height: var(--card-image-height);
            }

            .user-card-placeholder {
                height: var(--card-image-height);
                min-height: var(--card-image-height);
                max-height: var(--card-image-height);
            }

            .user-card-placeholder i {
                font-size: 48px;
            }

            .user-card>div {
                padding: 12px 10px;
                height: auto;
                min-height: var(--card-info-min-height);
                max-height: none;
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

        /* Очень маленькие телефоны: делаем карточки чуть уже/компактнее */
        @media (max-width: 360px) {
            .ad-carousel-container {
                padding: 0 2px;
            }

            .ad-banner-image-wrapper {
                height: 150px;
            }

            .ad-banner-image {
                height: 100%;
                object-fit: cover;
            }

            .ad-banner-placeholder {
                height: 150px;
            }

            .ad-banner-badge {
                padding: 4px 8px;
                font-size: 10px;
            }

            .ad-click-indicator__hand {
                width: 60px;
                height: 86px;
            }

            .user-photo-grid {
                gap: 10px;
                padding: 0 10px;
                --card-image-height: clamp(140px, 40vw, 190px);
            }
        }

        /* Большие телефоны / маленькие планшеты (всё ещё "мобилка" по дизайну) */
        @media (min-width: 576px) and (max-width: 767px) {
            .user-photo-grid {
                gap: 14px;
                padding: 0 16px;
                --card-image-height: clamp(160px, 24vw, 220px);
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

            /* Рекламный слайдер для ПК */
            .ad-carousel-container {
                margin-bottom: 30px;
            }

            .ad-carousel {
                border-radius: 10px;
            }

            .ad-carousel .carousel-inner {
                margin: 8px;
            }

            .ad-banner-image-wrapper {
                height: 220px;
            }

            .ad-banner-image {
                height: 100%;
                object-fit: cover;
            }

            .ad-banner-placeholder {
                height: 220px;
            }

            .ad-banner-footer {
                padding: 14px 20px;
            }

            .ad-banner-name {
                font-size: 16px;
            }

            .ad-banner-icon {
                font-size: 18px;
            }

            .ad-banner-badge {
                top: 16px;
                right: 16px;
                padding: 8px 18px;
                font-size: 13px;
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

            .user-card>div {
                height: 70px;
                min-height: 70px;
                max-height: 70px;
            }

            /* Секция чатов для ПК */
            .conversations-section {
                margin-bottom: 40px;
                padding: 0;
                padding-bottom: 30px;
                border-radius: 24px;
            }

            .conversations-list {
                padding: 16px 24px;
            }

            .chat-page-header {
                padding: 18px 24px;
                border-radius: 24px 24px 0 0;
            }

            .chat-page-header h2 {
                font-size: 26px;
            }

            .chat-page-header h4 {
                font-size: 22px;
            }

            .chat-page-header h2 i,
            .chat-page-header h4 i {
                font-size: 28px;
            }

            .conversations-list {
                gap: 20px;
                padding: 16px 0;
            }

            .conversation-card {
                min-width: 120px;
                max-width: 120px;
                border-radius: 24px;
            }

            .conversation-card::before {
                border-radius: 26px;
            }

            .conversation-photo-wrapper {
                height: 120px;
                border-radius: 22px 22px 0 0;
            }

            .conversation-photo-placeholder i {
                font-size: 48px;
            }

            .conversation-info {
                padding: 14px 10px;
                border-radius: 0 0 22px 22px;
            }

            .conversation-name {
                font-size: 13px;
            }

            .conversation-unread-badge {
                min-width: 26px;
                height: 26px;
                font-size: 11px;
                top: 8px;
                right: 8px;
                padding: 0 7px;
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

            .user-card:nth-child(1) {
                animation-delay: 0.05s;
            }

            .user-card:nth-child(2) {
                animation-delay: 0.1s;
            }

            .user-card:nth-child(3) {
                animation-delay: 0.15s;
            }

            .user-card:nth-child(4) {
                animation-delay: 0.2s;
            }

            .user-card:nth-child(5) {
                animation-delay: 0.25s;
            }

            .user-card:nth-child(6) {
                animation-delay: 0.3s;
            }

            .user-card:nth-child(7) {
                animation-delay: 0.35s;
            }

            .user-card:nth-child(8) {
                animation-delay: 0.4s;
            }

            .user-card:nth-child(n+9) {
                animation-delay: 0.45s;
            }

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

        /* Плавающая мини-кнопка PWA (мобилки) */
        .pwa-fab {
            position: fixed;
            right: 14px;
            bottom: calc(92px + env(safe-area-inset-bottom, 0px));
            /* выше нижней навигации + safe area */
            z-index: 1200;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .pwa-fab-btn {
            width: 52px;
            height: 52px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 45%, #f093fb 100%);
            box-shadow:
                0 14px 34px rgba(102, 126, 234, 0.35),
                0 8px 18px rgba(0, 0, 0, 0.14);
            position: relative;
            isolation: isolate;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            -webkit-tap-highlight-color: transparent;
        }

        /* Глянцевый блик */
        .pwa-fab-btn::before {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: inherit;
            background: radial-gradient(circle at 30% 25%,
                    rgba(255, 255, 255, 0.55) 0%,
                    rgba(255, 255, 255, 0.18) 22%,
                    rgba(255, 255, 255, 0) 55%);
            opacity: 0.9;
            z-index: 0;
            pointer-events: none;
        }

        /* Светящийся ободок */
        .pwa-fab-btn::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: inherit;
            background: radial-gradient(circle,
                    rgba(240, 147, 251, 0.55) 0%,
                    rgba(102, 126, 234, 0.35) 45%,
                    rgba(102, 126, 234, 0) 70%);
            filter: blur(6px);
            opacity: 0.65;
            z-index: -1;
            pointer-events: none;
            animation: pwa-fab-pulse 2.2s ease-in-out infinite;
        }

        .pwa-fab-btn i {
            font-size: 20px;
            line-height: 1;
            position: relative;
            z-index: 1;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .pwa-fab-btn:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow:
                0 18px 44px rgba(102, 126, 234, 0.42),
                0 10px 22px rgba(0, 0, 0, 0.16);
            filter: saturate(1.08);
        }

        .pwa-fab-btn:focus-visible {
            outline: none;
            box-shadow:
                0 0 0 4px rgba(102, 126, 234, 0.28),
                0 18px 44px rgba(102, 126, 234, 0.42),
                0 10px 22px rgba(0, 0, 0, 0.16);
        }

        .pwa-fab-btn:active {
            transform: translateY(0px) scale(0.98);
        }

        .pwa-fab-hint {
            position: absolute;
            right: calc(100% + 10px);
            bottom: 50%;
            transform: translateY(50%) translateX(8px);
            opacity: 0;
            pointer-events: none;

            font-size: 12px;
            font-weight: 800;
            letter-spacing: -0.2px;
            color: #0f172a;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(102, 126, 234, 0.22);
            border-radius: 999px;
            padding: 8px 12px;
            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.12),
                0 4px 12px rgba(102, 126, 234, 0.12);
            white-space: nowrap;
            transition: opacity 0.18s ease, transform 0.18s ease;
        }

        .pwa-fab-hint::after {
            content: '';
            position: absolute;
            right: -6px;
            top: 50%;
            transform: translateY(-50%);
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.94);
            border-right: 1px solid rgba(102, 126, 234, 0.22);
            border-top: 1px solid rgba(102, 126, 234, 0.22);
            transform: translateY(-50%) rotate(45deg);
        }

        .pwa-fab:hover .pwa-fab-hint,
        .pwa-fab:focus-within .pwa-fab-hint,
        .pwa-fab-btn:active+.pwa-fab-hint {
            opacity: 1;
            transform: translateY(50%) translateX(0);
        }

        @keyframes pwa-fab-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.55;
            }

            50% {
                transform: scale(1.08);
                opacity: 0.75;
            }
        }

        /* Очень маленькие телефоны */
        @media (max-width: 360px) {
            .pwa-fab {
                right: 12px;
                bottom: calc(86px + env(safe-area-inset-bottom, 0px));
            }

            .pwa-fab-btn {
                width: 48px;
                height: 48px;
            }
        }
    </style>

    <!-- Рекламный слайдер -->
    <?php if (!empty($ads) && is_array($ads) && count($ads) > 0): ?>
        <?php
        // Фильтруем только баннеры с изображениями
        $validAds = array_filter($ads, function ($ad) {
            return !empty($ad['image_path']);
        });
        ?>
        <?php if (count($validAds) > 0): ?>
            <div class="ad-carousel-container">
                <div id="adCarousel" class="carousel slide ad-carousel" data-bs-ride="carousel" data-bs-interval="3000">
                    <?php if (count($validAds) > 1): ?>
                        <!-- Индикаторы -->
                        <div class="carousel-indicators">
                            <?php foreach ($validAds as $index => $ad): ?>
                                <button type="button"
                                    data-bs-target="#adCarousel"
                                    data-bs-slide-to="<?= $index ?>"
                                    <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?>
                                    aria-label="Слайд <?= $index + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Слайды -->
                    <div class="carousel-inner">
                        <?php foreach ($validAds as $index => $ad): ?>
                            <?php
                            $imagePath = $ad['image_path'];
                            $imageUrl = BASE_URL . UPLOAD_DIR . 'ads/' . $imagePath;
                            $clickUrl = !empty($ad['click_url']) ? Helper::escape($ad['click_url']) : '#';
                            $advertiserName = !empty($ad['advertiser_name']) ? Helper::escape($ad['advertiser_name']) : 'Реклама';
                            ?>
                            <div class="carousel-item ad-carousel-item" data-ad-index="<?= $index ?>">
                                <?php if ($clickUrl !== '#'): ?>
                                    <a href="<?= $clickUrl ?>" target="_blank" rel="noopener noreferrer" class="d-block">
                                    <?php endif; ?>
                                    <div class="ad-banner-content">
                                        <div class="ad-banner-image-wrapper">
                                            <img src="<?= $imageUrl ?>"
                                                alt="<?= $advertiserName ?>"
                                                class="ad-banner-image"
                                                loading="lazy"
                                                onerror="this.parentElement.innerHTML='<div class=\'ad-banner-placeholder\'><i class=\'bi bi-image\'></i></div>';">
                                            <div class="ad-banner-overlay"></div>
                                            <?php if (!empty($ad['advertiser_name'])): ?>
                                                <!-- <div class="ad-banner-badge">
                                                <i class="bi bi-megaphone"></i>
                                                <span>Реклама</span>
                                            </div> -->
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($clickUrl !== '#'): ?>
                                    </a>
                                    <div class="ad-click-indicator"><span class="ad-click-indicator__hand"></span></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($validAds) > 1): ?>
                        <!-- Кнопки навигации -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#adCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Предыдущий</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#adCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Следующий</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

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

<script>
    // Улучшение работы рекламного слайдера
    document.addEventListener('DOMContentLoaded', function() {
        const adCarousel = document.getElementById('adCarousel');
        if (adCarousel) {
            const carouselItems = adCarousel.querySelectorAll('.ad-carousel-item');
            const totalAds = carouselItems.length;
            const rotationInterval = 3000; // 3 секунды в миллисекундах

            if (totalAds > 0) {
                // Функция для расчета текущего активного индекса
                function calculateActiveIndex() {
                    const now = Date.now();
                    const savedTimestamp = localStorage.getItem('adCarouselTimestamp');
                    const savedIndex = localStorage.getItem('adCarouselActiveIndex');

                    if (!savedTimestamp || !savedIndex) {
                        // Первое посещение - начинаем с индекса 0
                        localStorage.setItem('adCarouselTimestamp', now.toString());
                        localStorage.setItem('adCarouselActiveIndex', '0');
                        return 0;
                    }

                    const timeDiff = now - parseInt(savedTimestamp);
                    const intervalsPassed = Math.floor(timeDiff / rotationInterval);
                    const lastIndex = parseInt(savedIndex);
                    const currentIndex = (lastIndex + intervalsPassed) % totalAds;

                    // Обновляем сохраненные данные
                    localStorage.setItem('adCarouselTimestamp', (parseInt(savedTimestamp) + intervalsPassed * rotationInterval).toString());
                    localStorage.setItem('adCarouselActiveIndex', currentIndex.toString());

                    return currentIndex;
                }

                // Устанавливаем активный слайд
                const activeIndex = calculateActiveIndex();
                carouselItems.forEach((item, index) => {
                    if (index === activeIndex) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

                // Обновляем индикаторы
                const indicators = adCarousel.querySelectorAll('.carousel-indicators button');
                indicators.forEach((indicator, index) => {
                    if (index === activeIndex) {
                        indicator.classList.add('active');
                        indicator.setAttribute('aria-current', 'true');
                    } else {
                        indicator.classList.remove('active');
                        indicator.removeAttribute('aria-current');
                    }
                });

                // Инициализируем карусель
                const carousel = new bootstrap.Carousel(adCarousel, {
                    interval: rotationInterval,
                    ride: 'carousel',
                    wrap: true,
                    pause: 'hover'
                });

                // Сохраняем текущее состояние при смене слайда
                adCarousel.addEventListener('slid.bs.carousel', function() {
                    const currentActive = adCarousel.querySelector('.carousel-item.active');
                    if (currentActive) {
                        const currentIndex = Array.from(carouselItems).indexOf(currentActive);
                        const now = Date.now();
                        localStorage.setItem('adCarouselActiveIndex', currentIndex.toString());
                        localStorage.setItem('adCarouselTimestamp', now.toString());
                    }
                });

                // Пауза при наведении мыши
                adCarousel.addEventListener('mouseenter', function() {
                    carousel.pause();
                });

                adCarousel.addEventListener('mouseleave', function() {
                    carousel.cycle();
                });

                // Плавная анимация переходов
                adCarousel.addEventListener('slide.bs.carousel', function() {
                    const activeItem = this.querySelector('.carousel-item.active');
                    if (activeItem) {
                        activeItem.style.transition = 'opacity 0.6s ease-in-out';
                    }
                });

                // Обработка клика по слайду (если есть ссылка)
                carouselItems.forEach(function(item) {
                    const link = item.querySelector('a');
                    if (link) {
                        item.style.cursor = 'pointer';
                        item.addEventListener('click', function(e) {
                            // Если клик не по кнопкам навигации
                            if (!e.target.closest('.carousel-control-prev') &&
                                !e.target.closest('.carousel-control-next') &&
                                !e.target.closest('.carousel-indicators')) {
                                // Переход по ссылке уже обрабатывается тегом <a>
                            }
                        });
                    }
                });
            }
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Главная страница';
include __DIR__ . '/../layout.php';
?>