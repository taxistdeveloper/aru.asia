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
.earn-pubs {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.55rem;
    width: 96px;
}
.earn-pub {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    width: 100%;
    text-align: center;
}
.earn-pub-type {
    font-size: 0.9rem;
    font-weight: 650;
    color: #1f1f2e;
    line-height: 1.15;
}
.earn-pub-thumb {
    width: 88px;
    height: 56px;
    border-radius: 8px;
    overflow: hidden;
    background: #e5e7eb;
    color: #6b7280;
    font-size: 0.66rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #22c55e;
}
.earn-pub-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.earn-pub-state {
    font-size: 0.68rem;
    line-height: 1.2;
    color: #15803d;
    font-weight: 600;
}
.earn-pub.is-pending .earn-pub-thumb { border-color: #f59e0b; }
.earn-pub.is-pending .earn-pub-state { color: #b45309; }
.earn-pub.is-finished .earn-pub-thumb { border-color: #d1d5db; filter: grayscale(1); opacity: .65; }
.earn-pub.is-finished .earn-pub-state,
.earn-pub.is-finished .earn-pub-type { color: #9ca3af; }
.earn-none {
    color: #9ca3af;
    font-size: 0.8rem;
    text-align: center;
    width: 96px;
}
.earn-person.is-inactive { background: #fafafb; }
.earn-group-title {
    font-size: 0.85rem;
    font-weight: 650;
    color: #6b6b80;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin: 0.6rem 0 -0.2rem;
}
</style>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <?php if (empty($people) && empty($companiesWithoutAccount)): ?>
        <div class="earn-empty">Пока нет партнёров.</div>
    <?php else: ?>
        <div class="earn-people">
            <?php $shownInactiveTitle = false; $shownActiveTitle = false; ?>
            <?php foreach ($people as $person): ?>
                <?php
                $pubs = $person['publications'] ?? [];
                $isLive = !empty($pubs) && in_array($pubs[0]['state'], ['live', 'scheduled'], true);
                ?>
                <?php if ($isLive && !$shownActiveTitle): $shownActiveTitle = true; ?>
                    <div class="earn-group-title">Активные</div>
                <?php elseif (!$isLive && !$shownInactiveTitle): $shownInactiveTitle = true; ?>
                    <div class="earn-group-title">Неактивные</div>
                <?php endif; ?>
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
                $personBlocked = (int)($person['profile_blocked'] ?? 0) === 1;
                $stateLabels = ['live' => 'Идёт', 'scheduled' => 'Скоро старт', 'pending' => 'На модерации', 'finished' => 'Завершено'];
                $profileUrl = BASE_URL . 'profile/view?id=' . $personId . '&from=partners';
                ?>
                <article class="earn-person <?= $isLive ? '' : 'is-inactive' ?>">
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
                    <?php if (!empty($pubs)): ?>
                        <div class="earn-pubs">
                            <?php foreach ($pubs as $pub): ?>
                                <?php
                                $until = !empty($pub['until']) ? date('d.m.Y', strtotime($pub['until'])) : '';
                                $stateText = $stateLabels[$pub['state']] ?? '';
                                if ($until !== '' && in_array($pub['state'], ['live', 'scheduled'], true)) {
                                    $stateText .= ' до ' . $until;
                                } elseif ($until !== '' && $pub['state'] === 'finished') {
                                    $stateText .= ' ' . $until;
                                }
                                ?>
                                <div class="earn-pub is-<?= Helper::escape($pub['state']) ?>">
                                    <span class="earn-pub-type"><?= Helper::escape($pub['type']) ?></span>
                                    <span class="earn-pub-thumb">
                                        <?php if (!empty($pub['image'])): ?>
                                            <img src="<?= BASE_URL . Helper::escape($pub['image']) ?>" alt="<?= Helper::escape($pub['type']) ?>" loading="lazy">
                                        <?php else: ?>
                                            Нет фото
                                        <?php endif; ?>
                                    </span>
                                    <span class="earn-pub-state"><?= Helper::escape($stateText) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($personBlocked): ?><span class="earn-pub-state text-danger">Заблокирован</span><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="earn-none">Нет рекламы и мероприятий<?= $personBlocked ? '<br><span class="text-danger">Заблокирован</span>' : '' ?></span>
                    <?php endif; ?>
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
