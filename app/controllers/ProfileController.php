<?php

/**
 * КОНТРОЛЛЕР ЛИЧНОГО КАБИНЕТА
 *
 * Обрабатывает просмотр и редактирование профиля пользователя
 */

class ProfileController
{
    /** Макс. размер файла в МБ (из config или по умолчанию 10) */
    private static function getMaxFileSizeMb()
    {
        return defined('MAX_FILE_SIZE_MB') ? (int) MAX_FILE_SIZE_MB : 10;
    }

    private $userModel;
    private $photoModel;
    private $eventModel;
    private $dateModel;
    private $blockedModel;
    private $adModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->photoModel = new UserPhoto();
        $this->eventModel = new Event();
        $this->dateModel = new Date();
        $this->blockedModel = new BlockedUser();
        $this->adModel = new Ad();
    }

    /**
     * Простейшая проверка "О себе" на бессмысленный текст
     */
    private function isGibberishAbout($text)
    {
        if (empty($text)) {
            return true;
        }

        $normalized = preg_replace('/\s+/u', ' ', trim($text));

        // Слишком короткое описание
        if (mb_strlen($normalized) < 20) {
            return true;
        }

        $noSpaces = preg_replace('/\s+/u', '', $normalized);

        // Только "хз", "хзхз" и т.п.
        if (preg_match('/^(хз)+$/iu', $noSpaces)) {
            return true;
        }

        $hasLatin = preg_match('/[A-Za-z]/u', $normalized);
        $hasCyrillic = preg_match('/[А-Яа-яЁё]/u', $normalized);

        // Смешение латиницы и кириллицы считаем мусором (asdsadsa ывавыа)
        if ($hasLatin && $hasCyrillic) {
            return true;
        }

        // Если вообще нет гласных (рус/англ) — тоже считаем мусором
        if (!preg_match('/[аеёиоуыэюяaeiouy]/iu', $normalized)) {
            return true;
        }

        return false;
    }

    /**
     * Показывает личный кабинет
     */
    public function index()
    {
        // Проверяем авторизацию
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        // Автоматически удаляем просроченные свидания и мероприятия
        $this->dateModel->deleteExpired();
        $this->eventModel->deleteExpired();

        $userId = Helper::getUserId();

        // Получаем данные пользователя
        $user = $this->userModel->findById($userId);
        if (!$user || !is_array($user)) {
            Helper::redirect('auth/login');
            return;
        }

        $photos = $this->photoModel->getByUserId($userId);
        if (!is_array($photos)) {
            $photos = [];
        }

        $userEvent = $this->eventModel->getByUserId($userId);
        $userDate = $this->dateModel->getByUserId($userId);

        // Нормализуем значения: если методы вернули false, устанавливаем null
        $userEvent = ($userEvent && is_array($userEvent)) ? $userEvent : null;
        $userDate = ($userDate && is_array($userDate)) ? $userDate : null;
        
        // Дополнительная проверка: если свидание просрочено по времени PHP, удаляем его
        if ($userDate && !empty($userDate['date_time']) && strtotime($userDate['date_time']) < time()) {
            $this->dateModel->delete($userDate['id'], $userId);
            $userDate = null;
        }
        
        // Аналогично для мероприятий
        if ($userEvent && !empty($userEvent['event_date']) && strtotime($userEvent['event_date']) < time()) {
            $this->eventModel->delete($userEvent['id'], $userId);
            $userEvent = null;
        }

        // Получаем рекламу пользователя, если email существует
        $userAds = [];
        if (!empty($user['email'])) {
            try {
                $userAds = $this->adModel->getByUserEmail($user['email']);
                // Убеждаемся, что это массив
                $userAds = is_array($userAds) ? $userAds : [];
            } catch (Exception $e) {
                // В случае ошибки просто используем пустой массив
                $userAds = [];
                error_log("Error getting user ads: " . $e->getMessage());
            }
        }

        // Проверяем, заблокирован ли профиль
        $isBlocked = $this->userModel->isProfileBlocked($userId);
        $adminRemark = $this->userModel->getAdminRemark($userId);
        $remarkType = $this->userModel->getRemarkType($userId);

        View::render('profile/index', [
            'user' => $user,
            'photos' => $photos,
            'userEvent' => $userEvent,
            'userDate' => $userDate,
            'userAds' => $userAds,
            'isBlocked' => $isBlocked,
            'adminRemark' => $adminRemark,
            'remarkType' => $remarkType,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Показывает профиль другого пользователя
     */
    public function view()
    {
        // Получаем ID пользователя из параметров
        $viewUserId = $_GET['id'] ?? null;

        if (!$viewUserId) {
            Helper::redirect('home');
            return;
        }

        // Получаем данные пользователя
        $viewUser = $this->userModel->findById($viewUserId);

        if (!$viewUser) {
            Helper::redirect('home');
            return;
        }

        // Проверяем, является ли текущий пользователь администратором
        $isAdmin = Helper::isAdminLoggedIn();

        // Проверяем, не заблокирован ли пользователь (если текущий пользователь авторизован)
        $currentUserId = Helper::getUserId();
        $isBlocked = false;
        $isBlockedBy = false;
        $isCurrentUser = false;

        if ($currentUserId) {
            $isCurrentUser = ($currentUserId == $viewUserId);
            // Проверяем, заблокирован ли просматриваемый пользователь текущим
            $isBlocked = $this->blockedModel->isBlocked($currentUserId, $viewUserId);
            // Также проверяем, не заблокировал ли просматриваемый пользователь текущего
            $isBlockedBy = $this->blockedModel->isBlocked($viewUserId, $currentUserId);
        }

        // Если это текущий пользователь, перенаправляем на его профиль
        if ($isCurrentUser) {
            Helper::redirect('profile');
            return;
        }

        // Если пользователь заблокирован, не показываем профиль (кроме админа)
        if (!$isAdmin && ($isBlocked || $isBlockedBy)) {
            Helper::redirect('home');
            return;
        }

        // Автоматически удаляем просроченные свидания и мероприятия
        $this->dateModel->deleteExpired();
        $this->eventModel->deleteExpired();

        // Получаем фотографии пользователя
        $photos = $this->photoModel->getByUserId($viewUserId);

        // Получаем активные объявления пользователя
        $userEvent = $this->eventModel->getByUserId($viewUserId);
        $userDate = $this->dateModel->getByUserId($viewUserId);

        // Для админа получаем информацию о блокировке профиля
        $isProfileBlocked = false;
        $adminRemark = null;
        $remarkType = null;
        if ($isAdmin) {
            $isProfileBlocked = $this->userModel->isProfileBlocked($viewUserId);
            $adminRemark = $this->userModel->getAdminRemark($viewUserId);
            $remarkType = $this->userModel->getRemarkType($viewUserId);
        }

        View::render('profile/view', [
            'user' => $viewUser,
            'photos' => $photos,
            'userEvent' => $userEvent,
            'userDate' => $userDate,
            'currentUserId' => $currentUserId,
            'isAdmin' => $isAdmin,
            'isProfileBlocked' => $isProfileBlocked,
            'adminRemark' => $adminRemark,
            'remarkType' => $remarkType,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Показывает форму редактирования профиля
     */
    public function edit()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $user = $this->userModel->findById($userId);
        $photos = $this->photoModel->getByUserId($userId);

        // Получаем временные фотографии из сессии
        $tempPhotos = $_SESSION['temp_photos'] ?? [];

        // Проверяем, заблокирован ли профиль
        $isBlocked = $this->userModel->isProfileBlocked($userId);
        $adminRemark = $this->userModel->getAdminRemark($userId);
        $remarkType = $this->userModel->getRemarkType($userId);

        View::render('profile/edit', [
            'user' => $user,
            'photos' => $photos,
            'tempPhotos' => $tempPhotos,
            'isBlocked' => $isBlocked,
            'adminRemark' => $adminRemark,
            'remarkType' => $remarkType,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Обновляет профиль пользователя
     */
    public function update()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $user = $this->userModel->findById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => !empty($_POST['full_name']) ? $_POST['full_name'] : null,
                'gender' => $_POST['gender'] ?? null,
                'age' => $_POST['age'] ?? null,
                'marital_status' => $_POST['marital_status'] ?? null,
                'country' => !empty($_POST['country']) ? $_POST['country'] : ($user['country'] ?? null),
                'city' => !empty($_POST['city']) ? $_POST['city'] : ($user['city'] ?? null),
                'about' => !empty($_POST['about']) ? $_POST['about'] : null,
                'latitude' => !empty($_POST['latitude']) ? $_POST['latitude'] : ($user['latitude'] ?? null),
                'longitude' => !empty($_POST['longitude']) ? $_POST['longitude'] : ($user['longitude'] ?? null),
                'age_changes_count' => $user['age_changes_count'] ?? 0
            ];

            // Проверка изменения возраста (максимум 2 раза)
            if (isset($_POST['age']) && $user['age'] != $_POST['age']) {
                if ($user['age_changes_count'] >= 2) {
                    $error = "Вы уже изменили возраст максимальное количество раз (2 раза)";
                } else {
                    $data['age_changes_count'] = $user['age_changes_count'] + 1;
                }
            }

            // Валидация обязательных полей
            if (empty($data['gender'])) {
                $error = "Пожалуйста, укажите пол";
            } elseif (empty($data['age'])) {
                $error = "Пожалуйста, укажите возраст";
            } elseif (empty($data['marital_status'])) {
                $error = "Пожалуйста, укажите семейный статус";
            } elseif (empty($data['country'])) {
                $error = "Пожалуйста, укажите страну. Используйте кнопку 'Определить местоположение'";
            } elseif (empty($data['city'])) {
                $error = "Пожалуйста, укажите город. Используйте кнопку 'Определить местоположение'";
            }

            // Если обязательные поля валидны — проверяем "О себе" на мат и бессмысленный текст (многоязычный фильтр)
            if (!isset($error) && !empty($data['about']) && ProfanityFilter::containsProfanity($data['about'])) {
                // Просто не даём сохранить профиль и показываем ошибку
                $error = 'В поле "О себе" обнаружена нецензурная лексика. '
                    . 'Удалите мат из описания и попробуйте ещё раз.';
            } elseif (!isset($error) && !empty($data['about']) && $this->isGibberishAbout($data['about'])) {
                $error = 'Пожалуйста, опишите себя нормальным текстом. Короткие или бессмысленные наборы символов в поле "О себе" запрещены.';
            }

            if (!isset($error)) {
                // Обработка временных фотографий из сессии (переносим в постоянные)
                if (isset($_SESSION['temp_photos']) && !empty($_SESSION['temp_photos'])) {
                    $photoCount = $this->photoModel->countByUserId($userId);
                    foreach ($_SESSION['temp_photos'] as $tempPhoto) {
                        if ($photoCount >= MAX_PHOTOS) break;
                        if ($this->photoModel->add($userId, $tempPhoto['filename'])) {
                            $photoCount++;
                        }
                    }
                    // Очищаем временные фотографии из сессии
                    unset($_SESSION['temp_photos']);
                }

                // Обработка загрузки фотографий из формы (если есть)
                if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
                    $photoCount = $this->photoModel->countByUserId($userId);

                    // Получаем абсолютный путь к корню проекта
                    $projectRoot = dirname(__DIR__, 2);
                    $realProjectRoot = realpath($projectRoot);
                    if ($realProjectRoot !== false) {
                        $projectRoot = $realProjectRoot;
                    }

                    // Формируем путь к директории для фотографий
                    $directory = rtrim(UPLOAD_DIR, '/') . '/photos/';
                    $absoluteDirectory = $projectRoot . DIRECTORY_SEPARATOR . $directory;
                    $absoluteDirectory = str_replace('\\', '/', $absoluteDirectory);
                    $absoluteDirectory = preg_replace('#/+#', '/', $absoluteDirectory);

                    // Создаем папку если её нет
                    if (!is_dir($absoluteDirectory)) {
                        @mkdir($absoluteDirectory, 0775, true);
                        @chmod($absoluteDirectory, 0775);
                    }

                    foreach ($_FILES['photos']['name'] as $key => $name) {
                        if ($photoCount >= MAX_PHOTOS) break;

                        $file = [
                            'name' => $_FILES['photos']['name'][$key],
                            'type' => $_FILES['photos']['type'][$key],
                            'tmp_name' => $_FILES['photos']['tmp_name'][$key],
                            'error' => $_FILES['photos']['error'][$key],
                            'size' => $_FILES['photos']['size'][$key]
                        ];

                        // Проверяем ошибки загрузки
                        if ($file['error'] !== UPLOAD_ERR_OK) {
                            continue;
                        }

                        // Проверяем, что файл является изображением
                        $imageInfo = @getimagesize($file['tmp_name']);
                        if ($imageInfo === false) {
                            continue;
                        }

                        // Проверяем наличие расширения GD перед обработкой
                        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
                            continue; // Пропускаем файл, если GD не установлен
                        }

                        // Генерируем уникальное имя файла (всегда JPEG после обработки)
                        $filename = uniqid() . '.jpg';
                        $outputPath = $absoluteDirectory . $filename;
                        $outputPath = str_replace('\\', '/', $outputPath);

                        // Обрабатываем и оптимизируем изображение
                        if (Helper::processImage($file['tmp_name'], $outputPath, 1920, 1080, 85)) {
                            // Проверяем, что файл был создан и является валидным JPEG
                            if (file_exists($outputPath)) {
                                $processedImageInfo = @getimagesize($outputPath);
                                if ($processedImageInfo !== false && $processedImageInfo['mime'] === 'image/jpeg') {
                                    $this->photoModel->add($userId, $filename);
                                    $photoCount++;
                                } else {
                                    // Удаляем невалидный файл
                                    @unlink($outputPath);
                                }
                            }
                        }
                    }
                }

                // Обновляем профиль
                if ($this->userModel->updateProfile($userId, $data)) {
                    // Обновляем данные в сессии, если они изменились
                    if (isset($data['gender'])) {
                        $_SESSION['user_gender'] = $data['gender'];
                    }
                    Helper::redirect('profile');
                    return;
                }
            }
        }

        // Обновляем данные о фотографиях и блокировке профиля перед показом формы
        $photos = $this->photoModel->getByUserId($userId);
        $isBlocked = $this->userModel->isProfileBlocked($userId);
        $adminRemark = $this->userModel->getAdminRemark($userId);
        $remarkType = $this->userModel->getRemarkType($userId);

        View::render('profile/edit', [
            'user' => $user,
            'photos' => $photos,
            'error' => $error ?? null,
            'isBlocked' => $isBlocked,
            'adminRemark' => $adminRemark,
            'remarkType' => $remarkType,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Удаляет фотографию пользователя
     */
    public function deletePhoto()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $photoId = $_GET['id'] ?? null;

        if (!$photoId) {
            Helper::redirect('profile');
            return;
        }

        // Получаем информацию о фотографии перед удалением
        $photos = $this->photoModel->getByUserId($userId);
        $photoToDelete = null;
        foreach ($photos as $photo) {
            if ($photo['id'] == $photoId) {
                $photoToDelete = $photo;
                break;
            }
        }

        if ($photoToDelete) {
            // Удаляем фотографию из базы данных
            if ($this->photoModel->delete($photoId, $userId)) {
                // Удаляем физический файл
                $projectRoot = dirname(__DIR__, 2);
                $photoPath = $projectRoot . '/' . UPLOAD_DIR . 'photos/' . $photoToDelete['photo'];
                if (file_exists($photoPath)) {
                    @unlink($photoPath);
                }
            }
        }

        Helper::redirect('profile');
    }

    /**
     * Добавляет фотографии пользователю
     */
    public function addPhoto()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $error = null;
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
            // Проверяем, что файлы были загружены
            if (empty($_FILES['photos']['name'][0])) {
                $error = "Пожалуйста, выберите файлы для загрузки";
            } else {
                $photoCount = $this->photoModel->countByUserId($userId);
                $uploadedCount = 0;
                $errors = [];

                // Проверяем лимит перед загрузкой
                if ($photoCount >= MAX_PHOTOS) {
                    $error = "Достигнут лимит фотографий (" . MAX_PHOTOS . ")";
                } else {
                    foreach ($_FILES['photos']['name'] as $key => $name) {
                        // Пропускаем пустые файлы
                        if (empty($name)) {
                            continue;
                        }

                        // Проверяем лимит
                        if ($photoCount + $uploadedCount >= MAX_PHOTOS) {
                            $errors[] = "Достигнут лимит фотографий. Загружено: " . $uploadedCount;
                            break;
                        }

                        $file = [
                            'name' => $_FILES['photos']['name'][$key],
                            'type' => $_FILES['photos']['type'][$key],
                            'tmp_name' => $_FILES['photos']['tmp_name'][$key],
                            'error' => $_FILES['photos']['error'][$key],
                            'size' => $_FILES['photos']['size'][$key]
                        ];

                        // Проверяем ошибки загрузки
                        if ($file['error'] !== UPLOAD_ERR_OK) {
                            $errorMessages = [
                                UPLOAD_ERR_INI_SIZE => "Файл слишком большой (превышен лимит php.ini)",
                                UPLOAD_ERR_FORM_SIZE => "Файл слишком большой (превышен лимит формы)",
                                UPLOAD_ERR_PARTIAL => "Файл загружен частично",
                                UPLOAD_ERR_NO_FILE => "Файл не был загружен",
                                UPLOAD_ERR_NO_TMP_DIR => "Отсутствует временная папка",
                                UPLOAD_ERR_CANT_WRITE => "Ошибка записи на диск",
                                UPLOAD_ERR_EXTENSION => "Загрузка остановлена расширением PHP"
                            ];
                            $errors[] = $file['name'] . ": " . ($errorMessages[$file['error']] ?? "Неизвестная ошибка");
                            continue;
                        }

                        // Проверяем расширение файла перед загрузкой
                        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                        if (!in_array($extension, $allowedExtensions)) {
                            $errors[] = $file['name'] . ": Неподдерживаемый формат файла. Разрешены: JPG, JPEG, PNG, GIF, WebP";
                            continue;
                        }

                        // Проверяем размер файла (см. MAX_FILE_SIZE_MB в config.php)
                        $maxFileSizeMb = self::getMaxFileSizeMb();
                        $maxSize = $maxFileSizeMb * 1024 * 1024;
                        if ($file['size'] > $maxSize) {
                            $errors[] = $file['name'] . ": Файл слишком большой (максимум " . $maxFileSizeMb . " МБ)";
                            continue;
                        }

                        // Проверяем, что файл является изображением
                        $imageInfo = @getimagesize($file['tmp_name']);
                        if ($imageInfo === false) {
                            $errors[] = $file['name'] . ": Файл не является изображением или поврежден";
                            continue;
                        }

                        // Получаем абсолютный путь к корню проекта
                        $projectRoot = dirname(__DIR__, 2);
                        $realProjectRoot = realpath($projectRoot);
                        if ($realProjectRoot !== false) {
                            $projectRoot = $realProjectRoot;
                        }

                        // Формируем путь к директории для фотографий
                        $directory = rtrim(UPLOAD_DIR, '/') . '/photos/';
                        $absoluteDirectory = $projectRoot . DIRECTORY_SEPARATOR . $directory;
                        $absoluteDirectory = str_replace('\\', '/', $absoluteDirectory);
                        $absoluteDirectory = preg_replace('#/+#', '/', $absoluteDirectory);

                        // Создаем папку если её нет
                        if (!is_dir($absoluteDirectory)) {
                            if (!@mkdir($absoluteDirectory, 0775, true)) {
                                $errors[] = $file['name'] . ": Не удалось создать папку для загрузки";
                                continue;
                            }
                            @chmod($absoluteDirectory, 0775);
                        }

                        // Проверяем права на запись
                        if (!is_writable($absoluteDirectory)) {
                            @chmod($absoluteDirectory, 0775);
                            if (!is_writable($absoluteDirectory)) {
                                $errors[] = $file['name'] . ": Папка для загрузки недоступна для записи";
                                continue;
                            }
                        }

                        // Генерируем уникальное имя файла (всегда JPEG после обработки)
                        $filename = uniqid() . '.jpg';
                        $outputPath = $absoluteDirectory . $filename;
                        $outputPath = str_replace('\\', '/', $outputPath);

                        // Проверяем наличие расширения GD перед обработкой
                        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
                            $errors[] = $file['name'] . ": Расширение GD не установлено. Обработка изображений невозможна.";
                            continue;
                        }

                        // Обрабатываем и оптимизируем изображение
                        if (!Helper::processImage($file['tmp_name'], $outputPath, 1920, 1080, 85)) {
                            $errors[] = $file['name'] . ": Не удалось обработать изображение. Проверьте формат и размер файла.";
                            continue;
                        }

                        // Проверяем, что файл был создан и является валидным изображением
                        if (!file_exists($outputPath)) {
                            $errors[] = $file['name'] . ": Обработанный файл не был сохранен";
                            continue;
                        }

                        // Проверяем, что обработанный файл является валидным JPEG изображением
                        $processedImageInfo = @getimagesize($outputPath);
                        if ($processedImageInfo === false || $processedImageInfo['mime'] !== 'image/jpeg') {
                            @unlink($outputPath);
                            $errors[] = $file['name'] . ": Обработанный файл не является валидным изображением";
                            continue;
                        }

                        // Сохраняем информацию о фотографии в базу данных
                        if ($this->photoModel->add($userId, $filename)) {
                            $uploadedCount++;
                        } else {
                            // Удаляем файл, если не удалось сохранить в БД
                            @unlink($outputPath);
                            $errors[] = $file['name'] . ": Ошибка сохранения в базу данных";
                        }
                    }

                    if ($uploadedCount > 0) {
                        $success = true;
                        if (!empty($errors)) {
                            $error = "Загружено фотографий: " . $uploadedCount . ". Ошибки: " . implode(", ", $errors);
                        }
                    } else {
                        $error = !empty($errors) ? implode(", ", $errors) : "Не удалось загрузить фотографии";
                    }
                }
            }
        } else {
            $error = "Неверный запрос";
        }

        // Сохраняем сообщения в сессию для отображения после редиректа
        if ($error) {
            $_SESSION['photo_upload_error'] = $error;
        }
        if ($success) {
            $_SESSION['photo_upload_success'] = "Фотографии успешно загружены";
        }

        Helper::redirect('profile');
    }

    /**
     * Временно загружает фотографии (до сохранения профиля).
     * Всегда возвращает JSON; лишний вывод отключается через буфер.
     */
    public function uploadTempPhoto()
    {
        // Буфер: чтобы случайный вывод (предупреждения PHP и т.д.) не портил JSON
        ob_start();

        header('Content-Type: application/json; charset=utf-8');

        if (!Helper::isLoggedIn()) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        $userId = Helper::getUserId();
        $uploadedFiles = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
            // При одном файле PHP может отдать строки вместо массивов — приводим к массиву
            if (!is_array($_FILES['photos']['name'])) {
                $_FILES['photos']['name'] = [$_FILES['photos']['name']];
                $_FILES['photos']['type'] = [$_FILES['photos']['type']];
                $_FILES['photos']['tmp_name'] = [$_FILES['photos']['tmp_name']];
                $_FILES['photos']['error'] = [$_FILES['photos']['error']];
                $_FILES['photos']['size'] = [$_FILES['photos']['size']];
            }
            // Инициализируем массив временных фотографий в сессии, если его нет
            if (!isset($_SESSION['temp_photos'])) {
                $_SESSION['temp_photos'] = [];
            }

            // Получаем текущее количество фотографий (постоянных + временных)
            $photoCount = $this->photoModel->countByUserId($userId);
            $tempPhotoCount = count($_SESSION['temp_photos']);
            $totalPhotoCount = $photoCount + $tempPhotoCount;

            // Проверяем лимит
            if ($totalPhotoCount >= MAX_PHOTOS) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Достигнут лимит фотографий (' . MAX_PHOTOS . ')']);
                return;
            }

            // Получаем абсолютный путь к корню проекта
            $projectRoot = dirname(__DIR__, 2);
            $realProjectRoot = realpath($projectRoot);
            if ($realProjectRoot !== false) {
                $projectRoot = $realProjectRoot;
            }

            // Формируем путь к директории для временных фотографий
            $directory = rtrim(UPLOAD_DIR, '/') . '/photos/';
            $absoluteDirectory = $projectRoot . DIRECTORY_SEPARATOR . $directory;
            $absoluteDirectory = str_replace('\\', '/', $absoluteDirectory);
            $absoluteDirectory = preg_replace('#/+#', '/', $absoluteDirectory);

            // Создаем папку если её нет
            if (!is_dir($absoluteDirectory)) {
                @mkdir($absoluteDirectory, 0775, true);
                @chmod($absoluteDirectory, 0775);
            }

            foreach ($_FILES['photos']['name'] as $key => $name) {
                // Пропускаем пустые файлы
                if (empty($name)) {
                    continue;
                }

                // Проверяем лимит
                if ($totalPhotoCount + count($uploadedFiles) >= MAX_PHOTOS) {
                    break;
                }

                $file = [
                    'name' => $_FILES['photos']['name'][$key],
                    'type' => $_FILES['photos']['type'][$key],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$key],
                    'error' => $_FILES['photos']['error'][$key],
                    'size' => $_FILES['photos']['size'][$key]
                ];

                // Проверяем ошибки загрузки
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }

                // Проверяем расширение файла
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($extension, $allowedExtensions)) {
                    continue;
                }

                // Проверяем размер файла (см. MAX_FILE_SIZE_MB в config.php)
                $maxFileSizeMb = self::getMaxFileSizeMb();
                $maxSize = $maxFileSizeMb * 1024 * 1024;
                if ($file['size'] > $maxSize) {
                    continue;
                }

                // Проверяем, что файл является изображением
                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    continue;
                }

                // Проверяем наличие расширения GD
                if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
                    continue;
                }

                // Генерируем уникальное имя файла
                $filename = uniqid() . '.jpg';
                $outputPath = $absoluteDirectory . $filename;
                $outputPath = str_replace('\\', '/', $outputPath);

                // Обрабатываем и оптимизируем изображение
                if (Helper::processImage($file['tmp_name'], $outputPath, 1920, 1080, 85)) {
                    if (file_exists($outputPath)) {
                        $processedImageInfo = @getimagesize($outputPath);
                        if ($processedImageInfo !== false && $processedImageInfo['mime'] === 'image/jpeg') {
                            // Сохраняем во временное хранилище (сессию)
                            $_SESSION['temp_photos'][] = [
                                'filename' => $filename,
                                'original_name' => $file['name']
                            ];
                            $uploadedFiles[] = [
                                'filename' => $filename,
                                'url' => BASE_URL . UPLOAD_DIR . 'photos/' . $filename
                            ];
                        } else {
                            @unlink($outputPath);
                        }
                    }
                }
            }
        }

        if (count($uploadedFiles) > 0) {
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'files' => $uploadedFiles,
                'message' => 'Фотографии загружены. Нажмите "Сохранить изменения" для сохранения.'
            ]);
        } else {
            ob_end_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Не удалось загрузить фотографии. Проверьте формат (JPG, PNG, GIF, WebP) и размер (до ' . self::getMaxFileSizeMb() . ' МБ).'
            ]);
        }
    }

    /**
     * Удаляет временную фотографию. Всегда возвращает JSON.
     */
    public function deleteTempPhoto()
    {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        if (!Helper::isLoggedIn()) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        $filename = $_GET['filename'] ?? null;
        if (!$filename) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Не указано имя файла']);
            return;
        }

        // Удаляем из сессии
        if (isset($_SESSION['temp_photos'])) {
            $_SESSION['temp_photos'] = array_filter($_SESSION['temp_photos'], function($photo) use ($filename) {
                return $photo['filename'] !== $filename;
            });
            $_SESSION['temp_photos'] = array_values($_SESSION['temp_photos']); // Переиндексируем массив
        }

        // Удаляем файл
        $projectRoot = dirname(__DIR__, 2);
        $realProjectRoot = realpath($projectRoot);
        if ($realProjectRoot !== false) {
            $projectRoot = $realProjectRoot;
        }
        $filePath = $projectRoot . '/' . UPLOAD_DIR . 'photos/' . $filename;
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        ob_end_clean();
        echo json_encode(['success' => true]);
    }
}
