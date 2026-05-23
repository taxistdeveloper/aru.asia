<?php

/**
 * СЕРВИС ОТПРАВКИ EMAIL
 *
 * Отправляет email письма пользователям
 * Поддерживает отправку через SMTP (Gmail) или PHP mail() функцию
 */

class EmailService
{
    private $fromEmail;
    private $fromName;
    private $smtpEnabled;
    private $smtpHost;
    private $smtpPort;
    private $smtpSecure;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpAuth;

    public function __construct()
    {
        $this->fromEmail = defined('MAIL_FROM') ? MAIL_FROM : 'shotaev96@gmail.com';
        $this->fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Aru';

        // Настройки SMTP
        $this->smtpEnabled = defined('SMTP_ENABLED') ? SMTP_ENABLED : false;
        $this->smtpHost = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $this->smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->smtpSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
        $this->smtpUsername = defined('SMTP_USERNAME') ? trim(SMTP_USERNAME) : '';
        // Убираем пробелы из пароля (Gmail App Password может содержать пробелы)
        $this->smtpPassword = defined('SMTP_PASSWORD') ? str_replace(' ', '', trim(SMTP_PASSWORD)) : '';
        $this->smtpAuth = defined('SMTP_AUTH') ? SMTP_AUTH : true;
        
        // Логируем настройки (без пароля)
        error_log("EmailService initialized: SMTP_ENABLED=" . ($this->smtpEnabled ? 'true' : 'false') . 
                  ", HOST={$this->smtpHost}, PORT={$this->smtpPort}, SECURE={$this->smtpSecure}" .
                  ", USERNAME={$this->smtpUsername}, PASSWORD_SET=" . (!empty($this->smtpPassword) ? 'yes' : 'no'));
    }

    /**
     * Отправляет email письмо
     *
     * @param string $to Email получателя
     * @param string $subject Тема письма
     * @param string $body Тело письма (HTML)
     * @param string $altBody Альтернативный текст (plain text)
     * @return bool Успешность отправки
     */
    public function send($to, $subject, $body, $altBody = '')
    {
        // Валидация email
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("EmailService: Invalid email address: $to");
            return false;
        }

        // Если нет альтернативного текста, создаем простой текст из HTML
        if (empty($altBody)) {
            $altBody = strip_tags($body);
            $altBody = html_entity_decode($altBody, ENT_QUOTES, 'UTF-8');
        }

