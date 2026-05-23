<?php

/**
 * СТРАНИЦА 404 - СТРАНИЦА НЕ НАЙДЕНА
 */

// Подключаем базовый шаблон
ob_start();
?>

<style>
    /* СТРАНИЦА 404 - СТИЛИ В СТИЛЕ LANDING */
    .error-container {
        min-height: 100vh;
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 50%, #ffffff 100%);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
    }

    .error-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 30%, rgba(102, 126, 234, 0.03) 0%, transparent 50%),
            radial-gradient(circle at 80% 70%, rgba(240, 147, 251, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    .error-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #1a1a1a;
        max-width: 700px;
        width: 100%;
        animation: fadeInUp 0.8s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .error-code {
        font-size: 120px;
        font-weight: 800;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        animation: pulse 3s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { 
            transform: scale(1);
            opacity: 1;
        }
        50% { 
            transform: scale(1.05);
            opacity: 0.9;
        }
    }

    .error-icon {
        font-size: 80px;
        margin-bottom: 30px;
        color: #667eea;
        animation: bounce 2s ease-in-out infinite;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    .error-title {
        font-size: 42px;
        font-weight: 800;
        margin-bottom: 20px;
        color: #1a1a1a;
        letter-spacing: -1px;
        line-height: 1.2;
    }

    .error-description {
        font-size: 20px;
        margin-bottom: 50px;
        color: #4a5568;
        line-height: 1.8;
    }

    .error-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 40px;
    }

    .error-btn {
        padding: 18px 40px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 50px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }

    .error-btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .error-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }

    .error-btn-secondary {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
    }

    .error-btn-secondary:hover {
        background: #667eea;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        text-decoration: none;
    }

    .error-suggestions {
        margin-top: 60px;
        padding: 40px 30px;
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .error-suggestions-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 25px;
        color: #1a1a1a;
    }

    .error-suggestions-list {
        list-style: none;
        padding: 0;
        margin: 0;
        text-align: left;
    }

    .error-suggestions-item {
        padding: 15px 0;
        font-size: 16px;
        color: #4a5568;
        line-height: 1.7;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .error-suggestions-item:last-child {
        border-bottom: none;
    }

    .error-suggestions-icon {
        font-size: 20px;
        color: #667eea;
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* Mobile styles */
    @media (max-width: 767px) {
        .error-container {
            padding: 40px 15px;
        }

        .error-code {
            font-size: 80px;
            margin-bottom: 15px;
        }

        .error-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 28px;
            margin-bottom: 15px;
        }

        .error-description {
            font-size: 17px;
            margin-bottom: 40px;
        }

        .error-buttons {
            flex-direction: column;
            align-items: stretch;
            margin-top: 30px;
        }

        .error-btn {
            width: 100%;
            padding: 16px 30px;
            font-size: 16px;
            justify-content: center;
        }

        .error-suggestions {
            margin-top: 40px;
            padding: 30px 20px;
            border-radius: 15px;
        }

        .error-suggestions-title {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .error-suggestions-item {
            font-size: 15px;
            padding: 12px 0;
        }
    }
</style>

<div class="error-container">
    <div class="error-content">
        <div class="error-code">404</div>
        
        <div class="error-icon" aria-hidden="true">
            <i class="bi bi-emoji-frown"></i>
        </div>
        
        <h1 class="error-title">Страница не найдена</h1>
        
        <p class="error-description">
            К сожалению, страница, которую вы ищете, не существует или была перемещена. 
            Возможно, вы ввели неправильный адрес или страница была удалена.
        </p>
        
        <div class="error-buttons">
            <a href="<?= BASE_URL ?>" class="error-btn error-btn-primary" aria-label="Вернуться на главную страницу">
                <i class="bi bi-house-door" aria-hidden="true"></i> На главную
            </a>
            <a href="javascript:history.back()" class="error-btn error-btn-secondary" aria-label="Вернуться назад">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Назад
            </a>
        </div>

        <div class="error-suggestions">
            <h2 class="error-suggestions-title">Попробуйте:</h2>
            <ul class="error-suggestions-list">
                <li class="error-suggestions-item">
                    <i class="bi bi-check-circle error-suggestions-icon" aria-hidden="true"></i>
                    <span>Проверить правильность введенного адреса</span>
                </li>
                <li class="error-suggestions-item">
                    <i class="bi bi-check-circle error-suggestions-icon" aria-hidden="true"></i>
                    <span>Вернуться на <a href="<?= BASE_URL ?>" style="color: #667eea; text-decoration: none; font-weight: 600;">главную страницу</a></span>
                </li>
                <li class="error-suggestions-item">
                    <i class="bi bi-check-circle error-suggestions-icon" aria-hidden="true"></i>
                    <span>Посетить раздел <a href="<?= BASE_URL ?>events" style="color: #667eea; text-decoration: none; font-weight: 600;">мероприятия</a> или <a href="<?= BASE_URL ?>dates" style="color: #667eea; text-decoration: none; font-weight: 600;">свидания</a></span>
                </li>
                <li class="error-suggestions-item">
                    <i class="bi bi-check-circle error-suggestions-icon" aria-hidden="true"></i>
                    <span>Использовать <a href="<?= BASE_URL ?>map" style="color: #667eea; text-decoration: none; font-weight: 600;">карту</a> для поиска интересных мест</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = '404 - Страница не найдена | Aru';
$metaDescription = 'Страница не найдена. Вернитесь на главную страницу Aru или используйте навигацию сайта.';
$metaKeywords = '404, страница не найдена, aru';
include __DIR__ . '/../layout.php';
?>


