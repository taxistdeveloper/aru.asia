<?php
/**
 * АДМИН-ПАНЕЛЬ - ЛОГИ ДЕЙСТВИЙ ПОЛЬЗОВАТЕЛЕЙ
 */

ob_start();
?>

<style>
@media (max-width: 767.98px) {
    .activity-logs-table-wrapper {
        display: none;
    }
}
@media (min-width: 768px) {
    .activity-logs-mobile-list {
        display: none;
    }
}
</style>

<?php
$buildUrl = function ($page) use ($query, $method, $userId) {
    $params = array_filter([
        'q' => $query ?: null,
        'method' => $method ?: null,
        'user_id' => $userId ?: null,
        'page' => $page
    ], function ($value) {
        return $value !== null && $value !== '';
    });
    $queryString = http_build_query($params);
    return BASE_URL . 'admin/activity-logs' . ($queryString ? ('?' . $queryString) : '');
};
?>

<div class="mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h2 class="mb-1">Логи действий пользователей</h2>
            <small class="text-muted">Всего записей: <?= (int)$total ?></small>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row gy-2 gx-2 align-items-end" method="GET" action="<?= BASE_URL ?>admin/activity-logs">
                <div class="col-12 col-md-5">
                    <label class="form-label mb-1">Поиск (email / маршрут / действие)</label>
                    <input type="text" name="q" value="<?= Helper::escape($query ?? '') ?>" class="form-control form-control-sm" placeholder="например: profile, user@mail.com">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1">Метод</label>
                    <select name="method" class="form-select form-select-sm">
                        <option value="">Все</option>
                        <option value="GET" <?= ($method ?? '') === 'GET' ? 'selected' : '' ?>>GET</option>
                        <option value="POST" <?= ($method ?? '') === 'POST' ? 'selected' : '' ?>>POST</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1">User ID</label>
                    <input type="number" name="user_id" value="<?= (int)($userId ?? 0) ?: '' ?>" class="form-control form-control-sm" min="1" placeholder="ID">
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i> Найти
                    </button>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>admin/activity-logs">
                        Сброс
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($logs ?? [])): ?>
        <div class="alert alert-info">Записей пока нет.</div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <!-- Десктоп: таблица -->
                <div class="table-responsive activity-logs-table-wrapper">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Пользователь</th>
                                <th>Действие</th>
                                <th>Маршрут</th>
                                <th>Метод</th>
                                <th>IP</th>
                                <th>Agent</th>
                                <th>Параметры</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                $route = $log['route'] ?? '';
                                $queryString = $log['query_string'] ?? '';
                                $fullRoute = $route . (!empty($queryString) ? ('?' . $queryString) : '');
                                ?>
                                <tr>
                                    <td><small class="text-muted"><?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></small></td>
                                    <td>
                                        <div><strong><?= Helper::escape($log['user_email'] ?? '—') ?></strong></div>
                                        <small class="text-muted">ID: <?= (int)$log['user_id'] ?></small>
                                    </td>
                                    <td><?= Helper::escape($log['action'] ?? '-') ?></td>
                                    <td><code><?= Helper::escape($fullRoute) ?></code></td>
                                    <td><span class="badge bg-secondary"><?= Helper::escape($log['method'] ?? '-') ?></span></td>
                                    <td><small><?= Helper::escape($log['ip_address'] ?? '-') ?></small></td>
                                    <td><small class="text-muted"><?= Helper::escape($log['user_agent'] ?? '-') ?></small></td>
                                    <td><small class="text-muted"><?= Helper::escape($log['params_json'] ?? '-') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Мобилка: список карточек -->
                <div class="activity-logs-mobile-list">
                    <div class="list-group">
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $route = $log['route'] ?? '';
                            $queryString = $log['query_string'] ?? '';
                            $fullRoute = $route . (!empty($queryString) ? ('?' . $queryString) : '');
                            ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?= Helper::escape($log['user_email'] ?? '—') ?></strong>
                                    <small class="text-muted"><?= date('d.m H:i', strtotime($log['created_at'])) ?></small>
                                </div>
                                <div class="small text-muted">ID: <?= (int)$log['user_id'] ?> • <?= Helper::escape($log['method'] ?? '-') ?> • <?= Helper::escape($log['action'] ?? '-') ?></div>
                                <div class="mt-1"><code><?= Helper::escape($fullRoute) ?></code></div>
                                <?php if (!empty($log['params_json'])): ?>
                                    <div class="mt-1 small text-muted"><?= Helper::escape($log['params_json']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $buildUrl(max(1, $page - 1)) ?>">Назад</a>
                            </li>
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $buildUrl($p) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $buildUrl(min($totalPages, $page + 1)) ?>">Вперед</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Логи действий пользователей';
include __DIR__ . '/../admin_layout.php';
?>
