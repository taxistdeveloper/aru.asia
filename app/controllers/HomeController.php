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

        $searchQuery = '';
        $searchStatus = null;
        $searchMessage = null;
        if (!empty($_SESSION['aru_search_result'])) {
            $searchResult = $_SESSION['aru_search_result'];
            unset($_SESSION['aru_search_result']);
            $searchQuery = $searchResult['query'] ?? '';
            $searchStatus = $searchResult['status'] ?? null;
            $searchMessage = $searchResult['message'] ?? null;
        }

        View::render('home/landing', [
            'seo' => $seoData,
            'stats' => $stats,
            'users' => $users,
            'ads' => $ads,
            'searchQuery' => $searchQuery,
            'searchStatus' => $searchStatus,
            'searchMessage' => $searchMessage,
        ]);
    }

    /**
     * Отображает платформу (главную страницу с пользователями)
     */
    public function platform()
    {
        // Поиск по номеру aru_N (SEO: platform?search=...)
        $searchQuery = trim((string) ($_GET['search'] ?? $_GET['q'] ?? ''));
        if ($searchQuery !== '') {
            $_GET['q'] = $searchQuery;
            $this->search();
            return;
        }

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
        $aruNumber = null;
        $aruNumberEnabled = true;
        $searchQuery = '';
        $searchStatus = null;
        $searchMessage = null;

        if (Helper::isLoggedIn()) {
            $userId = Helper::getUserId();
            $user = $this->userModel->findById($userId);
            if ($user) {
                $aruCode = $this->userModel->ensureAruCode($userId);
                $aruNumber = User::formatAruNumber($aruCode);
                $aruNumberEnabled = (int) ($user['aru_number_enabled'] ?? 1) === 1;
            }
        }

        // Результат поиска после редиректа с /search
        if (!empty($_SESSION['aru_search_result'])) {
            $searchResult = $_SESSION['aru_search_result'];
            unset($_SESSION['aru_search_result']);
            $searchQuery = $searchResult['query'] ?? '';
            $searchStatus = $searchResult['status'] ?? null;
            $searchMessage = $searchResult['message'] ?? null;
        }

        $toggleMessage = $_SESSION['aru_toggle_message'] ?? null;
        if ($toggleMessage) {
            unset($_SESSION['aru_toggle_message']);
        }

        View::render('home/info', [
            'title' => 'Информация',
            'aruNumber' => $aruNumber,
            'aruNumberEnabled' => $aruNumberEnabled,
            'searchQuery' => $searchQuery,
            'searchStatus' => $searchStatus,
            'searchMessage' => $searchMessage,
            'toggleMessage' => $toggleMessage,
        ]);
    }

    /**
     * Включить / отключить публичный поиск по номеру aru_N
     */
    public function toggleAruNumber()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            Helper::redirect('info');
            return;
        }

        $userId = Helper::getUserId();
        $enabled = isset($_POST['aru_number_enabled']) && (string) $_POST['aru_number_enabled'] === '1';

        if ($this->userModel->setAruNumberEnabled($userId, $enabled)) {
            $_SESSION['aru_toggle_message'] = $enabled
                ? 'Номер включён — другие смогут найти вас по номеру.'
                : 'Номер отключён — поиск по вашему номеру скрыт.';
        } else {
            $_SESSION['aru_toggle_message'] = 'Не удалось изменить статус номера.';
        }

        Helper::redirect('info');
    }

    /**
     * Поиск пользователя по номеру aru N (из TikTok)
     */
    public function search()
    {
        $query = trim((string) ($_GET['q'] ?? $_GET['search'] ?? ''));
        $failRedirect = Helper::isLoggedIn() ? 'info' : '';

        if ($query === '') {
            $_SESSION['aru_search_result'] = [
                'query' => '',
                'status' => 'empty',
                'message' => 'Введите номер, например aru789136',
            ];
            Helper::redirect($failRedirect);
            return;
        }

        $result = $this->userModel->searchByAruNumber($query);

        if ($result['status'] === 'found' && !empty($result['user']['id'])) {
            Helper::redirect('profile/view?id=' . (int) $result['user']['id']);
            return;
        }

        if ($result['status'] === 'disabled') {
            $_SESSION['aru_search_result'] = [
                'query' => $query,
                'status' => 'disabled',
                'message' => 'Пользователь отключил номер',
            ];
        } else {
            $_SESSION['aru_search_result'] = [
                'query' => $query,
                'status' => 'not_found',
                'message' => 'Пользователь с таким номером не найден',
            ];
        }

        Helper::redirect($failRedirect);
    }
}
