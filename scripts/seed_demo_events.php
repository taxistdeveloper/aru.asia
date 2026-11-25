<?php
/**
 * Постоянные мероприятия: по 5 в Казахстане, Кыргызстане, Узбекистане, России и Беларуси.
 * Запуск на сервере: php scripts/seed_demo_events.php
 * Повторный запуск не создаёт дубликаты — только обновляет описания и цены.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/autoload.php';

$result = DemoEvents::seed();
echo "Готово: создано — {$result['created']}, обновлено — {$result['updated']} (владелец: user #{$result['owner_id']}).\n";
