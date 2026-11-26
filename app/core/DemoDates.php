<?php

/**
 * ПРИМЕРЫ СВИДАНИЙ
 *
 * По пять примеров в каждой стране (Казахстан, Кыргызстан, Узбекистан, Россия, Беларусь) — для мужчин и для женщин.
 * Помечены dates.is_demo = 1: в списке у карточки небольшая пометка «Пример», чат закрыт
 * (за примером не стоит живой человек, писать некому).
 * Дата сама переносится на неделю вперёд, когда проходит, — примеры стоят весь год.
 *
 * Создание/обновление: php scripts/seed_demo_events.php (вместе с мероприятиями).
 */
class DemoDates
{
    public const OWNERS = [
        'female' => 'demo-dates-female@aru.asia',
        'male' => 'demo-dates-male@aru.asia',
    ];

    private static $schemaReady = false;
    private static $refreshed = false;

    /** Заголовки — в том же формате, что пишут пользователи. Час встречи — для разнообразия. */
    public static function templates(): array
    {
        return [
            ['title' => 'ИНТЕРЕСНО: серьёзные отношения', 'hour' => 19, 'slug' => 'serious'],
            ['title' => 'ИНТЕРЕСНО: новые друзья', 'hour' => 18, 'slug' => 'friends'],
            ['title' => 'ИНТЕРЕСНО: прогулка и кофе', 'hour' => 17, 'slug' => 'coffee'],
            ['title' => 'ИНТЕРЕСНО: ужин в ресторане', 'hour' => 20, 'slug' => 'dinner'],
            ['title' => 'ИНТЕРЕСНО: спонтанное приключение', 'hour' => 15, 'slug' => 'adventure'],
        ];
    }

    /** Возвращает относительный путь к демо-картинке (в assets/images/demo-dates) по заголовку и полу автора. */
    public static function photoFor(string $title, ?string $gender): ?string
    {
        $g = ($gender === 'male') ? 'male' : 'female';
        $slug = null;
        foreach (self::templates() as $t) {
            if ($t['title'] === $title) {
                $slug = $t['slug'];
                break;
            }
        }
        if ($slug === null) {
            $templates = self::templates();
            $slug = $templates[abs(crc32($title)) % count($templates)]['slug'];
        }
        $rel = 'assets/images/demo-dates/' . $g . '-' . $slug . '.jpg';
        $abs = dirname(__DIR__, 2) . '/' . $rel;
        return is_file($abs) ? $rel : null;
    }

    public static function ensureSchema(PDO $db): void
    {
        if (self::$schemaReady) {
            return;
        }
        try {
            $check = $db->query("SHOW COLUMNS FROM dates LIKE 'is_demo'");
            if ($check && $check->rowCount() === 0) {
                $db->exec("ALTER TABLE dates ADD COLUMN is_demo TINYINT(1) NOT NULL DEFAULT 0, ADD KEY idx_dates_is_demo (is_demo)");
            }
            self::$schemaReady = true;
        } catch (Throwable $e) {
            error_log('DemoDates::ensureSchema: ' . $e->getMessage());
        }
    }

