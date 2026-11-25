<?php

/**
 * ПОСТОЯННЫЕ МЕРОПРИЯТИЯ (наполнение площадки)
 *
 * Пять одинаковых мероприятий в каждой стране: Казахстан, Кыргызстан, Узбекистан, Россия, Беларусь.
 * Помечены events.is_demo = 1 (только для служебных целей): показываются в общем списке и на карте как обычные
 * для страны пользователя, но не считаются в статистике и не удаляются как прошедшие.
 * Дата сама переносится на неделю вперёд, когда проходит, — поэтому они стоят весь год.
 *
 * Создание/обновление: php scripts/seed_demo_events.php (повторный запуск ничего не дублирует).
 */
class DemoEvents
{
    public const DEMO_EMAIL = 'demo-events@aru.asia';
    public const DEMO_NAME = 'Aru';

    private static $schemaReady = false;
    private static $refreshed = false;

    /** Мероприятия-примеры (одни и те же для всех стран). Цена — по валютам стран. */
    public static function templates(): array
    {
        return [
            [
                'title' => 'Гончарный мастер-класс',
                'description' => 'Лепка чашек, тарелок и ваз своими руками. Мастер покажет работу на гончарном круге, глина и инструменты включены. Готовые изделия обжигаем и отдаём через неделю.',
                'hour' => 12,
                'price' => ['KZT' => 12000, 'KGS' => 2000, 'UZS' => 300000, 'RUB' => 2500, 'BYN' => 80],
            ],
            [
                'title' => 'Арт-вечер: рисуем картину акрилом',
                'description' => 'Рисование картины акрилом под руководством художника — даже если вы никогда не рисовали. Холст, краски и фартуки выдаём, картину забираете с собой.',
                'hour' => 19,
                'price' => ['KZT' => 10000, 'KGS' => 1700, 'UZS' => 250000, 'RUB' => 2000, 'BYN' => 65],
            ],
            [
                'title' => 'Кондитерский мастер-класс',
                'description' => 'Готовим капкейки, макаронс и небольшой торт. Все ингредиенты включены, десерты забираете домой в красивой упаковке.',
                'hour' => 15,
                'price' => ['KZT' => 15000, 'KGS' => 2500, 'UZS' => 350000, 'RUB' => 3000, 'BYN' => 95],
            ],
            [
                'title' => 'Танцевальный мастер-класс',
                'description' => 'Латина, бачата, сальса и женские танцы для начинающих. Пара не обязательна — партнёров подберём на месте. Удобная обувь и хорошее настроение.',
                'hour' => 18,
                'price' => ['KZT' => 5000, 'KGS' => 850, 'UZS' => 120000, 'RUB' => 1000, 'BYN' => 35],
            ],
            [
                'title' => 'Speed Dating — вечер знакомств',
                'description' => 'Быстрые свидания по 7 минут, общение, игры и музыка. Ведущий поможет начать разговор, а в конце вечера вы узнаете о взаимных симпатиях.',
                'hour' => 20,
                'price' => ['KZT' => 7000, 'KGS' => 1200, 'UZS' => 170000, 'RUB' => 1500, 'BYN' => 45],
            ],
        ];
    }

