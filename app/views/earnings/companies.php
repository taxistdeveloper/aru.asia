<?php
ob_start();
$people = $people ?? [];
$companiesWithoutAccount = $companiesWithoutAccount ?? [];
$isPartner = !empty($isPartner);
$partnerLink = $partnerLink ?? null;
$shareText = 'Присоединяйтесь к Aru — размещайте рекламу и мероприятия. Моя ссылка:';
$earnAction = [
    'label' => 'Пригласить',
    'icon' => 'bi-share',
    'class' => 'btn-primary',
    'attrs' => $partnerLink
        ? 'data-bs-toggle="modal" data-bs-target="#partnerShareModal"'
        : 'disabled title="Сначала получите партнёрскую ссылку на обзоре"',
];
?>

<style>
.earn-people {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.earn-person {
    display: grid;
    grid-template-columns: 92px minmax(0, 1fr) auto;
    gap: 0.85rem;
    align-items: center;
    background: #fff;
    border: 1px solid #ececf3;
    border-radius: 16px;
    padding: 0.9rem 1rem;
}
.earn-person-photo {
    width: 92px;
    height: 96px;
    border-radius: 8px;
    overflow: hidden;
    background: #d5d5d5;
    color: #222;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    text-decoration: none;
    font-size: 0.68rem;
    line-height: 1.15;
    font-weight: 600;
    padding: 0.2rem;
}
.earn-person-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.earn-person-name {
    color: #1f1f2e;
    text-decoration: none;
    font-size: 0.98rem;
    line-height: 1.35;
}
.earn-person-name:hover {
    color: #4338ca;
}
.earn-person-status {
    color: #1f1f2e;
    font-size: 0.95rem;
    white-space: nowrap;
}
</style>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <?php if (empty($people) && empty($companiesWithoutAccount)): ?>
        <div class="earn-empty">Пока нет партнёров.</div>
    <?php else: ?>
        <div class="earn-people">
            <?php foreach ($people as $person): ?>
                <?php
                $personId = (int)($person['id'] ?? 0);
                $personName = trim((string)($person['full_name'] ?? ''));
                if ($personName === '') {
                    $personName = 'Без имени';
                }
                $personAge = (int)($person['age'] ?? 0);
                $personLabel = 'Имя пользователя: ' . $personName;
                if ($personAge > 0) {
                    $personLabel .= ' ' . $personAge . ' лет';
                }
                $personActive = (int)($person['profile_blocked'] ?? 0) !== 1;
                $personStatus = $personActive ? 'Активен' : 'Заблокирован';
                $profileUrl = BASE_URL . 'profile/view?id=' . $personId . '&from=partners';
                $photoFile = trim((string)($person['main_photo'] ?? ''));
                ?>
                <article class="earn-person">
                    <a class="earn-person-photo" href="<?= $profileUrl ?>" aria-label="Открыть профиль">
                        <?php if ($photoFile !== ''): ?>
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . rawurlencode($photoFile) ?>" alt="">
                        <?php else: ?>
                            <span>Фото<br>пользователя</span>
                        <?php endif; ?>
                    </a>
                    <a class="earn-person-name" href="<?= $profileUrl ?>"><?= Helper::escape($personLabel) ?></a>
                    <span class="earn-person-status"><?= Helper::escape($personStatus) ?></span>
                </article>
            <?php endforeach; ?>
            <?php foreach ($companiesWithoutAccount as $company): ?>
                <article class="earn-person">
                    <div class="earn-person-photo" aria-hidden="true"><span>Нет<br>аккаунта</span></div>
                    <div class="earn-person-name"><?= Helper::escape($company['company_name'] ?? '') ?></div>
                    <span class="earn-person-status"><?= Helper::escape(Partner::statusLabel((string)($company['status'] ?? ''))) ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_share_modal.php'; ?>

<?php
$content = ob_get_clean();
$title = 'Партнёры';
include __DIR__ . '/../layout.php';
?>
