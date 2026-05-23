<?php

/**
 * КОНТРОЛЛЕР РЕКЛАМЫ
 *
 * Обрабатывает запросы рекламодателей для создания рекламы
 */

class AdController
{
    private $adModel;

    public function __construct()
    {
        $this->adModel = new Ad();
    }

    /**
     * Отображает форму для создания рекламы
     */
    public function create()
    {
        // Получаем email текущего пользователя, если он авторизован
        $userEmail = '';
        $hasPending = false;
        $pendingAd = null;

        if (Helper::isLoggedIn()) {
            $userId = Helper::getUserId();
            $userModel = new User();
            $user = $userModel->findById($userId);
            if ($user && !empty($user['email'])) {
                $userEmail = $user['email'];
                // Проверяем, есть ли реклама на модерации
                $hasPending = $this->adModel->hasPendingByEmail($userEmail);
                if ($hasPending) {
                    $pendingAd = $this->adModel->getPendingByEmail($userEmail);
                }
            }
        }

        View::render('ads/create', [
            'title' => 'Разместить рекламу',
            'userEmail' => $userEmail,
            'hasPending' => $hasPending,
            'pendingAd' => $pendingAd
        ]);
    }

    /**
     * Сохраняет рекламу
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('ads/create');
            return;
        }

        // Валидация данных
        $advertiserName = trim($_POST['advertiser_name'] ?? '');
        $advertiserEmail = trim($_POST['advertiser_email'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $cityCount = trim((string)($_POST['city_count'] ?? '1'));
        $cities = $_POST['cities'] ?? [];
        $clickUrl = trim($_POST['click_url'] ?? '');
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');

        // Если пользователь авторизован — привязываем рекламу к email аккаунта
        // (иначе реклама не будет отображаться в профиле, если в форме введён другой email).
        $accountEmail = '';
        if (Helper::isLoggedIn()) {
            $userId = Helper::getUserId();
            $userModel = new User();
            $user = $userModel->findById($userId);
            if ($user && !empty($user['email'])) {
                $accountEmail = trim((string)$user['email']);
                $advertiserEmail = $accountEmail;
            }
        }

        // Нормализуем email (для устойчивого поиска/сравнения)
        $advertiserEmail = strtolower(trim($advertiserEmail));

        /**
         * Нормализация дат из формы.
         * Форма использует input[type=date], а в БД поля DATETIME.
         * Если сохранить как YYYY-MM-DD, MySQL преобразует в YYYY-MM-DD 00:00:00,
         * и реклама может "пропасть" в день окончания сразу после полуночи.
         */
        $normalizeDateTime = function (string $value, bool $isEndDate): string {
            $value = trim($value);
            if ($value === '') {
                return '';
            }

            // Если это только дата (YYYY-MM-DD) — добавляем время
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return $isEndDate ? ($value . ' 23:59:59') : ($value . ' 00:00:00');
            }