    /** Страны и по пять городов в каждой (одно мероприятие-пример на город). */
    public static function countries(): array
    {
        return [
            'Казахстан' => ['currency' => 'KZT', 'cities' => [
                ['Алматы', 43.2389, 76.8897], ['Астана', 51.1694, 71.4491], ['Шымкент', 42.3417, 69.5901],
                ['Караганда', 49.8028, 73.0872], ['Актобе', 50.2839, 57.1670],
            ]],
            'Кыргызстан' => ['currency' => 'KGS', 'cities' => [
                ['Бишкек', 42.8746, 74.5698], ['Ош', 40.5283, 72.7985], ['Джалал-Абад', 40.9333, 73.0000],
                ['Каракол', 42.4907, 78.3936], ['Токмок', 42.8419, 75.3015],
            ]],
            'Узбекистан' => ['currency' => 'UZS', 'cities' => [
                ['Ташкент', 41.2995, 69.2401], ['Самарканд', 39.6542, 66.9597], ['Бухара', 39.7747, 64.4286],
                ['Наманган', 40.9983, 71.6726], ['Андижан', 40.7821, 72.3442],
            ]],
            'Россия' => ['currency' => 'RUB', 'cities' => [
                ['Москва', 55.7558, 37.6173], ['Санкт-Петербург', 59.9343, 30.3351], ['Новосибирск', 55.0084, 82.9357],
                ['Екатеринбург', 56.8389, 60.6057], ['Казань', 55.7961, 49.1064],
            ]],
            'Беларусь' => ['currency' => 'BYN', 'cities' => [
                ['Минск', 53.9006, 27.5590], ['Гомель', 52.4345, 30.9754], ['Брест', 52.0976, 23.7341],
                ['Гродно', 53.6694, 23.8131], ['Витебск', 55.1904, 30.2049],
            ]],
        ];
    }

    /** Приводит название страны пользователя к одной из стран примеров (или null). */
    public static function normalizeCountry(?string $country): ?string
    {
        $c = mb_strtolower(trim((string)$country));
        if ($c === '') {
            return null;
        }
        $map = [
            'казах' => 'Казахстан', 'kazakh' => 'Казахстан', 'қазақ' => 'Казахстан',
            'кыргыз' => 'Кыргызстан', 'киргиз' => 'Кыргызстан', 'kyrgyz' => 'Кыргызстан',
            'узбек' => 'Узбекистан', 'uzbek' => 'Узбекистан',
            'росси' => 'Россия', 'russia' => 'Россия',
            'беларус' => 'Беларусь', 'белорус' => 'Беларусь', 'belarus' => 'Беларусь',
        ];
        foreach ($map as $needle => $name) {
            if (mb_strpos($c, $needle) !== false) {
                return $name;
            }
        }
        return null;
    }

    /** Колонка events.is_demo (idempotent). */
    public static function ensureSchema(PDO $db): void
    {
        if (self::$schemaReady) {
            return;
        }
        try {
            $check = $db->query("SHOW COLUMNS FROM events LIKE 'is_demo'");
            if ($check && $check->rowCount() === 0) {
                $db->exec("ALTER TABLE events ADD COLUMN is_demo TINYINT(1) NOT NULL DEFAULT 0, ADD KEY idx_events_is_demo (is_demo)");
            }
            self::$schemaReady = true;
        } catch (Throwable $e) {
            error_log('DemoEvents::ensureSchema: ' . $e->getMessage());
        }
    }

