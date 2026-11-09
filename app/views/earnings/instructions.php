<?php
ob_start();
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <section class="earn-panel mb-0">
        <ol class="mb-0" style="line-height: 1.7;">
            <li class="mb-2">На обзоре получите партнёрскую ссылку.</li>
            <li class="mb-2">Отправьте её предпринимателю.</li>
            <li>
                Когда он оплачивает рекламу, вам начисляется <strong>50%</strong> от суммы.
                Компания остаётся вашей: с её следующих оплат доля приходит снова,
                передать её другому партнёру нельзя.
            </li>
        </ol>
    </section>
</div>

<?php
$content = ob_get_clean();
$title = 'Инструкция по заработку';
include __DIR__ . '/../layout.php';
?>
