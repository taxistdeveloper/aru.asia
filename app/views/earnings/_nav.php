<?php
/**
 * Шапка кабинета заработка.
 * Меню — сетка кнопок (как в первом варианте).
 */
$earnPath = $_SERVER['REQUEST_URI'] ?? '';
$earnAction = $earnAction ?? null;
$items = [
    ['url' => 'profile/earnings', 'label' => 'Обзор', 'icon' => 'bi-grid', 'exact' => true],
    ['url' => 'profile/earnings/payouts', 'label' => 'Выплаты', 'icon' => 'bi-cash-stack', 'also' => ['profile/earnings/withdraw']],
    ['url' => 'profile/earnings/companies', 'label' => 'Партнёры', 'icon' => 'bi-people', 'also' => ['profile/earnings/invite']],
    ['url' => 'profile/earnings/instructions', 'label' => 'Инструкция', 'icon' => 'bi-book'],
    ['url' => 'profile/earnings/history', 'label' => 'Начисления', 'icon' => 'bi-clock-history'],
];
?>
<style>
.earn-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 0.75rem 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.earn-title {
    font-size: 1.55rem;
    font-weight: 650;
    letter-spacing: -0.02em;
    margin: 0;
}
.earn-menu {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.55rem;
    margin: 0 0 1.25rem;
}
.earn-menu a {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    min-height: 2.75rem;
    padding: 0.7rem 0.85rem;
    border-radius: 10px;
    background: #e9e9ef;
    color: #1f1f2e;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 550;
    text-align: center;
    border: 1px solid transparent;
    transition: background 0.15s ease, color 0.15s ease;
}
.earn-menu a:hover {
    background: #dddee6;
    color: #111;
}
.earn-menu a.active {
    background: #4b5563;
    color: #fff;
}
.earn-menu a.active:hover {
    background: #374151;
    color: #fff;
}
.earn-panel {
    background: #fff;
    border: 1px solid #ececf3;
    border-radius: 16px;
    padding: 1.15rem 1.25rem;
    margin-bottom: 1rem;
}
.earn-panel-title {
    font-size: 1.05rem;
    font-weight: 650;
    margin: 0 0 0.35rem;
}
.earn-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    background: #fff;
    border: 1px solid #ececf3;
    border-radius: 16px;
    overflow: hidden;
}
.earn-stat {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.95rem 1.05rem;
    text-decoration: none;
    color: inherit;
    border-right: 1px solid #ececf3;
    min-width: 0;
}
.earn-stat:last-child { border-right: 0; }
.earn-stat:hover { background: #fafafe; }
.earn-stat span {
    color: #6b6b80;
    font-size: 0.78rem;
}
.earn-stat strong {
    font-size: 1.2rem;
    font-weight: 650;
    letter-spacing: -0.02em;
    line-height: 1.2;
}
.earn-stat-main { background: #f5f4ff; }
.earn-stat-main strong { color: #4338ca; }
.earn-stat-main:hover { background: #eeedff; }
.earn-note {
    margin: 0.65rem 0 0;
    color: #6b6b80;
    font-size: 0.85rem;
}
.earn-empty {
    padding: 2rem 1rem;
    text-align: center;
    color: #6b6b80;
    background: #fff;
    border: 1px solid #ececf3;
    border-radius: 16px;
}
.earn-table {
    border: 1px solid #ececf3;
    border-radius: 16px;
    overflow: hidden;
}
.earn-table .table { margin-bottom: 0; }
@media (min-width: 768px) {
    .earn-menu {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
}
@media (max-width: 767px) {
    .earn-stats { grid-template-columns: 1fr 1fr; }
    .earn-stat { border-bottom: 1px solid #ececf3; }
    .earn-stat:nth-child(2n) { border-right: 0; }
    .earn-stat:nth-last-child(-n+2) { border-bottom: 0; }
    .earn-title { font-size: 1.35rem; }
    .earn-panel .input-group { flex-wrap: wrap; }
    .earn-panel .input-group > .form-control,
    .earn-panel .input-group > .btn {
        width: 100%;
        border-radius: 0.5rem !important;
    }
    .earn-panel .input-group > .btn { margin-top: 0.5rem; }
}
</style>

<div class="earn-head">
    <div>
        <h1 class="earn-title">Личный кабинет</h1>
    </div>
    <?php if (!empty($earnAction['href'])): ?>
        <a href="<?= $earnAction['href'] ?>" class="btn btn-sm <?= Helper::escape($earnAction['class'] ?? 'btn-primary') ?>">
            <?php if (!empty($earnAction['icon'])): ?>
                <i class="bi <?= Helper::escape($earnAction['icon']) ?>"></i>
            <?php endif; ?>
            <?= Helper::escape($earnAction['label'] ?? '') ?>
        </a>
    <?php endif; ?>
</div>

<nav class="earn-menu" aria-label="Разделы кабинета">
    <?php foreach ($items as $item): ?>
        <?php
        $active = false;
        $checks = array_merge([$item['url']], $item['also'] ?? []);
        foreach ($checks as $index => $url) {
            $pathCheck = '/' . $url;
            $isExact = !empty($item['exact']) && $index === 0;
            if ($isExact) {
                if (preg_match('#/profile/earnings/?(\?|$)#', $earnPath) === 1) {
                    $active = true;
                    break;
                }
            } elseif (strpos($earnPath, $pathCheck) !== false) {
                $active = true;
                break;
            }
        }
        ?>
        <a href="<?= BASE_URL . $item['url'] ?>"<?= $active ? ' class="active" aria-current="page"' : '' ?>>
            <i class="bi <?= $item['icon'] ?>"></i> <?= Helper::escape($item['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>

<?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Helper::escape($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Helper::escape($_SESSION['error_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
