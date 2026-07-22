<?php
/**
 * АДМИН-ПАНЕЛЬ - СТАТИСТИКА
 */

ob_start();
?>

<div class="mt-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-graph-up"></i> Статистика</h2>
            <p class="text-muted mb-0">Уникальные люди по разделам (один человек = 1, сколько бы раз ни заходил)</p>
        </div>
        <div class="mt-2 mt-md-0 text-md-end">
            <small class="text-muted d-block">Обновлено: <?= date('d.m.Y H:i') ?></small>
        </div>
    </div>

    <!-- KPI -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Главная сегодня</div>
                    <div class="fs-3 fw-semibold"><?= $stats['visits_today'] ?? 0 ?></div>
                    <div class="text-muted small">Уникальных гостей: <?= $stats['unique_today'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Пользователей всего</div>
                    <div class="fs-3 fw-semibold"><?= $stats['total_users'] ?? 0 ?></div>
                    <div class="text-muted small">+<?= $stats['users_today'] ?? 0 ?> сегодня</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Мероприятий одобрено</div>
                    <div class="fs-3 fw-semibold"><?= $stats['total_events'] ?? 0 ?></div>
                    <div class="text-muted small">Активных: <?= $stats['active_events'] ?? 0 ?> · Ожидают: <?= $stats['pending_events'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Сообщений всего</div>
                    <div class="fs-3 fw-semibold"><?= $stats['total_messages'] ?? 0 ?></div>
                    <div class="text-muted small">Активная реклама: <?= $stats['active_ads'] ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Заходы по разделам -->
    <div class="card mb-3">
        <div class="card-header">
            <strong><i class="bi bi-diagram-3"></i> Заходы по разделам</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Раздел</th>
                            <th class="text-end">Людей сегодня</th>
                            <th class="text-end">Людей за 30 дней</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($section_labels ?? []) as $sectionKey => $sectionLabel): ?>
                            <tr>
                                <td><?= Helper::escape($sectionLabel) ?></td>
                                <td class="text-end"><strong><?= (int)($section_visits_today[$sectionKey] ?? 0) ?></strong></td>
                                <td class="text-end"><?= (int)($section_visits_month[$sectionKey] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted">
                Один человек = 1 в каждом разделе, даже если заходил или писал сообщения много раз.
                «Вход» — кто открывал страницу входа; «Регистрация» — кто реально создал аккаунт.
                Админка не учитывается.
            </small>
        </div>
    </div>

    <!-- Детальная сводка -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <strong><i class="bi bi-list-check"></i> Сводка</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Пользователи (подтв./неподтв.)</td>
                                    <td class="text-end">
                                        <strong><?= $stats['verified_users'] ?? 0 ?></strong> /
                                        <strong><?= $stats['unverified_users'] ?? 0 ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Новых пользователей (неделя)</td>
                                    <td class="text-end"><strong><?= $stats['users_week'] ?? 0 ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Мероприятия (одобрено / активных / ожидают)</td>
                                    <td class="text-end">
                                        <strong><?= $stats['total_events'] ?? 0 ?></strong> /
                                        <strong><?= $stats['active_events'] ?? 0 ?></strong> /
                                        <strong><?= $stats['pending_events'] ?? 0 ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Отклонённых мероприятий</td>
                                    <td class="text-end"><strong><?= $stats['rejected_events'] ?? 0 ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Новых мероприятий (сегодня / неделя)</td>
                                    <td class="text-end">
                                        <strong><?= $stats['events_today'] ?? 0 ?></strong> /
                                        <strong><?= $stats['events_week'] ?? 0 ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Новых свиданий (сегодня / неделя)</td>
                                    <td class="text-end">
                                        <strong><?= $stats['dates_today'] ?? 0 ?></strong> /
                                        <strong><?= $stats['dates_week'] ?? 0 ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Свиданий (всего / активных)</td>
                                    <td class="text-end">
                                        <strong><?= $stats['total_dates'] ?? 0 ?></strong> /
                                        <strong><?= $stats['active_dates'] ?? 0 ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Реклама (активная / ожидает)</td>
                                    <td class="text-end">
                                        <strong><?= $stats['active_ads'] ?? 0 ?></strong> /
                                        <strong><?= $stats['pending_ads'] ?? 0 ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Менеджеров</td>
                                    <td class="text-end"><strong><?= $stats['managers'] ?? 0 ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-calendar3"></i> Главная страница — посещения по дням (30 дней)</strong>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>admin">
                        <i class="bi bi-arrow-left"></i> Назад
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($daily_visits ?? [])): ?>
                        <div class="text-muted small">Данных пока нет. Посещения начнут учитываться после первых заходов на сайт.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th class="text-end">Посещений</th>
                                        <th class="text-end">Уникальных</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daily_visits as $row): ?>
                                        <tr>
                                            <td><?= date('d.m.Y', strtotime($row['visit_date'])) ?></td>
                                            <td class="text-end"><?= (int)($row['visits_total'] ?? 0) ?></td>
                                            <td class="text-end"><?= (int)($row['unique_total'] ?? 0) ?></td>
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
</div>

<?php
$content = ob_get_clean();
$title = 'Статистика - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>


