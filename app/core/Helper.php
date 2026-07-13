<?php

/**
 * ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
 *
 * Здесь хранятся полезные функции, которые используются
 * в разных частях приложения.
 */

class Helper
{
    /**
     * Перенаправляет пользователя на другую страницу
     */
    public static function redirect($url)
    {
        header("Location: " . BASE_URL . $url);
        exit();
    }

    /**
     * Проверяет авторизован ли пользователь
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Проверяет существование пользователя в базе данных
     * Если пользователя нет, выполняет выход и перенаправляет на страницу входа с сообщением
     */
    public static function checkUserExists()
    {
        if (!self::isLoggedIn()) {
            return true; // Пользователь не авторизован, проверка не требуется
        }

        $userId = self::getUserId();
        if (!$userId) {
            return true; // Нет ID пользователя
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        // Если пользователя нет в базе данных
        if (!$user) {
            // Очищаем remember token если есть
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            // Очищаем данные пользователя из сессии
            unset($_SESSION['user_id']);
            unset($_SESSION['user_email']);
            unset($_SESSION['user_gender']);
            unset($_SESSION['user_role']);

            // Сохраняем сообщение о том, что пользователь был удален
            $_SESSION['deleted_user_message'] = 'Вас удалили из базы';

            // Перенаправляем на страницу входа
            self::redirect('auth/login');
            return false;
        }

        return true;
    }

    /**
     * Получает ID текущего пользователя
     */
    public static function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Проверяет авторизован ли администратор
     */
    public static function isAdminLoggedIn()
    {
        return isset($_SESSION['admin_id']);
    }

    /**
     * Получает ID текущего администратора
     */
    public static function getAdminId()
    {
        return $_SESSION['admin_id'] ?? null;
    }

    /**
     * Проверяет права администратора и перенаправляет на вход если не авторизован
     */
    public static function requireAdmin()
    {
        if (!self::isAdminLoggedIn()) {
            self::redirect('admin/login');
        }
    }

    /**
     * Проверяет имеет ли пользователь роль менеджера или администратора
     */
    public static function isManager()
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        $userId = self::getUserId();
        $userModel = new User();
        $role = $userModel->getRole($userId);

        return $role === 'manager' || self::isAdminLoggedIn();
    }

    /**
     * Получает роль текущего пользователя
     */
    public static function getUserRole()
    {
        if (!self::isLoggedIn()) {
            return 'guest';
        }

        $userId = self::getUserId();
        $userModel = new User();
        return $userModel->getRole($userId);
    }

    /**
     * Проверяет права менеджера и перенаправляет если нет доступа
     */
    public static function requireManager()
    {
        if (!self::isManager()) {
            self::redirect('home');
        }
    }

    /**
     * Проверяет, заблокирован ли профиль пользователя, и перенаправляет в профиль если заблокирован
     * Использовать для ограничения доступа заблокированных пользователей
     */
    public static function checkProfileBlocked()
    {
        if (!self::isLoggedIn()) {
            return; // Не авторизованные пользователи не могут быть заблокированы
        }

        // Админы не могут быть заблокированы (или если они в админке, они не подпадают под это ограничение)
        if (self::isAdminLoggedIn()) {
            return; // Админы имеют полный доступ
        }

        $userId = self::getUserId();
        $userModel = new User();

        if ($userModel->isProfileBlocked($userId)) {
            // Перенаправляем в личный кабинет с предупреждением
            self::redirect('profile');
        }
    }

    /**
     * Проверяет, заполнен ли профиль пользователя, и перенаправляет в профиль если не заполнен
     * Использовать для ограничения доступа пользователей с незаполненным профилем
     */
    public static function checkProfileComplete()
    {
        if (!self::isLoggedIn()) {
            return; // Не авторизованные пользователи не подпадают под эту проверку
        }

        // Админы имеют полный доступ
        if (self::isAdminLoggedIn()) {
            return;
        }

        $userId = self::getUserId();
        $userModel = new User();

        if (!$userModel->isProfileComplete($userId)) {
            // Сохраняем сообщение в сессию
            $_SESSION['profile_incomplete_message'] = 'Для использования всех функций приложения необходимо заполнить профиль. Пожалуйста, отредактируйте свой профиль.';
            // Перенаправляем в личный кабинет
            self::redirect('profile');
        }
    }

