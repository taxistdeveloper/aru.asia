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
}
