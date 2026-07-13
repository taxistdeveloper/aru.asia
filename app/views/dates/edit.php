<?php

/**
 * РЕДАКТИРОВАНИЕ ОБЪЯВЛЕНИЯ О СВИДАНИИ
 */

ob_start();
?>

<style>
    /* Мобильная оптимизация формы редактирования свидания */
    @media (max-width: 767px) {
        .date-edit-container {
            margin: 0;
            padding: 16px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .date-edit-container h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #2d3748;
            text-align: center;
            letter-spacing: -0.5px;
            padding-top: 8px;
        }

        .date-edit-form .card {
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: none;
            overflow: hidden;
            background: #ffffff;
        }

        .date-edit-form .card-body {
            padding: 24px 20px;
        }

        .date-edit-form .mb-3 {
            margin-bottom: 24px !important;
        }

        .date-edit-form .form-label {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            color: #2d3748;
            display: block;
            letter-spacing: -0.2px;
        }

        .date-edit-form .form-label::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            margin-top: 4px;
        }

        .date-edit-form .form-control,
        .date-edit-form .form-select {
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

        .date-edit-form .form-control:focus,
        .date-edit-form .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            background-color: #ffffff;
            outline: none;
        }

        .date-edit-form .form-control::placeholder {
            color: #a0aec0;
            font-size: 15px;
        }

        .date-edit-form .form-text {
            font-size: 13px;
            margin-top: 8px;
            margin-bottom: 0;
            display: block;
            color: #718096;
            line-height: 1.5;
            padding-left: 4px;
        }

        .date-edit-form .alert {
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 14px;
            margin-bottom: 20px;
            border: none;
            line-height: 1.6;
        }

        .date-edit-form .alert-danger {
            background-color: #fed7d7;
            color: #c53030;
        }

        .date-edit-form .alert-warning {
            background-color: #feebc8;
            color: #c05621;
        }

        .date-edit-form .alert-info {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .date-edit-form .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .date-edit-form .btn {
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

        .date-edit-form .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
        }

        .date-edit-form .btn-primary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .date-edit-form .btn-secondary {
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

        .date-edit-form .btn-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(245, 87, 108, 0.4);
        }

        .date-edit-form .btn-outline-secondary {
            width: 100%;
            padding: 14px 20px;
            font-size: 16px;
            margin-top: 12px;
            margin-left: 0 !important;
            background: #ffffff;
            color: #718096;
            border: 2px solid #e2e8f0;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .date-edit-form .btn-outline-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            background-color: #f7fafc;
        }

        /* Улучшение селектов на мобильных */
        .date-edit-form .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 18px center;
            background-size: 14px;
            padding-right: 45px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
        }

        /* Улучшение datetime-local на мобильных */
        .date-edit-form input[type="datetime-local"] {
            min-height: 52px;
            cursor: pointer;
        }

        /* Улучшение иконок */
        .date-edit-form .bi {
            margin-right: 8px;
            font-size: 18px;
            vertical-align: middle;
        }

        /* Разделители между секциями */
        .date-edit-form .mb-3:not(:last-child) {
            position: relative;
        }

        .date-edit-form .mb-3:not(:last-child)::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        /* Улучшение для ошибок */
        .date-edit-container>.alert {
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 20px;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(197, 48, 48, 0.15);
        }
    }

    /* Десктоп версия */
    @media (min-width: 768px) {
        .date-edit-container {
            max-width: 700px;
            margin: 20px auto;
        }

        .date-edit-form .card-body {
            padding: 30px;
        }
    }
</style>

<div class="date-edit-container mt-4">
    <h2 class="mb-4">Редактировать объявление о свидании</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= Helper::escape($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>dates/update?id=<?= (int)$date['id'] ?>" class="date-edit-form">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Заголовок *</label>
                    <select class="form-select"
                        id="title"
                        name="title"
                        required>
                        <option value="">Выберите заголовок...</option>
                        <option value="ИНТЕРЕСНО: серьёзные отношения" <?= $date['title'] === 'ИНТЕРЕСНО: серьёзные отношения' ? 'selected' : '' ?>>ИНТЕРЕСНО: серьёзные отношения</option>
                        <option value="ИНТЕРЕСНО: новые друзья" <?= $date['title'] === 'ИНТЕРЕСНО: новые друзья' ? 'selected' : '' ?>>ИНТЕРЕСНО: новые друзья</option>
                        <option value="ИНТЕРЕСНО: общение-флирт" <?= $date['title'] === 'ИНТЕРЕСНО: общение-флирт' ? 'selected' : '' ?>>ИНТЕРЕСНО: общение-флирт</option>
                        <option value="ИНТЕРЕСНО: просто повеселится" <?= $date['title'] === 'ИНТЕРЕСНО: просто повеселится' ? 'selected' : '' ?>>ИНТЕРЕСНО: просто повеселится</option>
                        <option value="ИНТЕРЕСНО: спонтанное приключение" <?= $date['title'] === 'ИНТЕРЕСНО: спонтанное приключение' ? 'selected' : '' ?>>ИНТЕРЕСНО: спонтанное приключение</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Категория *</label>
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Категории еще не добавлены. Обратитесь к менеджеру для добавления категорий.
                        </div>
                    <?php else: ?>
                        <select class="form-select"
                            id="category_id"
                            name="category_id"
                            required>
                            <option value="">Выберите категорию...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"
                                    <?= (int)$date['category_id'] === (int)$category['id'] ? 'selected' : '' ?>>
                                    <?= Helper::escape($category['name']) ?>
                                    <?php if ($category['description']): ?>
                                        - <?= Helper::escape($category['description']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            Выберите категорию для вашего свидания. Категории добавляются менеджером.
                        </small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="date_time" class="form-label">Дата и время *</label>
                    <input type="datetime-local"
                        class="form-control"
                        id="date_time"
                        name="date_time"
                        max="<?= Helper::getMaxPlanningDateTimeLocal() ?>"
                        required
                        value="<?= date('Y-m-d\TH:i', strtotime($date['date_time'])) ?>">
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle"></i> Сохранить изменения
                </button>
                <a href="<?= BASE_URL ?>profile" class="btn btn-outline-secondary btn-lg ms-2">
                    Отмена
                </a>
            </div>
        </div>
    </form>
</div>


<?php
$content = ob_get_clean();
$title = 'Редактировать свидание';
include __DIR__ . '/../layout.php';
?>