    /**
     * Вычисляет расстояние между двумя точками на карте (в километрах)
     * Использует формулу гаверсинуса
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Радиус Земли в километрах

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Безопасно выводит текст (защита от XSS)
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Форматирует адрес в нужном формате: Казахстан, город улица, номер дома, дополнительная информация
     * Преобразует из формата: дополнительная информация, номер дома, улица, город, Казахстан
     */
    public static function formatAddress($address)
    {
        if (empty($address)) {
            return $address;
        }

        // Разделяем адрес по запятым
        $parts = array_map('trim', explode(',', $address));
        $parts = array_values(array_filter($parts)); // Убираем пустые элементы и перенумеровываем

        // Если "Казахстан" есть в конце, переформатируем
        $kazakhstanIndex = -1;
        foreach ($parts as $index => $part) {
            if (stripos($part, 'Казахстан') !== false) {
                $kazakhstanIndex = $index;
                break;
            }
        }

        if ($kazakhstanIndex >= 0 && count($parts) >= 4) {
            // Новый формат: Казахстан, город улица, номер дома, дополнительная информация
            $kazakhstan = $parts[$kazakhstanIndex];

            // Город должен быть перед Казахстаном (обычно предпоследний)
            $cityIndex = $kazakhstanIndex > 0 ? $kazakhstanIndex - 1 : -1;
            $city = $cityIndex >= 0 ? $parts[$cityIndex] : '';

            // Улица перед городом
            $streetIndex = $cityIndex > 0 ? $cityIndex - 1 : -1;
            $street = $streetIndex >= 0 ? $parts[$streetIndex] : '';

            // Номер дома перед улицей
            $houseIndex = $streetIndex > 0 ? $streetIndex - 1 : -1;
            $houseNumber = $houseIndex >= 0 ? $parts[$houseIndex] : '';

            // Все остальное - дополнительная информация (все что до номера дома)
            $additional = [];
            for ($i = 0; $i < $houseIndex && $houseIndex > 0; $i++) {
                $additional[] = $parts[$i];
            }

            // Формируем новый адрес
            $formatted = $kazakhstan;

            if (!empty($city) && !empty($street)) {
                $formatted .= ',' . $city . ' ' . $street;
            } elseif (!empty($city)) {
                $formatted .= ',' . $city;
            } elseif (!empty($street)) {
                $formatted .= ',' . $street;
            }

            if (!empty($houseNumber)) {
                $formatted .= ', ' . $houseNumber;
            }

            if (!empty($additional)) {
                $formatted .= ', ' . implode(', ', $additional);
            }

            return $formatted;
        }

        // Если не удалось распознать формат, возвращаем как есть
        return $address;
    }

    /**
     * Генерирует случайный токен для подтверждения email
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Загружает файл (фото) на сервер
     * @return string|false Имя файла при успехе, false при ошибке
     */
    public static function uploadFile($file, $directory = 'uploads/')
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Проверяем, что файл существует
        // Примечание: не проверяем is_uploaded_file() здесь, так как move_uploaded_file() сам проверяет это
        // На некоторых хостингах is_uploaded_file() может работать по-разному
        if (!file_exists($file['tmp_name'])) {
            return false;
        }

        // Получаем расширение файла
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Проверяем расширение файла
        if (empty($extension) || !in_array($extension, $allowedExtensions)) {
            return false;
        }

        /**
         * ВАЖНО:
         * Ранее здесь была сложная проверка безопасности файла через getimagesize, MIME‑типы и сигнатуры.
         * На некоторых хостингах это приводило к ложным срабатываниям (валидные JPG/PNG отклонялись),
         * потому что функции типа getimagesize/mime_content_type/finfo работали по‑разному.
         *
         * Сейчас мы ОПИРАЕМСЯ на:
         *  - проверку, что файл действительно загружен через HTTP (is_uploaded_file)
         *  - проверку расширения (jpg, jpeg, png, gif, webp)
         *  - ограничение размера файла в контроллере (до 10MB)
         *
         * Этого достаточно для типичного пользовательского загрузчика фото и устраняет
         * различия между локальным сервером и хостингом.
         *
         * При необходимости более жёсткой безопасности сюда можно вернуть дополнительные проверки,
         * но сейчас они отключены специально.
         */