    /** Переносит прошедшие примеры на ближайшую будущую дату (шагом в неделю). */
    public static function refreshDates(PDO $db): void
    {
        if (self::$refreshed) {
            return;
        }
        self::$refreshed = true;
        try {
            $db->exec("
                UPDATE events
                SET event_date = DATE_ADD(event_date, INTERVAL (FLOOR(TIMESTAMPDIFF(DAY, event_date, NOW()) / 7) + 1) * 7 DAY)
                WHERE is_demo = 1 AND event_date < NOW()
            ");
        } catch (Throwable $e) {
            error_log('DemoEvents::refreshDates: ' . $e->getMessage());
        }
    }

    /** Примеры для страны пользователя; если страна не из списка — для Казахстана. */
    public static function forCountry(?string $country): array
    {
        $name = self::normalizeCountry($country) ?? 'Казахстан';
        $db = Database::getInstance()->getConnection();
        self::ensureSchema($db);
        self::refreshDates($db);
        $stmt = $db->prepare("
            SELECT e.*, u.email, u.gender, 0 AS distance
            FROM events e
            JOIN users u ON e.user_id = u.id
            WHERE e.is_demo = 1 AND e.status = 'approved' AND e.location LIKE :country
            ORDER BY e.event_date
        ");
        $stmt->execute([':country' => '%' . $name]);
        return $stmt->fetchAll();
    }

    /** Страна для показа примеров: из профиля, иначе по IP (кэш в сессии). */
    public static function viewerCountry(?array $user): ?string
    {
        if (!empty($user['country'])) {
            return (string)$user['country'];
        }
        if (array_key_exists('demo_events_country', $_SESSION ?? [])) {
            return $_SESSION['demo_events_country'];
        }
        $country = null;
        try {
            $country = Helper::getCountryByIp();
        } catch (Throwable $e) {
            $country = null;
        }
        $_SESSION['demo_events_country'] = $country;
        return $country;
    }

    public static function isDemo($event): bool
    {
        return is_array($event) && !empty($event['is_demo']);
    }

    /** Создаёт (или обновляет) пользователя-владельца примеров и все примеры. Возвращает счётчики. */
    public static function seed(): array
    {
        $db = Database::getInstance()->getConnection();
        self::ensureSchema($db);

        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([self::DEMO_EMAIL]);
        $ownerId = (int)$stmt->fetchColumn();
        if (!$ownerId) {
            (new User())->create(self::DEMO_EMAIL, bin2hex(random_bytes(24)), bin2hex(random_bytes(16)));
            $stmt->execute([self::DEMO_EMAIL]);
            $ownerId = (int)$stmt->fetchColumn();
        }
        if (!$ownerId) {
            throw new RuntimeException('Не удалось создать пользователя для примеров');
        }
        $db->prepare("UPDATE users SET full_name = ? WHERE id = ?")->execute([self::DEMO_NAME, $ownerId]);

        $find = $db->prepare("SELECT id FROM events WHERE is_demo = 1 AND user_id = :uid AND title = :title AND location LIKE :city LIMIT 1");
        $insert = $db->prepare("
            INSERT INTO events (user_id, title, description, event_date, location, latitude, longitude, price, currency_code, photo, status, is_demo, created_at)
            VALUES (:uid, :title, :description, :event_date, :location, :lat, :lon, :price, :currency, NULL, 'approved', 1, NOW())
        ");
        $update = $db->prepare("
            UPDATE events SET description = :description, price = :price, currency_code = :currency,
                              latitude = :lat, longitude = :lon, location = :location, status = 'approved'
            WHERE id = :id
        ");

        $created = 0;
        $updated = 0;
        $templates = self::templates();
        foreach (self::countries() as $country => $info) {
            foreach ($info['cities'] as $i => [$city, $lat, $lon]) {
                $t = $templates[$i % count($templates)];
                // Мероприятия разнесены по дням недели: 1-е через 2 дня, 2-е через 3 и т.д.
                $date = date('Y-m-d', strtotime('+' . (2 + $i) . ' days')) . sprintf(' %02d:00:00', $t['hour']);
                $location = 'Центр города, ' . $city . ', ' . $country;
                $params = [
                    ':description' => $t['description'],
                    ':price' => $t['price'][$info['currency']],
                    ':currency' => $info['currency'],
                    ':lat' => $lat,
                    ':lon' => $lon,
                ];
                $find->execute([':uid' => $ownerId, ':title' => $t['title'], ':city' => '%, ' . $city . ', ' . $country]);
                $id = (int)$find->fetchColumn();
                if ($id) {
                    $update->execute($params + [':id' => $id, ':location' => $location]);
                    $updated++;
                } else {
                    $insert->execute($params + [
                        ':uid' => $ownerId,
                        ':title' => $t['title'],
                        ':event_date' => $date,
                        ':location' => $location,
                    ]);
                    $created++;
                }
            }
        }

        return ['created' => $created, 'updated' => $updated, 'owner_id' => $ownerId];
    }
}
