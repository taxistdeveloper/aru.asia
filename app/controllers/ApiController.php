<?php

/**
 * Лёгкие JSON-эндпоинты для фронтенда (курсы валют и т.п.).
 */
class ApiController
{
    public function exchangeRates()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        $data = CurrencyExchangeService::getLatestFromKzt();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Heartbeat: пользователь на сайте → онлайн.
     */
    public function presence()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if (!Helper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'online' => false]);
            return;
        }

        User::touchLastActivity(null, true);

        echo json_encode(['ok' => true, 'online' => true]);
    }
}