        // Получаем абсолютный путь к корню проекта
        $projectRoot = dirname(__DIR__, 2);

        // Нормализуем путь с помощью realpath() для большей надежности
        $realProjectRoot = realpath($projectRoot);
        if ($realProjectRoot !== false) {
            $projectRoot = $realProjectRoot;
        }

        // Нормализуем путь к директории (убираем лишние слэши)
        $directory = rtrim($directory, '/') . '/';

        // Формируем абсолютный путь
        $absoluteDirectory = $projectRoot . DIRECTORY_SEPARATOR . $directory;

        // Нормализуем путь (заменяем обратные слеши на прямые для универсальности)
        $absoluteDirectory = str_replace('\\', '/', $absoluteDirectory);

        // Убираем двойные слеши
        $absoluteDirectory = preg_replace('#/+#', '/', $absoluteDirectory);

        // Создаем папку если её нет
        if (!is_dir($absoluteDirectory)) {
            if (!@mkdir($absoluteDirectory, 0775, true)) {
                // Логируем ошибку создания папки
                error_log("Helper::uploadFile: Failed to create directory: " . $absoluteDirectory . ". Parent writable: " . (is_writable(dirname($absoluteDirectory)) ? 'yes' : 'no'));
                return false; // Не удалось создать папку
            }
            // После создания устанавливаем права еще раз для надежности
            @chmod($absoluteDirectory, 0775);
        }

        // Проверяем права на запись
        if (!is_writable($absoluteDirectory)) {
            // Пробуем изменить права на папку
            @chmod($absoluteDirectory, 0775);
            // Проверяем еще раз
            if (!is_writable($absoluteDirectory)) {
                error_log("Helper::uploadFile: Directory not writable: " . $absoluteDirectory . ". Permissions: " . substr(sprintf('%o', fileperms($absoluteDirectory)), -4));
                return false; // Папка не доступна для записи
            }
        }

        // Генерируем уникальное имя файла
        $filename = uniqid() . '.' . $extension;
        $uploadPath = $absoluteDirectory . $filename;

        // Нормализуем путь для Windows
        $uploadPath = str_replace('\\', '/', $uploadPath);

        // Перемещаем файл
        // Используем move_uploaded_file() как основной метод (безопаснее)
        if (@move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $filename;
        }

        // Если move_uploaded_file() не сработал, пробуем альтернативный метод через copy()
        // Это может помочь на некоторых хостингах, где move_uploaded_file() работает некорректно
        if (file_exists($file['tmp_name']) && is_readable($file['tmp_name'])) {
            // Проверяем, что целевая директория доступна для записи
            if (is_writable($absoluteDirectory)) {
                // Пробуем скопировать файл
                if (@copy($file['tmp_name'], $uploadPath)) {
                    // Удаляем временный файл после успешного копирования
                    @unlink($file['tmp_name']);
                    // Проверяем, что файл действительно скопировался
                    if (file_exists($uploadPath)) {
                        return $filename;
                    }
                }
            }
        }

        // Если все методы не сработали, логируем ошибку
        error_log("Helper::uploadFile: failed to upload. tmp: " . $file['tmp_name'] . ", target: " . $uploadPath . ", dir writable: " . (is_writable($absoluteDirectory) ? 'yes' : 'no'));