    private static function refreshDates(PDO $db): void
    {
        if (self::$refreshed) {
            return;
        }
        self::$refreshed = true;
        try {
            $db->exec("
                UPDATE dates
                SET date_time = DATE_ADD(date_time, INTERVAL (FLOOR(TIMESTAMPDIFF(DAY, date_time, NOW()) / 7) + 1) * 7 DAY)
                WHERE is_demo = 1 AND date_time < NOW()
            ");
        } catch (Throwable $e) {
            error_log('DemoDates::refreshDates: ' . $e->getMessage());
        }
    }

    /**
     * Примеры для страны зрителя. Мужчинам — примеры «от девушек», девушкам — «от мужчин»,
     * если пол неизвестен — оба варианта. Страна не из списка — Казахстан.
     */
    public static function forViewer(?string $country, ?string $viewerGender): array
    {
        $name = DemoEvents::normalizeCountry($country) ?? 'Казахстан';
        $db = Database::getInstance()->getConnection();
        self::ensureSchema($db);
        self::refreshDates($db);

        $genders = ['female', 'male'];
        if ($viewerGender === 'male') {
            $genders = ['female'];
        } elseif ($viewerGender === 'female') {
            $genders = ['male'];
        }
        $in = implode(',', array_fill(0, count($genders), '?'));
        $stmt = $db->prepare("
            SELECT d.id, d.user_id, d.title, d.category_id, d.date_time, d.location, d.latitude, d.longitude, d.created_at, d.is_demo,
                   u.email, u.age, u.gender,
                   dc.name AS category_name, dc.description AS category_description,
                   NULL AS photo, 0 AS distance
            FROM dates d
            JOIN users u ON d.user_id = u.id
            LEFT JOIN date_categories dc ON d.category_id = dc.id
            WHERE d.is_demo = 1 AND d.location LIKE ? AND u.gender IN ($in)
            ORDER BY d.date_time
        ");
        $stmt->execute(array_merge(['%' . $name], $genders));
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['photo_url'] = self::photoFor((string)($row['title'] ?? ''), $row['gender'] ?? null);
        }
        unset($row);
        return $rows;
    }

    public static function isDemo($date): bool
    {
        return is_array($date) && !empty($date['is_demo']);
    }

    public static function seed(): array
    {
        $db = Database::getInstance()->getConnection();
        self::ensureSchema($db);

        $owners = [];
        $find = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        foreach (self::OWNERS as $gender => $email) {
            $find->execute([$email]);
            $id = (int)$find->fetchColumn();
            if (!$id) {
                (new User())->create($email, bin2hex(random_bytes(24)), bin2hex(random_bytes(16)));
                $find->execute([$email]);
                $id = (int)$find->fetchColumn();
            }
            if (!$id) {
                throw new RuntimeException('Не удалось создать пользователя для примеров свиданий');
            }
            $db->prepare("UPDATE users SET full_name = 'Aru', gender = ? WHERE id = ?")->execute([$gender, $id]);
            $owners[$gender] = $id;
        }

        $findDate = $db->prepare("SELECT id FROM dates WHERE is_demo = 1 AND user_id = :uid AND location LIKE :city LIMIT 1");
        $insert = $db->prepare("
            INSERT INTO dates (user_id, title, category_id, date_time, location, latitude, longitude, is_demo, created_at)
            VALUES (:uid, :title, NULL, :date_time, :location, :lat, :lon, 1, NOW())
        ");
        $update = $db->prepare("UPDATE dates SET title = :title, location = :location, latitude = :lat, longitude = :lon WHERE id = :id");

        $created = 0;
        $updated = 0;
        $templates = self::templates();
        foreach (DemoEvents::countries() as $country => $info) {
            foreach ($info['cities'] as $i => [$city, $lat, $lon]) {
                foreach ($owners as $gender => $ownerId) {
                    // У «мужских» примеров другой заголовок, чтобы списки не повторяли друг друга
                    $t = $templates[($i + ($gender === 'male' ? 2 : 0)) % count($templates)];
                    $location = 'Центр города, ' . $city . ', ' . $country;
                    $params = [':title' => $t['title'], ':location' => $location, ':lat' => $lat, ':lon' => $lon];
                    $findDate->execute([':uid' => $ownerId, ':city' => '%, ' . $city . ', ' . $country]);
                    $id = (int)$findDate->fetchColumn();
                    if ($id) {
                        $update->execute($params + [':id' => $id]);
                        $updated++;
                    } else {
                        $dateTime = date('Y-m-d', strtotime('+' . (1 + $i) . ' days')) . sprintf(' %02d:00:00', $t['hour']);
                        $insert->execute($params + [':uid' => $ownerId, ':date_time' => $dateTime]);
                        $created++;
                    }
                }
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }
}
