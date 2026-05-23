<?php

/**
 * КОНТРОЛЛЕР АВТОРИЗАЦИИ И РЕГИСТРАЦИИ
 *
 * Обрабатывает регистрацию, вход и выход пользователей
 */

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Показывает форму входа
     */
    public function login()
    {
        // Если уже авторизован, перенаправляем в профиль
        if (Helper::isLoggedIn()) {
            Helper::redirect('profile');
            return;
        }

        // Если форма отправлена
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';

            // Нормализуем email: убираем пробелы и приводим к нижнему регистру
            $email = trim(strtolower($email));

            // Находим пользователя
            $user = $this->userModel->findByEmail($email);

            // Проверяем пароль
            if ($user && password_verify($password, $user['password'])) {
                if ($user['email_verified']) {
                    // Сохраняем данные в сессию
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_gender'] = $user['gender'];
                    $_SESSION['user_role'] = $user['role'] ?? 'user';

                    // Если пользователь выбрал "Запомнить меня", создаем токен
                    if ($rememberMe) {
                        $rememberToken = Helper::generateToken(64);
                        $this->userModel->saveRememberToken($user['id'], $rememberToken);

                        // Устанавливаем cookie на 30 дней
                        setcookie('remember_token', $rememberToken, [
                            'expires' => time() + (30 * 24 * 60 * 60),
                            'path' => '/',
                            'domain' => '',
                            'secure' => isset($_SERVER['HTTPS']),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }

                    Helper::redirect('profile');
                } else {
                    $error = "Пожалуйста, подтвердите ваш email";
                }
            } else {
                $error = "Неверный email или пароль";
            }
        }

        View::render('auth/login', [
            'error' => $error ?? null,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Показывает форму регистрации
     */
    public function register()
    {
        // Если уже авторизован, перенаправляем в профиль
        if (Helper::isLoggedIn()) {
            Helper::redirect('profile');
            return;
        }

        // Если форма отправлена
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Нормализуем email: убираем пробелы и приводим к нижнему регистру
            $email = trim(strtolower($email));

            // Валидация
            if (empty($email) || empty($password)) {
                $error = "Заполните все поля";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Некорректный email адрес";
            } elseif ($password !== $confirmPassword) {
                $error = "Пароли не совпадают";
            } elseif (strlen($password) < 6) {
                $error = "Пароль должен быть не менее 6 символов";
            } elseif ($this->userModel->findByEmail($email)) {
                $error = "Пользователь с таким email уже существует";
            } else {
                // Генерируем токен для подтверждения email
                $token = Helper::generateToken();

                // Получаем IP и страну
                $ip = Helper::getClientIp();
                $country = Helper::getCountryByIp($ip);

                // Создаем пользователя
                if ($this->userModel->create($email, $password, $token, $ip, $country)) {
                    // Отправляем email с подтверждением
                    $verifyUrl = BASE_URL . 'auth/verify?token=' . $token;
                    $emailService = new EmailService();
                    
                    // Пытаемся отправить email
                    $emailSent = false;
                    try {
                        $emailSent = $emailService->sendVerificationEmail($email, $verifyUrl);
                        if ($emailSent) {
                            error_log("AuthController: Verification email sent successfully to $email");
                        } else {
                            error_log("AuthController: Failed to send verification email to $email");
                        }
                    } catch (Exception $e) {
                        error_log("AuthController: Exception while sending email to $email: " . $e->getMessage());
                        $emailSent = false;
                    }

                    // Всегда показываем ссылку активации, даже если email отправлен (на случай если письмо не дойдет)
                    View::render('auth/register_success', [
                        'email' => $email,
                        'emailSent' => $emailSent,
                        'verifyUrl' => $verifyUrl, // Всегда передаем ссылку
                        'isMobile' => View::isMobile()
                    ]);
                    return;
                } else {
                    $error = "Ошибка при регистрации";
                }
            }
        }

        View::render('auth/register', [
            'error' => $error ?? null,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Подтверждает email пользователя
     */
    public function verify()
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            die("Неверная ссылка подтверждения");
        }

        // Находим пользователя по токену
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE verification_token = :token");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if ($user && $this->userModel->verifyEmail($token)) {
            // Автоматически авторизуем пользователя
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_gender'] = $user['gender'];
            $_SESSION['user_role'] = $user['role'] ?? 'user';

            Helper::redirect('profile');
        } else {
            die("Ошибка подтверждения email. Неверный токен.");
        }
    }

    /**
     * Показывает форму восстановления пароля (ввод email)
     */
    public function forgotPassword()
    {
        // Если уже авторизован, перенаправляем в профиль
        if (Helper::isLoggedIn()) {
            Helper::redirect('profile');
            return;
        }

        $error = null;
        $success = null;

        // Если форма отправлена
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';

            // Нормализуем email: убираем пробелы и приводим к нижнему регистру
            $email = trim(strtolower($email));

            if (empty($email)) {
                $error = "Введите email адрес";
            } else {
                // Находим пользователя
                $user = $this->userModel->findByEmail($email);

                if ($user) {
                    // Генерируем токен восстановления пароля
                    $token = Helper::generateToken(64);
                    $this->userModel->savePasswordResetToken($user['id'], $token);

                    // Формируем ссылку для восстановления пароля
                    $resetUrl = BASE_URL . 'auth/reset-password?token=' . $token;

                    // Отправляем email с ссылкой для восстановления пароля
                    $emailService = new EmailService();
                    $emailService->sendPasswordResetEmail($email, $resetUrl);

                    // Для безопасности не сообщаем, если пользователь не найден
                    $success = "Если пользователь с таким email существует, на него будет отправлена ссылка для восстановления пароля. Проверьте вашу почту (в том числе папку 'Спам').";
                } else {
                    // Для безопасности не сообщаем, что пользователь не найден
                    $success = "Если пользователь с таким email существует, на него будет отправлена ссылка для восстановления пароля. Проверьте вашу почту (в том числе папку 'Спам').";
                }
            }
        }

        View::render('auth/forgot_password', [
            'error' => $error,
            'success' => $success,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Показывает форму сброса пароля (ввод нового пароля)
     */
    public function resetPassword()
    {
        // Если уже авторизован, перенаправляем в профиль
        if (Helper::isLoggedIn()) {
            Helper::redirect('profile');
            return;
        }

        $error = null;
        $token = $_GET['token'] ?? $_POST['token'] ?? '';

        if (empty($token)) {
            $error = "Неверная ссылка восстановления пароля";
            View::render('auth/reset_password', [
                'error' => $error,
                'token' => '',
                'isMobile' => View::isMobile()
            ]);
            return;
        }

        // Если форма отправлена
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Валидация
            if (empty($password) || empty($confirmPassword)) {
                $error = "Заполните все поля";
            } elseif ($password !== $confirmPassword) {
                $error = "Пароли не совпадают";
            } elseif (strlen($password) < 6) {
                $error = "Пароль должен быть не менее 6 символов";
            } else {
                // Находим пользователя по токену
                $user = $this->userModel->findByPasswordResetToken($token);

                if ($user) {
                    // Обновляем пароль
                    if ($this->userModel->updatePassword($user['id'], $password)) {
                        Helper::redirect('auth/login?password_reset=success');
                        return;
                    } else {
                        $error = "Ошибка при обновлении пароля";
                    }
                } else {
                    $error = "Неверный или истекший токен восстановления пароля";
                }
            }
        } else {
            // Проверяем валидность токена при загрузке страницы
            $user = $this->userModel->findByPasswordResetToken($token);
            if (!$user) {
                $error = "Неверный или истекший токен восстановления пароля";
            }
        }

        View::render('auth/reset_password', [
            'error' => $error,
            'token' => $token,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Выход пользователя
     */
    public function logout()
    {
        // Сохраняем сообщение перед выходом
        $_SESSION['logout_message'] = 'Мы рады вас еще раз видеть!';

        // Очищаем remember token если есть
        if (Helper::isLoggedIn()) {
            $userId = Helper::getUserId();
            $this->userModel->clearRememberToken($userId);
        }

        // Удаляем cookie
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

        Helper::redirect('home');
    }
}
