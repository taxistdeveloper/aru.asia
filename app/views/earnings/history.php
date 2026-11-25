<?php
ob_start();
$tree = $tree ?? [];
$monthNames = [
    1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь',
    7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
];
$money = function ($v) {
    return Partner::formatMoney((float)$v, 'KZT');
};
// По умолчанию открыта папка последнего месяца с начислениями
$openYear = !empty($tree) ? (int)array_key_first($tree) : 0;
$openMonth = $openYear ? (int)max(array_keys($tree[$openYear])) : 0;
?>

<style>
.earn-folders { display: flex; flex-direction: column; gap: 0.75rem; }
.earn-folder {
    background: #fff;
    border: 1px solid #ececf3;
    border-radius: 16px;
    overflow: hidden;
}
.earn-folder > summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.95rem 1rem;
    font-weight: 650;
    font-size: 1.05rem;
    color: #1f1f2e;
}
.earn-folder > summary::-webkit-details-marker { display: none; }
.earn-folder > summary .bi-chevron-down { transition: transform .15s ease; color: #9ca3af; }
.earn-folder[open] > summary .bi-chevron-down { transform: rotate(180deg); }
.earn-folder-icon { color: #f59e0b; margin-right: 0.45rem; }
.earn-folder-sum { color: #15803d; font-weight: 650; font-size: 0.95rem; white-space: nowrap; }
.earn-months { padding: 0 0.75rem 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; }
.earn-month { border: 1px solid #ececf3; border-radius: 12px; background: #fafafb; }
.earn-month > summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.7rem 0.85rem;
    font-weight: 600;
    color: #1f1f2e;
}
.earn-month > summary::-webkit-details-marker { display: none; }
.earn-month-count { color: #6b6b80; font-weight: 500; font-size: 0.8rem; margin-left: 0.35rem; }
.earn-month-body { padding: 0 0.6rem 0.6rem; display: flex; flex-direction: column; gap: 0.5rem; }
.earn-row {
    display: grid;
    grid-template-columns: 56px minmax(0, 1fr) auto;
    gap: 0.7rem;
    align-items: center;
    background: #fff;
    border: 1px solid #ececf3;
    border-radius: 12px;
    padding: 0.55rem 0.7rem;
    color: inherit;
    text-decoration: none;
}
.earn-row-photo {
    width: 56px; height: 60px; border-radius: 8px; overflow: hidden;
    background: #d5d5d5; color: #222; font-size: 0.58rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center; text-align: center; line-height: 1.1;
}
.earn-row-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.earn-row-name { font-size: 0.92rem; line-height: 1.3; color: #1f1f2e; word-break: break-word; }
.earn-row-meta { font-size: 0.75rem; color: #6b6b80; }
.earn-row-sum { color: #15803d; font-weight: 700; white-space: nowrap; }
.earn-month-total {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.6rem 0.75rem; border-radius: 10px;
    background: #ecfdf5; color: #065f46; font-weight: 700;
}
.earn-month-empty { color: #c0c0cc; }
</style>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <?php if (empty($tree)): ?>
        <div class="earn-empty">Начислений пока нет.</div>
    <?php else: ?>
        <div class="earn-folders">
            <?php foreach ($tree as $y => $months): ?>
                <?php $yearTotal = array_sum(array_column($months, 'total')); ?>
                <details class="earn-folder" <?= (int)$y === $openYear ? 'open' : '' ?>>
                    <summary>
                        <span><i class="bi bi-folder-fill earn-folder-icon"></i><?= (int)$y ?> год</span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="earn-folder-sum">+<?= Helper::escape($money($yearTotal)) ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </span>
                    </summary>
                    <div class="earn-months">
                        <?php foreach ($months as $m => $data): ?>
                            <details class="earn-month" <?= ((int)$y === $openYear && (int)$m === $openMonth) ? 'open' : '' ?>>
                                <summary>
                                    <span>
                                        <i class="bi bi-folder2 earn-folder-icon"></i><?= $monthNames[(int)$m] ?>
                                        <span class="earn-month-count"><?= count($data['people']) ?> чел.</span>
                                    </span>
                                    <span class="earn-folder-sum">+<?= Helper::escape($money($data['total'])) ?></span>
                                </summary>
                                <div class="earn-month-body">
                                    <?php foreach ($data['people'] as $person): ?>
                                        <?php
                                        $label = $person['name'];
                                        if (!empty($person['age'])) {
                                            $label .= ', ' . (int)$person['age'] . ' лет';
                                        }
                                        $tag = !empty($person['id']) ? 'a' : 'div';
                                        $href = !empty($person['id']) ? ' href="' . BASE_URL . 'profile/view?id=' . (int)$person['id'] . '&from=partners"' : '';
                                        ?>
                                        <<?= $tag . $href ?> class="earn-row">
                                            <span class="earn-row-photo">
                                                <?php if (!empty($person['photo'])): ?>
                                                    <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . rawurlencode($person['photo']) ?>" alt="" loading="lazy">
                                                <?php else: ?>
                                                    Фото<br>пользователя
                                                <?php endif; ?>
                                            </span>
                                            <span>
                                                <span class="earn-row-name d-block"><?= Helper::escape($label) ?></span>
                                                <?php if (!empty($person['email']) && empty($person['photo'])): ?>
                                                    <span class="earn-row-meta d-block"><?= Helper::escape($person['email']) ?></span>
                                                <?php endif; ?>
                                                <span class="earn-row-meta d-block">Начислений: <?= (int)$person['count'] ?></span>
                                            </span>
                                            <span class="earn-row-sum">+<?= Helper::escape($money($person['sum'])) ?></span>
                                        </<?= $tag ?>>
                                    <?php endforeach; ?>
                                    <div class="earn-month-total">
                                        <span>Итого за <?= mb_strtolower($monthNames[(int)$m]) ?></span>
                                        <span>+<?= Helper::escape($money($data['total'])) ?></span>
                                    </div>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Начисления';
include __DIR__ . '/../layout.php';
?>
