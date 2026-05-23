<?php

/**
 * ПРОСМОТР РЕКЛАМЫ
 */

ob_start();
?>

<style>
    .ad-view-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .ad-view-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .ad-image-container {
        background: #f0f2f5;
        padding: 14px 16px;
        text-align: center;
        border-bottom: 1px solid rgba(11, 20, 26, 0.06);
    }

    .ad-image-container img {
        max-width: 100%;
        max-height: 500px;
        border-radius: 8px;
        box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.12);
        object-fit: contain;
    }

    .ad-info-section {
        padding: 30px;
    }

    .ad-info-item {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .ad-info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .ad-info-label {
        font-weight: 600;
        color: #667eea;
        margin-bottom: 8px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ad-info-value {
        font-size: 1.1rem;
        color: #1a1a1a;
    }

    .ad-status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .btn-back {
        background: #6c757d;
        border: none;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-back,
    .btn-back .bi {
        color: #fff !important;
    }

    .btn-back:hover {
        background: #5a6268;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-click-url {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 14px 32px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        margin-top: 20px;
    }

    .btn-click-url,
    .btn-click-url .bi {
        color: #fff !important;
    }

    .btn-click-url:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(102, 126, 234, 0.3);
    }

    @media (max-width: 767px) {
        .ad-view-container {
            padding: 15px;
        }

        .btn-back,
        .btn-back .bi,
        .btn-click-url,
        .btn-click-url .bi {
            color: #fff !important;
        }

        .ad-image-container {
            padding: 20px 15px;
        }

        .ad-info-section {
            padding: 20px 15px;
        }

        .ad-image-container img {
            max-height: 400px;
        }
    }
</style>

<div class="mobile-page-container">
    <div class="ad-view-container">
        <div class="mb-4">
            <a href="<?= BASE_URL ?>profile" class="btn-back">
                <i class="bi bi-arrow-left"></i> Вернуться в кабинет
            </a>
        </div>

        <div class="ad-view-card">
            <!-- Изображение рекламы -->
            <?php if (!empty($ad['image_path'])): ?>
                <div class="ad-image-container">
                    <img src="<?= BASE_URL . UPLOAD_DIR . 'ads/' . $ad['image_path'] ?>"
                        alt="<?= Helper::escape($ad['advertiser_name']) ?>"
                        class="img-fluid">
                </div>
            <?php endif; ?>

            <!-- Информация о рекламе -->
            <div class="ad-info-section">
                <div class="ad-info-item">
                    <div class="ad-info-label">Название рекламодателя</div>
                    <div class="ad-info-value"><?= Helper::escape($ad['advertiser_name']) ?></div>
                </div>

                <div class="ad-info-item">
                    <div class="ad-info-label">Страна</div>
                    <div class="ad-info-value"><?= Helper::escape($ad['country']) ?></div>
                </div>

                <?php if (!empty($ad['city'])): ?>
                    <div class="ad-info-item">
                        <div class="ad-info-label">Город</div>
                        <div class="ad-info-value"><?= Helper::escape($ad['city']) ?></div>
                    </div>
                <?php endif; ?>

                <div class="ad-info-item">
                    <div class="ad-info-label">Период показа</div>
                    <div class="ad-info-value">
                        <?php if (!empty($ad['start_date'])): ?>
                            <div class="mb-2">
                                <i class="bi bi-calendar-event text-primary"></i>
                                <strong>С:</strong> <?= date('d.m.Y H:i', strtotime($ad['start_date'])) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($ad['end_date'])): ?>
                            <div>
                                <i class="bi bi-calendar-x text-danger"></i>
                                <strong>По:</strong> <?= date('d.m.Y H:i', strtotime($ad['end_date'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ad-info-item">
                    <div class="ad-info-label">Статус</div>
                    <div class="ad-info-value">
                        <?php
                        $statusClass = 'secondary';
                        $statusText = 'Ожидает';
                        if (isset($ad['status'])) {
                            if ($ad['status'] === 'active') {
                                $statusClass = 'success';
                                $statusText = 'Активна';
                            } elseif ($ad['status'] === 'expired') {
                                $statusClass = 'danger';
                                $statusText = 'Истекла';
                            } elseif ($ad['status'] === 'pending') {
                                $statusClass = 'warning';
                                $statusText = 'На модерации';
                            }
                        }
                        ?>
                        <span class="badge bg-<?= $statusClass ?> ad-status-badge"><?= $statusText ?></span>
                    </div>
                </div>

                <?php if (!empty($ad['click_url'])): ?>
                    <div class="ad-info-item">
                        <div class="ad-info-label">Ссылка для перехода</div>
                        <div class="ad-info-value">
                            <a href="<?= Helper::escape($ad['click_url']) ?>"
                                target="_blank"
                                class="btn-click-url">
                                <i class="bi bi-box-arrow-up-right"></i> Перейти по ссылке
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($ad['created_at'])): ?>
                    <div class="ad-info-item">
                        <div class="ad-info-label">Дата создания</div>
                        <div class="ad-info-value">
                            <i class="bi bi-clock"></i> <?= date('d.m.Y H:i', strtotime($ad['created_at'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Просмотр рекламы';
include __DIR__ . '/../layout.php';
?>