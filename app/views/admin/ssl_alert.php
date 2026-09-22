<?php

/**
 * БОЛЬШОЕ УВЕДОМЛЕНИЕ ОБ ИСТЕЧЕНИИ SSL-СЕРТИФИКАТА
 *
 * Подключается из admin_layout.php. Ожидает переменную $sslStatus
 * из SslCertificateChecker::status().
 */

if (!isset($sslStatus) || !is_array($sslStatus) || !SslCertificateChecker::needsAttention($sslStatus)) {
    return;
}

$sslState = $sslStatus['state'];
$sslDaysLeft = $sslStatus['days_left'];
$sslValidTo = $sslStatus['valid_to'] ? date('d.m.Y H:i', (int) $sslStatus['valid_to']) : '—';
$sslIsExpired = $sslState === 'expired';
$sslIsCritical = $sslState === 'critical';

// Критичные и просроченные уведомления закрыть нельзя
$sslDismissible = !$sslIsExpired && !$sslIsCritical;
$sslDismissKey = 'ssl-alert-' . (int) ($sslStatus['valid_to'] ?? 0) . '-' . date('Y-m-d');

if ($sslIsExpired) {
    $sslHeadline = 'Срок действия SSL-сертификата истёк!';
    $sslLead = 'Сайт открывается с ошибкой безопасности. Обновите сертификат немедленно.';
} elseif ((int) $sslDaysLeft === 0) {
    $sslHeadline = 'Истекает SSL-сертификат — обновите';
    $sslLead = 'Сертификат истекает уже сегодня. После этого браузеры начнут блокировать сайт.';
} else {
    $days = (int) $sslDaysLeft;
    $lastTwo = $days % 100;
    $last = $days % 10;
    if ($lastTwo > 4 && $lastTwo < 21) {
        $dayWord = 'дней';
    } elseif ($last === 1) {
        $dayWord = 'день';
    } elseif ($last >= 2 && $last <= 4) {
        $dayWord = 'дня';
    } else {
        $dayWord = 'дней';
    }

    $sslHeadline = 'Истекает SSL-сертификат — обновите';
    $sslLead = 'Через ' . $days . ' ' . $dayWord . ' сертификат перестанет действовать, и браузеры начнут блокировать сайт.';
}
?>

<div class="ssl-alert <?= $sslIsExpired || $sslIsCritical ? 'ssl-alert-danger' : 'ssl-alert-warning' ?>"
     data-ssl-dismiss-key="<?= Helper::escape($sslDismissKey) ?>"
     <?= $sslDismissible ? '' : 'data-ssl-locked="1"' ?>>
    <div class="ssl-alert-icon">
        <i class="bi bi-shield-exclamation"></i>
    </div>
    <div class="ssl-alert-body">
        <div class="ssl-alert-title"><?= Helper::escape($sslHeadline) ?></div>
        <div class="ssl-alert-lead"><?= Helper::escape($sslLead) ?></div>
        <div class="ssl-alert-meta">
            <span><i class="bi bi-globe2"></i> <?= Helper::escape($sslStatus['host']) ?></span>
            <span><i class="bi bi-calendar-x"></i> Действует до <?= Helper::escape($sslValidTo) ?></span>
            <?php if (!$sslIsExpired): ?>
                <span><i class="bi bi-hourglass-split"></i> Осталось <?= (int) $sslDaysLeft ?> дн.</span>
            <?php endif; ?>
            <?php if (!empty($sslStatus['issuer'])): ?>
                <span><i class="bi bi-patch-check"></i> <?= Helper::escape($sslStatus['issuer']) ?></span>
            <?php endif; ?>
        </div>
        <div class="ssl-alert-hint">
            Продлить: <code>sudo certbot renew --force-renewal</code> на сервере, затем перезагрузить веб-сервер.
        </div>
    </div>
    <?php if ($sslDismissible): ?>
        <button type="button" class="ssl-alert-close" aria-label="Скрыть до завтра" title="Скрыть до завтра">
            <i class="bi bi-x-lg"></i>
        </button>
    <?php endif; ?>
</div>

<style>
    .ssl-alert {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        position: relative;
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        color: #fff;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    .ssl-alert-danger {
        background: linear-gradient(135deg, #b02a37, #dc3545);
    }

    .ssl-alert-warning {
        background: linear-gradient(135deg, #b8860b, #ffc107);
        color: #212529;
    }

    .ssl-alert-icon {
        font-size: 3rem;
        line-height: 1;
        opacity: 0.95;
    }

    .ssl-alert-body {
        flex: 1 1 auto;
        min-width: 0;
    }

    .ssl-alert-title {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .ssl-alert-lead {
        font-size: 1rem;
        margin-top: 0.25rem;
    }

    .ssl-alert-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 1.25rem;
        margin-top: 0.6rem;
        font-size: 0.85rem;
        opacity: 0.95;
    }

    .ssl-alert-hint {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        opacity: 0.9;
    }

    .ssl-alert-hint code {
        color: inherit;
        background: rgba(0, 0, 0, 0.18);
        padding: 0.1rem 0.35rem;
        border-radius: 0.25rem;
    }

    .ssl-alert-close {
        position: absolute;
        top: 0.6rem;
        right: 0.75rem;
        border: none;
        background: transparent;
        color: inherit;
        opacity: 0.7;
        font-size: 0.9rem;
        line-height: 1;
        padding: 0.25rem;
    }

    .ssl-alert-close:hover {
        opacity: 1;
    }

    .ssl-alert-danger {
        animation: ssl-alert-pulse 2.5s ease-in-out infinite;
    }

    @keyframes ssl-alert-pulse {
        0%, 100% {
            box-shadow: 0 6px 18px rgba(220, 53, 69, 0.35);
        }

        50% {
            box-shadow: 0 6px 26px rgba(220, 53, 69, 0.65);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ssl-alert-danger {
            animation: none;
        }
    }

    @media (max-width: 767.98px) {
        .ssl-alert {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 1rem;
        }

        .ssl-alert-icon {
            font-size: 2.2rem;
        }

        .ssl-alert-title {
            font-size: 1.15rem;
        }

        .ssl-alert-lead {
            font-size: 0.9rem;
        }
    }
</style>

<script>
    (function () {
        document.querySelectorAll('.ssl-alert[data-ssl-dismiss-key]').forEach(function (alertEl) {
            var key = alertEl.dataset.sslDismissKey;
            var locked = alertEl.dataset.sslLocked === '1';

            if (!locked && key) {
                try {
                    if (localStorage.getItem(key) === '1') {
                        alertEl.remove();
                        return;
                    }
                } catch (e) {
                    // localStorage может быть недоступен — просто показываем уведомление
                }
            }

            var closeBtn = alertEl.querySelector('.ssl-alert-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    try {
                        localStorage.setItem(key, '1');
                    } catch (e) {
                        // игнорируем, уведомление вернется при перезагрузке
                    }
                    alertEl.remove();
                });
            }
        });
    })();
</script>
