<?php
/**
 * АДМИН-ПАНЕЛЬ — ЖУРНАЛ ДЕЙСТВИЙ И ОШИБОК
 */

ob_start();

$level = $level ?? '';
$action = $action ?? '';
$q = $q ?? '';
$userId = (int)($userId ?? 0);
$page = (int)($page ?? 1);
$total = (int)($total ?? 0);
$pages = (int)($pages ?? 1);
$logs = $logs ?? [];
$counts = $counts ?? ['info' => 0, 'warning' => 0, 'error' => 0];
$prefixes = $prefixes ?? [];

$buildUrl = function ($overrides = []) use ($level, $action, $q, $userId, $page) {
    $params = array_filter([
        'level' => array_key_exists('level', $overrides) ? $overrides['level'] : ($level ?: null),
        'action' => array_key_exists('action', $overrides) ? $overrides['action'] : ($action ?: null),
        'q' => array_key_exists('q', $overrides) ? $overrides['q'] : ($q ?: null),
        'user_id' => array_key_exists('user_id', $overrides) ? $overrides['user_id'] : ($userId ?: null),
        'page' => array_key_exists('page', $overrides) ? $overrides['page'] : $page,
    ], function ($value) {
        return $value !== null && $value !== '' && $value !== 0 && $value !== '0';
    });
    $queryString = http_build_query($params);
    return BASE_URL . 'admin/logs' . ($queryString ? ('?' . $queryString) : '');
};

$levelBadge = function ($logLevel) {
    $map = [
        'info' => 'bg-info text-dark',
        'warning' => 'bg-warning text-dark',
        'error' => 'bg-danger',
    ];
    $class = $map[$logLevel] ?? 'bg-secondary';
    $label = Lang::t('admin.logs_level_' . $logLevel, $logLevel);
    return '<span class="badge ' . $class . '">' . Helper::escape($label) . '</span>';
};

$allCount = ($counts['info'] ?? 0) + ($counts['warning'] ?? 0) + ($counts['error'] ?? 0);
?>

<style>
@media (max-width: 767.98px) {
    .admin-logs-table-wrapper { display: none; }
}
@media (min-width: 768px) {
    .admin-logs-mobile-list { display: none; }
}
.admin-logs-filter-card {
    text-decoration: none;
    color: inherit;
    display: block;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 0.85rem 1rem;
    background: #fff;
    transition: box-shadow 0.15s, border-color 0.15s;
}
.admin-logs-filter-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    color: inherit;
}
.admin-logs-filter-card.active {
    border-color: #1e3c72;
    box-shadow: 0 0 0 1px #1e3c72;
}
.admin-logs-filter-card .value {
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.2;
}
.admin-logs-code {
    font-size: 0.75rem;
    color: #6c757d;
}
.admin-logs-context pre {
    font-size: 0.75rem;
    margin: 0.35rem 0 0;
    white-space: pre-wrap;
    word-break: break-word;
}
</style>

