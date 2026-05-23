<?php

/**
 * КОНТРОЛЛЕР ГЛАВНОЙ СТРАНИЦЫ
 *
 * Контроллер обрабатывает запросы к главной странице.
 * Здесь отображаются фотографии пользователей.
 */

class HomeController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Отображает landing страницу
     */
    public function landing()
    {
        // Скрытый счётчик посещений (по дням) — для отображения в админке
        DailyVisit::trackToday();

        // Получаем статистику
        $eventModel = new Event();
        $dateModel = new Date();

        $stats = [
            'users' => $this->userModel->getTotalCount(),
            'events' => $eventModel->getActiveCount(),
            'dates' => $dateModel->getActiveCount()
        ];

        // Получаем всех пользователей для отображения на лендинге (без фильтров)
        $currentUserId = Helper::getUserId();
        $users = $this->userModel->getAllWithPhotos(10000, $currentUserId, null, null);

        // Получаем активную рекламу для лендинга
        $adModel = new Ad();
        $ads = $adModel->getAllActive(10);

        // SEO данные для landing страницы - оптимизировано для поисковых запросов "aru знакомство" и "aru"
        $seoData = [
            'title' => 'Aru знакомство - Платформа для знакомств и мероприятий | aru.asia',
            'description' => 'Aru знакомство - современная платформа для знакомств. Найдите свою вторую половинку на aru.asia. Создавайте мероприятия, находите интересные свидания и общайтесь с единомышленниками в Казахстане.',
            'keywords' => 'aru знакомство, aru, aru.asia, знакомства, знакомства онлайн, знакомства в Казахстане, сайт знакомств aru, aru платформа знакомств, свидания, мероприятия, поиск пары, онлайн знакомства Казахстан',
            'og_title' => 'Aru знакомство - Платформа для знакомств и мероприятий | aru.asia',
            'og_description' => 'Aru знакомство - найдите свою вторую половинку на aru.asia. Современная платформа для знакомств и мероприятий в Казахстане.',
            'og_image' => BASE_URL . 'assets/images/logo.jpg',
            'og_url' => BASE_URL,
            'og_type' => 'website',
            'canonical' => BASE_URL
        ];

        View::render('home/landing', [
            'seo' => $seoData,
            'stats' => $stats,
            'users' => $users,
            'ads' => $ads
        ]);
    }

    /**
     * Отображает платформу (главную страницу с пользователями)
     */
    public function platform()
    {
        // Получаем ID текущего пользователя (если авторизован)
        $currentUserId = Helper::getUserId();
        $currentUserGender = $_SESSION['user_gender'] ?? null;
        $currentUserCountry = null;
        $currentUserCity = null;

        // Получаем данные текущего пользователя (если авторизован)
        $currentUser = null;
        if ($currentUserId) {
            $currentUser = $this->userModel->findById($currentUserId);

            // Обновляем пол из базы данных, если не установлен в сессии
            if ($currentUser && !$currentUserGender && isset($currentUser['gender'])) {
                $currentUserGender = $currentUser['gender'];
                $_SESSION['user_gender'] = $currentUserGender;
            }

            // Получаем страну и город пользователя
            if ($currentUser && !empty($currentUser['country'])) {
                $currentUserCountry = $currentUser['country'];
            }
            if ($currentUser && !empty($currentUser['city'])) {
                $currentUserCity = $currentUser['city'];
            }
        }

        // Проверяем, заблокирован ли профиль пользователя
        $isBlocked = false;
        $adminRemark = null;
        $remarkType = null;
        if ($currentUserId && !Helper::isAdminLoggedIn()) {
            $isBlocked = $this->userModel->isProfileBlocked($currentUserId);
            if ($isBlocked) {
                $adminRemark = $this->userModel->getAdminRemark($currentUserId);
                $remarkType = $this->userModel->getRemarkType($currentUserId);
            }
        }

        // Получаем пользователей с фотографиями для главной страницы
        // Если пользователь авторизован, показываем только противоположный пол
        // Показываем только пользователей из той же страны
        // Мужчины видят женщин, женщины видят мужчин
        $users = $this->userModel->getAllWithPhotos(20, $currentUserId, $currentUserGender, $currentUserCountry);

        // Получаем активную рекламу (с приоритетом по стране и городу пользователя, если известны)
        $adModel = new Ad();
        $ads = $adModel->getActiveForUser($currentUserCountry, $currentUserCity, 10);

        // Временная отладка (можно удалить после проверки)
        // error_log('Ads count: ' . count($ads));
        // error_log('Ads data: ' . print_r($ads, true));

        // Получаем чаты пользователя (если авторизован)
        $conversations = [];
        if ($currentUserId) {
            $messageModel = new Message();
            $conversations = $messageModel->getConversations($currentUserId);
            // Ограничиваем количество чатов для отображения (например, последние 5)
            $conversations = array_slice($conversations, 0, 5);
        }

        // Проверяем наличие сообщения о выходе
        $logoutMessage = $_SESSION['logout_message'] ?? null;
        if ($logoutMessage) {
            unset($_SESSION['logout_message']); // Удаляем сообщение после получения
        }

        // Передаем данные в представление
        View::render('home/platform', [
            'users' => $users,
            'ads' => $ads,
            'conversations' => $conversations,
            'isMobile' => View::isMobile(),
            'logoutMessage' => $logoutMessage,
            'isBlocked' => $isBlocked,
            'adminRemark' => $adminRemark,
            'remarkType' => $remarkType
        ]);
    }

    /**
     * Отображает информационную страницу с кнопками
     */
    public function info()
    {
        View::render('home/info', [
            'title' => 'Информация'
        ]);
    }
}
