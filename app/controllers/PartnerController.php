<?php

/**
 * Публичный вход по партнёрской ссылке: /r?ref=XXXXXX
 */

class PartnerController
{
    public function landing()
    {
        PartnerReferral::captureFromRequest();

        $ref = PartnerReferral::getRefCodeFromCookie();
        if ($ref) {
            $_SESSION['partner_ref_notice'] = 'Вы перешли по партнёрской ссылке. После регистрации и оплаты рекламы партнёр будет получать 50% автоматически.';
        }

        if (Helper::isLoggedIn()) {
            Helper::redirect('ads/create');
            return;
        }

        Helper::redirect('auth/register' . ($ref ? ('?ref=' . urlencode($ref)) : ''));
    }
}
