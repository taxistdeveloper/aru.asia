<?php

/**
 * АДМИН-ПАНЕЛЬ - DASHBOARD
 */

ob_start();
?>

<style>
    .admin-card {
        transition: transform 0.15s, box-shadow 0.15s;
        border: none;
        padding: 0.5rem 0.75rem;
    }

    .admin-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
    }

    .admin-card.primary {
        border-left: 3px solid #0d6efd;
    }

    .admin-card.success {
        border-left: 3px solid #198754;
    }

    .admin-card.info {
        border-left: 3px solid #0dcaf0;
    }

    .admin-card.warning {
        border-left: 3px solid #ffc107;
    }

    .admin-card.danger {
        border-left: 3px solid #dc3545;
    }

    .admin-card.purple {
        border-left: 3px solid #6f42c1;
    }

    .admin-card.teal {
        border-left: 3px solid #20c997;
    }

    .admin-card.orange {
        border-left: 3px solid #fd7e14;
    }

    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.7;
    }

    .stat-number {
        font-size: 1.6rem;
        font-weight: bold;
        margin: 0.25rem 0;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.8rem;
    }

    .recent-item {
        padding: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .recent-item:last-child {
        border-bottom: none;
    }

    .dashboard-title h2 {
        font-size: 1.6rem;
    }

    .dashboard-title p {
        font-size: 0.9rem;
    }

    .nav-dashboard-tabs .nav-link {
        padding: 0.4rem 0.75rem;
        font-size: 0.9rem;
    }

    .small-badge {
        font-size: 0.7rem;
    }

    .kpi-row {
        border-radius: 0.5rem;
        background: #f8f9fa;
        padding: 0.75rem 0.75rem 0.5rem;
    }

    .activity-section-title {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .activity-section {
        margin-bottom: 1rem;
    }

    .activity-section:last-child {
        margin-bottom: 0;
    }

    .pill-badge {
        border-radius: 999px;
        padding: 0.1rem 0.45rem;
        font-size: 0.7rem;
    }

    .stat-box {
        border-radius: 0.5rem;
        padding: 0.9rem 1rem;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .stat-box .icon {
        font-size: 2.2rem;
        opacity: 0.9;
    }

    .stat-box .value {
        font-size: 1.6rem;
        font-weight: 600;
        line-height: 1;
    }

    .stat-box .label {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .stat-box.primary {
        background: linear-gradient(135deg, #0d6efd, #4dabff);
    }

    .stat-box.success {
        background: linear-gradient(135deg, #198754, #34c38f);
    }

    .stat-box.info {
        background: linear-gradient(135deg, #0dcaf0, #17a2b8);
    }

    .stat-box.warning {
        background: linear-gradient(135deg, #ffc107, #ff9f43);
        color: #212529;
    }

    .table-dashboard th,
    .table-dashboard td {
        vertical-align: middle;
        font-size: 0.85rem;
    }

    .table-dashboard th {
        white-space: nowrap;
    }

    .table-dashboard tr td:last-child {
        text-align: right;
    }

    @media (max-width: 767.98px) {
        .dashboard-title h2 {
            font-size: 1.2rem;
        }

        .dashboard-title p {
            font-size: 0.8rem;
        }

        .stat-box {
            padding: 0.7rem 0.8rem;
            border-radius: 0.6rem;
        }

        .stat-box .value {
            font-size: 1.3rem;
        }

        .stat-box .icon {
            font-size: 1.8rem;
        }

        .admin-card {
            padding: 0.5rem 0.6rem;
        }

        .activity-section-title {
            font-size: 0.75rem;
        }

        .card-header h5,
        .card-header h6 {
            font-size: 1rem;
        }

        .table-dashboard thead {
            display: none;
        }

        .table-dashboard,
        .table-dashboard tbody,
        .table-dashboard tr,
        .table-dashboard td {
            display: block;
            width: 100%;
        }

        .table-dashboard tr {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.4rem 0.5rem;
            margin-bottom: 0.6rem;
        }

        .table-dashboard tr:last-child {
            margin-bottom: 0;
        }

        .table-dashboard td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            border: none;
            text-align: right;
            gap: 0.75rem;
        }

        .table-dashboard td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6c757d;
            text-align: left;
        }

        .table-dashboard tr td:last-child {
            text-align: right;
        }
    }
</style>

<div class="mt-3">
    <!-- Заголовок -->
    <div class="dashboard-title mb-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h2 class="mb-1"><i class="bi bi-speedometer2"></i> Обзор системы</h2>
                <p class="text-muted mb-0">Главные показатели и последние действия пользователей</p>
            </div>
            <div class="mt-2 mt-md-0 text-md-end">
                <a href="<?= BASE_URL ?>dev-panel.php" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-tools"></i> Dev panel
                </a>
                <small class="text-muted d-block">Сейчас: <?= date('d.m.Y H:i') ?></small>
                <small class="text-muted">Всего пользователей: <strong><?= $stats['total_users'] ?? 0 ?></strong></small>
            </div>
        </div>
    </div>

    <!-- Верхние статистические блоки в стиле админ-панели -->
    <div class="mb-3">
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-box primary">
                    <div>
                        <div class="label">Пользователи</div>
                        <div class="value"><?= $stats['total_users'] ?? 0 ?></div>
                        <small><i class="bi bi-arrow-up"></i> +<?= $stats['users_week'] ?? 0 ?> / нед.</small>
                    </div>
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-box success">
                    <div>
                        <div class="label">Подтвержденные</div>
                        <div class="value"><?= $stats['verified_users'] ?? 0 ?></div>
                        <small><?= $stats['unverified_users'] ?? 0 ?> ждут</small>
                    </div>
                    <div class="icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-box info">
                    <div>
                        <div class="label">Мероприятия</div>
                        <div class="value"><?= $stats['total_events'] ?? 0 ?></div>
                        <small>+<?= $stats['events_today'] ?? 0 ?> сегодня</small>
                    </div>
                    <div class="icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-box warning">
                    <div>
                        <div class="label">Свидания</div>
                        <div class="value"><?= $stats['total_dates'] ?? 0 ?></div>
                        <small>+<?= $stats['dates_today'] ?? 0 ?> сегодня</small>
                    </div>
                    <div class="icon">
                        <i class="bi bi-heart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Левая колонка: таблицы с активностью -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-activity"></i> Активность</h5>
                    <small class="text-muted">Сообщений: <?= $stats['total_messages'] ?? 0 ?></small>
                </div>
                <div class="card-body">
                    <!-- Пользователи -->
                    <div class="activity-section">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="activity-section-title">Последние пользователи</span>
                            <a href="<?= BASE_URL ?>admin/users" class="btn btn-sm btn-outline-primary">
                                Все
                            </a>
                        </div>
                        <?php if (empty($recent_users ?? [])): ?>
                            <div class="text-muted small">Нет новых пользователей</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-dashboard mb-0">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Пол / Возраст</th>
                                            <th>Город</th>
                                            <th>Статус</th>
                                            <th>Дата</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_users as $user): ?>
                                            <tr>
                                                <td data-label="Email"><?= Helper::escape($user['email']) ?></td>
                                                <td data-label="Пол / Возраст">
                                                    <?= $user['gender'] === 'male' ? 'М' : ($user['gender'] === 'female' ? 'Ж' : '?') ?>
                                                    <?php if ($user['age']): ?> / <?= $user['age'] ?><?php endif; ?>
                                                </td>
                                                <td data-label="Город"><?= $user['city'] ? Helper::escape($user['city']) : '-' ?></td>
                                                <td data-label="Статус">
                                                    <?php if ($user['email_verified']): ?>
                                                        <span class="badge bg-success small-badge">Подтв.</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning small-badge">Не подтв.</span>
                                                    <?php endif; ?>
                                                    <?php
                                                    $userRole = $user['role'] ?? 'user';
                                                    if ($userRole === 'manager'):
                                                    ?>
                                                        <span class="badge bg-info small-badge">Менеджер</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Дата"><small class="text-muted"><?= date('d.m H:i', strtotime($user['created_at'])) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Мероприятия -->
                    <div class="activity-section">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="activity-section-title">Последние мероприятия</span>
                        </div>
                        <?php if (empty($recent_events ?? [])): ?>
                            <div class="text-muted small">Нет новых мероприятий</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-dashboard mb-0">
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Организатор</th>
                                            <th>Локация</th>
                                            <th>Дата</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_events as $event): ?>
                                            <tr>
                                                <td data-label="Название"><?= Helper::escape($event['title']) ?></td>
                                                <td data-label="Организатор"><?= Helper::escape($event['user_email']) ?></td>
                                                <td data-label="Локация"><?= Helper::escape($event['location']) ?></td>
                                                <td data-label="Дата"><small class="text-muted"><?= date('d.m', strtotime($event['created_at'])) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Свидания -->
                    <div class="activity-section mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="activity-section-title">Последние свидания</span>
                        </div>
                        <?php if (empty($recent_dates ?? [])): ?>
                            <div class="text-muted small">Нет новых свиданий</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-dashboard mb-0">
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Пользователь</th>
                                            <th>Локация</th>
                                            <th>Дата</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_dates as $date): ?>
                                            <tr>
                                                <td data-label="Название"><?= Helper::escape($date['title']) ?></td>
                                                <td data-label="Пользователь"><?= Helper::escape($date['user_email']) ?></td>
                                                <td data-label="Локация"><?= Helper::escape($date['location']) ?></td>
                                                <td data-label="Дата"><small class="text-muted"><?= date('d.m', strtotime($date['created_at'])) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Правая колонка: системный статус и действия -->
        <div class="col-lg-5">
            <div class="row g-3">
                <?php
                // Dev panel показываем только на localhost, чтобы случайно не засветить на проде
                $allowedHosts = ['localhost', '127.0.0.1', '::1'];
                $isLocalDevPanelAllowed =
                    in_array($_SERVER['SERVER_NAME'] ?? '', $allowedHosts, true) ||
                    in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedHosts, true);
                ?>
                <?php if ($isLocalDevPanelAllowed): ?>
                    <!-- Dev panel (встроено) -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="bi bi-tools"></i> Dev panel</h6>
                                <a href="<?= BASE_URL ?>dev-panel.php" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                    Открыть
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <iframe
                                    src="<?= BASE_URL ?>dev-panel.php"
                                    title="Dev panel"
                                    loading="lazy"
                                    style="width: 100%; height: 680px; border: 0;"></iframe>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Дополнительные показатели -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-graph-up"></i> Дополнительные показатели</h6>
                        </div>
                        <div class="card-body">
                            <div class="row gy-2">
                                <div class="col-12 col-sm-6">
                                    <div class="admin-card primary h-100">
                                        <div class="stat-label">Посещений сегодня</div>
                                        <div class="stat-number" style="color: #0d6efd;"><?= $stats['visits_today'] ?? 0 ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="admin-card info h-100">
                                        <div class="stat-label">Уникальных сегодня</div>
                                        <div class="stat-number" style="color: #0dcaf0;"><?= $stats['unique_today'] ?? 0 ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="admin-card purple h-100">
                                        <div class="stat-label">Сообщений</div>
                                        <div class="stat-number" style="color: #6f42c1;"><?= $stats['total_messages'] ?? 0 ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="admin-card teal h-100">
                                        <div class="stat-label">Активная реклама</div>
                                        <div class="stat-number" style="color: #20c997;"><?= $stats['active_ads'] ?? 0 ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="admin-card orange h-100">
                                        <div class="stat-label">Новых сегодня</div>
                                        <div class="stat-number" style="color: #fd7e14;"><?= $stats['users_today'] ?? 0 ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="admin-card" style="border-left: 3px solid #17a2b8;">
                                        <div class="stat-label">Менеджеров</div>
                                        <div class="stat-number" style="color: #17a2b8;"><?= $stats['managers'] ?? 0 ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Быстрые действия -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-lightning-charge"></i> Действия администратора</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>admin/users" class="btn btn-outline-primary btn-sm text-start">
                                    <i class="bi bi-people"></i> Открыть список пользователей
                                </a>
                                <a href="<?= BASE_URL ?>admin/events" class="btn btn-outline-info btn-sm text-start d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-calendar-event"></i> Проверить мероприятия</span>
                                    <?php if (isset($stats['pending_events']) && ($stats['pending_events'] ?? 0) > 0): ?>
                                        <span class="badge bg-danger small-badge"><?= $stats['pending_events'] ?></span>
                                    <?php endif; ?>
                                </a>
                                <a href="<?= BASE_URL ?>admin/ads" class="btn btn-outline-warning btn-sm text-start d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-megaphone"></i> Модерировать рекламу</span>
                                    <?php if (($stats['pending_ads'] ?? 0) > 0): ?>
                                        <span class="badge bg-danger small-badge"><?= $stats['pending_ads'] ?></span>
                                    <?php endif; ?>
                                </a>
                                <a href="<?= BASE_URL ?>admin/change-password" class="btn btn-outline-danger btn-sm text-start">
                                    <i class="bi bi-key"></i> Сменить пароль администратора
                                </a>
                                <a href="<?= BASE_URL ?>home" class="btn btn-outline-secondary btn-sm text-start">
                                    <i class="bi bi-house"></i> Перейти на сайт
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Риски / модерация -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-shield-exclamation"></i> Зона риска</h6>
                            <?php if (($stats['pending_ads'] ?? 0) > 0): ?>
                                <span class="badge bg-light text-danger small-badge">
                                    <?= $stats['pending_ads'] ?> реклам
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pending_ads ?? [])): ?>
                                <div class="text-muted small">Критичных задач сейчас нет.</div>
                            <?php else: ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($pending_ads as $ad): ?>
                                        <li class="mb-1">
                                            <small>
                                                <strong><?= Helper::escape($ad['advertiser_name']) ?></strong>
                                                <span class="text-muted">• <?= Helper::escape($ad['country']) ?></span>
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Dashboard - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>