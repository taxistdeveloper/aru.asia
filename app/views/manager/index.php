<?php

/**
 * ПАНЕЛЬ МЕНЕДЖЕРА - DASHBOARD
 */

ob_start();
?>

<style>
    .manager-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-left: 4px solid;
    }

    .manager-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .manager-card.info {
        border-left-color: #0dcaf0;
    }

    .manager-card.success {
        border-left-color: #198754;
    }

    .manager-card.warning {
        border-left-color: #ffc107;
    }

    .stat-icon {
        font-size: 2rem;
        opacity: 0.6;
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: bold;
        margin: 0.3rem 0;
        line-height: 1.2;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 0.2rem;
        font-weight: 500;
    }

    .manager-card .card-body {
        padding: 1rem;
    }

    @media (max-width: 768px) {
        .stat-icon {
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 1.4rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .manager-card .card-body {
            padding: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .stat-icon {
            font-size: 1.3rem;
        }

        .stat-number {
            font-size: 1.2rem;
        }

        .stat-label {
            font-size: 0.7rem;
        }

        .manager-card .card-body {
            padding: 0.6rem;
        }
    }

    .recent-item {
        padding: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .recent-item:last-child {
        border-bottom: none;
    }
</style>

<div class="mt-4">


    <!-- Статистика -->
    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card manager-card info h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="text-muted stat-label">Всего пользователей</div>
                            <div class="stat-number text-info"><?= $stats['total_users'] ?></div>
                        </div>
                        <i class="bi bi-people stat-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card manager-card success h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="text-muted stat-label">Подтвержденных</div>
                            <div class="stat-number text-success"><?= $stats['verified_users'] ?></div>
                        </div>
                        <i class="bi bi-check-circle stat-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card manager-card warning h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="text-muted stat-label">Активных мероприятий</div>
                            <div class="stat-number text-warning"><?= $stats['active_events'] ?></div>
                            <small class="text-info d-block mt-1" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar-event"></i> <?= $stats['total_events'] ?> одобрено
                            </small>
                        </div>
                        <i class="bi bi-calendar-event stat-icon text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card manager-card info h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="text-muted stat-label">Активных свиданий</div>
                            <div class="stat-number text-info"><?= $stats['active_dates'] ?></div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                <?= $stats['total_dates'] ?> всего
                            </small>
                        </div>
                        <i class="bi bi-heart stat-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Быстрые действия -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>manager/users" class="btn btn-primary">
                            <i class="bi bi-people"></i> Просмотр пользователей
                        </a>
                        <a href="<?= BASE_URL ?>manager/stats" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up"></i> Статистика
                        </a>
                        <a href="<?= BASE_URL ?>manager/events" class="btn btn-info">
                            <i class="bi bi-calendar-event"></i> Модерация мероприятий
                            <?php if (isset($stats['pending_events']) && $stats['pending_events'] > 0): ?>
                                <span class="badge bg-danger"><?= $stats['pending_events'] ?></span>
                            <?php endif; ?>
                        </a>
                      
                        <a href="<?= BASE_URL ?>manager/ads" class="btn btn-success">
                            <i class="bi bi-megaphone"></i> Управление рекламой
                            <?php
                            $db = Database::getInstance()->getConnection();
                            $pending_ads = $db->query("SELECT COUNT(*) FROM ads WHERE status = 'pending'")->fetchColumn();
                            if ($pending_ads > 0): ?>
                                <span class="badge bg-danger"><?= $pending_ads ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= BASE_URL ?>home" class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> На главную сайта
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Последние данные -->
    <div class="row g-4">
        <!-- Последние пользователи -->
        <!-- <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Последние пользователи</h5>
                    <a href="<?= BASE_URL ?>manager/users" class="btn btn-sm btn-light">Все</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_users)): ?>
                        <div class="p-3 text-center text-muted">Нет пользователей</div>
                    <?php else: ?>
                        <?php foreach ($recent_users as $user): ?>
                            <div class="recent-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= Helper::escape($user['email']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= $user['gender'] === 'male' ? 'М' : ($user['gender'] === 'female' ? 'Ж' : '?') ?>
                                            <?php if ($user['age']): ?> • <?= $user['age'] ?> лет<?php endif; ?>
                                                <?php if ($user['city']): ?> • <?= Helper::escape($user['city']) ?><?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($user['email_verified']): ?>
                                            <span class="badge bg-success">Подтвержден</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Не подтвержден</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?= date('d.m.Y', strtotime($user['created_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div> -->

        <!-- Последние мероприятия -->
        <!-- <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Последние мероприятия</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_events)): ?>
                        <div class="p-3 text-center text-muted">Нет мероприятий</div>
                    <?php else: ?>
                        <?php foreach ($recent_events as $event): ?>
                            <div class="recent-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <strong><?= Helper::escape($event['title']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= Helper::escape($event['user_email']) ?> •
                                            <?= Helper::escape($event['location']) ?>
                                        </small>
                                    </div>
                                    <small class="text-muted"><?= date('d.m.Y', strtotime($event['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div> -->


    </div>

    <!-- Информация -->
    <div class="alert alert-info mt-3 py-2" style="font-size: 0.85rem;">
        <h6 class="mb-1" style="font-size: 0.9rem;"><i class="bi bi-info-circle"></i> О панели менеджера</h6>
        <p class="mb-0" style="font-size: 0.85rem;">
            Вы вошли как <strong>менеджер</strong>. У вас есть доступ к просмотру статистики, данных пользователей, модерации мероприятий и управлению рекламой.
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Панель менеджера';
include __DIR__ . '/../admin_layout.php';
?>
