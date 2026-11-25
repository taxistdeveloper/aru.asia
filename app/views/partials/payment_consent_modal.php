<?php
/**
 * Окно согласия на оплату (баннерная реклама / мероприятия).
 *
 * Ожидает переменные:
 *   $paymentFormSelector  — CSS-селектор формы, перед отправкой которой показывается окно
 *   $paymentTitle         — что оплачивается («Баннерная реклама», «Мероприятие»)
 *   $paymentAmountKzt     — сумма (int) или null, если считается на клиенте через window.aruPaymentAmount()
 *   $paymentRefundText    — текст о невозврате
 */
$paymentFormSelector = $paymentFormSelector ?? 'form';
$paymentTitle = $paymentTitle ?? 'Оплата';
$paymentAmountKzt = $paymentAmountKzt ?? null;
$paymentRefundText = $paymentRefundText ?? 'После оплаты и выхода рекламы средства не возвращаются.';
?>
<style>
    .pay-consent-overlay {
        position: fixed; inset: 0; z-index: 10600;
        background: rgba(15, 23, 42, .55);
        display: none; align-items: center; justify-content: center; padding: 16px;
    }
    .pay-consent-overlay.is-open { display: flex; }
    .pay-consent-box {
        background: #fff; border-radius: 16px; max-width: 460px; width: 100%;
        box-shadow: 0 20px 50px rgba(0,0,0,.25); overflow: hidden;
    }
    .pay-consent-head {
        background: linear-gradient(135deg, #667eea, #764ba2); color: #fff;
        padding: 18px 22px; font-size: 20px; font-weight: 800; letter-spacing: .5px;
    }
    .pay-consent-body { padding: 20px 22px; }
    .pay-consent-item { color: #4a5568; font-size: 15px; margin-bottom: 6px; }
    .pay-consent-amount { font-size: 28px; font-weight: 800; color: #2d3748; margin: 4px 0 14px; }
    .pay-consent-warning {
        background: #fff5f5; border: 1px solid #feb2b2; color: #9b2c2c;
        border-radius: 10px; padding: 12px 14px; font-weight: 600; font-size: 15px;
    }
    .pay-consent-note { color: #718096; font-size: 13px; margin-top: 10px; }
    .pay-consent-actions { display: flex; gap: 10px; padding: 0 22px 22px; flex-wrap: wrap; }
    .pay-consent-actions .btn { flex: 1 1 180px; font-weight: 700; padding: 12px 10px; }
    .pay-consent-declined {
        display: none; margin-top: 12px;
    }
</style>

<div class="pay-consent-overlay" id="payConsentModal" role="dialog" aria-modal="true" aria-labelledby="payConsentTitle">
    <div class="pay-consent-box">
        <div class="pay-consent-head" id="payConsentTitle"><i class="bi bi-credit-card"></i> ОПЛАТИТЬ</div>
        <div class="pay-consent-body">
            <div class="pay-consent-item"><?= Helper::escape($paymentTitle) ?></div>
            <div class="pay-consent-amount"><span id="payConsentAmount"><?= $paymentAmountKzt !== null ? number_format((int)$paymentAmountKzt, 0, ',', ' ') : '—' ?></span> ₸</div>
            <div class="pay-consent-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= Helper::escape($paymentRefundText) ?>
            </div>
            <div class="pay-consent-note">
                После подтверждения оплаты чек придёт на email, указанный при регистрации, и в раздел «Сообщения» от Администрации.
            </div>
        </div>
        <div class="pay-consent-actions">
            <button type="button" class="btn btn-success" id="payConsentAgree">
                <i class="bi bi-check2-circle"></i> СОГЛАСЕН ОПЛАТИТЬ
            </button>
            <button type="button" class="btn btn-outline-secondary" id="payConsentDecline">
                <i class="bi bi-x-circle"></i> ОТКАЗАТЬСЯ
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector(<?= json_encode($paymentFormSelector) ?>);
        const modal = document.getElementById('payConsentModal');
        if (!form || !modal) return;

        let agreeInput = form.querySelector('input[name="payment_agree"]');
        if (!agreeInput) {
            agreeInput = document.createElement('input');
            agreeInput.type = 'hidden';
            agreeInput.name = 'payment_agree';
            agreeInput.value = '0';
            form.appendChild(agreeInput);
        }

        const amountEl = document.getElementById('payConsentAmount');
        const open = function () {
            if (typeof window.aruPaymentAmount === 'function') {
                const amount = Number(window.aruPaymentAmount()) || 0;
                amountEl.textContent = amount.toLocaleString('ru-RU');
            }
            modal.classList.add('is-open');
            document.getElementById('payConsentAgree').focus();
        };
        const close = function () { modal.classList.remove('is-open'); };

        // Слушатель регистрируется последним: срабатывает только если остальные проверки формы прошли
        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            if (agreeInput.value === '1') return;
            e.preventDefault();
            open();
        });

        document.getElementById('payConsentAgree').addEventListener('click', function () {
            agreeInput.value = '1';
            close();
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });

        document.getElementById('payConsentDecline').addEventListener('click', function () {
            agreeInput.value = '0';
            close();
            let note = form.querySelector('.pay-consent-declined');
            if (!note) {
                note = document.createElement('div');
                note.className = 'alert alert-warning pay-consent-declined';
                note.textContent = 'Вы отказались от оплаты — заявка не отправлена. Данные формы сохранены, вы можете вернуться к оплате в любой момент.';
                form.appendChild(note);
            }
            note.style.display = 'block';
            note.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
        });
    });
</script>