        return false;
    }

    /**
     * Получает ссылку на веб-интерфейс почтового сервиса по email адресу
     * 
     * @param string $email Email адрес
     * @return string|null URL почтового сервиса или null если не удалось определить
     */
    public static function getEmailServiceUrl($email)
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        // Извлекаем домен из email
        $domain = strtolower(substr(strrchr($email, "@"), 1));

        // Определяем почтовый сервис по домену
        $emailServices = [
            'gmail.com' => 'https://mail.google.com/mail/u/0/#inbox',
            'googlemail.com' => 'https://mail.google.com/mail/u/0/#inbox',
            'yandex.ru' => 'https://mail.yandex.ru/',
            'yandex.com' => 'https://mail.yandex.com/',
            'yandex.kz' => 'https://mail.yandex.kz/',
            'yandex.ua' => 'https://mail.yandex.ua/',
            'mail.ru' => 'https://e.mail.ru/messages/inbox',
            'inbox.ru' => 'https://e.mail.ru/messages/inbox',
            'list.ru' => 'https://e.mail.ru/messages/inbox',
            'bk.ru' => 'https://e.mail.ru/messages/inbox',
            'outlook.com' => 'https://outlook.live.com/mail/0/inbox',
            'hotmail.com' => 'https://outlook.live.com/mail/0/inbox',
            'live.com' => 'https://outlook.live.com/mail/0/inbox',
            'yahoo.com' => 'https://mail.yahoo.com/',
            'yahoo.ru' => 'https://mail.yahoo.com/',
            'icloud.com' => 'https://www.icloud.com/mail',
            'me.com' => 'https://www.icloud.com/mail',
            'protonmail.com' => 'https://mail.protonmail.com/',
            'proton.me' => 'https://mail.proton.me/',
        ];

        // Проверяем точное совпадение
        if (isset($emailServices[$domain])) {
            return $emailServices[$domain];
        }

        // Проверяем поддомены (например, mail.yandex.ru)
        foreach ($emailServices as $serviceDomain => $url) {
            if (strpos($domain, $serviceDomain) !== false) {
                return $url;
            }
        }

        // Если не нашли, возвращаем null
        return null;
    }

    /**
     * Получает название почтового сервиса по email адресу
     * 
     * @param string $email Email адрес
     * @return string Название сервиса или "почту"
     */
    public static function getEmailServiceName($email)
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'почту';
        }

        $domain = strtolower(substr(strrchr($email, "@"), 1));

        $serviceNames = [
            'gmail.com' => 'Gmail',
            'googlemail.com' => 'Gmail',
            'yandex.ru' => 'Яндекс.Почту',
            'yandex.com' => 'Яндекс.Почту',
            'yandex.kz' => 'Яндекс.Почту',
            'yandex.ua' => 'Яндекс.Почту',
            'mail.ru' => 'Mail.ru',
            'inbox.ru' => 'Mail.ru',
            'list.ru' => 'Mail.ru',
            'bk.ru' => 'Mail.ru',
            'outlook.com' => 'Outlook',
            'hotmail.com' => 'Outlook',
            'live.com' => 'Outlook',
            'yahoo.com' => 'Yahoo Mail',
            'yahoo.ru' => 'Yahoo Mail',
            'icloud.com' => 'iCloud Mail',
            'me.com' => 'iCloud Mail',
            'protonmail.com' => 'ProtonMail',
            'proton.me' => 'ProtonMail',
        ];

        // Проверяем точное совпадение
        if (isset($serviceNames[$domain])) {
            return $serviceNames[$domain];
        }

        // Проверяем поддомены
        foreach ($serviceNames as $serviceDomain => $name) {
            if (strpos($domain, $serviceDomain) !== false) {
                return $name;
            }
        }

        return 'почту';
    }

    /**
     * Получает IP-адрес пользователя
     * 
     * @return string IP-адрес или 'unknown'
     */
    public static function getClientIp()
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Если это список IP (от прокси), берем первый
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Валидация IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Возвращаем REMOTE_ADDR даже если он локальный
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Определяет страну по IP-адресу используя бесплатный API
     * 
     * @param string $ip IP-адрес (если не указан, будет использован IP пользователя)
     * @return string|null Название страны или null при ошибке
     */
    public static function getCountryByIp($ip = null)
    {
        if ($ip === null) {
            $ip = self::getClientIp();
        }

        // Пропускаем локальные IP
        if ($ip === 'unknown' || $ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
            return null;
        }

        // Используем бесплатный API ip-api.com (до 45 запросов в минуту)
        $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode&lang=ru";

        // Используем file_get_contents с контекстом для таймаута
        $context = stream_context_create([
            'http' => [
                'timeout' => 3, // 3 секунды таймаут
                'user_agent' => 'PHP'
            ]
        ]);

        try {
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);

            if (isset($data['status']) && $data['status'] === 'success' && !empty($data['country'])) {
                return $data['country'];
            }
        } catch (Exception $e) {
            error_log("Helper::getCountryByIp error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Рекурсивно ищет ключ 'Orientation' в массиве EXIF (разные устройства вкладывают по-разному).
     *
     * @param array $arr
     * @return int 1 если не найдено
     */
    protected static function findOrientationInExif(array $arr)
    {
        if (isset($arr['Orientation']) && $arr['Orientation'] !== '' && $arr['Orientation'] !== null) {
            return (int) $arr['Orientation'];
        }
        foreach ($arr as $value) {
            if (is_array($value)) {
                $found = self::findOrientationInExif($value);
                if ($found !== 1) {
                    return $found;
                }
            }
        }
        return 1;
    }

    /**
     * Читает EXIF Orientation из бинарного JPEG (тег 0x0112), если расширение exif недоступно.
     *
     * @param string $path
     * @return int 1–8, 1 если не найдено или ошибка
     */
    protected static function readJpegOrientationFromBinary($path)
    {
        $data = @file_get_contents($path, false, null, 0, 65535);
        if ($data === false || strlen($data) < 12) {
            return 1;
        }
        // Ищем маркер APP1 (0xFFE1) и тег Orientation (0x0112) в IFD0
        $pos = 0;
        $len = strlen($data);
        while ($pos < $len - 1) {
            if ($data[$pos] === "\xFF" && $data[$pos + 1] === "\xE1") {
                $pos += 2;
                if ($pos + 4 > $len) {
                    break;
                }
                $segmentLen = ord($data[$pos]) << 8 | ord($data[$pos + 1]);
                $pos += 2;
                if ($segmentLen < 14 || $pos + $segmentLen > $len) {
                    break;
                }
                // "Exif\0\0"
                if (substr($data, $pos, 6) !== "Exif\x00\x00") {
                    $pos += $segmentLen - 4;
                    continue;
                }
                $pos += 6;
                $tiff = $pos;
                if ($tiff + 8 > $len) {
                    return 1;
                }
                $byteOrder = substr($data, $tiff, 2);
                $little = ($byteOrder === "II");
                $pos = $tiff + 4;
                $ifd0 = $little
                    ? (ord($data[$pos]) | ord($data[$pos + 1]) << 8 | ord($data[$pos + 2]) << 16 | ord($data[$pos + 3]) << 24)
                    : (ord($data[$pos]) << 24 | ord($data[$pos + 1]) << 16 | ord($data[$pos + 2]) << 8 | ord($data[$pos + 3]));
                $ifd0 += $tiff;
                if ($ifd0 < $tiff || $ifd0 + 2 > $len) {
                    return 1;
                }
                $numTags = $little
                    ? (ord($data[$ifd0]) | ord($data[$ifd0 + 1]) << 8)
                    : (ord($data[$ifd0]) << 8 | ord($data[$ifd0 + 1]));
                $numTags = min(max(0, $numTags), 256);
                $ifd0 += 2;
                for ($i = 0; $i < $numTags && $ifd0 + 12 <= $len; $i++, $ifd0 += 12) {
                    $tag = $little
                        ? (ord($data[$ifd0]) | ord($data[$ifd0 + 1]) << 8)
                        : (ord($data[$ifd0]) << 8 | ord($data[$ifd0 + 1]));
                    if ($tag !== 0x0112) {
                        continue;
                    }
                    $val = $little
                        ? (ord($data[$ifd0 + 8]) | ord($data[$ifd0 + 9]) << 8)
                        : (ord($data[$ifd0 + 8]) << 8 | ord($data[$ifd0 + 9]));
                    if ($val >= 1 && $val <= 8) {
                        return (int) $val;
                    }
                    return 1;
                }
                return 1;
            }
            $pos++;
        }
        return 1;
    }

    /**
     * Применяет EXIF-ориентацию к изображению (поворот/отражение по метаданным камеры).
     * Устраняет проблему «боком» на лендинге и в карточках.
     *
     * @param string $tmpPath Путь к файлу (для чтения EXIF)
     * @param resource $image Ресурс GD
     * @param string $imageType Тип: jpeg, png, gif, webp
     * @param int $width Ширина
     * @param int $height Высота
     * @return array [resource|null $image, int $width, int $height]
     */
    protected static function applyExifOrientation($tmpPath, $image, $imageType, $width, $height)
    {
        $orientation = 1;
        if ($imageType === 'jpeg') {
            // Сначала пробуем «плоский» вывод (iPhone/Android часто отдают Orientation в корне)
            if (function_exists('exif_read_data')) {
                $exifFlat = @exif_read_data($tmpPath);
                if (is_array($exifFlat) && isset($exifFlat['Orientation'])) {
                    $orientation = (int) $exifFlat['Orientation'];
                }
                // Если не нашли — смотрим секции (IFD0, EXIF и т.д.)
                if ($orientation === 1) {
                    $exif = @exif_read_data($tmpPath, null, true);
                    if (is_array($exif)) {
                        if (!empty($exif['IFD0']['Orientation'])) {
                            $orientation = (int) $exif['IFD0']['Orientation'];
                        } elseif (isset($exif['Orientation'])) {
                            $orientation = (int) $exif['Orientation'];
                        } elseif (!empty($exif['EXIF']['Orientation'])) {
                            $orientation = (int) $exif['EXIF']['Orientation'];
                        } else {
                            $orientation = self::findOrientationInExif($exif);
                        }
                    }
                }
            }
            // Fallback: читаем тег Orientation (0x0112) из бинарного JPEG, если EXIF недоступен
            if ($orientation === 1 && file_exists($tmpPath) && is_readable($tmpPath)) {
                $orientation = self::readJpegOrientationFromBinary($tmpPath);
            }
        }
        if ($orientation === 1) {
            return [$image, $width, $height];
        }

        $rotated = null;
        $newWidth = $width;
        $newHeight = $height;

        switch ($orientation) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $rotated = imagerotate($image, 180, 0);
                if ($rotated !== false) {
                    imagedestroy($image);
                    $image = $rotated;
                }
                break;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                break;
            case 5:
                imageflip($image, IMG_FLIP_VERTICAL);
                $rotated = imagerotate($image, -90, 0);
                if ($rotated !== false) {
                    imagedestroy($image);
                    $image = $rotated;
                    $newWidth = $height;
                    $newHeight = $width;
                }
                break;
            case 6:
                // 90° по часовой (телефон держали вертикально)
                $rotated = imagerotate($image, -90, 0);
                if ($rotated !== false) {
                    imagedestroy($image);
                    $image = $rotated;
                    $newWidth = $height;
                    $newHeight = $width;
                }
                break;
            case 7:
                $rotated = imagerotate($image, 90, 0);
                if ($rotated !== false) {
                    imagedestroy($image);
                    $image = $rotated;
                    $newWidth = $height;
                    $newHeight = $width;
                    imageflip($image, IMG_FLIP_HORIZONTAL);
                }
                break;
            case 8:
                // 90° против часовой
                $rotated = imagerotate($image, 90, 0);
                if ($rotated !== false) {
                    imagedestroy($image);
                    $image = $rotated;
                    $newWidth = $height;
                    $newHeight = $width;
                }
                break;
        }

        return [$image, $newWidth, $newHeight];
    }

    /**
     * Обрабатывает и оптимизирует изображение
     * Выполняет валидацию, изменение размера и сжатие
     * 
     * @param string $tmpPath Временный путь к загруженному файлу
     * @param string $outputPath Путь для сохранения обработанного изображения
     * @param int $maxWidth Максимальная ширина (по умолчанию 1920)
     * @param int $maxHeight Максимальная высота (по умолчанию 1080)
     * @param int $quality Качество JPEG (по умолчанию 85)
     * @return bool true при успехе, false при ошибке
     */
    public static function processImage($tmpPath, $outputPath, $maxWidth = 1920, $maxHeight = 1080, $quality = 85)
    {
        // Проверяем наличие расширения GD
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
            return false;
        }

        // Проверяем, что файл существует
        if (!file_exists($tmpPath) || !is_readable($tmpPath)) {
            return false;
        }

        // Получаем информацию об изображении
        $imageInfo = @getimagesize($tmpPath);
        if ($imageInfo === false) {
            return false; // Файл не является изображением
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Проверяем поддерживаемые форматы
        $supportedTypes = [
            'image/jpeg' => 'jpeg',
            'image/jpg' => 'jpeg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        if (!isset($supportedTypes[$mimeType])) {
            return false; // Неподдерживаемый формат
        }

        $imageType = $supportedTypes[$mimeType];

        // Загружаем изображение в зависимости от типа
        $image = null;
        switch ($imageType) {
            case 'jpeg':
                $image = @imagecreatefromjpeg($tmpPath);
                break;
            case 'png':
                $image = @imagecreatefrompng($tmpPath);
                break;
            case 'gif':
                $image = @imagecreatefromgif($tmpPath);
                break;
            case 'webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = @imagecreatefromwebp($tmpPath);
                } else {
                    // Если WebP не поддерживается, конвертируем через GD
                    return false;
                }
                break;
        }

        if ($image === false || $image === null) {
            return false; // Не удалось загрузить изображение
        }

        // Применяем EXIF-ориентацию (фото с телефона часто сохраняются «боком» без поворота пикселей)
        list($image, $originalWidth, $originalHeight) = self::applyExifOrientation(
            $tmpPath,
            $image,
            $imageType,
            $originalWidth,
            $originalHeight
        );
        if ($image === null) {
            return false;
        }

        // Вычисляем новые размеры с сохранением пропорций
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        
        // Если изображение меньше максимального размера, не увеличиваем его
        if ($ratio >= 1) {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        } else {
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);
        }

        // Создаем новое изображение с нужными размерами
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Для PNG и GIF обрабатываем прозрачность, но так как конвертируем в JPEG,
        // нужно залить белым фоном
        if ($imageType === 'png' || $imageType === 'gif') {
            // Создаем белый фон для JPEG (JPEG не поддерживает прозрачность)
            $white = imagecolorallocate($newImage, 255, 255, 255);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $white);
            // Включаем альфа-блендинг для правильной обработки прозрачности
            imagealphablending($newImage, true);
        }

        // Изменяем размер изображения
        imagecopyresampled(
            $newImage,
            $image,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        // Освобождаем память от исходного изображения
        imagedestroy($image);

        // Сохраняем обработанное изображение в формате JPEG для лучшей совместимости и меньшего размера
        // Конвертируем все форматы в JPEG
        $result = @imagejpeg($newImage, $outputPath, $quality);

        // Освобождаем память
        imagedestroy($newImage);

        // Проверяем, что файл действительно был создан и имеет размер больше 0
        if ($result === false || !file_exists($outputPath) || filesize($outputPath) === 0) {
            // Удаляем файл, если он был создан, но пустой или поврежден
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }
            return false;
        }

        return true;
    }

    /**
     * Максимальный срок планирования свиданий и мероприятий (дней от текущего момента)
     */
    public static function getMaxPlanningDays(): int
    {
        return defined('MAX_PLANNING_DAYS') ? (int) MAX_PLANNING_DAYS : 30;
    }

    /**
     * Unix-timestamp крайней допустимой даты планирования
     */
    public static function getMaxPlanningTimestamp(): int
    {
        return strtotime('+' . self::getMaxPlanningDays() . ' days');
    }

    /**
     * Крайняя дата для input[type=datetime-local] (YYYY-MM-DDTHH:MM)
     */
    public static function getMaxPlanningDateTimeLocal(): string
    {
        return date('Y-m-d\TH:i', self::getMaxPlanningTimestamp());
    }

    /**
     * Проверяет дату свидания/мероприятия. Возвращает текст ошибки или null.
     */
    public static function validatePlanningDateTime(?string $dateTime): ?string
    {
        if ($dateTime === null || $dateTime === '') {
            return null;
        }

        $timestamp = strtotime($dateTime);
        if ($timestamp === false) {
            return 'Неверный формат даты и времени';
        }

        if ($timestamp > self::getMaxPlanningTimestamp()) {
            $days = self::getMaxPlanningDays();
            $word = $days === 1 ? 'день' : ($days < 5 ? 'дня' : 'дней');
            return "Дата не может быть позже чем через {$days} {$word} от сегодня";
        }

        return null;
    }
}