<div class="mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-journal-text"></i> <?= Helper::escape(Lang::t('admin.logs')) ?></h2>
            <p class="text-muted mb-0"><?= Helper::escape(Lang::t('admin.logs_hint')) ?></p>
        </div>
        <div class="mt-2 mt-md-0 text-md-end">
            <small class="text-muted"><?= Helper::escape(Lang::t('admin.logs_total')) ?>: <strong><?= $total ?></strong></small>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-4">
            <a class="admin-logs-filter-card <?= $level === '' ? 'active' : '' ?>" href="<?= $buildUrl(['level' => '', 'page' => 1]) ?>">
                <div class="text-muted small"><?= Helper::escape(Lang::t('admin.logs_all')) ?></div>
                <div class="value"><?= (int)$allCount ?></div>
            </a>
        </div>
        <div class="col-4">
            <a class="admin-logs-filter-card <?= $level === 'info' ? 'active' : '' ?>" href="<?= $buildUrl(['level' => 'info', 'page' => 1]) ?>">
                <div class="text-muted small"><?= Helper::escape(Lang::t('admin.logs_events')) ?></div>
                <div class="value" style="color: #0dcaf0;"><?= (int)($counts['info'] ?? 0) ?></div>
            </a>
        </div>
        <div class="col-4">
            <a class="admin-logs-filter-card <?= $level === 'error' ? 'active' : '' ?>" href="<?= $buildUrl(['level' => 'error', 'page' => 1]) ?>">
                <div class="text-muted small"><?= Helper::escape(Lang::t('admin.logs_errors')) ?></div>
                <div class="value text-danger"><?= (int)($counts['error'] ?? 0) ?></div>
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row gy-2 gx-2 align-items-end" method="GET" action="<?= BASE_URL ?>admin/logs">
                <?php if ($level !== ''): ?>
                    <input type="hidden" name="level" value="<?= Helper::escape($level) ?>">
                <?php endif; ?>
                <?php if ($userId > 0): ?>
                    <input type="hidden" name="user_id" value="<?= $userId ?>">
                <?php endif; ?>
                <div class="col-12 col-md-4">
                    <label class="form-label mb-1"><?= Helper::escape(Lang::t('admin.logs_action')) ?></label>
                    <select name="action" class="form-select form-select-sm">
                        <option value=""><?= Helper::escape(Lang::t('admin.logs_all_actions')) ?></option>
                        <?php foreach ($prefixes as $prefix): ?>
                            <option value="<?= Helper::escape($prefix) ?>" <?= $action === $prefix ? 'selected' : '' ?>>
                                <?= Helper::escape(Lang::t('admin.log_cat_' . $prefix, $prefix)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label mb-1"><?= Helper::escape(Lang::t('admin.logs_search')) ?></label>
                    <input type="text" name="q" value="<?= Helper::escape($q) ?>" class="form-control form-control-sm"
                           placeholder="<?= Helper::escape(Lang::t('admin.logs_search_placeholder')) ?>">
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i> <?= Helper::escape(Lang::t('admin.logs_find')) ?>
                    </button>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>admin/logs">
                        <?= Helper::escape(Lang::t('admin.logs_reset')) ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($userId > 0): ?>
        <div class="mb-3">
            <span class="badge bg-primary">
                <?= Helper::escape(Lang::t('admin.logs_user_filter')) ?><?= $userId ?>
            </span>
            <a class="btn btn-sm btn-link" href="<?= $buildUrl(['user_id' => '', 'page' => 1]) ?>">
                <?= Helper::escape(Lang::t('admin.logs_clear_user')) ?>
            </a>
        </div>
    <?php endif; ?>

    <?php if (empty($logs)): ?>
        <div class="alert alert-info mb-0"><?= Helper::escape(Lang::t('admin.logs_empty')) ?></div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive admin-logs-table-wrapper">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th><?= Helper::escape(Lang::t('admin.logs_when')) ?></th>
                                <th><?= Helper::escape(Lang::t('admin.logs_level')) ?></th>
                                <th><?= Helper::escape(Lang::t('admin.logs_who')) ?></th>
                                <th><?= Helper::escape(Lang::t('admin.logs_action')) ?></th>
                                <th><?= Helper::escape(Lang::t('admin.logs_message')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                $whoName = $log['user_name'] ?? '';
                                $whoId = (int)($log['user_id'] ?? 0);
                                $entityType = $log['entity_type'] ?? '';
                                $entityId = $log['entity_id'] ?? null;
                                $contextPretty = '';
                                if (!empty($log['context_json'])) {
                                    $decoded = json_decode($log['context_json'], true);
                                    $contextPretty = json_encode($decoded ?? $log['context_json'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div><?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></div>
                                        <?php if (!empty($log['ip'])): ?>
                                            <small class="text-muted"><?= Helper::escape($log['ip']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $levelBadge($log['level'] ?? 'info') ?></td>
                                    <td>
                                        <?php if ($whoId > 0): ?>
                                            <a href="<?= BASE_URL ?>profile/view?id=<?= $whoId ?>" target="_blank">
                                                <?= Helper::escape($whoName !== '' ? $whoName : ('#' . $whoId)) ?>
                                            </a>
                                            <div class="small text-muted">ID: <?= $whoId ?></div>
                                        <?php elseif ($whoName !== ''): ?>
                                            <?= Helper::escape($whoName) ?>
                                        <?php else: ?>
                                            <span class="text-muted"><?= Helper::escape(Lang::t('admin.logs_guest')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?= Helper::escape(ActivityLogger::actionLabel($log['action'] ?? '')) ?></div>
                                        <code class="admin-logs-code"><?= Helper::escape($log['action'] ?? '') ?></code>
                                        <?php if ($entityType !== ''): ?>
                                            <div class="small text-muted">
                                                <?= Helper::escape($entityType) ?><?= $entityId ? ' #' . (int)$entityId : '' ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= Helper::escape($log['message'] ?? '') ?>
                                        <?php if ($contextPretty !== ''): ?>
                                            <details class="admin-logs-context mt-1">
                                                <summary class="small text-muted"><?= Helper::escape(Lang::t('admin.logs_details')) ?></summary>
                                                <pre><?= Helper::escape($contextPretty) ?></pre>
                                            </details>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-logs-mobile-list">
                    <div class="list-group">
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $whoName = $log['user_name'] ?? '';
                            $whoId = (int)($log['user_id'] ?? 0);
                            $entityType = $log['entity_type'] ?? '';
                            $entityId = $log['entity_id'] ?? null;
                            $contextPretty = '';
                            if (!empty($log['context_json'])) {
                                $decoded = json_decode($log['context_json'], true);
                                $contextPretty = json_encode($decoded ?? $log['context_json'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            }
                            ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <?= $levelBadge($log['level'] ?? 'info') ?>
                                        <div class="mt-1">
                                            <?php if ($whoId > 0): ?>
                                                <a href="<?= BASE_URL ?>profile/view?id=<?= $whoId ?>" target="_blank">
                                                    <?= Helper::escape($whoName !== '' ? $whoName : ('#' . $whoId)) ?>
                                                </a>
                                            <?php elseif ($whoName !== ''): ?>
                                                <?= Helper::escape($whoName) ?>
                                            <?php else: ?>
                                                <span class="text-muted"><?= Helper::escape(Lang::t('admin.logs_guest')) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted text-nowrap"><?= date('d.m H:i', strtotime($log['created_at'])) ?></small>
                                </div>
                                <?php if (!empty($log['ip'])): ?>
                                    <div class="small text-muted"><?= Helper::escape($log['ip']) ?></div>
                                <?php endif; ?>
                                <div class="mt-1">
                                    <?= Helper::escape(ActivityLogger::actionLabel($log['action'] ?? '')) ?>
                                    <code class="admin-logs-code"><?= Helper::escape($log['action'] ?? '') ?></code>
                                    <?php if ($entityType !== ''): ?>
                                        <span class="small text-muted"><?= Helper::escape($entityType) ?><?= $entityId ? ' #' . (int)$entityId : '' ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-1"><?= Helper::escape($log['message'] ?? '') ?></div>
                                <?php if ($contextPretty !== ''): ?>
                                    <details class="admin-logs-context mt-1">
                                        <summary class="small text-muted"><?= Helper::escape(Lang::t('admin.logs_details')) ?></summary>
                                        <pre><?= Helper::escape($contextPretty) ?></pre>
                                    </details>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($pages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $buildUrl(['page' => max(1, $page - 1)]) ?>">
                                    <?= Helper::escape(Lang::t('admin.logs_prev')) ?>
                                </a>
                            </li>
                            <?php
                            $from = max(1, $page - 3);
                            $to = min($pages, $page + 3);
                            for ($p = $from; $p <= $to; $p++):
                            ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $buildUrl(['page' => $p]) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $buildUrl(['page' => min($pages, $page + 1)]) ?>">
                                    <?= Helper::escape(Lang::t('admin.logs_next')) ?>
                                </a>
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
$title = Lang::t('admin.logs') . ' - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>