        // Используем SMTP если включен и настроен
        if ($this->smtpEnabled && !empty($this->smtpUsername) && !empty($this->smtpPassword)) {
            $result = $this->sendViaSMTP($to, $subject, $body, $altBody);
            // Если SMTP не сработал, пробуем через mail() как fallback
            if (!$result) {
                error_log("EmailService: SMTP failed, trying mail() as fallback");
                return $this->sendViaMail($to, $subject, $body, $altBody);
            }
            return $result;
        } else {
            // Используем стандартную функцию mail()
            return $this->sendViaMail($to, $subject, $body, $altBody);
        }
    }

    /**
     * Отправка через SMTP
     */
    private function sendViaSMTP($to, $subject, $body, $altBody)
    {
        try {
            // Создаем подключение к SMTP серверу
            $socket = $this->connectSMTP();
            if (!$socket) {
                error_log("EmailService: SMTP connection failed for $to");
                return false;
            }

            // Авторизация
            if ($this->smtpAuth && !$this->authenticateSMTP($socket)) {
                error_log("EmailService: SMTP authentication failed for $to");
                fclose($socket);
                return false;
            }

            // Отправка письма
            $result = $this->sendEmailSMTP($socket, $to, $subject, $body, $altBody);

            // Закрываем соединение
            try {
                $this->sendCommand($socket, 'QUIT');
            } catch (Exception $e) {
                // Игнорируем ошибки при закрытии
            }
            fclose($socket);

            if ($result) {
                error_log("EmailService: Email sent successfully via SMTP to $to");
            } else {
                error_log("EmailService: Failed to send email via SMTP to $to");
            }

            return $result;
        } catch (Exception $e) {
            error_log("EmailService SMTP Error: " . $e->getMessage());
            error_log("EmailService SMTP Error Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Подключение к SMTP серверу
     */
    private function connectSMTP()
    {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $host = $this->smtpHost;
        $port = $this->smtpPort;

        // Для SSL используем ssl://, для TLS используем обычное подключение
        if ($this->smtpSecure === 'ssl') {
            $host = 'ssl://' . $host;
        }

        $socket = @stream_socket_client(
            "$host:$port",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            error_log("EmailService: Failed to connect to SMTP server: $errstr ($errno)");
            return false;
        }

        // Читаем приветствие сервера
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '220') {
            error_log("EmailService: SMTP server greeting failed: $response");
            fclose($socket);
            return false;
        }

        // Получаем домен для EHLO (извлекаем из email или используем localhost)
        $ehloDomain = 'localhost';
        if (!empty($this->fromEmail) && strpos($this->fromEmail, '@') !== false) {
            $parts = explode('@', $this->fromEmail);
            $ehloDomain = $parts[1];
        }

        // Отправляем EHLO (до TLS)
        $response = $this->sendCommand($socket, 'EHLO ' . $ehloDomain);
        if (substr($response, 0, 3) !== '250') {
            error_log("EmailService: EHLO failed: $response");
            // Пробуем HELO как fallback
            $response = $this->sendCommand($socket, 'HELO ' . $ehloDomain);
            if (substr($response, 0, 3) !== '250') {
                error_log("EmailService: HELO also failed: $response");
                fclose($socket);
                return false;
            }
        }

        // Если нужен TLS, включаем его
        if ($this->smtpSecure === 'tls') {
            $response = $this->sendCommand($socket, 'STARTTLS');
            if (substr($response, 0, 3) !== '220') {
                error_log("EmailService: STARTTLS command failed: $response");
                fclose($socket);
                return false;
            }
            
            // Включаем TLS
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            
            if (!@stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                error_log("EmailService: Failed to enable TLS encryption");
                fclose($socket);
                return false;
            }
            
            // После TLS отправляем EHLO снова
            $response = $this->sendCommand($socket, 'EHLO ' . $ehloDomain);
            if (substr($response, 0, 3) !== '250') {
                error_log("EmailService: EHLO after TLS failed: $response");
                fclose($socket);
                return false;
            }
        }

        return $socket;
    }

    /**
     * Авторизация на SMTP сервере
     */
    private function authenticateSMTP($socket)
    {
        // Отправляем AUTH LOGIN
        $response = $this->sendCommand($socket, 'AUTH LOGIN');
        $code = substr($response, 0, 3);
        if ($code !== '334') {
            error_log("EmailService: AUTH LOGIN failed. Code: $code, Response: $response");
            return false;
        }

        // Отправляем username (base64)
        $response = $this->sendCommand($socket, base64_encode($this->smtpUsername));
        $code = substr($response, 0, 3);
        if ($code !== '334') {
            error_log("EmailService: Username authentication failed. Code: $code, Response: $response");
            error_log("EmailService: Username: " . $this->smtpUsername);
            return false;
        }

        // Отправляем password (base64)
        $response = $this->sendCommand($socket, base64_encode($this->smtpPassword));
        $code = substr($response, 0, 3);
        if ($code !== '235') {
            error_log("EmailService: Password authentication failed. Code: $code, Response: $response");
            error_log("EmailService: Password length: " . strlen($this->smtpPassword));
            // Не логируем сам пароль из соображений безопасности
            return false;
        }

        error_log("EmailService: SMTP authentication successful");
        return true;
    }

    /**
     * Отправка команды SMTP
     */
    private function sendCommand($socket, $command)
    {
        if (!$socket) {
            error_log("EmailService: Invalid socket resource");
            return '';
        }
        
        $result = @fputs($socket, $command . "\r\n");
        if ($result === false) {
            error_log("EmailService: Failed to send command: $command");
            return '';
        }
        
        $response = '';
        $timeout = 10; // таймаут в секундах
        $startTime = time();
        
        while (time() - $startTime < $timeout) {
            $str = @fgets($socket, 515);
            if ($str === false) {
                break;
            }
            $response .= $str;
            if (strlen($str) >= 4 && substr($str, 3, 1) === ' ') {
                break;
            }
        }
        
        return $response;
    }

    /**
     * Отправка письма через SMTP
     */
    private function sendEmailSMTP($socket, $to, $subject, $body, $altBody)
    {
        // MAIL FROM
        $response = $this->sendCommand($socket, 'MAIL FROM: <' . $this->fromEmail . '>');
        if (substr($response, 0, 3) !== '250') {
            error_log("EmailService: MAIL FROM failed: $response");
            return false;
        }

        // RCPT TO
        $response = $this->sendCommand($socket, 'RCPT TO: <' . $to . '>');
        if (substr($response, 0, 3) !== '250') {
            error_log("EmailService: RCPT TO failed: $response");
            return false;
        }

        // DATA
        $response = $this->sendCommand($socket, 'DATA');
        if (substr($response, 0, 3) !== '354') {
            error_log("EmailService: DATA failed: $response");
            return false;
        }

        // Формируем письмо
        $message = $this->buildEmailMessage($to, $subject, $body, $altBody);

        // Отправляем тело письма
        fputs($socket, $message . "\r\n.\r\n");

        $response = $this->sendCommand($socket, '');
        if (substr($response, 0, 3) !== '250') {
            error_log("EmailService: Email sending failed: $response");
            return false;
        }

        return true;
    }

    /**
     * Формирует полное сообщение email
     */
    private function buildEmailMessage($to, $subject, $body, $altBody)
    {
        $boundary = uniqid('boundary_');

        $message = "From: " . $this->encodeHeader($this->fromName) . " <{$this->fromEmail}>\r\n";
        $message .= "To: <$to>\r\n";
        $message .= "Subject: " . $this->encodeHeader($subject) . "\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        $message .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $message .= "\r\n";

        // Plain text часть
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($altBody)) . "\r\n";

        // HTML часть
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($body)) . "\r\n";

        $message .= "--$boundary--\r\n";

        return $message;
    }

    /**
     * Отправка через стандартную функцию mail()
     */
    private function sendViaMail($to, $subject, $body, $altBody)
    {
        $boundary = uniqid('boundary_');
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: multipart/alternative; boundary=\"$boundary\"";
        $headers[] = "From: " . $this->encodeHeader($this->fromName) . " <{$this->fromEmail}>";
        $headers[] = "Reply-To: {$this->fromEmail}";
        $headers[] = "X-Mailer: PHP/" . phpversion();

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($altBody)) . "\r\n";
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($body)) . "\r\n";
        $message .= "--$boundary--";

        $result = @mail($to, $encodedSubject, $message, implode("\r\n", $headers));

        if (!$result) {
            $lastError = error_get_last();
            $errorMsg = $lastError ? $lastError['message'] : 'Unknown error';
            error_log("EmailService: Failed to send email via mail() to $to. Error: $errorMsg");
        } else {
            error_log("EmailService: Email sent successfully via mail() to $to");
        }

        return $result;
    }

    /**
     * Отправляет письмо для подтверждения email при регистрации
     *
     * @param string $to Email получателя
     * @param string $verifyUrl Ссылка для подтверждения
     * @return bool Успешность отправки
     */
    public function sendVerificationEmail($to, $verifyUrl)
    {
        $subject = 'Подтвердите ваш email - ' . $this->fromName;

        $body = $this->getVerificationEmailTemplate($verifyUrl);
        $altBody = "Для подтверждения вашего email перейдите по ссылке: $verifyUrl";

        return $this->send($to, $subject, $body, $altBody);
    }

    /**
     * Отправляет письмо для восстановления пароля
     *
     * @param string $to Email получателя
     * @param string $resetUrl Ссылка для восстановления пароля
     * @return bool Успешность отправки
     */
    public function sendPasswordResetEmail($to, $resetUrl)
    {
        $subject = 'Восстановление пароля - ' . $this->fromName;

        $body = $this->getPasswordResetEmailTemplate($resetUrl);
        $altBody = "Для восстановления пароля перейдите по ссылке: $resetUrl\r\n\r\nЕсли вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.";

        return $this->send($to, $subject, $body, $altBody);
    }

    /**
     * Шаблон письма для подтверждения email
     */
    private function getVerificationEmailTemplate($verifyUrl)
    {
        $appName = $this->fromName;
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f4f4f4;
                }
                .container {
                    background-color: #ffffff;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .header h1 {
                    color: #007bff;
                    margin: 0;
                }
                .content {
                    margin-bottom: 30px;
                }
                .button {
                    display: inline-block;
                    padding: 12px 30px;
                    background-color: #007bff;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .button:hover {
                    background-color: #0056b3;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 12px;
                    color: #666;
                }
                .link {
                    word-break: break-all;
                    color: #007bff;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>$appName</h1>
                </div>
                <div class='content'>
                    <h2>Добро пожаловать!</h2>
                    <p>Спасибо за регистрацию в $appName. Для завершения регистрации необходимо подтвердить ваш email адрес.</p>
                    <p style='text-align: center;'>
                        <a href='$verifyUrl' class='button'>Подтвердить email</a>
                    </p>
                    <p>Если кнопка не работает, скопируйте и вставьте следующую ссылку в браузер:</p>
                    <p class='link'>$verifyUrl</p>
                    <p><small>Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.</small></p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " $appName. Все права защищены.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Шаблон письма для восстановления пароля
     */
    private function getPasswordResetEmailTemplate($resetUrl)
    {
        $appName = $this->fromName;
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f4f4f4;
                }
                .container {
                    background-color: #ffffff;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .header h1 {
                    color: #007bff;
                    margin: 0;
                }
                .content {
                    margin-bottom: 30px;
                }
                .button {
                    display: inline-block;
                    padding: 12px 30px;
                    background-color: #007bff;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .button:hover {
                    background-color: #0056b3;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 12px;
                    color: #666;
                }
                .link {
                    word-break: break-all;
                    color: #007bff;
                }
                .warning {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>$appName</h1>
                </div>
                <div class='content'>
                    <h2>Восстановление пароля</h2>
                    <p>Вы запросили восстановление пароля для вашего аккаунта.</p>
                    <p style='text-align: center;'>
                        <a href='$resetUrl' class='button'>Восстановить пароль</a>
                    </p>
                    <p>Если кнопка не работает, скопируйте и вставьте следующую ссылку в браузер:</p>
                    <p class='link'>$resetUrl</p>
                    <div class='warning'>
                        <p><strong>Важно:</strong> Ссылка действительна в течение 1 часа. Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.</p>
                    </div>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " $appName. Все права защищены.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Кодирует заголовок для правильной обработки кириллицы
     */
    private function encodeHeader($text)
    {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }
}