            // Если уже есть время — оставляем как есть
            return $value;
        };

        $startDate = $normalizeDateTime($startDate, false);
        $endDate = $normalizeDateTime($endDate, true);

        $errors = [];

        if (empty($advertiserName)) {
            $errors[] = 'Укажите название компании/рекламодателя';
        }

        if (empty($advertiserEmail) || !filter_var($advertiserEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Укажите корректный email';
        }

        // Проверяем, есть ли уже реклама на модерации
        if (!empty($advertiserEmail) && $this->adModel->hasPendingByEmail($advertiserEmail)) {
            $errors[] = 'У вас уже есть заявка на размещение рекламы, которая находится на модерации. Дождитесь рассмотрения текущей заявки.';
        }

        if (empty($country)) {
            $errors[] = 'Укажите страну';
        }

        // Подготовка городов для создания рекламы
        $cityValuesToCreate = [];
        $validCityCounts = ['1', '2', '3', '4', '5', 'all'];
        if (!in_array($cityCount, $validCityCounts, true)) {
            $errors[] = 'Некорректный выбор количества городов';
        } else {
            if ($cityCount === 'all') {
                $cityValuesToCreate = ['ВСЕ ГОРОДА'];
            } elseif ($cityCount === '1') {
                if (empty($city) || $city === 'ВСЕ ГОРОДА') {
                    $errors[] = 'Укажите город';
                } else {
                    $cityValuesToCreate = [$city];
                }
            } elseif (in_array($cityCount, ['2', '3', '4', '5'], true)) {
                $requiredCount = (int)$cityCount;

                // PHP: form `cities[]` уходит как массив
                $citiesList = [];
                if (is_array($cities)) {
                    $citiesList = $cities;
                } elseif (!empty($cities)) {
                    $citiesList = [$cities];
                }

                $citiesList = array_map(function ($c) {
                    return trim((string)$c);
                }, $citiesList);

                // Убираем пустые и "ВСЕ ГОРОДА"
                $citiesList = array_values(array_unique(array_filter($citiesList, function ($c) {
                    return $c !== '' && $c !== 'ВСЕ ГОРОДА';
                })));

                if (count($citiesList) !== $requiredCount) {
                    $n = $requiredCount;
                    $mod100 = $n % 100;
                    $mod10 = $n % 10;
                    if ($mod100 >= 11 && $mod100 <= 14) {
                        $cityWord = 'городов';
                    } elseif ($mod10 === 1) {
                        $cityWord = 'город';
                    } elseif ($mod10 >= 2 && $mod10 <= 4) {
                        $cityWord = 'города';
                    } else {
                        $cityWord = 'городов';
                    }
                    $errors[] = 'Выберите ровно ' . $requiredCount . ' ' . $cityWord;
                } else {
                    $cityValuesToCreate = $citiesList;
                }
            }
        }

        if (empty($clickUrl) || !filter_var($clickUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Укажите корректный URL для перехода';
        }

        if (empty($startDate)) {
            $errors[] = 'Укажите дату начала показа рекламы';
        }

        if (empty($endDate)) {
            $errors[] = 'Укажите дату окончания показа рекламы';
        }

        if (!empty($startDate) && !empty($endDate) && strtotime($endDate) <= strtotime($startDate)) {
            $errors[] = 'Дата окончания должна быть позже даты начала';
        }

        $endOfCurrentYear = strtotime(date('Y') . '-12-31 23:59:59');
        if (!empty($startDate) && strtotime($startDate) > $endOfCurrentYear) {
            $errors[] = 'Дата начала показа не может быть позже 31 декабря ' . date('Y') . ' года';
        }
        if (!empty($endDate) && strtotime($endDate) > $endOfCurrentYear) {
            $errors[] = 'Дата окончания показа не может быть позже 31 декабря ' . date('Y') . ' года';
        }

        $imageCroppedBase64 = trim($_POST['image_cropped_base64'] ?? '');
        $hasFile = !empty($_FILES['image']['name']);
        if (empty($imageCroppedBase64) && !$hasFile) {
            $errors[] = 'Загрузите рекламный баннер и обрежьте его в редакторе (нажмите «Применить»)';
        }

        // Обработка изображения: приоритет у обрезанного (base64) — так модератор видит то, что будет на выходе
        $imagePath = null;
        if (!empty($imageCroppedBase64) && preg_match('/^data:image\/(jpeg|jpg|png|gif);base64,/', $imageCroppedBase64)) {
            $uploadResult = $this->handleBase64ImageUpload($imageCroppedBase64);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        } elseif ($hasFile) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }

        if (!empty($errors)) {
            View::render('ads/create', [
                'title' => 'Разместить рекламу',
                'errors' => $errors,
                'old' => $_POST,
                'userEmail' => $accountEmail ?: $advertiserEmail
            ]);
            return;
        }

        $createdCount = 0;
        $totalToCreate = count($cityValuesToCreate);
        foreach ($cityValuesToCreate as $singleCity) {
            $result = $this->adModel->create([
                'advertiser_name' => $advertiserName,
                'advertiser_email' => $advertiserEmail,
                'country' => $country,
                'city' => $singleCity,
                'image_path' => $imagePath,
                'video_path' => null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'click_url' => $clickUrl
            ]);

            if ($result) $createdCount++;
        }

        if ($createdCount === $totalToCreate && $totalToCreate > 0) {
            $_SESSION['success_message'] = 'Ваша реклама отправлена на модерацию. После проверки она будет опубликована.';
            Helper::redirect('profile');
        } else {
            View::render('ads/create', [
                'title' => 'Разместить рекламу',
                'errors' => ['Произошла ошибка при сохранении рекламы. Попробуйте позже.'],
                'old' => $_POST
            ]);
        }
    }

    /**
     * Обрабатывает загрузку изображения
     */
    private function handleImageUpload($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Ошибка при загрузке файла'];
        }

        // Проверка размера (2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Размер файла не должен превышать 2MB'];
        }

        // Проверка типа файла
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'error' => 'Разрешены только изображения: JPG, PNG, GIF'];
        }

        // Дополнительная проверка через getimagesize (проверяет, что файл действительно является изображением)
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'error' => 'Файл не является изображением или поврежден'];
        }

        // Проверяем, что это действительно изображение
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($imageInfo['mime'], $allowedMimeTypes)) {
            return ['success' => false, 'error' => 'Недопустимый тип изображения'];
        }

        // Создаем папку для рекламы, если её нет
        $adsDir = __DIR__ . '/../../uploads/ads/';
        if (!is_dir($adsDir)) {
            mkdir($adsDir, 0755, true);
        }

        // Генерируем уникальное имя файла
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('ad_', true) . '.' . $extension;
        $targetPath = $adsDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'path' => $fileName];
        } else {
            return ['success' => false, 'error' => 'Не удалось сохранить файл'];
        }
    }

    /**
     * Сохраняет обрезанное изображение из base64 (data URL).
     * Модератор видит именно это изображение — как его обрезал пользователь и как оно будет на сайте.
     */
    private function handleBase64ImageUpload(string $dataUrl)
    {
        if (!preg_match('/^data:image\/(jpeg|jpg|png|gif);base64,(.+)$/s', $dataUrl, $m)) {
            return ['success' => false, 'error' => 'Недопустимый формат изображения'];
        }

        $ext = $m[1] === 'jpg' ? 'jpg' : $m[1];
        $raw = base64_decode($m[2], true);
        if ($raw === false || strlen($raw) === 0) {
            return ['success' => false, 'error' => 'Не удалось декодировать изображение'];
        }

        // Проверка размера (2MB)
        if (strlen($raw) > 2 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Размер файла не должен превышать 2MB'];
        }

        $adsDir = __DIR__ . '/../../uploads/ads/';
        if (!is_dir($adsDir)) {
            mkdir($adsDir, 0755, true);
        }

        $fileName = uniqid('ad_', true) . '.' . $ext;
        $targetPath = $adsDir . $fileName;

        if (file_put_contents($targetPath, $raw) === false) {
            return ['success' => false, 'error' => 'Не удалось сохранить файл'];
        }

        $imageInfo = @getimagesize($targetPath);
        if ($imageInfo === false) {
            @unlink($targetPath);
            return ['success' => false, 'error' => 'Файл не является изображением или поврежден'];
        }

        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($imageInfo['mime'], $allowedMimeTypes)) {
            @unlink($targetPath);
            return ['success' => false, 'error' => 'Недопустимый тип изображения'];
        }

        return ['success' => true, 'path' => $fileName];
    }

    /**
     * Отображает страницу просмотра рекламы
     */
    public function view()
    {
        if (!Helper::isLoggedIn()) {
            $_SESSION['error_message'] = 'Необходимо авторизоваться';
            Helper::redirect('auth/login');
            return;
        }

        $adId = $_GET['id'] ?? null;
        if (empty($adId)) {
            $_SESSION['error_message'] = 'Не указан ID рекламы';
            Helper::redirect('profile');
            return;
        }

        // Получаем рекламу
        $ad = $this->adModel->findById($adId);
        if (!$ad) {
            $_SESSION['error_message'] = 'Реклама не найдена';
            Helper::redirect('profile');
            return;
        }

        // Проверяем, что реклама принадлежит текущему пользователю
        $userId = Helper::getUserId();
        $userModel = new User();
        $user = $userModel->findById($userId);

        if (
            !$user ||
            empty($user['email']) ||
            strtolower(trim((string)$ad['advertiser_email'])) !== strtolower(trim((string)$user['email']))
        ) {
            $_SESSION['error_message'] = 'У вас нет доступа к этой рекламе';
            Helper::redirect('profile');
            return;
        }

        View::render('ads/view', [
            'title' => 'Просмотр рекламы',
            'ad' => $ad
        ]);
    }

    /**
     * Удаляет рекламу пользователя
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('profile');
            return;
        }

        if (!Helper::isLoggedIn()) {
            $_SESSION['error_message'] = 'Необходимо авторизоваться';
            Helper::redirect('auth/login');
            return;
        }

        $adId = $_POST['ad_id'] ?? null;
        if (empty($adId)) {
            $_SESSION['error_message'] = 'Не указан ID рекламы';
            Helper::redirect('profile');
            return;
        }

        // Получаем email текущего пользователя
        $userId = Helper::getUserId();
        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user || empty($user['email'])) {
            $_SESSION['error_message'] = 'Ошибка получения данных пользователя';
            Helper::redirect('profile');
            return;
        }

        // Удаляем рекламу
        if ($this->adModel->deleteByUser($adId, $user['email'])) {
            $_SESSION['success_message'] = 'Реклама успешно удалена';
        } else {
            $_SESSION['error_message'] = 'Не удалось удалить рекламу. Возможно, она не принадлежит вам или уже удалена.';
        }

        Helper::redirect('profile');
    }
}






