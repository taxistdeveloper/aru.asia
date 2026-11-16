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
.earn-person-body {
    min-width: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.28rem;
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
.earn-person-flag {
    display: inline-block;
    background: #f59e0b;
    color: #1f1403;
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1.2;
    border-radius: 999px;
    padding: 0.22rem 0.55rem;
}
.earn-person-email {
    color: #374151;
    font-size: 0.86rem;
    line-height: 1.3;
    word-break: break-all;
}
.earn-person-write {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    margin-top: 0.1rem;
    background: #4f46e5;
    color: #fff;
    text-decoration: none;
    font-size: 0.82rem;
    font-weight: 650;
    border-radius: 8px;
    padding: 0.38rem 0.7rem;
}
.earn-person-write:hover {
    color: #fff;
    background: #4338ca;
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
                $personAge = (int)($person['age'] ?? 0);
                $personEmail = trim((string)($person['email'] ?? ''));
                $photoFile = trim((string)($person['main_photo'] ?? ''));
                $profileIncomplete = $personName === '' || $photoFile === '';
                $personLabel = '';
                if ($personName !== '') {
                    $personLabel = 'Имя пользователя: ' . $personName;
                    if ($personAge > 0) {
                        $personLabel .= ' ' . $personAge . ' лет';
                    }
                }
                $personActive = (int)($person['profile_blocked'] ?? 0) !== 1;
                $personStatus = $personActive ? 'Активен' : 'Заблокирован';
                $profileUrl = BASE_URL . 'profile/view?id=' . $personId . '&from=partners';
                ?>
                <article class="earn-person">
                    <a class="earn-person-photo" href="<?= $profileUrl ?>" aria-label="Открыть профиль">
                        <?php if ($photoFile !== ''): ?>
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . rawurlencode($photoFile) ?>" alt="">
                        <?php else: ?>
                            <span>Фото<br>пользователя</span>
                        <?php endif; ?>
                    </a>
                    <div class="earn-person-body">
                        <?php if ($profileIncomplete): ?>
                            <span class="earn-person-flag">Профиль не заполнен</span>
                            <?php if ($personEmail !== ''): ?>
                                <span class="earn-person-email"><?= Helper::escape($personEmail) ?></span>
                            <?php endif; ?>
                            <?php
                            $draftText = 'Здравствуйте! Заполните, пожалуйста, профиль: укажите имя и добавьте фото.';
                            $messageUrl = BASE_URL . 'messages?user_id=' . $personId . '&draft=' . rawurlencode($draftText);
                            ?>
                            <a class="earn-person-write" href="<?= Helper::escape($messageUrl) ?>">
                                <i class="bi bi-envelope"></i> Написать сообщение
                            </a>
                        <?php endif; ?>
                        <?php if ($personLabel !== ''): ?>
                            <a class="earn-person-name" href="<?= $profileUrl ?>"><?= Helper::escape($personLabel) ?></a>
                        <?php endif; ?>
                    </div>
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
