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

        if (Helper::isLoggedIn()) {
            Helper::redirect('ads/create');
            return;
        }

        Helper::redirect('auth/register' . ($ref ? ('?ref=' . urlencode($ref)) : ''));
    }
}
