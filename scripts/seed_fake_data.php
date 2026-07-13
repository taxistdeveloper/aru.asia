<?php
/**
 * Скрипт наполнения тестовыми данными: 20 мероприятий и 20 свиданий.
 * Запуск: php scripts/seed_fake_data.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Event.php';
require_once __DIR__ . '/../app/models/Date.php';

$count = 20;
$eventModel = new Event();
$dateModel = new Date();
$db = Database::getInstance()->getConnection();

$dateTitles = [
    'ИНТЕРЕСНО: серьёзные отношения',
    'ИНТЕРЕСНО: новые друзья',
    'ИНТЕРЕСНО: общение-флирт',
    'ИНТЕРЕСНО: просто повеселится',
    'ИНТЕРЕСНО: спонтанное приключение',
];

$eventTemplates = [
    ['title' => 'Джазовый вечер в кофейне', 'description' => 'Живая музыка, уютная атмосфера и знакомство с единомышленниками. Вход свободный, напитки за свой счёт.'],
    ['title' => 'Открытая тренировка по бегу', 'description' => 'Лёгкий забег 5 км для новичков и опытных. Старт у главного входа в парк, разминка в 18:30.'],
    ['title' => 'Мастер-класс по рисованию акварелью', 'description' => 'Научимся базовым техникам за 2 часа. Все материалы предоставляются организатором.'],
    ['title' => 'Кинопоказ под открытым небом', 'description' => 'Показываем добрую комедию. Возьмите плед и хорошее настроение.'],
    ['title' => 'Настольные игры в антикафе', 'description' => 'Собираемся поиграть в «Мафию», «Кодовые имена» и другие игры. Новички welcome!'],
    ['title' => 'Йога на рассвете в парке', 'description' => 'Спокойная практика для всех уровней. Коврик с собой, вода и солнцезащитные очки приветствуются.'],
    ['title' => 'Благотворительный ярмарка handmade', 'description' => 'Поддержим местных мастеров: украшения, свечи, выпечка. Часть выручки — в фонд помощи.'],
    ['title' => 'Лекция о путешествиях по Казахстану', 'description' => 'Расскажем о маршрутах выходного дня, бюджете и лайфхаках для поездок.'],
    ['title' => 'Караоке-вечеринка для друзей', 'description' => 'Поём любимые хиты, знакомимся, делаем общие фото. Бронь столика уже оформлена.'],
    ['title' => 'Фотопрогулка по городу', 'description' => 'Ищем интересные ракурсы в центре. Подойдёт и телефон, и камера.'],
    ['title' => 'Кулинарный мастер-класс: паста', 'description' => 'Готовим итальянскую пасту с нуля. Дегустация включена в стоимость.'],
    ['title' => 'Встреча книжного клуба', 'description' => 'Обсуждаем роман месяца. Можно прийти даже если не дочитали — главное желание общаться.'],
    ['title' => 'Турнир по настольному теннису', 'description' => 'Любительский формат, призы за 1–3 места. Регистрация на месте за 30 минут до старта.'],
    ['title' => 'Экскурсия по историческому центру', 'description' => 'Пешая прогулка с гидом, 1,5 часа. Узнаем малоизвестные факты о родном городе.'],
    ['title' => 'Воркшоп по ораторскому мастерству', 'description' => 'Практические упражнения, обратная связь от тренера. Группа до 15 человек.'],
    ['title' => 'Велопрогулка выходного дня', 'description' => 'Маршрут ~12 км, средний темп. Обязательно шлем и вода.'],
    ['title' => 'Концерт местных музыкантов', 'description' => 'Инди, акустика и каверы. Поддержим талантливых артистов города.'],
    ['title' => 'Дегустация авторского чая и десертов', 'description' => 'Пробуем 6 сортов чая и пару десертов от кондитера. Количество мест ограничено.'],
    ['title' => 'Квест для компании друзей', 'description' => 'Командная игра в городе: загадки, маршрут, призы победителям.'],
    ['title' => 'Открытый микрофон: поэзия и музыка', 'description' => '5 минут на выступление для каждого желающего. Атмосфера дружелюбная и поддерживающая.'],
];

$karagandaPlaces = [
    ['location' => '15, проспект Нурсултана Назарбаева, Караганда, Казахстан', 'lat' => 49.8028, 'lon' => 73.0872],
    ['location' => '42, проспект Строителей, Караганда, Казахстан', 'lat' => 49.8121, 'lon' => 73.1045],
    ['location' => '8, улица Ермекова, Караганда, Казахстан', 'lat' => 49.7956, 'lon' => 73.1123],
    ['location' => '101, бульвар Мира, Караганда, Казахстан', 'lat' => 49.8210, 'lon' => 73.0950],
    ['location' => '3, улица Комиссарова, Караганда, Казахстан', 'lat' => 49.7889, 'lon' => 73.1288],
    ['location' => '27, проспект Шахтёров, Караганда, Казахстан', 'lat' => 49.8345, 'lon' => 73.1421],
    ['location' => '5, улица Алиханова, Караганда, Казахстан', 'lat' => 49.7765, 'lon' => 73.0998],
    ['location' => '19, улица Гоголя, Караганда, Казахстан', 'lat' => 49.8044, 'lon' => 73.0755],
    ['location' => '1, площадь Независимости, Караганда, Казахстан', 'lat' => 49.8078, 'lon' => 73.0889],
    ['location' => '12, улица Бухар-Жырау, Караганда, Казахстан', 'lat' => 49.7991, 'lon' => 73.1012],
    ['location' => '50, проспект Бухар-Жырау, Караганда, Казахстан', 'lat' => 49.7915, 'lon' => 73.1180],
    ['location' => '7, улица Мустафина, Караганда, Казахстан', 'lat' => 49.8188, 'lon' => 73.0821],
    ['location' => '22, улица Таттимбета, Караганда, Казахстан', 'lat' => 49.8263, 'lon' => 73.1078],
    ['location' => '10, улица Костенко, Караганда, Казахстан', 'lat' => 49.7842, 'lon' => 73.1156],
    ['location' => '33, улица Казыбек би, Караганда, Казахстан', 'lat' => 49.8102, 'lon' => 73.1245],
    ['location' => '6, улица Сатпаева, Караганда, Казахстан', 'lat' => 49.8033, 'lon' => 73.0934],
    ['location' => '18, улица Молокова, Караганда, Казахстан', 'lat' => 49.8167, 'lon' => 73.1312],
    ['location' => '2, улица Лободы, Караганда, Казахстан', 'lat' => 49.7988, 'lon' => 73.0865],
    ['location' => '9, улица Омарова, Караганда, Казахстан', 'lat' => 49.8310, 'lon' => 73.0890],
    ['location' => '14, улица Кривогуза, Караганда, Казахстан', 'lat' => 49.7895, 'lon' => 73.1055],
];

function pickUsers(PDO $db, int $limit): array
{
    // Только пользователи из Караганды (радиус ~25 км от центра города)
    $sql = "SELECT u.id, u.email, u.gender, u.latitude, u.longitude
            FROM users u
            WHERE u.gender IN ('male', 'female')
              AND (u.marital_status IS NULL OR u.marital_status != 'married')
              AND u.latitude IS NOT NULL
              AND u.longitude IS NOT NULL
              AND u.latitude BETWEEN 49.65 AND 49.95
              AND u.longitude BETWEEN 72.95 AND 73.35
              AND u.id NOT IN (
                  SELECT user_id FROM events
                  WHERE event_date >= NOW() AND status IN ('approved', 'pending')
              )
              AND u.id NOT IN (
                  SELECT user_id FROM dates WHERE date_time >= NOW()
              )
            ORDER BY u.id
            LIMIT :limit";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function randomDateTime(int $daysAheadMin = 1, int $daysAheadMax = 25): string
{
    $dayOffset = random_int($daysAheadMin, $daysAheadMax);
    $hour = random_int(10, 21);
    $minute = [0, 15, 30, 45][random_int(0, 3)];
    $dt = new DateTime('now', new DateTimeZone(APP_TIMEZONE));
    $dt->modify("+{$dayOffset} days");
    $dt->setTime($hour, $minute, 0);
    return $dt->format('Y-m-d H:i:s');
}

$categories = $db->query("SELECT id FROM date_categories WHERE is_active = 1 ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
if (empty($categories)) {
    fwrite(STDERR, "Нет активных категорий свиданий. Создайте категории в панели менеджера.\n");
    exit(1);
}

$users = pickUsers($db, $count);
if (count($users) < $count) {
    fwrite(STDERR, "Недостаточно подходящих пользователей: найдено " . count($users) . ", нужно {$count}.\n");
    fwrite(STDERR, "Нужны пользователи с полом, координатами, без активных свиданий/мероприятий.\n");
    exit(1);
}

echo "Создаём {$count} мероприятий и {$count} свиданий...\n\n";

$eventsCreated = 0;
$datesCreated = 0;

for ($i = 0; $i < $count; $i++) {
    $user = $users[$i];
    $template = $eventTemplates[$i];
    $place = $karagandaPlaces[$i % count($karagandaPlaces)];

    // Всегда координаты Караганды — не берём геолокацию пользователя
    $lat = $place['lat'];
    $lon = $place['lon'];
    $location = $place['location'];

    $eventOk = $eventModel->create([
        'user_id' => (int)$user['id'],
        'title' => $template['title'],
        'description' => $template['description'],
        'event_date' => randomDateTime(2, 28),
        'location' => $location,
        'latitude' => (float)$lat,
        'longitude' => (float)$lon,
        'price' => random_int(0, 1) ? random_int(0, 5) * 500 : 0,
        'photo' => null,
        'status' => 'approved',
    ]);

    if ($eventOk) {
        $eventsCreated++;
        echo "[мероприятие] {$template['title']} — пользователь #{$user['id']} ({$user['email']})\n";
    } else {
        echo "[ошибка] не удалось создать мероприятие для пользователя #{$user['id']}\n";
    }

    $dateOk = $dateModel->create([
        'user_id' => (int)$user['id'],
        'title' => $dateTitles[$i % count($dateTitles)],
        'category_id' => (int)$categories[$i % count($categories)],
        'date_time' => randomDateTime(1, 27),
        'location' => $location,
        'latitude' => (float)$lat,
        'longitude' => (float)$lon,
    ]);

    if ($dateOk) {
        $datesCreated++;
        echo "[свидание] {$dateTitles[$i % count($dateTitles)]} — пользователь #{$user['id']}\n";
    } else {
        echo "[ошибка] не удалось создать свидание для пользователя #{$user['id']}\n";
    }

    echo "\n";
}

echo "Готово: мероприятий — {$eventsCreated}, свиданий — {$datesCreated}.\n";
echo "Мероприятия сразу одобрены (status=approved) и видны в ленте.\n";

echo "\nГенерация фото...\n";
require __DIR__ . '/assign_seed_photos.php';
