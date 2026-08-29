<?php
/**
 * Страница 500 — необработанное исключение уже записано в журнал.
 */
$homeUrl = defined('BASE_URL') ? BASE_URL : '/';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка | Aru</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(180deg, #fff 0%, #f8f9fa 50%, #fff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1a1a1a;
        }
        .box { text-align: center; max-width: 480px; }
        .code {
            font-size: 64px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 16px;
        }
        h1 { font-size: 24px; font-weight: 700; margin-bottom: 12px; }
        p { font-size: 17px; color: #4a5568; line-height: 1.6; margin-bottom: 24px; }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="code">500</div>
        <h1>Что-то пошло не так</h1>
        <p>Ошибка записана в журнал. Попробуйте позже.</p>
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>">На главную</a>
    </div>
</body>
</html>
