<?php
/**
 * Приводит координаты мероприятий и свиданий к реальным точкам Караганды.
 * Запуск: php scripts/fix_karaganda_locations.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$karagandaPlaces = [
    '15, проспект Нурсултана Назарбаева, Караганда, Казахстан' => ['lat' => 49.8028, 'lon' => 73.0872],
    '42, проспект Строителей, Караганда, Казахстан' => ['lat' => 49.8121, 'lon' => 73.1045],
    '8, улица Ермекова, Караганда, Казахстан' => ['lat' => 49.7956, 'lon' => 73.1123],
    '101, бульвар Мира, Караганда, Казахстан' => ['lat' => 49.8210, 'lon' => 73.0950],
    '3, улица Комиссарова, Караганда, Казахстан' => ['lat' => 49.7889, 'lon' => 73.1288],
    '27, проспект Шахтёров, Караганда, Казахстан' => ['lat' => 49.8345, 'lon' => 73.1421],
    '5, улица Алиханова, Караганда, Казахстан' => ['lat' => 49.7765, 'lon' => 73.0998],
    '19, улица Гоголя, Караганда, Казахстан' => ['lat' => 49.8044, 'lon' => 73.0755],
    // Дополнительные точки Караганды
    '1, площадь Независимости, Караганда, Казахстан' => ['lat' => 49.8078, 'lon' => 73.0889],
    '12, улица Бухар-Жырау, Караганда, Казахстан' => ['lat' => 49.7991, 'lon' => 73.1012],
    '50, проспект Бухар-Жырау, Караганда, Казахстан' => ['lat' => 49.7915, 'lon' => 73.1180],
    '7, улица Мустафина, Караганда, Казахстан' => ['lat' => 49.8188, 'lon' => 73.0821],
    '22, улица Таттимбета, Караганда, Казахстан' => ['lat' => 49.8263, 'lon' => 73.1078],
    '10, улица Костенко, Караганда, Казахстан' => ['lat' => 49.7842, 'lon' => 73.1156],
    '33, улица Казыбек би, Караганда, Казахстан' => ['lat' => 49.8102, 'lon' => 73.1245],
    '6, улица Сатпаева, Караганда, Казахстан' => ['lat' => 49.8033, 'lon' => 73.0934],
    '18, улица Молокова, Караганда, Казахстан' => ['lat' => 49.8167, 'lon' => 73.1312],
    '2, улица Лободы, Караганда, Казахстан' => ['lat' => 49.7988, 'lon' => 73.0865],
    '9, улица Омарова, Караганда, Казахстан' => ['lat' => 49.8310, 'lon' => 73.0890],
    '14, улица Кривогуза, Караганда, Казахстан' => ['lat' => 49.7895, 'lon' => 73.1055],
];

$placeList = array_values(array_map(function ($location, $coords) {
    return ['location' => $location, 'lat' => $coords['lat'], 'lon' => $coords['lon']];
}, array_keys($karagandaPlaces), $karagandaPlaces));

$db = Database::getInstance()->getConnection();

function fixTable(PDO $db, string $table, array $karagandaPlaces, array $placeList): int
{
    $rows = $db->query("SELECT id, location FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
    $updateByLocation = $db->prepare("UPDATE {$table} SET location = :location, latitude = :lat, longitude = :lon WHERE id = :id");
    $fixed = 0;

    foreach ($rows as $i => $row) {
        $location = $row['location'] ?? '';
        if (isset($karagandaPlaces[$location])) {
            $coords = $karagandaPlaces[$location];
        } else {
            $place = $placeList[$i % count($placeList)];
            $location = $place['location'];
            $coords = ['lat' => $place['lat'], 'lon' => $place['lon']];
        }

        $updateByLocation->execute([
            ':id' => $row['id'],
            ':location' => $location,
            ':lat' => $coords['lat'],
            ':lon' => $coords['lon'],
        ]);
        $fixed++;
        echo "[{$table}] #{$row['id']} → {$location} ({$coords['lat']}, {$coords['lon']})\n";
    }

    return $fixed;
}

echo "Исправляем координаты по Караганде...\n\n";
$eventsFixed = fixTable($db, 'events', $karagandaPlaces, $placeList);
echo "\n";
$datesFixed = fixTable($db, 'dates', $karagandaPlaces, $placeList);

echo "\nГотово: мероприятий — {$eventsFixed}, свиданий — {$datesFixed}.\n";
