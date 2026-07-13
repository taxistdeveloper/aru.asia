<?php

/**
 * СОЗДАНИЕ ОБЪЯВЛЕНИЯ О СВИДАНИИ
 */

ob_start();
?>

<style>
    /* Стиль для красивого select заголовка (общий для мобилы и десктопа) */
    .date-create-form .title-select {
        background: linear-gradient(135deg, #fff0f7 0%, #e9d8fd 100%);
        border-radius: 16px;
        border: 2px solid #f687b3;
        padding: 14px 20px;
        font-weight: 600;
        color: #2d3748;
        box-shadow: 0 8px 20px rgba(214, 51, 132, 0.18);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 12 12'%3E%3Cpath fill='%23d53f8c' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 18px center;
        background-size: 16px;
        padding-right: 48px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .date-create-form .title-select:focus {
        border-color: #d53f8c;
        box-shadow: 0 0 0 4px rgba(214, 51, 132, 0.2);
        background: linear-gradient(135deg, #ffffff 0%, #fff5f7 100%);
        outline: none;
    }

    .dating-free-note {
        border: 1px solid #86efac;
        border-radius: 14px;
        background: linear-gradient(135deg, #ecfdf5 0%, #dcfce7 100%);
        color: #14532d;
        box-shadow: 0 8px 20px rgba(34, 197, 94, 0.12);
    }

    .dating-free-note strong {
        color: #166534;
        font-size: 16px;
    }

    .btn-free-date {
        background: linear-gradient(135deg, #10b981 0%, #22c55e 50%, #16a34a 100%) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: 0 12px 24px rgba(22, 163, 74, 0.28);
    }

    .btn-free-date:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px rgba(22, 163, 74, 0.34);
        filter: brightness(1.02);
    }

    /* Иконки в заголовках полей — по теме свиданий */
    .date-create-form .form-label i {
        color: #d53f8c;
        margin-right: 6px;
        font-size: 18px;
        vertical-align: -2px;
    }

    /* Мобильная оптимизация формы создания свидания */
    @media (max-width: 767px) {
        .date-create-container {
            margin: 0;
            padding: 16px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .date-create-container h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #2d3748;
            text-align: center;
            letter-spacing: -0.5px;
            padding-top: 8px;
        }

        .date-create-form .card {
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: none;
            overflow: hidden;
            background: #ffffff;
        }

        .date-create-form .card-body {
            padding: 24px 20px;
        }

        .date-create-form .mb-3 {
            margin-bottom: 24px !important;
        }

        .date-create-form .form-label {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            color: #2d3748;
            display: block;
            letter-spacing: -0.2px;
        }

        .date-create-form .form-label::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            margin-top: 4px;
        }

        .date-create-form .form-control,
        .date-create-form .form-select {
            font-size: 16px;
            /* Предотвращает зум на iOS */
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            margin-bottom: 0;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            color: #2d3748;
        }

        .date-create-form .form-control:focus,
        .date-create-form .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            background-color: #ffffff;
            outline: none;
        }

        .date-create-form .form-control::placeholder {
            color: #a0aec0;
            font-size: 15px;
        }

        .date-create-form .form-text {
            font-size: 13px;
            margin-top: 8px;
            margin-bottom: 0;
            display: block;
            color: #718096;
            line-height: 1.5;
            padding-left: 4px;
        }

        .date-create-form .alert {
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 14px;
            margin-bottom: 20px;
            border: none;
            line-height: 1.6;
        }

        .date-create-form .alert-danger {
            background-color: #fed7d7;
            color: #c53030;
        }

        .date-create-form .alert-warning {
            background-color: #feebc8;
            color: #c05621;
        }

        .date-create-form .alert-info {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .date-create-form .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .date-create-form .btn {
            font-size: 17px;
            padding: 16px 24px;
            border-radius: 14px;
            font-weight: 700;
            width: 100%;
            margin-top: 12px;
            transition: all 0.3s ease;
            border: none;
            text-transform: none;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .date-create-form .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
        }

        .date-create-form .btn-primary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .date-create-form .btn-secondary {
            width: 100%;
            padding: 14px 20px;
            font-size: 16px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #ffffff;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        .date-create-form .btn-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(245, 87, 108, 0.4);
        }

        /* Улучшение datetime-local на мобильных */
        .date-create-form input[type="datetime-local"] {
            min-height: 52px;
            cursor: pointer;
        }

        /* Улучшение иконок */
        .date-create-form .bi {
            margin-right: 8px;
            font-size: 18px;
            vertical-align: middle;
        }

        /* Разделители между секциями */
        .date-create-form .mb-3:not(:last-child) {
            position: relative;
        }

        .date-create-form .mb-3:not(:last-child)::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        /* Улучшение для ошибок */
        .date-create-container>.alert {
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 20px;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(197, 48, 48, 0.15);
        }
    }

    /* Десктоп версия */
    @media (min-width: 768px) {
        .date-create-container {
            max-width: 700px;
            margin: 20px auto;
        }

        .date-create-form .card-body {
            padding: 30px;
        }
    }
</style>

<div class="date-create-container mt-4">
    <h2 class="mb-4">Создать объявление о свидании</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= Helper::escape($error) ?>
        </div>
    <?php endif; ?>

    

    <form method="POST" action="<?= BASE_URL ?>dates/store" class="date-create-form">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">
                        <i class="bi bi-chat-heart-fill"></i>
                        Заголовок свидания *
                    </label>
                    <select class="form-select title-select"
                        id="title"
                        name="title"
                        required>
                        <option value="">✨Выберите тему свидания: игры, спорт, прогулки, кафе и т.д.</option>
                        <option value="ИНТЕРЕСНО: серьёзные отношения">💍 Серьёзные отношения</option>
                        <option value="ИНТЕРЕСНО: новые друзья">🤝 Новые друзья</option>
                        <option value="ИНТЕРЕСНО: общение-флирт">😉 Общение и флирт</option>
                        <option value="ИНТЕРЕСНО: просто повеселится">🎉 Просто повеселиться</option>
                        <option value="ИНТЕРЕСНО: спонтанное приключение">⚡ Спонтанное приключение</option>
                    </select>
                    <small class="form-text text-muted">
                        Выберите, чего хотите: серьёзных отношений, дружбы, флирта или приключений.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">
                        <i class="bi bi-collection-play-fill"></i>
                        Категория *
                    </label>
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Категории еще не добавлены. Обратитесь к менеджеру для добавления категорий.
                        </div>
                    <?php else: ?>
                        <select class="form-select title-select"
                            id="category_id"
                            name="category_id"
                            required>
                            <option value="">✨ Выберите категорию свидания...</option>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                $nameLower = mb_strtolower($category['name']);
                                $icon = '💘'; // по умолчанию — романтическое

                                // Соцсети / контент
                                if (str_contains($nameLower, 'youtube') || str_contains($nameLower, 'ютуб')) {
                                    $icon = '📺';
                                } elseif (str_contains($nameLower, 'tiktok') || str_contains($nameLower, 'тикток')) {
                                    $icon = '🎵';
                                } elseif (str_contains($nameLower, 'insta') || str_contains($nameLower, 'инста') || str_contains($nameLower, 'instagram')) {
                                    $icon = '📸';

                                    // Развлечения / медиа
                                } elseif (str_contains($nameLower, 'кино') || str_contains($nameLower, 'фильм') || str_contains($nameLower, 'cinema')) {
                                    $icon = '🎬';
                                } elseif (str_contains($nameLower, 'игр') || str_contains($nameLower, 'game') || str_contains($nameLower, 'кибер') || str_contains($nameLower, 'playstation') || str_contains($nameLower, 'ps5') || str_contains($nameLower, 'xbox')) {
                                    $icon = '🎮';
                                } elseif (str_contains($nameLower, 'настолк') || str_contains($nameLower, 'board')) {
                                    $icon = '🎲';
                                } elseif (str_contains($nameLower, 'караоке') || str_contains($nameLower, 'karaoke') || str_contains($nameLower, 'музык') || str_contains($nameLower, 'концерт')) {
                                    $icon = '🎤';
                                } elseif (str_contains($nameLower, 'вечерин') || str_contains($nameLower, 'тусов')) {
                                    $icon = '🎉';

                                    // Еда / напитки
                                } elseif (str_contains($nameLower, 'кафе') || str_contains($nameLower, 'кофе')) {
                                    $icon = '☕';
                                } elseif (str_contains($nameLower, 'ресторан') || str_contains($nameLower, 'ужин') || str_contains($nameLower, 'dinner')) {
                                    $icon = '🍽️';
                                } elseif (str_contains($nameLower, 'бар') || str_contains($nameLower, 'кальян') || str_contains($nameLower, 'cocktail') || str_contains($nameLower, 'паб')) {
                                    $icon = '🍹';

                                    // Активный отдых / спорт
                                } elseif (str_contains($nameLower, 'спорт') || str_contains($nameLower, 'футбол') || str_contains($nameLower, 'баскет') || str_contains($nameLower, 'тренаж') || str_contains($nameLower, 'фитнес')) {
                                    $icon = '🏃‍♂️';
                                } elseif (str_contains($nameLower, 'боулинг')) {
                                    $icon = '🎳';

                                    // Прогулки / природа / путешествия
                                } elseif (str_contains($nameLower, 'парк') || str_contains($nameLower, 'прогулк') || str_contains($nameLower, 'природ') || str_contains($nameLower, 'пикник')) {
                                    $icon = '🌳';
                                } elseif (str_contains($nameLower, 'путешеств') || str_contains($nameLower, 'поездк') || str_contains($nameLower, 'trip') || str_contains($nameLower, 'travel')) {
                                    $icon = '✈️';

                                    // Темы про отношения / знакомства
                                } elseif (str_contains($nameLower, 'серьёз') || str_contains($nameLower, 'серьез') || str_contains($nameLower, 'отношен')) {
                                    $icon = '❤️';
                                } elseif (str_contains($nameLower, 'друз')) {
                                    $icon = '🤝';
                                } elseif (str_contains($nameLower, 'флирт') || str_contains($nameLower, 'общение')) {
                                    $icon = '😉';
                                } elseif (str_contains($nameLower, 'приключ') || str_contains($nameLower, 'экстрим') || str_contains($nameLower, 'спонтан')) {
                                    $icon = '⚡';
                                }
                                ?>
                                <option value="<?= $category['id'] ?>">
                                    <?= $icon ?> <?= Helper::escape($category['name']) ?>
                                    <?php if ($category['description']): ?>
                                        (<?= Helper::escape($category['description']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="date_time" class="form-label">
                        <i class="bi bi-calendar-event-fill"></i>
                        Дата и время *
                    </label>
                    <input type="datetime-local"
                        class="form-control"
                        id="date_time"
                        name="date_time"
                        max="<?= Helper::getMaxPlanningDateTimeLocal() ?>"
                        required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-free-date">
                    <i class="bi bi-gift"></i> Разместить БЕСПЛАТНО 
                </button>
            </div>
        </div>
    </form>
</div>


<?php
$content = ob_get_clean();
$title = 'Создать свидание';
include __DIR__ . '/../layout.php';
?>